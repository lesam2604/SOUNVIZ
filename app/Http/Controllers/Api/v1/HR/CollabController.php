<?php

namespace App\Http\Controllers\Api\v1\HR;

use App\Http\Controllers\Controller;
use App\Models\History;
use App\Models\InvProduct;
use App\Models\Operation;
use App\Models\OperationType;
use App\Models\Partner;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use stdClass;
use App\Models\CollaboratorBalance;


class CollabController extends Controller
{
    public function dashboardData(Request $request)
    {
        $data = $request->validate([
            'from_date' => 'nullable|date',
            'to_date' => 'nullable|date'
        ]);

        if ($data['to_date'] ?? null) {
            $data['to_date'] = Carbon::parse($data['to_date'])->addDay()->format('Y-m-d');
        }

        $r = [];

        foreach (OperationType::all() as $operationType) {
            foreach (['pending', 'approved', 'rejected'] as $status) {
                $r["{$operationType->code}_{$status}"] = Operation::where('operation_type_id', $operationType->id)
                    ->where('status', $status)
                    ->when($data['from_date'] ?? null, function ($q, $fromDate) {
                        $q->where('created_at', '>=', $fromDate);
                    })->when($data['to_date'] ?? null, function ($q, $toDate) {
                        $q->where('created_at', '<', $toDate);
                    })
                    ->count();
            }
        }

        foreach (['withdrawals', 'money_transfers'] as $table) {
            $r[$table] = DB::table($table)
                ->when($data['from_date'] ?? null, function ($q, $fromDate) {
                    $q->where('created_at', '>=', $fromDate);
                })->when($data['to_date'] ?? null, function ($q, $toDate) {
                    $q->where('created_at', '<', $toDate);
                })->count();
        }

        foreach (['pending', 'enabled', 'disabled', 'rejected'] as $status) {
            $r["partners_{$status}"] = User::role('partner')->where('status', $status)->count();
        }

        foreach (
            [
                'card_categories',
                'cards',
                'inv_categories',
                'inv_products',
                'inv_supplies',
                'inv_orders',
                'inv_deliveries'
            ] as $table
        ) {
            $r[$table] = DB::table($table)->count();
        }

        $r['to_supply_products'] = InvProduct::whereColumn('stock_quantity', '<=', 'stock_quantity_min')->get();

        $r['histories'] = History::where('user_id', $request->user()->id)->latest()->limit(5)->get();
        $r['recent_partners'] = Partner::with('user')->latest()->limit(5)->get();

        return response()->json($r);
    }

    public function fetch(Request $request, $id)
    {
        $obj = User::role('collab')->findOrFail($id);
        return response()->json($obj);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'first_name' => 'required|string|max:191',
            'last_name' => 'required|string|max:191',
            'phone_number' => 'required|string|max:191|unique:users',
            'email' => 'required|string|email|confirmed|max:191|unique:users',
            'picture' => 'required|image',
        ]);

        DB::beginTransaction();

        try {
            $data['picture'] = saveFile($data['picture'], true);

            $collab = User::create([
                'code' => User::nextCode('collab', 'CSZ'),
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'phone_number' => $data['phone_number'],
                'email' => $data['email'],
                'picture' => $data['picture'],
                'status' => 'enabled',
                'creator_id' => $request->user()->id
            ])->assignRole(['reviewer', 'collab']);

            History::create([
                'user_id' => $request->user()->id,
                'title' => "Ajout du collaborateur {$collab->full_name} {$collab->code}",
                'content' => "Vous avez ajoute le collaborateur {$collab->full_name} {$collab->code}"
            ]);

            $token = createPasswordResetToken($collab->email);

            Mail::to($collab->email)->send(new \App\Mail\CollabWelcome($collab->email, $token));

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            removeFile($data['picture'], true);

            return response()->json(['message' => $e->getMessage()], 500);
        }

        return response()->json(['message' => 'Collaborateur ajouté']);
    }

    public function changeStatus(Request $request, $id)
    {
        $data = $request->validate([
            'status' => 'required|string|in:enabled,disabled',
        ]);

        $collab = User::role('collab')->findOrFail($id);

        DB::beginTransaction();

        try {
            $collab->update([
                'status' => $data['status'],
                'updator_id' => $request->user()->id
            ]);

            History::create([
                'user_id' => $request->user()->id,
                'title' => ($collab->status === 'enabled' ? 'Activation' : 'Désactivation') . " du compte du collaborateur {$collab->full_name} {$collab->code}",
                'content' => "Vous avez " . ($collab->status === 'enabled' ? 'activé' : 'désactivé') . " le compte du collaborateur {$collab->full_name} {$collab->code}"
            ]);

            Mail::to($collab->email)->send(new \App\Mail\CollabChangeStatus($collab->status));

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 500);
        }

        return response()->json(['message' => 'Collaborateur ' . ($collab->status === 'enabled' ? 'activé' : 'désactivé')]);
    }

    public function destroy(Request $request, $id)
    {
        $collab = User::role('collab')->findOrFail($id);

        DB::beginTransaction();

        try {
            $collab->delete();

            History::create([
                'user_id' => $request->user()->id,
                'title' => "Suppression du compte du collaborateur {$collab->full_name} {$collab->code}",
                'content' => "Vous avez supprime le compte du collaborateur {$collab->full_name} {$collab->code}"
            ]);

            Mail::to($collab->email)->send(new \App\Mail\CollabDestroy());

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 500);
        }

        return response()->json(['message' => "Collaborateur supprimé"]);
    }

    public function list(Request $request)
    {
        $data = $request->validate([
            'status' => 'nullable|string|in:enabled,disabled',
        ]);

        $params = new stdClass;

        $subQuery = User::role('collab')
            ->leftJoin('users AS creator_user', 'users.creator_id', 'creator_user.id')
            ->when($data['status'] ?? null, function ($q, $status) {
                $q->where('users.status', $status);
            })
            ->selectRaw('
                users.id,
                users.code,
                users.first_name,
                users.last_name,
                users.phone_number,
                users.email,
                users.status,
                users.picture,
                CONCAT(creator_user.first_name, " ", creator_user.last_name) AS creator
            ');

        $params->builder = DB::query()->fromSub($subQuery, 'sub');

        return fetchListData($request, $params);
    }



    public function meBalance(Request $request)
   {
    $userId = $request->user()->id;

    $bal = CollaboratorBalance::firstOrCreate(
        ['user_id' => $userId],
        ['balance' => 0, 'currency' => 'XOF', 'updated_by' => $userId]
    );

    return response()->json([
        'ok'       => true,
        'balance'  => (int) $bal->balance,
        'currency' => $bal->currency,
    ]);
  }


}



