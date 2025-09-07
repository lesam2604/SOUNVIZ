<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class HistoryController extends Controller
{
    public function get(Request $request, $id = null)
    {
        $user = $request->user();

        $target = $id && $user->hasRole('reviewer') ? User::findOrFail($id) : $user;

        if (
            ($user->hasRole('admin') && !$target->hasRole(['collab', 'partner'])) ||
            ($user->hasRole('collab') && !$target->hasRole('partner'))
        ) {
            return response()->json(['message' => 'Non autorisé'], 401);
        }

        $histories = $target->histories()
            ->latest()
            ->when($request->before_id, function ($q, $beforeId) {
                $q->where('id', '<', $beforeId);
            })
            ->limit(intval($request->length))
            ->get();

        return response()->json($histories);
    }
}
