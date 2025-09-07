<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'data' => 'json',
    ];

    public function broadcastToUsers($users)
    {
        foreach ($users as $user) {
            $newNot = $this->replicate();
            $newNot->recipient()->associate($user);
            $newNot->save();
        }
    }

    public function broadcastToAdmins()
    {
        $this->broadcastToUsers(User::role('admin')->get());
    }

    public function broadcastToCollabs()
    {
        $this->broadcastToUsers(User::role('collab')->get());
    }

    public function broadcastToActiveCollabs()
    {
        $activeCollabs = User::role('collab')->where('status', 'enabled')->get();
        $this->broadcastToUsers($activeCollabs);
    }

    public function broadcastToActiveReviewers()
    {
        $activeReviewers = User::role('admin')->orWhere(function ($q) {
            $q->role('collab')->where('status', 'enabled');
        })->get();

        $this->broadcastToUsers($activeReviewers);
    }

    public function recipient()
    {
        return $this->belongsTo(User::class);
    }
}
