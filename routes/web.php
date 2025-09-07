<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Admin\OperationCancelController;
use App\Http\Controllers\Collab\CollabOperationCancelController;
use App\Http\Controllers\Admin\CollaboratorBalanceController;
use App\Models\CollaboratorBalance;

// web.php (en local seulement)
Route::get('/_whoami', function () {
    return response()->json([
        'web_auth' => \Illuminate\Support\Facades\Auth::guard('web')->check(),
        'user'     => optional(\Illuminate\Support\Facades\Auth::guard('web')->user())->only('id','email','first_name','last_name'),
    ]);
});

// --- Auth / Login ---
Route::get('/login', fn() => view('auth.login'))->name('login');
Route::post('/login', function (Request $request) {
    $credentials = $request->only('email', 'password');
    if (Auth::guard('web')->attempt($credentials)) {
        $request->session()->regenerate();
        return redirect()->intended('/dashboard');
    }
    return back()->withErrors(['email' => 'Identifiants invalides.'])->withInput();
});

// --- Pages principales ---
Route::get('/', fn() => view('auth.login'));
Route::get('/passwordresetemail', fn() => view('auth.passwordresetemail'));
Route::get('/set-password', fn(Request $request) => view('auth.passwordreset', [
    'email' => $request->email,
    'token' => $request->token
]));
Route::get('/partners/register', fn() => view('partners.register'));
Route::get('/dashboard', fn() => view('dashboards.' . ($_COOKIE['user-type'] ?? 'partner')));

// --- Gestion des opérations ---
Route::prefix('operations')->group(function () {
    Route::get('/card-activation-export', fn() => view('operations.card-activation-export'));
    Route::get('/{opTypeCode}/{opStatus?}', fn($opTypeCode, $opStatus = '') => view('operations.list', compact('opTypeCode', 'opStatus')))
        ->whereIn('opStatus', ['pending', 'approved', 'rejected']);
    Route::get('/{opTypeCode}/create', fn($opTypeCode) => view('operations.create', compact('opTypeCode')));
    Route::get('/{opTypeCode}/{objectId}', fn($opTypeCode, $objectId) => view('operations.view', compact('opTypeCode', 'objectId')));
    Route::get('/{opTypeCode}/{objectId}/edit', fn($opTypeCode, $objectId) => view('operations.create', compact('opTypeCode', 'objectId')));
});

/* =======================
   Annulations d'opérations (ADMIN)
   Solde collaborateurs (ADMIN)
   -> En LOCAL: pas de middleware auth
   -> En PROD: POST protégés par auth:web (GET inchangés)
   ======================= */
/*if (app()->environment('local')) {
    // --- ADMIN: Annulations d'opérations (ouvertes en local) ---
    Route::get('/admin/operations-cancel', [OperationCancelController::class, 'index'])
        ->name('admin.operations-cancel.index');
    Route::get('/admin/operations-cancel/list', [OperationCancelController::class, 'list'])
        ->name('admin.operations-cancel.list');
    Route::post('/admin/operations-cancel/approve/{id}', [OperationCancelController::class, 'approve'])
        ->name('admin.operations-cancel.approve');
    Route::post('/admin/operations-cancel/reject/{id}', [OperationCancelController::class, 'reject'])
        ->name('admin.operations-cancel.reject');

    // --- ADMIN: Solde collaborateurs (ouvert en local) ---
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/collabs/{collab}/balance', [CollaboratorBalanceController::class, 'showByCollab'])
            ->name('collabs.balance.show');
        Route::post('/collabs/{collab}/balance/adjust', [CollaboratorBalanceController::class, 'adjustByCollab'])
            ->name('collabs.balance.adjust');
    });
} else {
    // --- ADMIN: Annulations d'opérations (GET ouverts, POST protégés en prod) ---
    Route::get('/admin/operations-cancel', [OperationCancelController::class, 'index'])
        ->name('admin.operations-cancel.index');
    Route::get('/admin/operations-cancel/list', [OperationCancelController::class, 'list'])
        ->name('admin.operations-cancel.list');

    Route::middleware('auth:web')->group(function () {
        Route::post('/admin/operations-cancel/approve/{id}', [OperationCancelController::class, 'approve'])
            ->name('admin.operations-cancel.approve');
        Route::post('/admin/operations-cancel/reject/{id}', [OperationCancelController::class, 'reject'])
            ->name('admin.operations-cancel.reject');

        // --- ADMIN: Solde collaborateurs (protégé en prod) ---
        Route::prefix('admin')->name('admin.')->group(function () {
            Route::get('/collabs/{collab}/balance', [CollaboratorBalanceController::class, 'showByCollab'])
                ->name('collabs.balance.show');
            Route::post('/collabs/{collab}/balance/adjust', [CollaboratorBalanceController::class, 'adjustByCollab'])
                ->name('collabs.balance.adjust');
        });
    });
}

// =======================
//  Annulations d'opérations (COLLABORATEUR)
//  -> un collaborateur envoie une demande d'annulation
// =======================
Route::middleware('auth:web')->prefix('collab')->name('collab.')->group(function () {
    // Envoi d’une demande d’annulation pour une opération validée
    // Payload attendu: { operation_id: <ID> }
    Route::post('/operations-cancel', [CollabOperationCancelController::class, 'store'])
        ->name('operations-cancel.store');
});*/

// --- Solde du collaborateur connecté (SELF) ---
Route::middleware('auth:web')->get('/me/balance', function () {
    $user = auth()->user();

    $bal = \App\Models\CollaboratorBalance::firstOrCreate(
        ['user_id' => $user->id],
        ['balance' => 0, 'currency' => 'XOF', 'updated_by' => $user->id]
    );

    return response()->json([
        'ok'       => true,
        'balance'  => (int) $bal->balance,
        'currency' => $bal->currency,
    ]);
});

// --- Autres pages ---
Route::get('/card-categories/to-supply', fn() => view('card-categories.list', ['toSupply' => true]));
Route::get('/inv-products/to-supply', fn() => view('inv-products.list', ['toSupply' => true]));

Route::prefix('tickets')->group(function () {
    Route::get('/{objStatus?}', fn($objStatus = '') => view('tickets.list', compact('objStatus')))
        ->whereIn('objStatus', ['responded', 'not-responded']);
    Route::get('/create', fn() => view("tickets.create"));
    Route::get('/{id}', fn($objectId) => view('tickets.view', compact('objectId')));
    Route::get('/{id}/edit', fn($objectId) => view('tickets.create', compact('objectId')));
});

Route::get('/cards/stock/{status?}', fn($status = '') => view('cards.stock', compact('status')));
Route::get('/decoders/stock/{status?}', fn($status = '') => view('decoders.stock', compact('status')));

foreach ([
    'money-transfers', 'withdrawals', 'inv-categories', 'inv-products', 'inv-supplies', 'inv-orders', 'inv-deliveries',
    'card-types', 'card-categories', 'cards', 'card-orders', 'decoders', 'decoder-orders', 'scrolling-messages',
    'broadcast-messages', 'extra-clients', 'invoices'
] as $groupName) {
    Route::prefix($groupName)->group(function () use ($groupName) {
        Route::get('/', fn() => view("{$groupName}.list"));
        Route::get('/create', fn() => view("{$groupName}.create"));
        Route::get('/{id}', fn($objectId) => view("{$groupName}.view", compact('objectId')));
        Route::get('/{id}/edit', fn($objectId) => view("{$groupName}.create", compact('objectId')));
    });
}

Route::prefix('partners')->group(function () {
    Route::get('/create', fn() => view('partners.create'));
    Route::get('/performances', fn() => view('partners.performances'));
    Route::get('/statement', fn() => view('partners.statement'));
    Route::get('/{opStatus?}', fn($opStatus = '') => view('partners.list', compact('opStatus')))
        ->whereIn('opStatus', ['pending', 'enabled', 'disabled', 'rejected']);
    Route::get('/{objectId}', fn($objectId) => view('partners.view', compact('objectId')));
    Route::get('/{objectId}/edit', fn($objectId) => view('partners.edit', compact('objectId')));
});

// Page admin: performances des collaborateurs
Route::get('/admin/collab-performances', fn() => view('admin.collab-performances'));

// Exports de factures (GET accessibles depuis le navigateur)
Route::get('/invoices/export-csv/{id}', [\App\Http\Controllers\Api\v1\InvoiceController::class, 'exportCsv']);
Route::get('/invoices/export-pdf/{id}', [\App\Http\Controllers\Api\v1\InvoiceController::class, 'exportPdf']);

Route::prefix('collabs')->group(function () {
    Route::get('/{opStatus?}', fn($opStatus = '') => view('collabs.list', compact('opStatus')))
        ->whereIn('opStatus', ['pending', 'enabled', 'disabled', 'rejected']);
    Route::get('/create', fn() => view('collabs.create'));
    Route::get('/{objectId}', fn($objectId) => view('collabs.view', compact('objectId')));
    Route::get('/{objectId}/edit', fn($objectId) => view('collabs.create', compact('objectId')));
});

Route::prefix('balance-adjustments')->group(function () {
    Route::get('/', fn() => view('balance-adjustments.list'));
    Route::get('/create', fn() => view('balance-adjustments.create'));
    Route::get('/{objectId}', fn($objectId) => view('balance-adjustments.view', compact('objectId')));
});

Route::get('/commissions/partners', fn() => view('commissions.list-partners'));
Route::get('/commissions/platform', fn() => view('commissions.list-platform'));
Route::get('/settings', fn() => view('settings.view'));
Route::get('/notifications', fn() => view('notifications.list'));
Route::get('/histories', fn() => view('histories.list'));

// routes/web.php (temporaire)
Route::get('/_db-debug', function () {
    return [
        'env' => app()->environment(),
        'default_connection' => config('database.default'),
        'DB_CONNECTION_env' => env('DB_CONNECTION'),
    ];
});

















// Page d’index des demandes (visible par l’admin)
/*Route::get('/admin/operations-cancel', [OperationCancelController::class, 'index'])
    ->name('admin.operations-cancel.index');

// Liste en JSON si votre DataTable l’utilise (optionnel)
Route::get('/admin/operations-cancel/list', [OperationCancelController::class, 'list'])
    ->name('admin.operations-cancel.list');

// Validation et refus des demandes (toujours en session web)
Route::post('/admin/operations-cancel/approve/{id}', [OperationCancelController::class, 'approve'])
    ->name('admin.operations-cancel.approve');

Route::post('/admin/operations-cancel/reject/{id}', [OperationCancelController::class, 'reject'])
    ->name('admin.operations-cancel.reject');
*/


Route::get('/admin/operations-cancel', [OperationCancelController::class, 'index'])
    ->name('admin.operations-cancel.index');
Route::get('/admin/operations-cancel/list', [OperationCancelController::class, 'list'])
    ->name('admin.operations-cancel.list');
Route::post('/admin/operations-cancel/approve/{id}', [OperationCancelController::class, 'approve'])
    ->name('admin.operations-cancel.approve');
Route::post('/admin/operations-cancel/reject/{id}', [OperationCancelController::class, 'reject'])
    ->name('admin.operations-cancel.reject');
