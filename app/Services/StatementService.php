<?php

namespace App\Services;

use App\Models\Statement;
use Carbon\Carbon;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use stdClass;

class StatementService
{
    public function createOperationStatement($operation, $master, $isReversal = false)
    {
        $opType = $operation->operationType;
        $data = $operation->data;

        if ($opType->code === 'account_recharge') {
            $amount = $data->trans_amount;
            $isDebit = false;
        } else if ($opType->code === 'balance_withdrawal') {
            $amount = $data->amount * 1.02;
            $isDebit = true;
        } else {
            $amount = $operation->amount + $operation->fee;
            $isDebit = true;
        }

        $amount *= ($isDebit ^ $isReversal) ? -1 : 1;
        $balance = $isReversal ? $master->balance + $amount : $master->balance;
        $type = ($isReversal ? "Annulation de l'opération " : '') . $opType->name .
            (($data->card_type ?? '') ? ' (' . $data->card_type . ')' : '');

        Statement::create([
            'partner_id' => $master->id,
            'type' => $type,
            'amount' => $amount,
            'balance' => $balance
        ]);
    }

    public function createWithdrawalStatement($obj)
    {
        Statement::create([
            'partner_id' => $obj->partner_id,
            'type' => 'Retrait de commissions',
            'amount' => $obj->amount,
            'balance' => $obj->partner->balance + $obj->amount
        ]);
    }

    public function createMoneyTransferStatement($obj)
    {
        Statement::create([
            'partner_id' => $obj->sender_id,
            'type' => 'Transfert envoyé à ' . $obj->recipient->user->full_name,
            'amount' => -$obj->amount,
            'balance' => $obj->sender->balance - $obj->amount
        ]);

        Statement::create([
            'partner_id' => $obj->recipient_id,
            'type' => 'Transfert reçu de ' . $obj->sender->user->full_name,
            'amount' => $obj->amount,
            'balance' => $obj->recipient->balance + $obj->amount
        ]);
    }

    public function createBalanceAdjustmentStatement($obj)
    {
        Statement::create([
            'partner_id' => $obj->partner_id,
            'type' => 'Ajustement de solde',
            'amount' => $obj->balance - $obj->old_balance,
            'balance' => $obj->balance
        ]);
    }

    private function getStatementListQuery($request)
    {
        $request->validate([
            'from_date' => 'nullable|date',
            'to_date' => 'nullable|date',
        ]);

        if ($request->to_date) {
            $request->merge(['to_date' => Carbon::parse($request->to_date)->addDay()->format('Y-m-d')]);
        }

        $query = DB::table('statements')
            ->join('partners', 'partner_id', 'partners.id')
            ->join('users', 'user_id', 'users.id')
            ->where('partner_id', $request->user()->partner->id)
            ->when($request->from_date, function ($q, $fromDate) {
                $q->where('statements.created_at', '>=', $fromDate);
            })
            ->when($request->to_date, function ($q, $toDate) {
                $q->where('statements.created_at', '<', $toDate);
            })
            ->selectRaw("
                statements.id,
                CONCAT(first_name, ' ', last_name) AS partner,
                type,
                amount,
                statements.balance,
                statements.created_at
            ");

        return $query;
    }

    public function listStatement($request)
    {
        $params = new stdClass;
        $subQuery = $this->getStatementListQuery($request);
        $params->builder = DB::query()->fromSub($subQuery, 'sub');

        return fetchListData($request, $params);
    }

    public function getExcelStatement($request)
    {
        $rows = $this->getStatementListQuery($request)
            ->orderBy('statements.id')
            ->lazy(10000);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Add header row
        $rowNum = 1;
        $colNum = 1;

        foreach (['Date', 'Partenaire', 'Opération', 'Montant', 'Solde'] as $header) {
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($colNum) . '1', $header);
            $colNum++;
        }

        // Add data rows
        $rowNum = 2;
        $colNum = 1;

        foreach ($rows as $row) {
            $sheet->setCellValue('A' . $rowNum, $row->created_at);
            $sheet->setCellValue('B' . $rowNum, $row->partner);
            $sheet->setCellValue('C' . $rowNum, $row->type);
            $sheet->setCellValue('D' . $rowNum, $row->amount);
            $sheet->setCellValue('E' . $rowNum, $row->balance);
            $rowNum++;
        }

        return new Xlsx($spreadsheet);
    }

    public function getPdfStatement($request)
    {
        $rows = $this->getStatementListQuery($request)
            ->orderBy('statements.id')
            ->lazy(10000);

        $options = new Options();
        $options->set('defaultFont', 'sans-serif');

        $pdf = new Dompdf($options);

        $html = view('partners.statement-pdf', [
            'partner' => $request->user()->partner,
            'fromDate' => $request->from_date,
            'toDate' => $request->to_date ? Carbon::parse($request->to_date)->subDay()->format('Y-m-d') : '',
            'rows' => $rows,
        ])->render();

        $pdf->loadHtml($html);

        $pdf->setPaper('A4', 'landscape');

        $pdf->render();

        return $pdf;
    }
}
