<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\Operation;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function updateProfilePicture(Request $request)
    {
        $data = $request->validate([
            'picture' => 'required|image',
        ]);

        DB::beginTransaction();

        try {
            $data['picture'] = saveFile($data['picture'], true);

            $oldPic = $request->user()->picture;

            $request->user()->update(['picture' => $data['picture']]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            removeFile($data['picture'], true);

            return response()->json(['message' => $e->getMessage()], 500);
        }

        if ($oldPic && filter_var($oldPic, FILTER_VALIDATE_URL) === false) {
            removeFile($oldPic, true);
        }

        return response()->json(['message' => 'Photo de profile mise a jour']);
    }

    public function unseens(Request $request)
    {
        if ($request->user()->hasRole('reviewer')) {
            $notifications = Operation::where('status', 'pending')->pluck('id');
        } else {
            $notifications = [];
        }

        if ($request->user()->can('respond ticket')) {
            $tickets = Ticket::whereNull('response')->pluck('id');
        } else {
            $tickets = [];
        }

        if ($request->user()->hasRole('admin')) {
            $broadcastMessages = [];
        } else {
            $broadcastMessages = DB::table('broadcast_messages')
                ->when(true, function ($q) use ($request) {
                    if ($request->user()->hasRole('collab')) {
                        $q->whereIn('group', ['all', 'collab']);
                    } else if ($request->user()->hasRole('partner')) {
                        $q->whereIn('group', ['all', 'partner']);
                    }
                })
                ->leftJoin('broadcast_message_seen', function ($join) use ($request) {
                    $join->on('broadcast_messages.id', 'broadcast_message_id')
                        ->where('user_id', $request->user()->id);
                })
                ->whereNull('broadcast_message_seen.id')
                ->pluck('broadcast_messages.id');
        }

        return response()->json(compact('notifications', 'tickets', 'broadcastMessages'));
    }
}
