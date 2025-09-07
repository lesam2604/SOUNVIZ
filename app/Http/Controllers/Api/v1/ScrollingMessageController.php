<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\ScrollingMessage;
use App\Models\History;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use stdClass;

class ScrollingMessageController extends Controller
{
    public function fetch(Request $request, $id)
    {
        $obj = ScrollingMessage::with(['creator', 'updator'])->findOrFail($id);
        return response()->json($obj);
    }

    public function fetchVisibles(Request $request)
    {
        $request->validate([
            'target' => 'nullable|string|in:auth,app'
        ]);

        $messages = ScrollingMessage::where('status', 'enabled')
            ->where(function ($query) {
                $query->whereNull('from')
                    ->orWhere('from', '<=', DB::raw('CURDATE()'));
            })
            ->where(function ($query) {
                $query->whereNull('to')
                    ->orWhere('to', '>=', DB::raw('CURDATE()'));
            })
            ->when($request->target, function ($q, $target) {
                $q->where("show_$target", true);
            })
            ->get();

        return response()->json($messages);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'label' => 'required|string|max:191|unique:scrolling_messages',
            'content' => 'required|string|max:1000',
            'from' => 'nullable|date',
            'to' => 'nullable|date|after:from',
            'time' => 'required|integer|gt:0',
            'size' => 'required|string|in:small,medium,large',
            'color' => 'required|string|in:black,blue,red,yellow,green',
            'show_auth' => 'required|integer|in:0,1',
            'show_app' => 'required|integer|in:0,1',
        ], [
            '*.required' => 'Ce champs est requis',
            '*.string' => 'Ce champs doit être une chaîne de caractères',
            '*.integer' => 'Ce champs doit être une valeur entière',
            '*.unique' => 'La valeur fournie pour ce champs existe deja',
            '*.date' => 'La date fournie n\'est pas valide',
            '*.in' => "La valeur fournie pour ce champs n'est pas valide",
            'label.max' => 'La longueur maximale pour ce champs est de 191 caractères',
            'content.max' => 'La longueur maximale pour ce champs est de 1000 caractères',
            'to.after' => 'La date de fin doit être postérieure a la date de debut',
            '*.gt' => 'Ce champs doit être une valeur strictement positive',
        ]);

        $reviewer = $request->user();

        DB::beginTransaction();

        try {
            $obj = ScrollingMessage::create([
                'label' => $data['label'],
                'content' => $data['content'],
                'from' => $data['from'],
                'to' => $data['to'],
                'time' => $data['time'],
                'size' => $data['size'],
                'color' => $data['color'],
                'show_auth' => $data['show_auth'],
                'show_app' => $data['show_app'],
                'creator_id' => $reviewer->id
            ]);

            History::create([
                'user_id' => $reviewer->id,
                'title' => "Message défilant enregistré.",
                'content' => "Vous avez enregistré un nouveau message défilant \"{$obj->code}\"."
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 500);
        }

        return response()->json(['message' => "Message défilant enregistré."]);
    }

    public function update(Request $request, $id)
    {
        $obj = ScrollingMessage::findOrFail($id);

        $data = $request->validate([
            'label' => "required|string|max:191|unique:scrolling_messages,label,{$obj->id}",
            'content' => 'required|string|max:1000',
            'from' => 'nullable|date',
            'to' => 'nullable|date|after:from',
            'time' => 'required|integer|gt:0',
            'size' => 'required|string|in:small,medium,large',
            'color' => 'required|string|in:black,blue,red,yellow,green',
            'show_auth' => 'required|integer|in:0,1',
            'show_app' => 'required|integer|in:0,1',
        ], [
            '*.required' => 'Ce champs est requis',
            '*.string' => 'Ce champs doit être une chaîne de caractères',
            '*.integer' => 'Ce champs doit être une valeur entière',
            '*.unique' => 'La valeur fournie pour ce champs existe deja',
            '*.date' => 'La date fournie n\'est pas valide',
            '*.in' => "La valeur fournie pour ce champs n'est pas valide",
            'label.max' => 'La longueur maximale pour ce champs est de 191 caractères',
            'content.max' => 'La longueur maximale pour ce champs est de 1000 caractères',
            'to.after' => 'La date de fin doit être postérieure a la date de début',
            '*.gt' => 'Ce champs doit être une valeur strictement positive',
        ]);

        $reviewer = $request->user();

        DB::beginTransaction();

        try {
            $obj->update([
                'label' => $data['label'],
                'content' => $data['content'],
                'from' => $data['from'],
                'to' => $data['to'],
                'time' => $data['time'],
                'size' => $data['size'],
                'color' => $data['color'],
                'show_auth' => $data['show_auth'],
                'show_app' => $data['show_app'],
                'updator_id' => $reviewer->id
            ]);

            History::create([
                'user_id' => $reviewer->id,
                'title' => "Mise a jour de message.",
                'content' => "Vous avez mis a jour les informations du message défilant \"{$obj->label}\"."
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 500);
        }

        return response()->json(['message' => "Message défilant mis a jour."]);
    }

    public function changeStatus(Request $request, $id)
    {
        $data = $request->validate([
            'status' => 'required|string|in:enabled,disabled',
        ]);

        $obj = ScrollingMessage::findOrFail($id);

        $reviewer = $request->user();

        DB::beginTransaction();

        try {
            $obj->update([
                'status' => $data['status'],
                'updator_id' => $reviewer->id
            ]);

            History::create([
                'user_id' => $reviewer->id,
                'title' => ($obj->status === 'enabled' ? 'Activation' : 'Désactivation') . " du message défilant \"{$obj->label}\".",
                'content' => "Vous avez " . ($obj->status === 'enabled' ? 'activé' : 'désactivé') . " le message \"{$obj->label}\"."
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 500);
        }

        return response()->json(['message' => "Message défilant " . ($obj->status === 'enabled' ? 'activé' : 'désactivé')]);
    }

    public function destroy(Request $request, $id)
    {
        $obj = ScrollingMessage::findOrFail($id);

        $reviewer = $request->user();

        DB::beginTransaction();

        try {
            $obj->delete();

            History::create([
                'user_id' => $reviewer->id,
                'title' => 'Suppression de message défilant.',
                'content' => "Vous avez supprimé le message défilant \"{$obj->label}\"."
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 500);
        }

        return response()->json(['message' => "Message défilant supprimé."]);
    }

    public function list(Request $request)
    {
        $data = $request->validate([
            'status' => 'nullable|string|in:enabled,disabled',
        ]);

        $params = new stdClass;

        $subQuery = DB::table('scrolling_messages')
            ->when($data['status'] ?? null, function ($q, $status) {
                $q->where('status', $status);
            });

        $params->builder = DB::query()->fromSub($subQuery, 'sub');

        return fetchListData($request, $params);
    }
}
