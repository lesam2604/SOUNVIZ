<?php

use App\Models\Card;
use App\Models\CardCategory;
use App\Models\CardOrder;
use App\Models\Company;
use App\Models\History;
use App\Models\InvCategory;
use App\Models\InvDelivery;
use App\Models\InvOrder;
use App\Models\InvProduct;
use App\Models\InvSupply;
use App\Models\MoneyTransfer;
use App\Models\Notification;
use App\Models\Operation;
use App\Models\OperationType;
use App\Models\Partner;
use App\Models\ScrollingMessage;
use App\Models\Setting;
use App\Models\Ticket;
use App\Models\User;
use App\Models\Withdrawal;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private $json;
    private $types;
    private $reversedTypes;
    private $indexes;

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
        $this->reversedTypes = array_flip($this->types);
        $this->indexes = [];
        $this->index('admins', 'user_id');
        $this->index('collabs', 'user_id');
        $this->index('agents', 'user_id');
        $this->index('admins', 'id');
        foreach ($this->types as $key => $value) {
            $this->index($key . 's', 'id');
            $this->index($key . 's_rejected', 'id');
        }
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

    protected function fillSettings()
    {
        $setting = $this->getTableData('settings')[0];
        $fees = json_decode($setting->fees);
        $agent_commission = json_decode($setting->agent_commission);

        foreach (OperationType::all() as $opType) {
            if (!in_array($opType->code, ['account_recharge', 'balance_withdrawal'])) {
                $reversedOpTypeCode = $this->reversedTypes[$opType->code];

                $opType->update([
                    'fees' => $fees->$reversedOpTypeCode,
                    'commissions' => $agent_commission->$reversedOpTypeCode
                ]);
            }
        }

        Setting::create(['dashboard_message' => $setting->dashboard_message]);
    }

    protected function fillHR()
    {
        foreach ($this->getTableData('users') as $user) {
            switch ($user->type) {
                case 'admin':
                    $admin = $this->getTableData('admins', 'user_id', $user->id);
                    User::create([
                        'id' => $user->id,
                        'code' => $admin->code,
                        'first_name' => $user->first_name,
                        'last_name' => $user->last_name,
                        'phone_number' => $user->phone_number,
                        'email' => $user->email,
                        'password' => $user->password,
                        'picture' => $user->picture,
                        'status' => 'enabled',
                        'feedback' => null,
                        'company_id' => null,
                        'creator_id' => null,
                        'updator_id' => null,
                        'reviewer_id' => null,
                        'reviewed_at' => null,
                        'created_at' => $user->created_at,
                        'updated_at' => $user->updated_at
                    ])->assignRole('reviewer', 'admin');
                    break;

                case 'collab':
                    $collab = $this->getTableData('collabs', 'user_id', $user->id);
                    User::create([
                        'id' => $user->id,
                        'code' => $collab->code,
                        'first_name' => $user->first_name,
                        'last_name' => $user->last_name,
                        'phone_number' => $user->phone_number,
                        'email' => $user->email,
                        'password' => $user->password,
                        'picture' => $user->picture,
                        'status' => $collab->status,
                        'feedback' => null,
                        'company_id' => null,
                        'creator_id' => $this->getTableData('admins', 'id', $collab->added_by_id)->user_id,
                        'updator_id' => null,
                        'reviewer_id' => null,
                        'reviewed_at' => null,
                        'created_at' => $user->created_at,
                        'updated_at' => $user->updated_at
                    ])->assignRole('reviewer', 'collab');
                    break;

                case 'agent':
                    $agent = $this->getTableData('agents', 'user_id', $user->id);

                    if ($agent->master_id === null) {
                        $company = Company::create([
                            'name' => $agent->company_name ?? '',
                            'tin' => $agent->tin,
                            'status' => $agent->status,
                            'creator_id' => '',
                            'updator_id' => $agent->reviewer_id,
                            'created_at' => $agent->created_at,
                            'updated_at' => $agent->updated_at
                        ]);

                        $newUser = User::create([
                            'id' => $user->id,
                            'code' => $agent->code,
                            'first_name' => $user->first_name,
                            'last_name' => $user->last_name,
                            'phone_number' => $user->phone_number,
                            'email' => $user->email,
                            'password' => $user->password,
                            'picture' => $user->picture,
                            'status' => $agent->status,
                            'feedback' => null,
                            'company_id' => $company->id,
                            'creator_id' => null,
                            'updator_id' => $agent->reviewer_id,
                            'reviewer_id' => $agent->reviewer_id,
                            'reviewed_at' => $agent->reviewed_at,
                            'created_at' => $user->created_at,
                            'updated_at' => $user->updated_at
                        ])->assignRole('partner', 'partner-master');
                    } else {
                        $newUser = User::create([
                            'id' => $user->id,
                            'code' => $agent->code,
                            'first_name' => $user->first_name,
                            'last_name' => $user->last_name,
                            'phone_number' => $user->phone_number,
                            'email' => $user->email,
                            'password' => $user->password,
                            'picture' => $user->picture,
                            'status' => $agent->status,
                            'feedback' => null,
                            'company_id' => User::find($agent->master_id)->company_id,
                            'creator_id' => $agent->added_by_id,
                            'updator_id' => null,
                            'reviewer_id' => null,
                            'reviewed_at' => null,
                            'created_at' => $user->created_at,
                            'updated_at' => $user->updated_at
                        ])->assignRole('partner', 'partner-pos');
                    }

                    Partner::create([
                        'id' => $agent->id,
                        'user_id' => $newUser->id,
                        'idcard_number' => $agent->idcard_number,
                        'idcard_picture' => $agent->idcard_picture,
                        'address' => $agent->address,
                        'balance' => $agent->balance,
                        'has_commissions' => $agent->has_commissions,
                        'company_id' => $newUser->company_id,
                        'created_at' => $agent->created_at,
                        'updated_at' => $agent->updated_at
                    ]);
                    break;
            }
        }

        foreach ($this->getTableData('agents_rejected') as $agentRejected) {
            $company = Company::create([
                'name' => $agentRejected->company_name ?? '',
                'tin' => $agentRejected->tin,
                'status' => 'rejected',
                'creator_id' => '',
                'updator_id' => '',
                'created_at' => $agentRejected->creation_timestamp,
                'updated_at' => $agentRejected->reviewed_at
            ]);

            $newUser = User::create([
                // 'id' => $user->id,
                'code' => $agentRejected->code,
                'first_name' => $agentRejected->first_name,
                'last_name' => $agentRejected->last_name,
                'phone_number' => $agentRejected->phone_number,
                'email' => $agentRejected->email,
                'password' => null,
                'picture' => $agentRejected->picture,
                'status' => 'rejected',
                'feedback' => $agentRejected->rejection_message,
                'company_id' => $company->id,
                'creator_id' => null,
                'updator_id' => $agentRejected->reviewer_id,
                'reviewer_id' => $agentRejected->reviewer_id,
                'reviewed_at' => $agentRejected->reviewed_at,
                'created_at' => $agentRejected->creation_timestamp,
                'updated_at' => $agentRejected->reviewed_at
            ])->assignRole('partner', 'partner-master');
        }
    }

    protected function fillOps()
    {
        foreach ($this->getTableData('activities') as $act) {
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

        foreach ($this->getTableData('activities_rejected') as $actRej) {
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

    protected function fillInv()
    {
        foreach ($this->getTableData('inv_categories') as $obj) {
            InvCategory::create((array)$obj);
        }
        foreach ($this->getTableData('inv_products') as $obj) {
            InvProduct::create((array)$obj);
        }
        foreach ($this->getTableData('inv_supplies') as $obj) {
            InvSupply::create((array)$obj);
        }
        foreach ($this->getTableData('inv_orders') as $obj) {
            InvOrder::create((array)$obj);
        }
        foreach ($this->getTableData('inv_order_product') as $obj) {
            DB::table('inv_order_product')->insert((array)$obj);
        }
        foreach ($this->getTableData('inv_deliveries') as $obj) {
            InvDelivery::create((array)$obj);
        }
        foreach ($this->getTableData('inv_delivery_product') as $obj) {
            DB::table('inv_delivery_product')->insert((array)$obj);
        }

        foreach ($this->getTableData('card_categories') as $obj) {
            CardCategory::create((array)$obj);
        }
        foreach ($this->getTableData('cards') as $obj) {
            Card::create((array)$obj);
        }
        foreach ($this->getTableData('card_orders') as $obj) {
            $arr = (array)$obj;
            $arr['partner_id'] = $arr['agent_id'];
            unset($arr['agent_id']);
            $arr['nbcards'] = Card::where('card_order_id', $obj->id)->count();
            CardOrder::create($arr);
        }
    }

    protected function fillNots()
    {
        foreach ($this->getTableData('histories') as $obj) {
            History::create((array)$obj);
        }

        foreach ($this->getTableData('notifications') as $obj) {
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
        foreach ($this->getTableData('withdrawals') as $obj) {
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

        foreach ($this->getTableData('money_transfers') as $obj) {
            MoneyTransfer::create((array)$obj);
        }

        foreach ($this->getTableData('scrolling_messages') as $obj) {
            ScrollingMessage::create((array)$obj);
        }

        foreach ($this->getTableData('tickets') as $obj) {
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
        $this->fillSettings();
        $this->fillHR();
        $this->fillOthers();
        $this->fillOps();
        $this->fillInv();
        $this->fillNots();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
