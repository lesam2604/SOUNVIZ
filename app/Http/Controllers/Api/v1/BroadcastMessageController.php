<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\BroadcastMessage;
use App\Models\History;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use stdClass;

class BroadcastMessageController extends Controller
{
    public function fetch(Request $request, $id)
    {
        $obj = BroadcastMessage::with(['creator', 'updator'])->findOrFail($id);
        return response()->json($obj);
    }

    public function store(Request $request)
    {
        $request->validate([
            'label' => 'required|string|max:191|unique:broadcast_messages',
            'content' => 'required|string',
            'group' => 'required|string|in:all,collab,partner',
        ], [
            '*.required' => 'Ce champs est requis',
            '*.string' => 'Ce champs doit être une chaîne de caractères',
            '*.unique' => 'La valeur fournie pour ce champs existe déjà',
            '*.in' => "La valeur fournie pour ce champs n'est pas valide",
            'label.max' => 'La longueur maximale pour ce champs est de 191 caractères',
        ]);

        $reviewer = $request->user();

        DB::beginTransaction();

        try {
            $obj = BroadcastMessage::create([
                'label' => $request->label,
                'content' => $request->content,
                'group' => $request->group,
                'creator_id' => $reviewer->id
            ]);

            History::create([
                'user_id' => $reviewer->id,
                'title' => 'Message de diffusion enregistre.',
                'content' => "Vous avez enregistre un nouveau message de diffusion \"{$obj->code}\"."
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 500);
        }

        return response()->json(['message' => 'Message de diffusion enregistre.']);
    }

    public function update(Request $request, $id)
    {
        $obj = BroadcastMessage::findOrFail($id);

        $request->validate([
            'label' => "required|string|max:191|unique:broadcast_messages,label,{$obj->id}",
            'content' => 'required|string',
            'group' => 'required|string|in:all,collab,partner',
        ], [
            '*.required' => 'Ce champs est requis',
            '*.string' => 'Ce champs doit être une chaîne de caractères',
            '*.unique' => 'La valeur fournie pour ce champs existe deja',
            '*.in' => "La valeur fournie pour ce champs n'est pas valide",
            'label.max' => 'La longueur maximale pour ce champs est de 191 caractères',
        ]);

        $reviewer = $request->user();

        DB::beginTransaction();

        try {
            $obj->update([
                'label' => $request->label,
                'content' => $request->content,
                'group' => $request->group,
                'updator_id' => $reviewer->id
            ]);

            History::create([
                'user_id' => $reviewer->id,
                'title' => "Mise a jour de message de diffusion \"{$obj->label}\".",
                'content' => "Vous avez mis a jour les informations du message de diffusion \"{$obj->label}\"."
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 500);
        }

        return response()->json(['message' => "Message de diffusion \"{$obj->label}\" mis a jour."]);
    }

    public function markAsSeen(Request $request, $id)
    {
        $obj = BroadcastMessage::findOrFail($id);

        $authUser = $request->user();

        $bms = DB::table('broadcast_message_seen')
            ->where([
                'user_id' => $authUser->id,
                'broadcast_message_id' => $obj->id,
            ])
            ->first();

        if ($bms) {
            DB::table('broadcast_message_seen')
                ->where('id', $bms->id)
                ->update(['seen_at' => Carbon::now()]);

            return response()->json(0);
        } else {
            DB::table('broadcast_message_seen')
                ->insert([
                    'user_id' => $authUser->id,
                    'broadcast_message_id' => $obj->id,
                    'seen_at' => Carbon::now()
                ]);

            return response()->json(1);
        }
    }

    public function destroy(Request $request, $id)
    {
        $obj = BroadcastMessage::findOrFail($id);

        $reviewer = $request->user();

        DB::beginTransaction();

        try {
            $obj->delete();

            History::create([
                'user_id' => $reviewer->id,
                'title' => "Suppression de message de diffusion \"{$obj->label}\".",
                'content' => "Vous avez supprime le message de diffusion \"{$obj->label}\"."
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 500);
        }

        return response()->json(['message' => "Message de diffusion \"{$obj->label}\" supprime."]);
    }

    public function list(Request $request)
    {
        $params = new stdClass;

        $subQuery = DB::table('broadcast_messages')
            ->when($request->user()->hasRole('collab'), function ($q) {
                $q->whereIn('group', ['all', 'collab']);
            })
            ->when($request->user()->hasRole('partner'), function ($q) {
                $q->whereIn('group', ['all', 'partner']);
            });

        $params->builder = DB::query()->fromSub($subQuery, 'sub');

        return fetchListData($request, $params);
    }
}
