<?php

use App\Models\Company;
use App\Models\Partner;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // $this->fill();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }

    private function fill()
    {
        $adminUser = User::create([
            'code' => User::nextCode('admin', 'ASZ'),
            'first_name' => 'S. Zehoue',
            'last_name' => 'Admin',
            'phone_number' => '123456789',
            'email' => 'chrstnsossa33@gmail.com',
            'password' => Hash::make('1234'),
            'picture' => 'https://randomuser.me/api/portraits/men/46.jpg',
            'status' => 'enabled'
        ])->assignRole('reviewer', 'admin');

        $company = Company::create([
            'name' => 'SOUNVI ZEHOUE',
            'tin' => '843949322234',
            'status' => 'enabled'
        ]);

        User::create([
            'code' => User::nextCode('collab', 'CSZ'),
            'first_name' => 'S. Zehoue',
            'last_name' => 'Collab',
            'phone_number' => '7483933344',
            'email' => 'chrstnsossa2@gmail.com',
            'password' => Hash::make('1234'),
            'picture' => 'https://randomuser.me/api/portraits/men/47.jpg',
            'status' => 'enabled',
            'creator_id' => $adminUser->id
        ])->assignRole('reviewer', 'collab');

        $partnerMaster = Partner::create([
            'user_id' => User::create([
                'code' => User::nextCode('partner', 'PSZ'),
                'first_name' => 'S. Zehoue',
                'last_name' => 'Partenaire',
                'phone_number' => '4343743843',
                'email' => 'chrstnsossa3@gmail.com',
                'password' => Hash::make('1234'),
                'picture' => 'https://randomuser.me/api/portraits/men/48.jpg',
                'status' => 'enabled',
                'company_id' => $company->id,
                'creator_id' => $adminUser->id
            ])->assignRole('partner', 'partner-master')->id,
            'idcard_number' => '016738383663',
            'idcard_picture' => 'https://picsum.photos/id/1/200/300',
            'address' => 'Cotonou',
            'balance' => 5000000,
            'company_id' => $company->id,
        ]);

        Partner::create([
            'user_id' => User::create([
                'code' => User::nextCode('partner', 'PSZ'),
                'first_name' => 'S. Zehoue',
                'last_name' => 'POS',
                'phone_number' => '3843474343',
                'email' => 'chrstnsossa1@gmail.com',
                'password' => Hash::make('1234'),
                'picture' => 'https://randomuser.me/api/portraits/men/49.jpg',
                'status' => 'enabled',
                'company_id' => $company->id,
                'creator_id' => $partnerMaster->user->id
            ])->assignRole('partner', 'partner-pos')->id,
            'idcard_number' => '738383663016',
            'idcard_picture' => 'https://picsum.photos/id/2/200/300',
            'address' => 'Parakou',
            'balance' => 2000000,
            'company_id' => $company->id,
        ]);
    }
};
