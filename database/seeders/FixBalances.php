<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Notification;
use App\Models\Partner;
use App\Models\Statement;
use Exception;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FixBalances extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::beginTransaction();

        try {
            $companies = Company::query()
                ->whereIn('status', ['enabled', 'disabled'])
                ->get();

            foreach ($companies as $company) {
                $master = Partner::query()
                    ->where('company_id', $company->id)
                    ->whereHas('user', function ($q) {
                        $q->role('partner-master');
                    })
                    ->first();

                if (is_null($master)) {
                    continue;
                }

                logger($master->user->email);

                $posQuery = Partner::query()
                    ->where('company_id', $company->id)
                    ->whereHas('user', function ($q) {
                        $q->role('partner-pos');
                    });

                $balances = intval($posQuery->clone()->sum('balance'));

                $master->balance += $balances;
                $master->save();

                $posQuery->update(['balance' => 0]);

                Statement::create([
                    'partner_id' => $master->id,
                    'type' => 'Reversement des soldes des boutiques',
                    'amount' => $balances,
                    'balance' => $master->balance
                ]);

                Notification::create([
                    'recipient_id' => $master->user_id,
                    'subject' => 'Reversement des soldes des boutiques',
                    'body' => "Un montant de {$balances} FCFA a été reversé sur votre compte par vos boutiques",
                    'icon_class' => 'fas fa-thumbs-up',
                    'link' => config('app.app_baseurl') . "/dashboard"
                ]);
            }

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
