<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Carbon\Carbon;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function get(Request $request)
    {
        $notifications = Notification::where('recipient_id', $request->user()->id)
            ->when($request->seen, function ($q, $seen) {
                $seen === 'true' ? $q->whereNotNull('seen_at') : $q->whereNull('seen_at');
            })
            ->when($request->before_id, function ($q, $beforeId) {
                $q->where('id', '<', $beforeId);
            })
            ->latest()
            ->limit(intval($request->length))
            ->get();

        return response()->json($notifications);
    }

    public function markAsSeen(Request $request, $id)
    {
        Notification::findOrFail($id)->update([
            'seen_at' => Carbon::now()
        ]);

        return response()->json(1);
    }
}
