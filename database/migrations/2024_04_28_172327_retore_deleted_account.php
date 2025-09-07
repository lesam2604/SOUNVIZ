<?php

use App\Models\Company;
use App\Models\History;
use App\Models\MoneyTransfer;
use App\Models\Notification;
use App\Models\Operation;
use App\Models\OperationType;
use App\Models\Partner;
use App\Models\Ticket;
use App\Models\User;
use App\Models\Withdrawal;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private $json;
    private $types;
    private $indexes;
    private $agentUser;
    private $agent;

    public function __construct()
    {
        $this->json = json_decode(file_get_contents(storage_path('app/data/olddb.json')));
        $this->types = [
            'card_activation' => 'card_activation',
            'card_recharge' => 'card_recharge',
            'card_deactivation' => 'card_deactivation',
            'canal_decoder_activation' => 'canal_activation',
            'canal_resub' => 'canal_resub',
            'bill_payment' => 'bill_payment',
            'dhl_operation' => 'dhl',
            'bmo_operation' => 'bmo',
            'xpress_operation' => 'xpress',
            'internetwork_transfer' => 'internetwork_transfer',
            'international_transfer' => 'international_transfer',
            'account_recharge' => 'account_recharge',
            'balance_withdrawal' => 'balance_withdrawal',
        ];

        $this->indexes = [];
        $this->index('users', 'id');
        $this->index('users', 'email');
        $this->index('admins', 'id');
        $this->index('admins', 'user_id');
        $this->index('collabs', 'user_id');
        $this->index('agents', 'user_id');
        // $this->index('activities', 'agent_id');
        // $this->index('activities_rejected', 'agent_id');
        // $this->index('histories', 'user_id');
        // $this->index('notifications', 'recipient_id');
        // $this->index('withdrawals', 'agent_id');
        // $this->index('money_transfers', 'sender_id');
        // $this->index('money_transfers', 'recipient_id');
        // $this->index('tickets', 'agent_id');

        foreach ($this->types as $key => $value) {
            $this->index($key . 's', 'id');
            $this->index($key . 's_rejected', 'id');
        }

        $this->agentUser = $this->getTableData('users', 'email', 'affoukoueric97@gmail.com');
        $this->agent = $this->getTableData('agents', 'user_id', $this->agentUser->id);
    }

    protected function rejTable($rejType)
    {
        return substr($rejType, 0, strrpos($rejType, '_')) . 's_rejected';
    }

    protected function typeCodeFromRej($rejType)
    {
        return $this->types[substr($rejType, 0, strrpos($rejType, '_'))];
    }

    protected function index($table, $field)
    {
        foreach ($this->json as $object) {
            if ($object->type === 'table' && $object->name === $table) {
                foreach ($object->data as &$o) {
                    $this->indexes[$table][$field][$o->$field] = &$o;
                }
            }
        }
    }

    protected function getTableData($table, $field = null, $value = null)
    {
        if (!is_null($field) && !is_callable($field)) {
            if (isset($this->indexes[$table][$field])) {
                return $this->indexes[$table][$field][$value] ?? null;
            }
        }

        foreach ($this->json as $object) {
            if ($object->type === 'table' && $object->name === $table) {
                if (is_null($field)) {
                    return $object->data;
                }
                $callback = is_callable($field) ? $field : fn ($o) => $o->$field === $value;

                return array_values(array_filter($object->data, $callback));
            }
        }
    }

    protected function fillHR()
    {
        $company = Company::create([
            'name' => $this->agent->company_name ?? '',
            'tin' => $this->agent->tin,
            'status' => $this->agent->status,
            'creator_id' => '',
            'updator_id' => $this->agent->reviewer_id,
            'created_at' => $this->agent->created_at,
            'updated_at' => $this->agent->updated_at
        ]);

        $newUser = User::create([
            'id' => $this->agentUser->id,
            'code' => $this->agent->code,
            'first_name' => $this->agentUser->first_name,
            'last_name' => $this->agentUser->last_name,
            'phone_number' => $this->agentUser->phone_number,
            'email' => $this->agentUser->email,
            'password' => $this->agentUser->password,
            'picture' => $this->agentUser->picture,
            'status' => $this->agent->status,
            'feedback' => null,
            'company_id' => $company->id,
            'creator_id' => null,
            'updator_id' => $this->agent->reviewer_id,
            'reviewer_id' => $this->agent->reviewer_id,
            'reviewed_at' => $this->agent->reviewed_at,
            'created_at' => $this->agentUser->created_at,
            'updated_at' => $this->agentUser->updated_at
        ])->assignRole('partner', 'partner-master');


        Partner::create([
            'id' => $this->agent->id,
            'user_id' => $newUser->id,
            'idcard_number' => $this->agent->idcard_number,
            'idcard_picture' => $this->agent->idcard_picture,
            'address' => $this->agent->address,
            'balance' => $this->agent->balance,
            'has_commissions' => $this->agent->has_commissions,
            'company_id' => $newUser->company_id,
            'created_at' => $this->agent->created_at,
            'updated_at' => $this->agent->updated_at
        ]);
    }

    protected function fillOps()
    {
        foreach ($this->getTableData('activities', 'agent_id', $this->agent->id) as $act) {
            $oldop = $this->getTableData($act->activitable_type . 's', 'id', $act->activitable_id);
            $opType = OperationType::firstWhere('code', $this->types[$act->activitable_type]);
            $data = [];

            foreach ($opType->fields as $fieldName => $fieldData) {
                if ($opType->code === 'account_recharge' && $fieldName === 'trans_date') {
                    $data['trans_date'] = $oldop->trans_timestamp;
                } else if ($fieldName === $opType->amount_field) {
                    $data[$fieldName] = $act->amount + $act->fees;
                } else {
                    $data[$fieldName] = $oldop->$fieldName;
                }
            }

            Operation::create([
                'operation_type_id' => $opType->id,
                'partner_id' => $act->agent_id,
                'code' => $oldop->code,
                'data' => $data,
                'amount' => $act->amount,
                'fee' => $act->fees,
                'status' => $act->status,
                'feedback' => null,
                'reviewer_id' => $act->reviewer_id,
                'reviewed_at' => $act->reviewed_at,
                'commission' => $act->agent_commission,
                'withdrawn' => $act->agent_withdrawn,
                'withdrawal_id' => $act->withdrawal_id,
                'company_id' => Partner::find($act->agent_id)->company_id,
                'created_at' => $act->created_at,
                'updated_at' => $act->updated_at,
            ]);
        }

        foreach ($this->getTableData('activities_rejected', 'agent_id', $this->agent->id) as $actRej) {
            $oldop = $this->getTableData($this->rejTable($actRej->activitable_type), 'id', $actRej->activitable_id);
            $opType = OperationType::firstWhere('code', $this->typeCodeFromRej($actRej->activitable_type));
            $data = [];

            foreach ($opType->fields as $fieldName => $fieldData) {
                if ($opType->code === 'account_recharge' && $fieldName === 'trans_date') {
                    $data['trans_date'] = $oldop->trans_timestamp;
                } else if ($fieldName === $opType->amount_field) {
                    $data[$fieldName] = $actRej->amount + $actRej->fees;
                } else {
                    $data[$fieldName] = $oldop->$fieldName;
                }
            }

            Operation::create([
                'operation_type_id' => $opType->id,
                'partner_id' => $actRej->agent_id,
                'code' => $oldop->code,
                'data' => $data,
                'amount' => $actRej->amount,
                'fee' => $actRej->fees,
                'status' => 'rejected',
                'feedback' => $actRej->rejection_message,
                'reviewer_id' => $actRej->reviewer_id,
                'reviewed_at' => $actRej->reviewed_at,
                'commission' => $actRej->agent_commission,
                'withdrawn' => null,
                'withdrawal_id' => null,
                'company_id' => Partner::find($actRej->agent_id)->company_id,
                'created_at' => $actRej->created_at,
                'updated_at' => $actRej->updated_at,
            ]);
        }
    }

    protected function fillNots()
    {
        foreach ($this->getTableData('histories', 'user_id', $this->agent->user_id) as $obj) {
            History::create((array)$obj);
        }

        foreach ($this->getTableData('notifications', 'recipient_id', $this->agent->user_id) as $obj) {
            $data = json_decode($obj->data);

            if ($data->type === 'agent_pending') {
                $link = config('app.app_baseurl') . "/partners/$data->id";
            } else if ($data->type === 'agent_approved') {
                $link = config('app.app_baseurl') . '/dashboard';
            } else if ($data->type === 'ticket-responded') {
                $link = config('app.app_baseurl') . "/tickets/$data->id";
            } else {
                $lastHyphenPos = strrpos($data->type, '-');
                $firstPart = str_replace('-', '_', substr($data->type, 0, $lastHyphenPos));
                $secondPart = substr($data->type, $lastHyphenPos + 1);

                $link = config('app.app_baseurl') . '/operations';

                if ($secondPart !== 'deleted' && $secondPart !== 'rejected') {
                    $act = $this->getTableData($firstPart . 's', 'id', $data->id) ?? null;

                    if ($act) {
                        $op = Operation::where('code', $act->code)
                            ->where('status', '<>', 'rejected')
                            ->first();

                        $link .= "/{$this->types[$firstPart]}/$op->id";
                    }
                }
            }

            Notification::create([
                'id' => $obj->id,
                'recipient_id' => $obj->recipient_id,
                'subject' => $obj->subject,
                'body' => $obj->body,
                'icon_class' => $obj->icon_class,
                'link' => $link,
                'seen_at' => $obj->seen_at,
                'created_at' => $obj->created_at,
                'updated_at' => $obj->updated_at,
            ]);
        }
    }

    protected function fillOthers()
    {
        foreach ($this->getTableData('withdrawals', 'agent_id', $this->agent->id) as $obj) {
            Withdrawal::create([
                'id' => $obj->id,
                'partner_id' => $obj->agent_id,
                'code' => $obj->code,
                'amount' => $obj->amount,
                'company_id' => Partner::find($obj->agent_id)->company_id,
                'created_at' => $obj->created_at,
                'updated_at' => $obj->updated_at,
            ]);
        }

        foreach ($this->getTableData('money_transfers', 'sender_id', $this->agent->id) as $obj) {
            MoneyTransfer::create((array)$obj);
        }

        foreach ($this->getTableData('money_transfers', 'recipient_id', $this->agent->id) as $obj) {
            MoneyTransfer::create((array)$obj);
        }

        foreach ($this->getTableData('tickets', 'agent_id', $this->agent->id) as $obj) {
            Ticket::create([
                'id' => $obj->id,
                'partner_id' => $obj->agent_id,
                'code' => $obj->code,
                'issue' => $obj->issue,
                'response' => $obj->response,
                'responder_id' => $obj->responder_id,
                'responded_at' => $obj->responded_at,
                'created_at' => $obj->created_at,
                'updated_at' => $obj->updated_at,
            ]);
        }
    }

    public function up(): void
    {
        // $this->fillHR();
        // $this->fillOthers();
        // $this->fillOps();
        // $this->fillNots();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
