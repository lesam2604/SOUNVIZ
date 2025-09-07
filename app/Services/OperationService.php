<?php

namespace App\Services;

use App\Models\Card;
use App\Models\CardHolder;
use App\Models\Country;
use App\Models\Decoder;
use App\Models\History;
use App\Models\Notification;
use App\Models\Operation;
use App\Models\Partner;
use Carbon\Carbon;
use Dompdf\Dompdf;
use Dompdf\Options;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class OperationService
{
    const NOT_ALLOWED_TEXT = "Vous n'êtes pas autorisé a effectuer cette opération.";
    const CARD_ID_ERROR = "Le numéro de carte ne peut être que soit de 10 chiffres ou de 16 chiffres";
    const STORE_PARTNER_HISTORY_TITLE = "Opération :opName :objCode enregistrée";
    const STORE_PARTNER_HISTORY_CONTENT = "Vous avez enregistré l'opération :opName :objCode.";
    const STORE_REV_NOT_SUBJECT = "Nouvelle opération :opName :objCode en attente de validation";
    const STORE_REV_NOT_BODY = "L'opération :opName :objCode initiée par :partnerFullName :partnerCode est en attente de validation.";
    const STORE_SUCCESS_MESSAGE = "Opération :opName :objCode en attente de validation.";
    const UPDATE_PARTNER_HISTORY_TITLE = "Mise a jour de l'opération :opName :objCode";
    const UPDATE_PARTNER_HISTORY_CONTENT = "Vous avez mis a jour les informations de l'opération :opName :objCode.";
    const UPDATE_REV_NOT_SUBJECT = "Opération :opName :objCode mise a jour";
    const UPDATE_REV_NOT_BODY = "L'opération :opName :objCode initiée par :partnerFullName :partnerCode a été mise a jour.";
    const UPDATE_SUCCESS_MESSAGE = "Opération :opName :objCode mise a jour.";
    const APPROVE_REV_HISTORY_TITLE = "Validation de l'opération :opName :objCode";
    const APPROVE_REV_HISTORY_CONTENT = "Vous avez validé l'opération :opName :objCode initiée par :partnerFullName :partnerCode.";
    const APPROVE_PARTNER_NOT_SUBJECT = "Opération :opName :objCode validée";
    const APPROVE_PARTNER_NOT_BODY = "L'opération :opName :objCode a été validée.";
    const APPROVE_SUCCESS_MESSAGE = "Opération :opName :objCode validée.";
    const REJECT_REV_HISTORY_TITLE = "Rejet de l'opération :opName :objCode";
    const REJECT_REV_HISTORY_CONTENT = "Vous avez rejeté l'opération :opName :objCode initiée par :partnerFullName :partnerCode.";
    const REJECT_PARTNER_NOT_SUBJECT = "Opération :opName :objCode rejetée";
    const REJECT_PARTNER_NOT_BODY = "L'opération :opName :objCode a été rejetée.";
    const REJECT_SUCCESS_MESSAGE = "Opération :opName :objCode rejetée.";
    const DELETE_PARTNER_HISTORY_TITLE = "Annulation de l'opération :opName :objCode";
    const DELETE_PARTNER_HISTORY_CONTENT = "Vous avez annulé l'opération :opName :objCode.";
    const DELETE_REV_NOT_SUBJECT = "Opération :opName :objCode annulée";
    const DELETE_REV_NOT_BODY = "L'opération :opName :objCode initiée par :partnerFullName :partnerCode a été annulée.";
    const DELETE_SUCCESS_MESSAGE = "Opération :opName :objCode annulée.";

    protected $statementService;
    protected $datatableService;

    public function __construct()
    {
        $this->statementService = app(StatementService::class);
        $this->datatableService = app(DatatableService::class);
    }

    public function getOperationValidator($opType, $mode)
    {
        $rules = [];
        $messages = [
            '*.required' => 'Ce champs est requis',
            '*.string' => 'Ce champs doit être une chaîne de caractères',
            '*.numeric' => 'Ce champs doit être une valeur numérique',
            '*.email' => 'Ce champs doit être un email valide',
            '*.file' => 'Ce champs doit être un fichier',
            '*.exists' => "La valeur fournie pour ce champs n'est pas valide",
            '*.date' => "La date fournie n'est pas valide",
            '*.before_or_equal' => "La valeur fournie pour ce champs n'est pas valide",
            '*.in' => "La valeur fournie pour ce champs n'est pas valide"
        ];

        foreach ($opType->fields as $fieldName => $fieldData) {
            if (
                ($mode === 'store' && !$fieldData->stored) ||
                ($mode === 'update' && !$fieldData->updated)
            ) {
                continue;
            }

            $fieldRules = [];

            if (is_array($fieldData->required)) {
                $fieldRules[] = 'nullable';
                $fieldRules[] = "required_if:{$fieldData->required[0]},{$fieldData->required[1]}";
                $messages["{$fieldName}.required_if"] = "Ce champs est requis quand la valeur du champs {$opType->fields->{$fieldData->required[0]}->label} est {$fieldData->required[1]}";
            } else if ($fieldData->required === true && ($mode === 'store' || $fieldData->type !== 'file')) {
                $fieldRules[] = 'required';
            } else {
                $fieldRules[] = 'nullable';
            }

            switch ($fieldData->type) {
                case 'select':
                    $fieldRules[] = 'string';
                    $fieldRules[] = 'in:' . implode(',', $fieldData->options);
                    break;

                case 'text':
                    $fieldRules[] = 'string';
                    $fieldRules[] = 'max:191';
                    $messages["{$fieldName}.max"] = 'La longueur maximale pour ce champs est de 191 caractères';
                    break;

                case 'textarea':
                    $fieldRules[] = 'string';
                    $fieldRules[] = 'max:1000';
                    $messages["{$fieldName}.max"] = 'La longueur maximale pour ce champs est de 1000 caractères';
                    break;

                case 'card':
                    $fieldRules[] = 'string';
                    $fieldRules[] = function ($attribute, $value, $fail) {
                        if (!in_array(Str::length($value), [10, 16])) {
                            return $fail(static::CARD_ID_ERROR);
                        }
                    };
                    break;

                case 'number':
                    $fieldRules[] = 'numeric';
                    if ($fieldData->is_amount) {
                        $fieldRules[] = 'min:1';
                        $messages["{$fieldName}.min"] = 'Ce champs doit être une valeur positive valide';
                        $fieldRules[] = 'max:9999999999999.99';
                    }
                    break;

                case 'email':
                    $fieldRules[] = 'string';
                    $fieldRules[] = 'email';
                    $fieldRules[] = 'max:191';
                    $messages["{$fieldName}.max"] = 'La longueur maximale pour ce champs est de 191 caractères';
                    break;

                case 'file':
                    $fieldRules[] = 'file';
                    break;

                case 'country':
                    $fieldRules[] = 'numeric';
                    $fieldRules[] = 'exists:countries,id';
                    break;

                case 'date':
                case 'datetime':
                    $fieldRules[] = 'date';
                    if ($fieldData->lte_today) {
                        $fieldRules[] = 'before_or_equal:today';
                    }
                    break;

                default:
                    # code...
                    break;
            }

            // If it is a card_activation
            if ($opType->code === 'card_activation') {
                if ($fieldName === 'card_id') {
                    $fieldRules[] = function ($attribute, $value, $fail) use ($opType) {
                        $card = Card::firstWhere('card_id', $value);

                        if (!$card) {
                            return $fail("Cette carte n'est pas reconnue dans notre système");
                        }

                        if (!$card->card_order_id) {
                            return $fail("Cette carte ne fait l'objet d'aucune commande");
                        }

                        // Check that the card is not already activated
                        $exists = Operation::query()
                            ->where('operation_type_id', $opType->id)
                            ->where('card_id', $value)
                            ->whereIn('status', ['approved', 'pending'])
                            ->exists();

                        if ($exists) {
                            return $fail('Cette carte a déjà été activée');
                        }
                    };
                }
            }

            // If it is a canal_activation
            if ($opType->code === 'canal_activation') {
                if ($fieldName === 'decoder_number') {
                    $fieldRules[] = function ($attribute, $value, $fail) use ($opType) {
                        // Check that the decoder number exists
                        if (!Decoder::where('decoder_number', $value)->exists()) {
                            return $fail("Ce décodeur n'est pas reconnu dans notre système");
                        }

                        // Check that the decoder is not already activated
                        $exists = Operation::query()
                            ->where('operation_type_id', $opType->id)
                            ->where('decoder_number', $value)
                            ->whereIn('status', ['approved', 'pending'])
                            ->exists();

                        if ($exists) {
                            return $fail('Ce décodeur a déjà été activé');
                        }
                    };
                }
            }

            $rules[$fieldName] = $fieldRules;
        }

        return [$rules, $messages];
    }

    public function renderText($opType, $template, $obj)
    {
        $replacements = [
            ':opName' => $opType->name,
            ':objCode' => $obj->code,
            ':partnerFullName' => $obj->partner->user->full_name,
            ':partnerCode' => $obj->partner->code,
        ];

        return str_replace(
            array_keys($replacements),
            array_values($replacements),
            $template
        );
    }

    public function getOperation($opType, $opId, $user)
    {
        // Get the operation
        $op = Operation::with('partner.user', 'reviewer', 'company')
            ->where([
                'operation_type_id' => $opType->id,
                'id' => $opId
            ])
            ->firstOrFail();

        $master = $op->partner->getMaster();
        $op = (object)$op->toArray();
        $op->partner = (object)$op->partner;

        $op->master = $master;

        // Make sure the user can access the operation
        if (
            $user->hasRole('partner-master') && $user->company_id !== $op->company_id ||
            $user->hasRole('partner-pos') && $user->id !== $op->partner->user_id
        ) {
            return [['message' => static::NOT_ALLOWED_TEXT], null];
        }

        // Append countries
        foreach ($opType->fields as $fieldName => $fieldData) {
            if ($fieldData->type === 'country') {
                $op->countries[$fieldName] = Country::selectRaw('name, LOWER(iso2) AS code')
                    ->find($op->data->$fieldName);
            }
        }

        // For card activations, add the number of approved operations with the same
        // (client_first_name, client_last_name, birth_date) combination

        if ($opType->code === 'card_activation') {
            $op->duplicates = Operation::query()
                ->where([
                    'operation_type_id' => $opType->id,
                    'status' => 'approved',
                    'client_first_name' => $op->client_first_name,
                    'client_last_name' => $op->client_last_name,
                    'birth_date' => $op->birth_date,
                ])
                ->where('id', '<>', $op->id)
                ->count();
        }

        return [null, $op];
    }

    protected function applySpecialStore($opType, $master, &$data)
    {
        switch ($opType->code) {
            case 'account_recharge':
                if ($data['sender_phone_number_type'] === 'MomoPay') {
                    $data['trans_amount'] = intval($data['trans_amount']) * (1 - 0.005);
                }
                break;

            case 'balance_withdrawal':
                $totalAmount = floatval($data['amount']) * 1.02;

                // The master's balance must be enough to perform this operation
                if ($master->balance < $totalAmount) {
                    throw new Exception("Solde insuffisant: {$totalAmount} requis");
                }
                $master->balance -= $totalAmount;
                $master->save();
                break;

            default:
                // Overrides manuels: frais de course et commission plateforme
                $amountInput = $opType->amount_field ? intval($data[$opType->amount_field] ?? 0) : 0;
                $manualFee = isset($data['manual_fee']) && $data['manual_fee'] !== ''
                    ? (int) round(floatval(str_replace([',', ' '], ['.', ''], $data['manual_fee'])))
                    : null;
                $manualPlat = isset($data['manual_platform_commission']) && $data['manual_platform_commission'] !== ''
                    ? (int) round(floatval(str_replace([',', ' '], ['.', ''], $data['manual_platform_commission'])))
                    : null;

                if ($manualFee !== null && $manualFee >= 0) {
                    $fee = max(0, $manualFee);
                    $amount = max(0, $amountInput - $fee);
                    // Commission partenaire ignorée si saisie manuelle
                    $commission = 0;
                    // Ne pas toucher au solde du master ici; le débit se gère au moment de l'approbation côté collaborateur
                    return [$amount, $fee, $commission];
                }

                // Sinon: flux actuel sans vérification ni débit du master
                $cardType = $data['card_type'] ?? null;
                if ($master->hasCommissions($opType->id, $cardType)) {
                    [$newAmount, $fee] = $opType->getFee($amountInput, $cardType);
                    $commission = $opType->getCommission($amountInput, $cardType);
                    $amount = $newAmount;
                } else {
                    if ($opType->code === 'card_recharge') {
                        $fee = 0;
                    } else {
                        $fee = $amountInput <= 500000 ? 100 : 200;
                    }
                    $amount = $amountInput < $fee ? 0 : $amountInput - $fee;
                    $commission = 0;
                }
                // Commission partenaire n'est pas prise en compte si créé par un collaborateur
                if (($data['created_by_collab'] ?? 'false') === 'true') {
                    $commission = 0;
                }
                return [$amount, $fee, $commission];
        }
    }

    protected function buildStoreData($opType, $partner, $data, $specialResult)
    {
        // Create store data
        $storeData = [
            'operation_type_id' => $opType->id,
            'partner_id' => $partner->id,
            'company_id' => $partner->company_id,
            'code' => $opType->nextCode(),
            'data' => []
        ];

        // Add fields data
        foreach ($opType->fields as $fieldName => $fieldData) {
            if ($fieldData->stored) {
                if (is_array($fieldData->required)) {
                    $storeData['data'][$fieldName] =
                        $data[$fieldData->required[0]] === $fieldData->required[1]
                        ? $data[$fieldName]
                        : null;
                } else {
                    $storeData['data'][$fieldName] = $data[$fieldName];
                }
            }
        }

        // Save amounts except for account_recharge and balance_withdrawal
        if (!in_array($opType->code, ['account_recharge', 'balance_withdrawal'])) {
            [$amount, $fee, $commission] = $specialResult;
            $storeData['amount'] = $amount;
            $storeData['fee'] = $fee;
            $storeData['commission'] = $commission;
            // Persist manual platform commission in data if provided
            if (isset($data['manual_platform_commission']) && $data['manual_platform_commission'] !== '') {
                $plat = (int) round(floatval(str_replace([',', ' '], ['.', ''], $data['manual_platform_commission'])));
                $storeData['data']['platform_commission'] = max(0, $plat);
            }
        }

        return $storeData;
    }

    public function createOperation($partner, $opType, $data)
    {
        $master = $partner->getMaster();

        DB::beginTransaction();

        try {
            // Handle images if any
            foreach ($opType->fields as $fieldName => $fieldData) {
                if ($fieldData->stored && $fieldData->type === 'file') {
                    $data[$fieldName] = saveFile($data[$fieldName]);
                }
            }

            // Create the operation
            $specialResult = $this->applySpecialStore($opType, $master, $data);
            $storeData = $this->buildStoreData($opType, $partner, $data, $specialResult);
            $obj = Operation::create($storeData);

            // Create an operation statement if not account_recharge
            if ($opType->code !== 'account_recharge') {
                $this->statementService->createOperationStatement($obj, $master);
            }

            // Add partner's history
            History::create([
                'user_id' => $partner->user_id,
                'title' => $this->renderText($opType, static::STORE_PARTNER_HISTORY_TITLE, $obj),
                'content' => $this->renderText($opType, static::STORE_PARTNER_HISTORY_CONTENT, $obj),
            ]);

            // Add reviewers notifications
            $revNot = new Notification();
            $revNot->subject = $this->renderText($opType, static::STORE_REV_NOT_SUBJECT, $obj);
            $revNot->body = $this->renderText($opType, static::STORE_REV_NOT_BODY, $obj);
            $revNot->icon_class = 'fas fa-clock';
            $revNot->link = config('app.app_baseurl') . "/operations/{$opType->code}/{$obj->id}";
            $revNot->broadcastToActiveReviewers();

            // Email all reviewers
            // Mail::to(User::activeReviewers())->send(new \App\Mail\OperationPending($this->opType, $obj));

            DB::commit();

            return [null, $obj];
        } catch (Exception $e) {
            DB::rollBack();

            // Delete all the files
            foreach ($opType->fields as $fieldName => $fieldData) {
                if ($fieldData->stored && $fieldData->type === 'file') {
                    removeFile($data[$fieldName]);
                }
            }

            return [['message' => $e->getMessage()], null];
        }
    }

    protected function buildUpdateData($opType, $data, $obj, $objData, $specialResult)
    {
        // Update fields values
        foreach ($opType->fields as $fieldName => $fieldData) {
            if ($fieldData->updated) {
                if (is_array($fieldData->required)) {
                    $objData->$fieldName = $data[$fieldData->required[0]] === $fieldData->required[1]
                        ? $data[$fieldName]
                        : null;
                } else {
                    $objData->$fieldName = $data[$fieldName] ?? $objData->$fieldName;
                }
            }
        }
        // Persist manual platform commission in data if provided during update
        if (isset($data['manual_platform_commission']) && $data['manual_platform_commission'] !== '') {
            $plat = (int) round(floatval(str_replace([',', ' '], ['.', ''], $data['manual_platform_commission'])));
            $objData->platform_commission = max(0, $plat);
        }
        $obj->data = $objData;

        // Manage commissions except for account recharges and balance withdrawals
        if (!in_array($opType->code, ['account_recharge', 'balance_withdrawal'])) {
            [$amount, $fee, $commission] = $specialResult;
            $obj->amount = $amount;
            $obj->fee = $fee;
            $obj->commission = $commission;
        }
    }

    public function updateOperation($partner, $opType, $obj, $data)
    {
        $master = $partner->getMaster();

        $objData = $obj->data;

        $oldPics = [];

        DB::beginTransaction();

        try {
            // Handle images and backup old files
            foreach ($opType->fields as $fieldName => $fieldData) {
                if ($fieldData->updated && $fieldData->type === 'file') {
                    if ($data[$fieldName] = saveFile($data[$fieldName])) {
                        $oldPics[] = $objData->$fieldName;
                    }
                }
            }

            // Update the operation
            $specialResult = $this->applySpecialStore($opType, $master, $data);
            $this->buildUpdateData($opType, $data, $obj, $objData, $specialResult);
            $obj->status = 'pending';
            $obj->feedback = null;
            $obj->reviewer_id = null;
            $obj->reviewed_at = null;
            $obj->save();

            // Create operation statement if not account_recharge
            if ($opType->code !== 'account_recharge') {
                $this->statementService->createOperationStatement($obj, $master);
            }

            // Add partner's history
            History::create([
                'user_id' => $partner->user_id,
                'title' => $this->renderText($opType, static::UPDATE_PARTNER_HISTORY_TITLE, $obj),
                'content' => $this->renderText($opType, static::UPDATE_PARTNER_HISTORY_CONTENT, $obj)
            ]);

            // Add reviewers notifications
            $revNot = new Notification();
            $revNot->subject = $this->renderText($opType, static::UPDATE_REV_NOT_SUBJECT, $obj);
            $revNot->body = $this->renderText($opType, static::UPDATE_REV_NOT_BODY, $obj);
            $revNot->icon_class = 'fas fa-edit';
            $revNot->link = config('app.app_baseurl') . "/operations/{$opType->code}/{$obj->id}";
            $revNot->broadcastToActiveReviewers();

            // Email all reviewers
            // Mail::to(User::activeReviewers())->send(new \App\Mail\OperationUpdate($this->opType, $obj));

            DB::commit();

            // Delete old files if any
            foreach ($oldPics as $oldPic) {
                removeFile($oldPic);
            }

            return [null, $obj];
        } catch (Exception $e) {
            DB::rollBack();

            // Delete all the files
            foreach ($opType->fields as $fieldName => $fieldData) {
                if ($fieldData->updated && $fieldData->type === 'file') {
                    if (isset($data[$fieldName])) {
                        removeFile($data[$fieldName]);
                    }
                }
            }

            return [['message' => $e->getMessage()], null];
        }
    }

    public function approveOperation($opType, $obj, $data, $reviewer)
    {
        $master = $obj->partner->getMaster();

        DB::beginTransaction();

        try {
            // Special updates for account recharges
            if ($opType->code === 'account_recharge') {
                $master->balance += $obj->data->trans_amount;
                $master->save();
            }

            // The operation commission can now be withdrawn
            if (!in_array($opType->code, ['account_recharge', 'balance_withdrawal'])) {
                $obj->withdrawn = false;
            }

            // Mark the operation as approved
            $obj->status = 'approved';

            if (
                $data['without_commission'] === 'true' &&
                (
                    $master->hasCommissions($opType->id, $obj->data->card_type ?? null) ||
                    $opType->code === 'card_activation'
                )
            ) {
                $obj->commission = 0;
            }

            $obj->feedback = $data['feedback'] ?? null;
            $obj->reviewer_id = $reviewer->id;
            $obj->reviewed_at = Carbon::now();
            $obj->save();

            // Create operation statement if account_recharge
            if ($opType->code === 'account_recharge') {
                $this->statementService->createOperationStatement($obj, $master);
            }

            // Update the card holder's information
            if (in_array($opType->code, ['card_activation', 'card_recharge', 'card_deactivation'])) {
                CardHolder::updateOrCreate([
                    'card_id' => $obj->data->card_id
                ], [
                    'card_type' => $obj->data->card_type,
                    'uba_type' => $obj->data->uba_type ?? '',
                    'card_four_digits' => $obj->data->card_four_digits ?? '',
                    'client_first_name' => $obj->data->client_first_name ?? '',
                    'client_last_name' => $obj->data->client_last_name ?? ''
                ]);
            }

            // Add reviewer's history
            History::create([
                'user_id' => $reviewer->id,
                'title' => $this->renderText($opType, static::APPROVE_REV_HISTORY_TITLE, $obj),
                'content' => $this->renderText($opType, static::APPROVE_REV_HISTORY_CONTENT, $obj)
            ]);

            // Add partner's notification
            Notification::create([
                'recipient_id' => $obj->partner->user_id,
                'subject' => $this->renderText($opType, static::APPROVE_PARTNER_NOT_SUBJECT, $obj),
                'body' => $this->renderText($opType, static::APPROVE_PARTNER_NOT_BODY, $obj),
                'icon_class' => 'fas fa-thumbs-up',
                'link' => config('app.app_baseurl') . "/operations/{$opType->code}/{$obj->id}"
            ]);

            // Email the partner
            // Mail::to($obj->partner->user->email)->send(new \App\Mail\OperationApprove($this->opType, $obj));

            DB::commit();

            return [null, $obj];
        } catch (Exception $e) {
            DB::rollBack();

            return [['message' => $e->getMessage()], null];
        }
    }

    protected function applySpecialCancel($opType, $obj, $master)
    {
        // Create operation reversal statement if not account_recharge
        if ($opType->code !== 'account_recharge') {
            $this->statementService->createOperationStatement($obj, $master, true);
        }

        // Handle specific case for balance withdrawals
        if ($opType->code === 'balance_withdrawal') {
            $master->balance += $obj->data->amount * 1.02;
            $master->save();
        }
        // Ne plus ajuster le solde du master pour les autres opérations:
        // le débit/crédit est géré sur le solde du collaborateur au moment de l'approbation.
    }

    public function rejectOperation($opType, $obj, $data, $reviewer)
    {
        $master = $obj->partner->getMaster();

        DB::beginTransaction();

        try {
            $this->applySpecialCancel($opType, $obj, $master);

            // Mark the operation as rejected
            $obj->status = 'rejected';
            $obj->feedback = $data['feedback'] ?? null;
            $obj->reviewer_id = $reviewer->id;
            $obj->reviewed_at = Carbon::now();
            $obj->save();

            // Add reviewer's history
            History::create([
                'user_id' => $reviewer->id,
                'title' => $this->renderText($opType, static::REJECT_REV_HISTORY_TITLE, $obj),
                'content' => $this->renderText($opType, static::REJECT_REV_HISTORY_CONTENT, $obj),
            ]);

            // Add partner's notification
            Notification::create([
                'recipient_id' => $obj->partner->user_id,
                'subject' => $this->renderText($opType, static::REJECT_PARTNER_NOT_SUBJECT, $obj),
                'body' => $this->renderText($opType, static::REJECT_PARTNER_NOT_BODY, $obj),
                'icon_class' => 'fas fa-thumbs-down',
                'link' => config('app.app_baseurl') . "/operations/{$opType->code}/{$obj->id}"
            ]);

            // Email the partner
            // Mail::to($obj->partner->user->email)->send(new \App\Mail\OperationReject($this->opType, $obj));

            DB::commit();

            return [null, $obj];
        } catch (Exception $e) {
            DB::rollBack();

            return [['message' => $e->getMessage()], null];
        }
    }

    public function deleteOperation($partner, $opType, $obj)
    {
        $master = $partner->getMaster();

        DB::beginTransaction();

        try {
            $this->applySpecialCancel($opType, $obj, $master);

            $obj->delete();

            // Add partner's history
            History::create([
                'user_id' => $partner->user_id,
                'title' => $this->renderText($opType, static::DELETE_PARTNER_HISTORY_TITLE, $obj),
                'content' => $this->renderText($opType, static::DELETE_PARTNER_HISTORY_CONTENT, $obj),
            ]);

            // Add reviewers notifications
            $revNot = new Notification();
            $revNot->subject = $this->renderText($opType, static::DELETE_REV_NOT_SUBJECT, $obj);
            $revNot->body = $this->renderText($opType, static::DELETE_REV_NOT_BODY, $obj);
            $revNot->icon_class = 'fas fa-trash';
            $revNot->link = config('app.app_baseurl') . "/operations/{$opType->code}";
            $revNot->broadcastToActiveReviewers();

            // Email all reviewers
            // Mail::to(User::activeReviewers())->send(new \App\Mail\OperationDelete($this->opType, $obj));

            DB::commit();

            // Remove files
            foreach ($opType->fields as $fieldName => $fieldData) {
                if ($fieldData->type === 'file') {
                    removeFile($obj->data->$fieldName);
                }
            }

            return [null, true];
        } catch (Exception $e) {
            DB::rollBack();

            return [['message' => $e->getMessage()], null];
        }
    }

    protected function getOperationListQuery($opType, $data, $user)
    {
        $status = $data['status'] ?? null;
        $partnerId = $data['partner_id'] ?? null;
        $fromDate = $data['from_date'] ?? null;
        $toDate = $data['to_date'] ?? null;
        $cardType = $data['card_type'] ?? null;
        $ubaType = $data['uba_type'] ?? null;

        $query = DB::table('operations')
            ->join('partners', 'partner_id', 'partners.id')
            ->join('users', 'user_id', 'users.id')
            ->where('operation_type_id', $opType->id)
            ->when($status, function ($q, $status) {
                $q->where('operations.status', $status);
            })
            ->when($user->hasRole('partner-master'), function ($q) use ($user) {
                $q->where('operations.company_id', $user->company_id);
            })
            ->when($user->hasRole('partner-pos'), function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->when($user->hasRole('reviewer') && $partnerId, function ($q) use ($partnerId) {
                $q->where('partners.id', $partnerId);
            })
            ->when($fromDate, function ($q, $fromDate) use ($status) {
                $q->where(
                    in_array($status, [null, 'pending'])
                        ? 'operations.created_at'
                        : 'operations.reviewed_at',
                    '>=',
                    $fromDate
                );
            })
            ->when($toDate, function ($q, $toDate) use ($status) {
                $q->where(
                    in_array($status, [null, 'pending'])
                        ? 'operations.created_at'
                        : 'operations.reviewed_at',
                    '<',
                    $toDate
                );
            })
            ->when($cardType, function ($q) use ($cardType) {
                $q->where('operations.card_type', $cardType);
            })
            ->when($user->hasRole('reviewer') && $ubaType, function ($q) use ($ubaType) {
                $q->where('operations.uba_type', $ubaType);
            })
            ->selectRaw("
                operations.id,
                operations.code,
                data,
                amount,
                fee,
                commission,
                CASE 
                    WHEN operations.status = 'pending' THEN 'En attente'
                    WHEN operations.status = 'approved' THEN 'Validée'
                    WHEN operations.status = 'rejected' THEN 'Rejetée'
                END AS status,
                partner_id,
                CONCAT(last_name, ' ', first_name) AS partner,
                users.code AS partner_code,
                operations.created_at,
                operations.reviewed_at,
                operations.feedback,

                card_id,
                decoder_number
            ");

        return $query;
    }

    public function getListOperations($opType, $data, $user, $dtParams)
    {
        $subQuery = $this->getOperationListQuery($opType, $data, $user);
        $dtParams['builder'] = DB::query()->fromSub($subQuery, 'sub');
        return $this->datatableService->fetch($dtParams);
    }

    public function getExcelOperation($opType, $data, $user)
    {
        $rows = $this->getOperationListQuery($opType, $data, $user)
            ->orderBy('operations.id')
            ->lazy(10000);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Add header row
        $rowNum = 1;
        $colNum = 1;
        $sheet->setCellValue(Coordinate::stringFromColumnIndex($colNum) . $rowNum, 'No');

        foreach ($opType->sorted_fields as [$fieldName, $fieldData]) {
            if ($fieldData->listed) {
                $colNum++;
                $sheet->setCellValue(Coordinate::stringFromColumnIndex($colNum) . $rowNum, $fieldData->label);
            }
        }

        // Add data rows
        $rowNum = 2;

        foreach ($rows as $rowData) {
            $colNum = 1;
            $rowData->data = json_decode($rowData->data);
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($colNum) . $rowNum, $rowNum - 1);
            foreach ($opType->sorted_fields as [$fieldName, $fieldData]) {
                if ($fieldData->listed) {
                    $colNum++;
                    $sheet->setCellValue(Coordinate::stringFromColumnIndex($colNum) . $rowNum, $rowData->data->{$fieldName});
                }
            }
            $rowNum++;
        }

        return new Xlsx($spreadsheet);
    }

    public function getPdfOperation($opType, $data, $user)
    {
        $rows = $this->getOperationListQuery($opType, $data, $user)
            ->orderBy('operations.id')
            ->lazy(10000);

        $options = new Options();
        $options->set('defaultFont', 'sans-serif');

        $pdf = new Dompdf($options);

        $partnerId = $data['partner_id'] ?? null;
        $status = $data['status'] ?? null;
        $fromDate = $data['from_date'] ?? null;
        $toDate = $data['to_date'] ?? null;

        $html = view('operations.export-pdf', [
            'partner' => $partnerId ? Partner::with('user')->find($partnerId) : null,
            'status' => [
                '' => 'Tout',
                'pending' => 'En attente',
                'approved' => 'Validée',
                'rejected' => 'Rejetée'
            ][$status],
            'fromDate' => $fromDate,
            'toDate' => $toDate ? Carbon::parse($toDate)->subDay()->format('Y-m-d') : '',
            'rows' => $rows,
            'opType' => $opType
        ])->render();

        $pdf->loadHtml($html);

        $pdf->setPaper('A4', 'landscape');

        $pdf->render();

        return $pdf;
    }
}
