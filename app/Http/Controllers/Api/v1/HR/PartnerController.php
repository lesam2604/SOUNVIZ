<?php

namespace App\Http\Controllers\Api\v1\HR;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\History;
use App\Models\Notification;
use App\Models\Operation;
use App\Models\OperationType;
use App\Models\OperationTypePartner;
use App\Models\Partner;
use App\Models\User;
use App\Services\StatementService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use stdClass;

class PartnerController extends Controller
{
    private $statementService;

    public function __construct()
    {
        $this->statementService = app(StatementService::class);
    }

    public function dashboardData(Request $request)
    {
        $data = $request->validate([
            'from_date' => 'nullable|date',
            'to_date' => 'nullable|date'
        ]);

        if ($data['to_date'] ?? null) {
            $data['to_date'] = Carbon::parse($data['to_date'])->addDay()->format('Y-m-d');
        }

        $r = [];

        $authUser = $request->user();
        $partner = $authUser->partner;

        foreach (OperationType::all() as $operationType) {
            foreach (['pending', 'approved', 'rejected'] as $status) {
                $r["{$operationType->code}_{$status}"] = Operation::query()
                    ->where('operation_type_id', $operationType->id)
                    ->when($authUser->hasRole('partner-master'), function ($q) use ($partner) {
                        $q->where('company_id', $partner->company_id);
                    })
                    ->when($authUser->hasRole('partner-pos'), function ($q) use ($partner) {
                        $q->where('partner_id', $partner->id);
                    })
                    ->where('status', $status)
                    ->when($data['from_date'] ?? null, function ($q, $fromDate) {
                        $q->where('created_at', '>=', $fromDate);
                    })->when($data['to_date'] ?? null, function ($q, $toDate) {
                        $q->where('created_at', '<', $toDate);
                    })
                    ->count();
            }
        }

        $r['sent_money_transfers'] = DB::table('money_transfers')
            ->where('sender_id', $partner->id)
            ->when($data['from_date'] ?? null, function ($q, $fromDate) {
                $q->where('created_at', '>=', $fromDate);
            })->when($data['to_date'] ?? null, function ($q, $toDate) {
                $q->where('created_at', '<', $toDate);
            })->count();

        $r['received_money_transfers'] = DB::table('money_transfers')
            ->where('recipient_id', $partner->id)
            ->when($data['from_date'] ?? null, function ($q, $fromDate) {
                $q->where('created_at', '>=', $fromDate);
            })->when($data['to_date'] ?? null, function ($q, $toDate) {
                $q->where('created_at', '<', $toDate);
            })->count();

        $r['withdrawals'] = DB::table('withdrawals')
            ->where('partner_id', $partner->id)
            ->when($data['from_date'] ?? null, function ($q, $fromDate) {
                $q->where('created_at', '>=', $fromDate);
            })->when($data['to_date'] ?? null, function ($q, $toDate) {
                $q->where('created_at', '<', $toDate);
            })->count();

        $r['histories'] = History::where('user_id', $request->user()->id)->latest()->limit(5)->get();

        return response()->json($r);
    }

    public function fetch(Request $request, $id)
    {
        $partner = Partner::with(['user', 'company'])->findOrFail($id);

        $authUser = $request->user();

        if (
            $authUser->hasRole('partner-master') &&
            $authUser->company_id !== $partner->company_id
        ) {
            return response()->json(['message' => 'Non autorisé'], 405);
        }

        $isMaster = $partner->user->hasRole('partner-master');
        $master = $partner->getMaster();
        $master->load('operation_types.operation_type');

        $partner = (object)$partner->toArray();

        $partner->is_master = $isMaster;
        $partner->master = $master;

        return response()->json($partner);
    }

    public function fetchByTerm(Request $request)
    {
        $request->validate([
            'term' => 'nullable|string|max:191',
            'fixed_code' => 'nullable|string'
        ]);

        $results = DB::table('partners')
            ->join('users', 'user_id', 'users.id')
            ->join('companies', 'partners.company_id', 'companies.id')
            ->when($request->user()->hasRole('partner'), function ($q) use ($request) {
                $q->where('users.id', '<>', $request->user()->id);
            })
            ->when(true, function ($q) use ($request) {
                if ($request->fixed_code === 'true') {
                    $q->where('code', $request->term);
                } else {
                    $q->where('name', 'LIKE', "%{$request->term}%")
                        ->orWhere('code', 'LIKE', "%{$request->term}%")
                        ->orWhere('first_name', 'LIKE', "%{$request->term}%")
                        ->orWhere('last_name', 'LIKE', "%{$request->term}%")
                        ->orWhere('address', 'LIKE', "%{$request->term}%");
                }
            })
            ->selectRaw('
                partners.id,
                code,
                first_name,
                last_name,
                phone_number,
                email,
                picture,
                address,
                name AS company_name,
                tin
            ')
            ->limit(10)
            ->get();

        return response()->json($results);
    }

    public function fetchCompaniesByTerm(Request $request)
    {
        $request->validate([
            'term' => 'nullable|string|max:191',
        ]);

        $results = DB::table('companies')
            ->whereIn('status', ['enabled', 'disabled'])
            ->when(true, function ($q) use ($request) {
                $q->where('name', 'LIKE', "%{$request->term}%")
                    ->orWhere('tin', 'LIKE', "%{$request->term}%");
            })
            ->select('companies.id', 'name', 'tin')
            ->limit(10)
            ->get();

        return response()->json($results);
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'first_name' => 'required|string|max:191',
            'last_name' => 'required|string|max:191',
            'phone_number' => [
                'required',
                'string',
                'max:191',
                function ($attribute, $value, $fail) {
                    $exists = User::where('status', '<>', 'rejected')
                        ->where('phone_number', $value)
                        ->exists();

                    if ($exists) {
                        return $fail('Ce numero de telephone est deja pris');
                    }
                }
            ],
            'email' => [
                'required',
                'string',
                'email',
                'confirmed',
                'max:191',
                function ($attribute, $value, $fail) {
                    $exists = User::where('status', '<>', 'rejected')
                        ->where('email', $value)
                        ->exists();

                    if ($exists) {
                        return $fail('Cet email est deja pris');
                    }
                }
            ],
            'picture' => 'required|image',

            'company_name' => 'required|string|max:191',
            'tin' => 'required|string|max:191',

            'idcard_number' => 'required|string|max:191',
            'idcard_picture' => 'required|image',
            'address' => 'required|string|max:191'
        ], [
            '*.required' => 'Ce champs est requis',
            '*.string' => 'Ce champs doit être une chaîne de caractères',
            '*.max' => 'La longueur maximale pour ce champs est de 191 caractères',
            '*.image' => 'Ce champs doit être une image',
            'email.email' => "L'email fournit n'est pas valide",
            'email.confirmed' => "L'email fournit n'a pas ete confirme"
        ]);

        DB::beginTransaction();

        try {
            $data['picture'] = saveFile($data['picture'], true);
            $data['idcard_picture'] = saveFile($data['idcard_picture']);

            $company = Company::create([
                'name' => $data['company_name'],
                'tin' => $data['tin'],
                'status' => 'pending'
            ]);

            $userPartner = User::create([
                'code' => User::nextCode('partner', 'PSZ'),
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'phone_number' => $data['phone_number'],
                'email' => $data['email'],
                'picture' => $data['picture'],
                'status' => 'pending',
                'company_id' => $company->id
            ])->assignRole(['partner', 'partner-master']);

            $partner = Partner::create([
                'user_id' => $userPartner->id,
                'idcard_number' => $data['idcard_number'],
                'idcard_picture' => $data['idcard_picture'],
                'address' => $data['address'],
                'balance' => null,
                'company_id' => $company->id
            ]);

            $revNot = new Notification();
            $revNot->subject = "Nouveau partenaire {$partner->user->full_name} {$partner->user->code} en attente de validation";
            $revNot->body = "Un nouveau partenaire {$partner->user->full_name} {$partner->user->code} souhaite rejoindre la plateforme";
            $revNot->icon_class = 'fas fa-plus-circle';
            $revNot->link = config('app.app_baseurl') . "/partners/{$partner->id}";
            $revNot->broadcastToActiveReviewers();

            $token = createPasswordResetToken($partner->user->email);

            Mail::to(User::activeReviewers())->send(new \App\Mail\PartnerPending($partner));

            Mail::to($partner->user->email)->send(new \App\Mail\PartnerWelcome($partner->user->email, $token));

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            removeFile($data['picture'], true);
            removeFile($data['idcard_picture']);

            return response()->json(['message' => $e->getMessage()], 500);
        }

        return response()->json(['message' => 'Votre adhesion est en attente de validation']);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'first_name' => 'required|string|max:191',
            'last_name' => 'required|string|max:191',
            'phone_number' => [
                'required',
                'string',
                'max:191',
                function ($attribute, $value, $fail) {
                    $exists = User::where('status', '<>', 'rejected')
                        ->where('phone_number', $value)
                        ->exists();

                    if ($exists) {
                        return $fail('Ce numero de telephone est deja pris');
                    }
                }
            ],
            'email' => [
                'required',
                'string',
                'email',
                'confirmed',
                'max:191',
                function ($attribute, $value, $fail) {
                    $exists = User::where('status', '<>', 'rejected')
                        ->where('email', $value)
                        ->exists();

                    if ($exists) {
                        return $fail('Cet email est deja pris');
                    }
                }
            ],
            'picture' => 'required|image',

            'idcard_number' => 'required|string|max:191',
            'idcard_picture' => 'required|image',
            'address' => 'required|string|max:191',
        ], [
            '*.required' => 'Ce champs est requis',
            '*.string' => 'Ce champs doit être une chaîne de caractères',
            '*.max' => 'La longueur maximale pour ce champs est de 191 caractères',
            '*.image' => 'Ce champs doit être une image',
            'email.email' => "L'email fournit n'est pas valide",
            'email.confirmed' => "L'email fournit n'a pas ete confirme"
        ]);

        DB::beginTransaction();

        $user = $request->user();

        try {
            $data['picture'] = saveFile($data['picture'], true);
            $data['idcard_picture'] = saveFile($data['idcard_picture']);

            $userPos = User::create([
                'code' => User::nextCode('partner', 'PSZ'),
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'phone_number' => $data['phone_number'],
                'email' => $data['email'],
                'picture' => $data['picture'],
                'status' => 'enabled',
                'creator_id' => $user->id,
                'company_id' => $user->company_id
            ])->assignRole(['partner', 'partner-pos']);

            $pos = Partner::create([
                'user_id' => $userPos->id,
                'idcard_number' => $data['idcard_number'],
                'idcard_picture' => $data['idcard_picture'],
                'address' => $data['address'],
                'balance' => 0,
                'company_id' => $user->company_id
            ]);

            History::create([
                'user_id' => $user->id,
                'title' => "Ajout du POS {$pos->user->full_name} {$pos->user->code}",
                'content' => "Vous avez ajoute le POS {$pos->user->full_name} {$pos->user->code}"
            ]);

            $token = createPasswordResetToken($pos->user->email);

            Mail::to($pos->user->email)->send(new \App\Mail\PartnerAdd($pos->user->email, $token));

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            removeFile($data['picture'], true);
            removeFile($data['idcard_picture']);

            return response()->json(['message' => $e->getMessage()], 500);
        }

        return response()->json(['message' => 'POS ajoute']);
    }

    public function update(Request $request, $id)
    {
        $partner = Partner::with(['user', 'company'])->findOrFail($id);
        $isMaster = $partner->user->hasRole('partner-master');

        $reviewer = $request->user();

        $rules = [
            'first_name' => 'required|string|max:191',
            'last_name' => 'required|string|max:191',
            'phone_number' => [
                'required',
                'string',
                'max:191',
                function ($attribute, $value, $fail) use ($partner) {
                    $exists = User::where('status', '<>', 'rejected')
                        ->where('phone_number', $value)
                        ->where('id', '<>', $partner->user_id)
                        ->exists();

                    if ($exists) {
                        return $fail('Ce numero de telephone est deja pris');
                    }
                }
            ],
            'email' => [
                'required',
                'string',
                'email',
                'confirmed',
                'max:191',
                function ($attribute, $value, $fail) use ($partner) {
                    $exists = User::where('status', '<>', 'rejected')
                        ->where('email', $value)
                        ->where('id', '<>', $partner->user_id)
                        ->exists();

                    if ($exists) {
                        return $fail('Cet email est deja pris');
                    }
                }
            ],
            'picture' => 'nullable|image',

            'idcard_number' => 'required|string|max:191',
            'idcard_picture' => 'nullable|image',
            'address' => 'required|string|max:191',
        ];

        if ($isMaster) {
            $rules['company_name'] = 'required|string|max:191';
            $rules['tin'] = 'required|string|max:191';
        }

        $data = $request->validate($rules, [
            '*.required' => 'Ce champs est requis',
            '*.string' => 'Ce champs doit être une chaîne de caractères',
            '*.max' => 'La longueur maximale pour ce champs est de 191 caractères',
            '*.image' => 'Ce champs doit être une image',
            'email.email' => "L'email fournit n'est pas valide",
            'email.confirmed' => "L'email fournit n'a pas ete confirme"
        ]);

        $oldPics = [];

        DB::beginTransaction();

        try {
            if ($data['picture'] ?? null) {
                $data['picture'] = saveFile($data['picture'], true);
                $oldPics['picture'] = $partner->user->picture;
            }

            if ($data['idcard_picture'] ?? null) {
                $data['idcard_picture'] = saveFile($data['idcard_picture']);
                $oldPics['idcard_picture'] = $partner->idcard_picture;
            }

            $partner->update([
                'idcard_number' => $data['idcard_number'],
                'idcard_picture' => $data['idcard_picture'] ?? $partner->idcard_picture,
                'address' => $data['address'],
            ]);

            $partner->user->update([
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'phone_number' => $data['phone_number'],
                'email' => $data['email'],
                'picture' => $data['picture'] ?? $partner->user->picture,
            ]);

            if ($isMaster) {
                $partner->company->update([
                    'name' => $data['company_name'],
                    'tin' => $data['tin'],
                ]);
            }

            History::create([
                'user_id' => $reviewer->id,
                'title' => "Mise a jour des informations du compte {$partner->user->full_name} ({$partner->user->code})",
                'content' => "Vous avez mis a jour les informations du compte {$partner->user->full_name} ({$partner->user->code})"
            ]);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            if ($data['picture'] ?? null) {
                removeFile($data['picture'], true);
            }

            if ($data['idcard_picture'] ?? null) {
                removeFile($data['idcard_picture']);
            }

            throw $e;
        }

        if ($oldPics['picture'] ?? null) {
            removeFile($oldPics['picture'], true);
        }

        if ($oldPics['idcard_picture'] ?? null) {
            removeFile($oldPics['idcard_picture']);
        }

        return response()->json(['message' => 'Informations mise a jour']);
    }

    public function approve(Request $request, $id)
    {
        $partner = Partner::with(['user', 'company'])->findOrFail($id);

        if ($partner->user->status !== 'pending') {
            return response()->json(['message' => 'Non autorisé'], 405);
        }

        $reviewer = $request->user();

        DB::beginTransaction();

        try {
            $partner->update(['balance' => 0]);
            $partner->company->update(['status' => 'enabled']);
            $partner->user->update([
                'status' => 'enabled',
                'reviewer_id' => $reviewer->id,
                'reviewed_at' => Carbon::now()
            ]);

            $partner->setHasCommissions(true);

            History::create([
                'user_id' => $reviewer->id,
                'title' => "Validation de la demande d'adhesion du partenaire {$partner->user->full_name} {$partner->user->code}",
                'content' => "Vous avez valide la demande d'adhesion du partenaire {$partner->user->full_name} {$partner->user->code}"
            ]);

            Notification::create([
                'recipient_id' => $partner->user_id,
                'subject' => 'Adhesion validée',
                'body' => "Votre adhesion a été validée par {$reviewer->full_name}",
                'icon_class' => 'fas fa-thumbs-up',
                'link' => config('app.app_baseurl') . "/dashboard"
            ]);

            $token = createPasswordResetToken($partner->user->email);
            Mail::to($partner->user->email)->send(new \App\Mail\PartnerApprove($partner->user->email, $token));

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 500);
        }

        return response()->json(['message' => 'Partenaire approuvé']);
    }

    public function reject(Request $request, $id)
    {
        $partner = Partner::with(['user', 'company'])->findOrFail($id);

        if ($partner->user->status !== 'pending') {
            return response()->json(['message' => 'Non autorisé'], 405);
        }

        $reviewer = $request->user();

        $data = $request->validate([
            'feedback' => 'nullable|string|max:1000',
        ]);

        DB::beginTransaction();

        try {
            $partner->company->update(['status' => 'rejected']);

            $partner->user->update([
                'status' => 'rejected',
                'feedback' => $data['feedback'] ?? null,
                'reviewer_id' => $reviewer->id,
                'reviewed_at' => Carbon::now(),
            ]);

            History::create([
                'user_id' => $reviewer->id,
                'title' => "Rejet de la demande d’adhésion du partenaire {$partner->user->full_name} {$partner->user->code}",
                'content' => "Vous avez rejeté la demande d’adhésion du partenaire {$partner->user->full_name} {$partner->user->code}"
            ]);

            Mail::to($partner->user->email)->send(new \App\Mail\PartnerReject());

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 500);
        }

        return response()->json(['message' => 'Adhesion du partenaire rejetée']);
    }

    public function changeStatus(Request $request, $id)
    {
        $reviewer = $request->user();

        $data = $request->validate([
            'status' => 'required|string|in:enabled,disabled',
        ]);

        $partner = Partner::with('user')->findOrFail($id);

        if ($partner->user->status === 'pending') {
            return response()->json(['message' => 'Non autorisé'], 405);
        }

        if ($reviewer->hasRole('partner-master') && $partner->company_id !== $reviewer->company_id) {
            return response()->json(['message' => 'Non autorisé'], 405);
        }

        DB::beginTransaction();

        try {
            $partner->user->update(['status' => $data['status']]);

            History::create([
                'user_id' => $reviewer->id,
                'title' => ($partner->user->status === 'enabled' ? 'Activation' : 'Désactivation') . " du compte {$partner->user->full_name} {$partner->user->code}",
                'content' => "Vous avez " . ($partner->user->status === 'enabled' ? 'activé' : 'désactivé') . " le compte de {$partner->user->full_name} {$partner->user->code}"
            ]);

            Mail::to($partner->user->email)->send(new \App\Mail\PartnerChangeStatus($partner));

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 500);
        }

        return response()->json(['message' => 'Compte ' . ($partner->user->status === 'enabled' ? 'activé' : 'désactivé')]);
    }

    public function changeCommissionsStatus(Request $request, $id)
    {
        $reviewer = $request->user();

        $data = $request->validate([
            'status' => 'required|string|in:enabled,disabled',
            'operation_type_id' => 'required|numeric|exists:operation_types,id',
            'card_type' => 'nullable|string|exists:card_types,name'
        ]);

        $partner = Partner::with('user')->findOrFail($id);
        $opType = OperationType::find($data['operation_type_id']);

        if (
            !in_array($partner->user->status, ['enabled', 'disabled']) ||
            !$partner->user->hasRole('partner-master') ||
            in_array($opType->code, ['account_recharge', 'balance_withdrawal'])
        ) {
            return response()->json(['message' => 'Non autorisé'], 405);
        }

        DB::beginTransaction();

        try {
            $otp = OperationTypePartner::updateOrCreate([
                'partner_id' => $partner->id,
                'operation_type_id' => $data['operation_type_id'],
                'card_type' => $data['card_type'],
            ], [
                'has_commissions' => $data['status'] === 'enabled'
            ]);

            History::create([
                'user_id' => $reviewer->id,

                'title' => ($otp->has_commissions ? 'Activation' : 'Désactivation') .
                    " des commissions de {$partner->user->full_name} {$partner->user->code} pour {$opType->name}" .
                    ($data['card_type'] ? " ({$data['card_type']})" : ''),

                'content' => "Vous avez " . ($otp->has_commissions ? 'activé' : 'désactivé') .
                    " les commissions de {$partner->user->full_name} {$partner->user->code} pour {$opType->name}" .
                    ($data['card_type'] ? " ({$data['card_type']})" : ''),
            ]);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 500);
        }

        return response()->json(['message' => "Commissions " . ($otp->has_commissions ? 'activées' : 'désactivées')]);
    }

    public function destroy(Request $request, $id)
    {
        $reviewer = $request->user();

        $partner = Partner::with('user')->findOrFail($id);

        if ($reviewer->hasRole('partner-master') && $partner->company_id !== $reviewer->company_id) {
            return response()->json(['message' => 'Non autorisé'], 405);
        }

        DB::beginTransaction();

        try {
            $partner->user->delete();

            History::create([
                'user_id' => $reviewer->id,
                'title' => "Suppression du compte {$partner->user->full_name} {$partner->user->code}",
                'content' => "Vous avez supprimé le compte {$partner->user->full_name} {$partner->user->code}"
            ]);

            Mail::to($partner->user->email)->send(new \App\Mail\PartnerDestroy());

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 500);
        }

        // We need to find a way to delete all the images for all activities the partner is tied to
        // Also delete teh profile picture and others images related to the partner

        return response()->json(['message' => "Compte {$partner->user->full_name} {$partner->user->code} supprimé"]);
    }

    protected function getListQuery(Request $request)
    {
        $request->validate([
            'status' => 'nullable|string|in:pending,enabled,disabled,rejected',
            'role' => 'nullable|string|in:partner-master,partner-pos',
            'company_id' => 'nullable|numeric|exists:companies,id',
        ]);

        $authUser = $request->user();

        return DB::table('partners')
            ->leftJoin('users AS partner_user', 'user_id', 'partner_user.id')
            ->leftJoin('companies', 'partner_user.company_id', 'companies.id')

            ->leftJoin('model_has_roles', 'partner_user.id', '=', 'model_has_roles.model_id')
            ->leftJoin('roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->whereIn('roles.name', ['partner-master', 'partner-pos'])

            ->when($authUser->hasRole('reviewer'), function ($q) use ($request) {
                if ($request->role) {
                    $q->where('roles.name', $request->role);
                }
                if ($request->company_id) {
                    $q->where('partner_user.company_id', $request->company_id);
                }
            })

            ->when($authUser->hasRole('partner-master'), function ($q) use ($authUser) {
                $q->where('roles.name', 'partner-pos')
                    ->where('partner_user.company_id', $authUser->company_id);
            })

            ->when($request->status, function ($q, $status) {
                $q->where('partner_user.status', $status);
            })

            ->select(
                'partners.id',
                'idcard_number',
                'address',
                'balance',
                'idcard_picture',

                'partner_user.code',
                'partner_user.first_name',
                'partner_user.last_name',
                'partner_user.phone_number',
                'partner_user.email',
                'partner_user.status',
                'partner_user.picture',
                'partner_user.created_at',
                'partner_user.reviewed_at',

                'companies.name AS company_name',

                'roles.name AS role'
            );
    }

    public function list(Request $request)
    {
        $params = new stdClass;
        $params->builder = DB::query()->fromSub($this->getListQuery($request), 'sub');
        return fetchListData($request, $params);
    }

    public function totalBalances(Request $request)
    {
        $totalBalances = $this->getListQuery($request)->sum('balance');
        return response()->json($totalBalances);
    }

    public function performances(Request $request)
    {
        $request->validate([
            'partner_id' => 'nullable|numeric|exists:partners,id',
            'from_date' => 'nullable|date',
            'to_date' => 'nullable|date'
        ]);

        if ($request->to_date) {
            $request->merge(['to_date' => Carbon::parse($request->to_date)->addDay()->format('Y-m-d')]);
        }

        $summary = DB::table('operations')
            ->where('status', 'approved')
            ->when($request->partner_id, function ($q, $partnerId) {
                $q->where('partner_id', $partnerId);
            })
            ->when($request->from_date, function ($q, $fromDate) {
                $q->where('created_at', '>=', $fromDate);
            })
            ->when($request->to_date, function ($q, $toDate) {
                $q->where('created_at', '<', $toDate);
            })
            ->groupBy('operation_type_id', 'card_type')
            ->selectRaw('
                operation_type_id AS id,
                card_type,
                COUNT(*) AS nb,
                SUM(amount) AS amount,
                SUM(fee) AS fee,
                SUM(commission) AS commission,
                SUM(JSON_UNQUOTE(JSON_EXTRACT(`data`, "$.trans_amount"))) AS data_trans_amount,
                SUM(JSON_UNQUOTE(JSON_EXTRACT(`data`, "$.amount"))) AS data_amount
            ')
            ->get();

        $opTypes = OperationType::orderBy('position')->get()->keyBy('code');
        $perfs = collect();

        foreach ($opTypes as $opType) {
            if (in_array($opType->code, ['card_activation', 'card_recharge', 'card_deactivation'])) {
                $cardTypes = $opTypes['card_activation']->fields->card_type->options;
            } else {
                $cardTypes = [null];
            }

            foreach ($cardTypes as $cardType) {
                $sum = $summary->firstWhere(fn($s) => $s->id === $opType->id && $s->card_type === $cardType);

                $perfs->push((object)[
                    'code' => $opType->code,
                    'name' => $opType->name,
                    'icon_class' => $opType->icon_class,
                    'card_type' => $cardType,
                    'nb' => $sum->nb ?? 0,
                    'amount' => $sum->amount ?? 0,
                    'fee' => $sum->fee ?? 0,
                    'commission' => $sum->commission ?? 0,
                    'data_trans_amount' => $sum->data_trans_amount ?? 0,
                    'data_amount' => $sum->data_amount ?? 0
                ]);
            }
        }

        // Ajouter performances des produits livrés (nombre total et montant total)
        $productAgg = DB::table('inv_delivery_product AS idp')
            ->join('inv_deliveries AS id', 'idp.delivery_id', 'id.id')
            ->join('inv_orders AS io', 'id.order_id', 'io.id')
            ->join('inv_order_product AS iop', function ($j) {
                $j->on('iop.order_id', '=', 'io.id')->on('iop.product_id', '=', 'idp.product_id');
            })
            ->when($request->partner_id, function ($q, $partnerId) {
                $q->where('io.partner_id', $partnerId);
            })
            ->when($request->from_date, function ($q, $fromDate) {
                $q->where('id.created_at', '>=', $fromDate);
            })
            ->when($request->to_date, function ($q, $toDate) {
                $q->where('id.created_at', '<', $toDate);
            })
            ->selectRaw('SUM(idp.quantity) AS nb, SUM(idp.quantity * iop.unit_price) AS amount')
            ->first();

        $perfs->push((object) [
            'code' => 'products',
            'name' => 'Produits',
            'icon_class' => 'fas fa-boxes',
            'card_type' => null,
            'nb' => (int) ($productAgg->nb ?? 0),
            'amount' => (float) ($productAgg->amount ?? 0),
            'fee' => 0,
            'commission' => 0,
            'data_trans_amount' => 0,
            'data_amount' => 0,
        ]);

        // Nombre total de cartes vendues (cartes rattachées à une commande)
        $cardsSold = DB::table('cards AS c')
            ->join('card_orders AS co', 'co.id', '=', 'c.card_order_id')
            ->when($request->partner_id, function ($q, $partnerId) {
                $q->where('co.partner_id', $partnerId);
            })
            ->when($request->from_date, function ($q, $fromDate) {
                $q->where('co.created_at', '>=', $fromDate);
            })
            ->when($request->to_date, function ($q, $toDate) {
                $q->where('co.created_at', '<', $toDate);
            })
            ->count('c.id');

        $perfs->push((object) [
            'code' => 'cards_sold',
            'name' => 'Cartes vendues',
            'icon_class' => 'fas fa-credit-card',
            'card_type' => null,
            'nb' => (int) $cardsSold,
            'amount' => 0,
            'fee' => 0,
            'commission' => 0,
            'data_trans_amount' => 0,
            'data_amount' => 0,
        ]);

        // Nombre total de décodeurs vendus (décodeurs rattachés à une commande)
        $decodersSold = DB::table('decoders AS d')
            ->join('decoder_orders AS do', 'do.id', '=', 'd.decoder_order_id')
            ->when($request->partner_id, function ($q, $partnerId) {
                $q->where('do.partner_id', $partnerId);
            })
            ->when($request->from_date, function ($q, $fromDate) {
                $q->where('do.created_at', '>=', $fromDate);
            })
            ->when($request->to_date, function ($q, $toDate) {
                $q->where('do.created_at', '<', $toDate);
            })
            ->count('d.id');

        $perfs->push((object) [
            'code' => 'decoders_sold',
            'name' => 'Décodeurs vendus',
            'icon_class' => 'fas fa-tv',
            'card_type' => null,
            'nb' => (int) $decodersSold,
            'amount' => 0,
            'fee' => 0,
            'commission' => 0,
            'data_trans_amount' => 0,
            'data_amount' => 0,
        ]);

        // Factures (payées / non payées)
        $invoicePaid = DB::table('invoices AS i')
            ->when($request->partner_id, function ($q, $partnerId) {
                $q->where('i.partner_id', $partnerId);
            })
            ->when($request->from_date, function ($q, $fromDate) {
                $q->where('i.created_at', '>=', $fromDate);
            })
            ->when($request->to_date, function ($q, $toDate) {
                $q->where('i.created_at', '<', $toDate);
            })
            ->where('i.status', 'paid')
            ->selectRaw('COUNT(*) AS nb, SUM(total_amount) AS amount')
            ->first();

        $perfs->push((object) [
            'code' => 'invoices_paid',
            'name' => 'Factures payées',
            'icon_class' => 'fas fa-file-invoice-dollar',
            'card_type' => null,
            'nb' => (int) ($invoicePaid->nb ?? 0),
            'amount' => 0,
            'fee' => 0,
            'commission' => 0,
            'data_trans_amount' => 0,
            'data_amount' => (int) ($invoicePaid->amount ?? 0),
        ]);

        $invoiceUnpaid = DB::table('invoices AS i')
            ->when($request->partner_id, function ($q, $partnerId) {
                $q->where('i.partner_id', $partnerId);
            })
            ->when($request->from_date, function ($q, $fromDate) {
                $q->where('i.created_at', '>=', $fromDate);
            })
            ->when($request->to_date, function ($q, $toDate) {
                $q->where('i.created_at', '<', $toDate);
            })
            ->where('i.status', 'unpaid')
            ->selectRaw('COUNT(*) AS nb, SUM(total_amount) AS amount')
            ->first();

        $perfs->push((object) [
            'code' => 'invoices_unpaid',
            'name' => 'Factures non payées',
            'icon_class' => 'fas fa-file-invoice',
            'card_type' => null,
            'nb' => (int) ($invoiceUnpaid->nb ?? 0),
            'amount' => 0,
            'fee' => 0,
            'commission' => 0,
            'data_trans_amount' => 0,
            'data_amount' => (int) ($invoiceUnpaid->amount ?? 0),
        ]);

        return response()->json($perfs);
    }

    public function statementList(Request $request)
    {
        return $this->statementService->listStatement($request);
    }

    public function exportExcelStatement(Request $request)
    {
        set_time_limit(0);

        $writer = $this->statementService->getExcelStatement($request);

        return response()->stream(function () use ($writer) {
            $writer->save('php://output');
        }, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment;filename="' . date('Y_m_d_H_i_s') . '_excel_export.xlsx' . '"',
            'Cache-Control' => 'max-age=0',
        ]);
    }

    public function exportPdfStatement(Request $request)
    {
        set_time_limit(0);

        $pdf = $this->statementService->getPdfStatement($request);

        $pdf->stream(date('Y_m_d_H_i_s') . '_pdf_export.pdf');

        exit;
    }
}
