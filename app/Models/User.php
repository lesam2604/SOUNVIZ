<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles, HasApiTokens;

    protected $guarded = [];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function getFullNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    public function getBalanceAttribute()
    {
        return $this->hasRole('partner-master')
            ? $this->partner->balance
            : $this->partner->getMaster()->balance;
    }

    public function partner()
    {
        return $this->hasOne(Partner::class);
    }

    public function histories()
    {
        return $this->hasMany(History::class);
    }

    public static function activeReviewers()
    {
        return static::role(['admin', 'collab'])->where('status', 'enabled')->get();
    }

    public function amountToWithdraw()
    {
        $amountToWithdraw = Operation::query()
            ->where('company_id', $this->company_id)
            ->where('withdrawn', false)
            ->sum('commission');

        return intval($amountToWithdraw);
    }

    public static function nextCode($role, $prefix)
    {
        $last = static::role($role)->latest('code')->first();
        $lastNum = $last ? intval(explode('-', $last->code)[1]) : 5000;

        for (
            $uniqueCode = $prefix . '-' . str_pad(++$lastNum, 6, '0', STR_PAD_LEFT);
            static::role($role)->where('code', $uniqueCode)->exists();
        );

        return $uniqueCode;
    }

    public function allRolesPermissions()
    {
        return [
            $this->getRoleNames(),
            $this->getAllPermissions()->map(fn($permission) => $permission->name)
        ];
    }

    public function loadRolesPermissions()
    {
        [$roles, $permissions] = $this->allRolesPermissions();
        $user = (object)$this->toArray();

        $user->roles = $roles;
        $user->permissions = $permissions;

        return $user;
    }


    public function collaboratorBalance()
    {
        return $this->hasOne(\App\Models\CollaboratorBalance::class, 'user_id');
    }


}
