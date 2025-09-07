<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\OperationType;
use App\Models\Partner;
use Illuminate\Http\Request;
use App\Models\User;
use Dompdf\Dompdf;
use Dompdf\Options;
use Carbon\Carbon;

class InvoiceController extends Controller
{
    protected function nextCodeWithPrefix(string $prefix = 'INV'): string
    {
        $prefix = strtoupper(trim($prefix ?: 'INV'));
        $prefix = preg_replace('/[^A-Z0-9]/', '', $prefix);
        if ($prefix === '') $prefix = 'INV';

        $like = $prefix . '-%';
        $startPos = strlen($prefix) + 2; // +1 for dash and SUBSTRING 1-based
        $maxNum = (int) (Invoice::where('code', 'like', $like)
            ->selectRaw("MAX(CAST(SUBSTRING(code, {$startPos}) AS UNSIGNED)) as m")
            ->value('m') ?? 0);

        $next = max($maxNum, 4999) + 1;
        return $prefix . '-' . $next;
    }
    protected function nextCode(): string
    {
        // Cherche le plus grand numÃ©ro aprÃ¨s le prÃ©fixe INV-
        $maxNum = (int) (Invoice::where('code', 'like', 'INV-%')
            ->selectRaw("MAX(CAST(SUBSTRING(code, 5) AS UNSIGNED)) as m")
            ->value('m') ?? 0);

        // DÃ©marre Ã  5000 si aucune facture n'existe encore
        $next = max($maxNum, 4999) + 1;
        return 'INV-' . $next;
    }

    public function fetch(Request $request, $id)
    {
        $inv = Invoice::with('partner.user', 'operationType')->findOrFail($id);
        // Ajouter l'Ã©metteur (utilisateur qui a crÃ©Ã© la facture)
        $issuer = $inv->created_by ? \App\Models\User::find($inv->created_by) : null;
        $arr = $inv->toArray();
        $arr['issuer'] = $issuer ? [
            'id' => $issuer->id,
            'first_name' => $issuer->first_name,
            'last_name' => $issuer->last_name,
            'email' => $issuer->email,
        ] : null;
        return response()->json($arr);
    }

    public function list(Request $request)
    {
        $q = Invoice::query()->with('partner.user', 'operationType');
        if ($status = $request->input('status')) {
            $q->where('status', $status);
        }
        if ($opType = $request->input('operation_type_id')) {
            $q->where('operation_type_id', $opType);
        }
        return response()->json($q->orderByDesc('id')->paginate($request->input('length', 10)));
    }

    public function store(Request $request, $opTypeCode)
    {
        $opType = OperationType::where('code', $opTypeCode)->firstOrFail();

        $payload = $request->validate([
            'client_type'   => 'required|in:partner,external',
            'partner_id'    => 'nullable|exists:partners,id',
            'client_name'   => 'nullable|string|max:191',
            'client_phone'  => 'nullable|string|max:50',
            'client_email'  => 'nullable|email|max:191',
            'items'         => 'nullable|array',
            'items.*.label' => 'required_with:items|string|max:191',
            'items.*.qty'   => 'required_with:items|numeric|min:1',
            'items.*.unit'  => 'required_with:items|numeric|min:0',
            'amount'        => 'nullable|numeric|min:0',
            'currency'      => 'nullable|string|max:8',
            'prefix'        => 'nullable|in:INV,SOUNV',
        ]);

        $clientType = $payload['client_type'];
        $partner = null;
        $clientName = $payload['client_name'] ?? null;
        $clientPhone = $payload['client_phone'] ?? null;
        $clientEmail = $payload['client_email'] ?? null;

        if ($clientType === 'partner') {
            $partner = Partner::with('user')->findOrFail($payload['partner_id'] ?? 0);
            $clientName = trim(($partner->user->first_name ?? '') . ' ' . ($partner->user->last_name ?? ''));
            $clientPhone = $partner->user->phone_number ?? $clientPhone;
            $clientEmail = $partner->user->email ?? $clientEmail;
        }

        // Compute total from items or amount
        $total = 0;
        if (!empty($payload['items'])) {
            foreach ($payload['items'] as $it) {
                $total += (int) round(($it['qty'] ?? 0) * ($it['unit'] ?? 0));
            }
        } else {
            $total = (int) round($payload['amount'] ?? 0);
        }

        $prefix = $payload['prefix'] ?? 'SOUNV';

        $inv = Invoice::create([
            'code'               => $this->nextCodeWithPrefix($prefix),
            'operation_type_id'  => $opType->id,
            'client_type'        => $clientType,
            'partner_id'         => $partner?->id,
            'client_name'        => $clientName,
            'client_phone'       => $clientPhone,
            'client_email'       => $clientEmail,
            'items'              => $payload['items'] ?? null,
            'total_amount'       => $total,
            'currency'           => $payload['currency'] ?? 'FCFA',
            'status'             => 'unpaid',
            'created_by'         => $request->user()->id,
            'updated_by'         => $request->user()->id,
        ]);

        return response()->json(['message' => 'Facture crÃ©Ã©e', 'invoice' => $inv]);
    }

    public function update(Request $request, $id)
    {
        $inv = Invoice::findOrFail($id);
        if ($inv->status === 'paid') {
            return response()->json(['message' => 'Impossible de modifier une facture payÃ©e'], 422);
        }

        $payload = $request->validate([
            'client_name'   => 'nullable|string|max:191',
            'client_phone'  => 'nullable|string|max:50',
            'client_email'  => 'nullable|email|max:191',
            'items'         => 'nullable|array',
            'items.*.label' => 'required_with:items|string|max:191',
            'items.*.qty'   => 'required_with:items|numeric|min:1',
            'items.*.unit'  => 'required_with:items|numeric|min:0',
            'amount'        => 'nullable|numeric|min:0',
            'currency'      => 'nullable|string|max:8',
        ]);

        // Recompute total
        $total = $inv->total_amount;
        if (!empty($payload['items'])) {
            $total = 0;
            foreach ($payload['items'] as $it) {
                $total += (int) round(($it['qty'] ?? 0) * ($it['unit'] ?? 0));
            }
        } elseif (array_key_exists('amount', $payload)) {
            $total = (int) round($payload['amount'] ?? 0);
        }

        $inv->fill([
            'client_name'  => $payload['client_name'] ?? $inv->client_name,
            'client_phone' => $payload['client_phone'] ?? $inv->client_phone,
            'client_email' => $payload['client_email'] ?? $inv->client_email,
            'items'        => $payload['items'] ?? $inv->items,
            'total_amount' => $total,
            'currency'     => $payload['currency'] ?? $inv->currency,
            'updated_by'   => $request->user()->id,
        ])->save();

        return response()->json(['message' => 'Facture mise Ã  jour', 'invoice' => $inv]);
    }

    public function markPaid(Request $request, $id)
    {
        $inv = Invoice::findOrFail($id);
        if ($inv->status === 'paid') {
            return response()->json(['message' => 'DÃ©jÃ  payÃ©e'], 422);
        }
        $inv->status = 'paid';
        $inv->updated_by = $request->user()->id;
        $inv->save();
        return response()->json(['message' => 'Facture validÃ©e (payÃ©e)', 'invoice' => $inv]);
    }

    public function exportCsv(Request $request, $id)
    {
        $inv = Invoice::with('partner.user', 'operationType')->findOrFail($id);
        $issuer = $inv->created_by ? optional(User::find($inv->created_by)) : null;
        $nowBenin = Carbon::now('Africa/Porto-Novo');
        $rows = [];
        $rows[] = ['Entreprise', 'AHOTANTI'];
        $rows[] = ['Code', $inv->code];
        $rows[] = ["Type d'opÃ©ration", optional($inv->operationType)->name];
        $rows[] = ['Date', $nowBenin->format('d/m/Y')];
        $rows[] = ['Heure', $nowBenin->format('H:i')];
        $client = $inv->client_type === 'partner'
            ? trim((($inv->partner?->user?->first_name) ?? '') . ' ' . (($inv->partner?->user?->last_name) ?? ''))
            : ($inv->client_name ?? '');
        $rows[] = ['Client', $client];
        $rows[] = ['TÃ©lÃ©phone', $inv->client_phone ?? ($inv->partner?->user?->phone_number)];
        $rows[] = ['Email', $inv->client_email ?? ($inv->partner?->user?->email)];
        $rows[] = ['Ã‰mis par', trim(($issuer->first_name ?? '') . ' ' . ($issuer->last_name ?? ''))];
        $rows[] = [];
        $rows[] = ['DÃ©signation', 'QtÃ©', 'PU', 'Total'];
        foreach (($inv->items ?? []) as $it) {
            $rows[] = [
                $it['label'] ?? '',
                $it['qty'] ?? 0,
                $it['unit'] ?? 0,
                (int) round(($it['qty'] ?? 0) * ($it['unit'] ?? 0))
            ];
        }
        $rows[] = [];
        $rows[] = ['Montant total', $inv->total_amount, $inv->currency];

        $out = fopen('php://temp', 'r+');
        foreach ($rows as $r) fputcsv($out, $r, ';');
        rewind($out);
        $csv = stream_get_contents($out);
        fclose($out);

        $filename = $inv->code . '.csv';
        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\""
        ]);
    }

    public function exportPdf(Request $request, $id)
    {
        $inv = Invoice::with('partner.user', 'operationType')->findOrFail($id);
        $issuer = $inv->created_by ? optional(User::find($inv->created_by)) : null;
        $nowBenin = Carbon::now('Africa/Porto-Novo');
        $client = $inv->client_type === 'partner'
            ? trim((($inv->partner?->user?->first_name) ?? '') . ' ' . (($inv->partner?->user?->last_name) ?? ''))
            : ($inv->client_name ?? '');
        $itemsRows = '';
        foreach (($inv->items ?? []) as $it) {
            $rowTotal = (int) round(($it['qty'] ?? 0) * ($it['unit'] ?? 0));
            $itemsRows .= '<tr><td>'.e($it['label'] ?? '').'</td><td>'.($it['qty'] ?? 0).'</td><td>'.($it['unit'] ?? 0).'</td><td>'.$rowTotal.'</td></tr>';
        }
        if ($itemsRows === '') {
            $itemsRows = '<tr><td colspan="4" style="text-align:center">Aucune ligne</td></tr>';
        }

        $html = '<html><head><meta charset="UTF-8"><style>body{margin:0} table{width:100%;border-collapse:collapse} th,td{border:1px solid #ddd;padding:6px} h2{margin:0 0 8px 0}</style></head><body>';

        // Force l'utilisation de logo.jpg pour les factures
        $logoData = null;
        $logoPath = public_path('assets/images/logo/logo.jpg');
        if (is_file($logoPath)) {
            $logoData = 'data:image/jpeg;base64,' . base64_encode(file_get_contents($logoPath));
        }
        if ($logoData) {
            $html .= '<div style="text-align:center;margin:0 0 16px 0"><img src="'.$logoData.'" style="max-height:180px;height:auto;width:auto;display:block;margin:0 auto;"></div>';
        } else {
            $html .= '<h2>AHOTANTI</h2>';
        }
        $html .= '<p><strong>Facture:</strong> '.e($inv->code).'</p>';
        $html .= '<p><strong>Date:</strong> '.$nowBenin->format('d/m/Y').'<br>';
        $html .= '<strong>Heure:</strong> '.$nowBenin->format('H:i').'</p>';
        $html .= '<p><strong>Type d\'opÃ©ration:</strong> '.e(optional($inv->operationType)->name).'<br>';
        $html .= '<strong>Client:</strong> '.e($client).'<br>';
        $html .= '<strong>TÃ©lÃ©phone:</strong> '.e($inv->client_phone ?? ($inv->partner?->user?->phone_number)).'<br>';
        $html .= '<strong>Email:</strong> '.e($inv->client_email ?? ($inv->partner?->user?->email)).'<br>';
        $html .= '<strong>Ã‰mis par:</strong> '.e(trim(($issuer->first_name ?? '') . ' ' . ($issuer->last_name ?? ''))).'</p>';
        $html .= '<table><thead><tr><th>DÃ©signation</th><th>QtÃ©</th><th>PU</th><th>Total</th></tr></thead><tbody>'.$itemsRows.'</tbody></table>';
        $html .= '<p style="text-align:right"><strong>Montant:</strong> '.$inv->total_amount.' '.$inv->currency.'</p>';
        $html .= '</body></html>';

        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        return response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$inv->code.'.pdf"'
        ]);
    }
}

