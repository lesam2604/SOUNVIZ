<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\History;
use App\Models\Notification;
use App\Models\Ticket;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use stdClass;

class TicketController extends Controller
{
    public function fetch(Request $request, $id)
    {
        $obj = Ticket::with(['partner', 'responder'])->findOrFail($id);

        $user = $request->user();

        if ($user->hasRole('partner') && $user->id !== $obj->partner->user_id) {
            return response()->json(['message' => 'Non autorisé'], 405);
        }

        return response()->json($obj);
    }

    public function store(Request $request)
    {
        $partner = $request->user()->partner;

        $data = $request->validate([
            'issue' => 'required|string|max:5000',
        ], [
            '*.required' => 'Ce champs est requis',
            '*.string' => 'Ce champs doit être une chaîne de caractères',
            'issue.max' => 'La longueur maximale pour ce champs est de 5000 caractères',
        ]);

        DB::beginTransaction();

        try {
            $obj = Ticket::create([
                'code' => generateUniqueCode('tickets', 'code', 'DMI'),
                'issue' => $data['issue'],
                'partner_id' => $partner->id
            ]);

            History::create([
                'user_id' => $partner->user_id,
                'title' => "Assistance service {$obj->code} effectuée.",
                'content' => "Vous avez effectué un nouvelle assistance service {$obj->code}."
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 500);
        }

        return response()->json(['message' => "Assistance service {$obj->code} effectuée."]);
    }

    public function update(Request $request, $id)
    {
        $obj = Ticket::with(['partner', 'responder'])->findOrFail($id);

        if ($obj->response !== null) {
            return response()->json(['message' => 'Non autorisé'], 405);
        }

        $data = $request->validate([
            'issue' => 'required|string|max:5000',
        ], [
            '*.required' => 'Ce champs est requis',
            '*.string' => 'Ce champs doit être une chaîne de caractères',
            'issue.max' => 'La longueur maximale pour ce champs est de 5000 caractères',
        ]);

        $partner = $request->user()->partner;

        DB::beginTransaction();

        try {
            $obj->update(['issue' => $data['issue']]);

            History::create([
                'user_id' => $partner->user_id,
                'title' => "Mise a jour de l'assistance service {$obj->code}.",
                'content' => "Vous avez mis a jour l'assistance service {$obj->code}."
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 500);
        }

        return response()->json(['message' => "Assistance service {$obj->code} mise a jour."]);
    }

    public function destroy(Request $request, $id)
    {
        $obj = Ticket::with(['partner', 'responder'])->findOrFail($id);

        if ($obj->response !== null) {
            return response()->json(['message' => 'Non autorisé'], 405);
        }

        $partner = $request->user()->partner;

        DB::beginTransaction();

        try {
            $obj->delete();

            History::create([
                'user_id' => $partner->user_id,
                'title' => "Annulation de l'assistance service {$obj->code}.",
                'content' => "Vous avez annule l'assistance service {$obj->code}."
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 500);
        }

        return response()->json(['message' => "Assistance service {$obj->code} annulée."]);
    }

    public function respond(Request $request, $id)
    {
        $obj = Ticket::with(['partner', 'responder'])->findOrFail($id);

        if ($obj->response !== null) {
            return response()->json(['message' => 'Non autorisé'], 405);
        }

        $data = $request->validate([
            'response' => 'required|string|max:5000',
        ], [
            '*.required' => 'Ce champs est requis',
            '*.string' => 'Ce champs doit être une chaîne de caractères',
            'response.max' => 'La longueur maximale pour ce champs est de 5000 caractères',
        ]);

        $responder = $request->user();

        DB::beginTransaction();

        try {
            $obj->update([
                'response' => $data['response'],
                'responder_id' => $responder->id,
                'responded_at' => Carbon::now()
            ]);

            History::create([
                'user_id' => $responder->id,
                'title' => "Réponse de l'assistance service {$obj->code}",
                'content' => "Vous avez répondu a l'assistance service {$obj->code} initiée par le partenaire {$obj->partner->user->full_name}"
            ]);

            Notification::create([
                'recipient_id' => $obj->partner->user_id,
                'subject' => "Assistance service {$obj->code} répondue",
                'body' => "L'assistance service {$obj->code} a été répondue par {$responder->full_name}",
                'icon_class' => 'fas fa-thumbs-up',
                'link' => config('app.app_baseurl') . "/tickets/{$obj->id}"
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 500);
        }

        return response()->json(['message' => "Assistance service {$obj->code} répondue"]);
    }

    public function list(Request $request)
    {
        $request->validate([
            'status' => 'nullable|string|in:responded,not-responded',
            'partner_id' => 'nullable|numeric|exists:partners,id'
        ]);

        $params = new stdClass;

        $subQuery = DB::table('tickets')
            ->join('partners', 'tickets.partner_id', 'partners.id')
            ->join('users', 'user_id', 'users.id')
            ->when($request->user()->hasRole('partner'), function ($q) use ($request) {
                $q->where('partners.user_id', $request->user()->id);
            })
            ->when(
                $request->user()->hasRole('reviewer') && $request->partner_id,
                function ($q) use ($request) {
                    $q->where('partners.id', $request->partner_id);
                }
            )
            ->when($request->status, function ($q, $status) {
                if ($status === 'responded') {
                    $q->whereNotNull('response');
                } else {
                    $q->whereNull('response');
                }
            })
            ->selectRaw('
                tickets.id,
                tickets.code,
                issue,
                response,
                CONCAT(last_name, " ", first_name) AS partner,
                users.code AS partner_code,
                tickets.created_at,
                responded_at
            ');

        $params->builder = DB::query()->fromSub($subQuery, 'sub');

        return fetchListData($request, $params);
    }
}
