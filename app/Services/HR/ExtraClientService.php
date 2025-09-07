<?php

namespace App\Services\HR;

use App\Models\ExtraClient;
use App\Services\DatatableService;
use Exception;
use Illuminate\Support\Facades\DB;

class ExtraClientService
{
    private $datatableService;

    public function __construct()
    {
        $this->datatableService = app(DatatableService::class);
    }

    public function getAllExtraClients()
    {
        return ExtraClient::get();
    }

    public function createExtraClient($data, $user)
    {
        DB::beginTransaction();

        try {
            $ec = ExtraClient::create([
                'code' => generateUniqueCode('extra_clients', 'code', 'XSZ'),
                'company_name' => $data['company_name'],
                'tin' => $data['tin'],
                'phone_number' => $data['phone_number'],
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'creator_id' => $user->id
            ]);

            DB::commit();

            return [null, $ec];
        } catch (Exception $e) {
            DB::rollBack();
            return [['message' => $e->getMessage()], null];
        }
    }

    public function updateExtraClient($data, $ec, $user)
    {
        DB::beginTransaction();

        try {
            $ec->update([
                'company_name' => $data['company_name'],
                'tin' => $data['tin'],
                'phone_number' => $data['phone_number'],
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'updator_id' => $user->id
            ]);

            DB::commit();

            return [null, $ec];
        } catch (Exception $e) {
            DB::rollBack();
            return [['message' => $e->getMessage()], null];
        }
    }

    public function deleteExtraClient($ec)
    {
        DB::beginTransaction();

        try {
            ExtraClient::where('id', $ec->id)->delete();

            DB::commit();

            return [null, true];
        } catch (Exception $e) {
            DB::rollBack();
            return [['message' => $e->getMessage()], null];
        }
    }

    public function getListExtraClients($dtParams)
    {
        $subQuery = DB::table('extra_clients')
            ->selectRaw('
                id,
                code,
                company_name,
                tin,
                phone_number,
                first_name,
                last_name,
                created_at
            ');

        $dtParams['builder'] = DB::query()->fromSub($subQuery, 'sub');

        return $this->datatableService->fetch($dtParams);
    }
}
