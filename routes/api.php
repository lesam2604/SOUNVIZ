<?php

use App\Http\Controllers\Api\v1\AuthController;
use App\Http\Controllers\Api\v1\BalanceAdjustmentController;
use App\Http\Controllers\Api\v1\BroadcastMessageController;
use App\Http\Controllers\Api\v1\CardHolderController;
use App\Http\Controllers\Api\v1\CountryController;
use App\Http\Controllers\Api\v1\HR\AdminController;
use App\Http\Controllers\Api\v1\HR\CollabController;
use App\Http\Controllers\Api\v1\CommissionController;
use App\Http\Controllers\Api\v1\HistoryController;
use App\Http\Controllers\Api\v1\HR\ExtraClientController;
use App\Http\Controllers\Api\v1\HR\PartnerController;
use App\Http\Controllers\Api\v1\Inventory\CardCategoryController;
use App\Http\Controllers\Api\v1\Inventory\CardController;
use App\Http\Controllers\Api\v1\Inventory\CardOrderController;
use App\Http\Controllers\Api\v1\Inventory\CardTypeController;
use App\Http\Controllers\Api\v1\Inventory\DecoderController;
use App\Http\Controllers\Api\v1\Inventory\DecoderOrderController;
use App\Http\Controllers\Api\v1\Inventory\InvCategoryController;
use App\Http\Controllers\Api\v1\Inventory\InvDeliveryController;
use App\Http\Controllers\Api\v1\Inventory\InvOrderController;
use App\Http\Controllers\Api\v1\Inventory\InvProductController;
use App\Http\Controllers\Api\v1\Inventory\InvSupplyController;
use App\Http\Controllers\Api\v1\MoneyTransferController;
use App\Http\Controllers\Api\v1\NotificationController;
use App\Http\Controllers\Api\v1\OperationController;
use App\Http\Controllers\Api\v1\PublicFileController;
use App\Http\Controllers\Api\v1\ScrollingMessageController;
use App\Http\Controllers\Api\v1\SettingController;
use App\Http\Controllers\Api\v1\TicketController;
use App\Http\Controllers\Api\v1\UserController;
use App\Http\Controllers\Api\v1\WithdrawalController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\v1\OperationCancellationRequestController;
use App\Http\Controllers\Api\v1\InvoiceController;
use App\Http\Controllers\Api\v1\PerformanceController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::prefix('v1')->group(function () {
    Route::middleware('guest.sanctum')->controller(AuthController::class)->group(function () {
        Route::post('login', 'login');
        Route::post('send-password-reset-code', 'sendPasswordResetToken');
        Route::post('reset-password', 'resetPassword');
    });

    Route::post('partners/register', [PartnerController::class, 'register'])->middleware('guest.sanctum');

    Route::middleware(['auth:sanctum', 'enabled'])->group(function () {
        Route::controller(AuthController::class)->group(function () {
            Route::post('logout', 'logout');
            Route::get('user', 'user');
            Route::post('refresh', 'refresh');
            Route::post('change-password', 'changePassword');
        });

        Route::prefix('admins')->controller(AdminController::class)->group(function () {
            Route::get('dashboard-data', 'dashboardData')->role('admin');
        });

        // Solde collaborateurs (recharge/débit par admin)
        Route::prefix('collab-balances')->controller(\App\Http\Controllers\Admin\CollaboratorBalanceController::class)->group(function () {
            Route::get('{collab}/balance', 'showByCollab')->role('admin');
            Route::post('{collab}/balance/adjust', 'adjustByCollab')->role('admin');
        });
        
        
        Route::middleware(['auth:sanctum', 'enabled', 'role:admin'])->prefix('admin')->group(function () {
        Route::get('collabs/{collab}/balance', [\App\Http\Controllers\Admin\CollaboratorBalanceController::class, 'showByCollab']);
        Route::post('collabs/{collab}/balance/adjust', [\App\Http\Controllers\Admin\CollaboratorBalanceController::class, 'adjustByCollab']);
        });


        Route::prefix('collabs')->controller(CollabController::class)->group(function () {
            Route::get('dashboard-data', 'dashboardData')->role('collab');
            Route::get('fetch/{id}', 'fetch')->can('view collab');
            Route::post('store', 'store')->can('add collab');
            Route::post('update/{id}', 'update')->can('edit collab');
            Route::post('change-status/{id}', 'changeStatus')->can('edit collab');
            Route::post('list', 'list')->can('view collab');
            Route::post('delete/{id}', 'destroy')->can('delete collab');
            Route::get('me/balance', 'meBalance')->role('collab');
        });

        Route::prefix('partners')->controller(PartnerController::class)->group(function () {
            Route::get('dashboard-data', 'dashboardData')->role('partner');
            Route::get('fetch/{id}', 'fetch')->can('view partner');
            Route::get('fetch-by-term', 'fetchByTerm')->can('view partner');
            Route::get('fetch-companies-by-term', 'fetchCompaniesByTerm')->can('view partner');
            Route::post('store', 'store')->can('add partner');
            Route::post('update/{id}', 'update')->can('edit partner');
            Route::post('approve/{id}', 'approve')->can('review partner');
            Route::post('reject/{id}', 'reject')->can('review partner');
            Route::post('change-status/{id}', 'changeStatus')->can('edit partner');
            Route::post('change-commissions-status/{id}', 'changeCommissionsStatus')->can('edit partner');
            Route::post('delete/{id}', 'destroy')->can('delete partner');
            Route::post('list', 'list')->can('view partner');
            Route::get('total-balances', 'totalBalances')->can('view partner');
            Route::get('performances', 'performances')->can('view performances');

            Route::post('statement-list', 'statementList')->can('view statement');
            Route::get('export-excel-statement', 'exportExcelStatement')->can('export-excel statement');
            Route::get('export-pdf-statement', 'exportPdfStatement')->can('export-pdf statement');
        });

        Route::prefix('extra-clients')->controller(ExtraClientController::class)->group(function () {
            Route::get('fetch/{ec}', 'fetch')->can('view extra-client');
            Route::get('fetch-all', 'fetchAll')->can('view extra-client');
            Route::post('store', 'store')->can('add extra-client');
            Route::post('update/{ec}', 'update')->can('edit extra-client');
            Route::post('delete/{ec}', 'destroy')->can('delete extra-client');
            Route::post('list', 'list')->can('view extra-client');
        });

        Route::get('operations/uba-types', [OperationController::class, 'ubaTypes'])->role('admin');
        Route::get('operations/card-activation-export-pdf', [OperationController::class, 'cardActivationExportPdf'])->role('admin');

        Route::prefix('operations/{opType}')->controller(OperationController::class)->group(function () {
            Route::get('fetch/{id}', 'fetch')->can('view operation');
            // Autoriser explicitement admin/collab/partner à créer
            Route::post('store', 'store')->role('admin|collab|partner');
            // Création sans sélection de partenaire: admin et collab, avec fallback interne
            Route::post('store-without-partner', 'storeWithoutPartner')->role('admin|collab|reviewer');
            // Création pour un partenaire: admin et collab
            Route::post('store-for-partner/{partner}', 'storeForPartner')->role('admin|collab');
            Route::post('update/{id}', 'update')->can('edit operation');
            // Validation par collab, reviewer et admin
            Route::post('approve/{id}', 'approve')->role('admin|collab|reviewer');
            Route::post('reject/{id}', 'reject')->can('review operation');
            Route::post('delete/{id}', 'destroy')->can('delete operation');
            Route::post('list', 'list')->can('view operation');
            Route::get('export-excel', 'exportExcel')->can('export-excel operation');
            Route::get('export-pdf', 'exportPdf')->can('export-pdf operation');
        });

        Route::prefix('card-holders')->controller(CardHolderController::class)->group(function () {
            Route::get('fetch/{id}', 'fetch');
        });

        Route::prefix('commissions')->controller(CommissionController::class)->group(function () {
            Route::post('list-partners', 'listPartners')->can('view partner_commission');
            Route::get('total-partners', 'totalPartners')->can('view partner_commission');
            Route::post('list-platform', 'listPlatform')->can('view platform_commission');
            Route::get('total-platform', 'totalPlatform')->can('view platform_commission');
        });

        Route::prefix('money-transfers')->controller(MoneyTransferController::class)->group(function () {
            Route::get('fetch/{id}', 'fetch')->can('view money_transfer');
            Route::post('store', 'store')->can('add money_transfer');
            Route::post('list', 'list')->can('view money_transfer');
        });

        Route::prefix('withdrawals')->controller(WithdrawalController::class)->group(function () {
            Route::get('fetch/{id}', 'fetch')->can('view withdrawal');
            Route::post('send-otp-code', 'sendOtpCode')->can('add withdrawal');
            Route::post('store', 'store')->can('add withdrawal');
            Route::post('list', 'list')->can('view withdrawal');
        });

        Route::prefix('inv-categories')->controller(InvCategoryController::class)->group(function () {
            Route::get('fetch/{id}', 'fetch')->can('view inv_category');
            Route::get('fetch-all', 'fetchAll')->can('view inv_category');
            Route::post('store', 'store')->can('add inv_category');
            Route::post('update/{id}', 'update')->can('edit inv_category');
            Route::post('delete/{id}', 'destroy')->can('delete inv_category');
            Route::post('list', 'list')->can('view inv_category');
        });

        Route::prefix('inv-products')->controller(InvProductController::class)->group(function () {
            Route::get('fetch/{id}', 'fetch')->can('view inv_product');
            Route::get('fetch-all', 'fetchAll')->can('view inv_product');
            Route::post('store', 'store')->can('add inv_product');
            Route::post('update/{id}', 'update')->can('edit inv_product');
            Route::post('delete/{id}', 'destroy')->can('delete inv_product');
            Route::post('list', 'list')->can('view inv_product');
        });

        Route::prefix('inv-supplies')->controller(InvSupplyController::class)->group(function () {
            Route::get('fetch/{id}', 'fetch')->can('view inv_supply');
            Route::post('store', 'store')->can('add inv_supply');
            Route::post('update/{id}', 'update')->can('edit inv_supply');
            Route::post('delete/{id}', 'destroy')->can('delete inv_supply');
            Route::post('list', 'list')->can('view inv_supply');
        });

        Route::prefix('inv-orders')->controller(InvOrderController::class)->group(function () {
            Route::get('fetch/{id}', 'fetch')->can('view inv_order');
            Route::get('fetch-all', 'fetchAll')->can('view inv_order');
            Route::post('store', 'store')->can('add inv_order');
            Route::post('update/{id}', 'update')->can('edit inv_order');
            Route::post('approve/{id}', 'approve')->can('edit inv_order');
            Route::post('delete/{id}', 'destroy')->can('delete inv_order');
            Route::post('list', 'list')->can('view inv_order');
            Route::post('products-list', 'productsList')->can('view inv_order');
        });

        Route::prefix('inv-deliveries')->controller(InvDeliveryController::class)->group(function () {
            Route::get('fetch/{id}', 'fetch')->can('view inv_delivery');
            Route::post('store', 'store')->can('add inv_delivery');
            Route::post('update/{id}', 'update')->can('edit inv_delivery');
            Route::post('delete/{id}', 'destroy')->can('delete inv_delivery');
            Route::post('list', 'list')->can('view inv_delivery');
            Route::post('products-list', 'productsList')->can('view inv_delivery');
        });

        Route::prefix('users')->controller(UserController::class)->group(function () {
            Route::post('update-profile-picture', 'updateProfilePicture');
            Route::get('unseens', 'unseens');
        });

        Route::prefix('histories')->controller(HistoryController::class)->group(function () {
            Route::get('/{id?}', 'get');
        });

        Route::prefix('notifications')->controller(NotificationController::class)->group(function () {
            Route::get('/', 'get');
            Route::post('/mark-as-seen/{id}', 'markAsSeen');
        });

        Route::prefix('settings')->controller(SettingController::class)->group(function () {
            Route::get('/', 'get')->can('get setting');
            Route::post('/', 'set')->can('set setting');
            Route::post('/update-dashboard-message', 'updateDashboardMessage')->can('set setting');
        });

        Route::prefix('scrolling-messages')->controller(ScrollingMessageController::class)->group(function () {
            Route::get('fetch/{id}', 'fetch')->can('view scrolling_message');
            Route::post('store', 'store')->can('add scrolling_message');
            Route::post('update/{id}', 'update')->can('edit scrolling_message');
            Route::post('change-status/{id}', 'changeStatus')->can('edit scrolling_message');
            Route::post('delete/{id}', 'destroy')->can('delete scrolling_message');
            Route::post('list', 'list')->can('view scrolling_message');
        });

        Route::prefix('broadcast-messages')->controller(BroadcastMessageController::class)->group(function () {
            Route::get('fetch/{id}', 'fetch')->can('view broadcast_message');
            Route::post('store', 'store')->can('add broadcast_message');
            Route::post('update/{id}', 'update')->can('edit broadcast_message');
            Route::post('/mark-as-seen/{id}', 'markAsSeen');
            Route::post('delete/{id}', 'destroy')->can('delete broadcast_message');
            Route::post('list', 'list')->can('view broadcast_message');
        });

        Route::prefix('card-types')->controller(CardTypeController::class)->group(function () {
            Route::get('fetch/{id}', 'fetch')->can('view card_type');
            Route::get('fetch-all', 'fetchAll')->can('view card_type');
            Route::post('store', 'store')->can('add card_type');
            Route::post('update/{id}', 'update')->can('edit card_type');
            Route::post('delete/{id}', 'destroy')->can('delete card_type');
            Route::post('list', 'list')->can('view card_type');
        });

        Route::prefix('card-categories')->controller(CardCategoryController::class)->group(function () {
            Route::get('fetch/{id}', 'fetch')->can('view card_category');
            Route::get('fetch-all', 'fetchAll')->can('view card_category');
            Route::post('store', 'store')->can('add card_category');
            Route::post('update/{id}', 'update')->can('edit card_category');
            Route::post('delete/{id}', 'destroy')->can('delete card_category');
            Route::post('list', 'list')->can('view card_category');
        });

        Route::prefix('cards')->controller(CardController::class)->group(function () {
            Route::get('fetch/{id}', 'fetch')->can('view card');
            Route::get('fetch-by-card-id/{cardId}', 'fetchByCardId')->role('partner');
            Route::post('store', 'store')->can('add card');
            Route::post('update/{id}', 'update')->can('edit card');
            Route::post('delete/{id}', 'destroy')->can('delete card');
            Route::post('delete-range', 'destroyRange')->can('delete card');
            Route::post('list', 'list')->can('view card');
            Route::post('list-stock', 'listStock')->can('view card stock');
            Route::post('total-stock', 'totalStock')->can('view card stock');
        });

        Route::prefix('card-orders')->controller(CardOrderController::class)->group(function () {
            Route::get('fetch/{id}', 'fetch')->can('view card_order');
            Route::get('fetch-by-code', 'fetchByCode')->can('view card_order');
            Route::post('store', 'store')->can('add card_order');
            Route::post('delete/{id}', 'destroy')->can('delete card_order');
            Route::post('list', 'list')->can('view card_order');
            Route::get('generate-bill/{id}', 'generateBill')->can('generate-bill card_order');
            Route::post('list-cards', [CardController::class, 'list'])->can('view card_order');
        });

        Route::prefix('decoders')->controller(DecoderController::class)->group(function () {
            Route::get('fetch/{id}', 'fetch')->can('view decoder');
            Route::get('fetch-by-decoder-number/{decoderNumber}', 'fetchByDecoderNumber')->role('partner');
            Route::post('store', 'store')->can('add decoder');
            Route::post('update/{id}', 'update')->can('edit decoder');
            Route::post('delete/{id}', 'destroy')->can('delete decoder');
            Route::post('delete-range', 'destroyRange')->can('delete decoder');
            Route::post('list', 'list')->can('view decoder');
            Route::post('list-stock', 'listStock')->can('view decoder stock');
            Route::post('total-stock', 'totalStock')->can('view decoder stock');
        });

        // Invoices
        Route::prefix('invoices')->controller(InvoiceController::class)->group(function () {
            Route::get('fetch/{id}', 'fetch')->role('admin|collab|reviewer');
            Route::post('list', 'list')->role('admin|collab|reviewer');
            Route::post('{opTypeCode}/store', 'store')->role('admin|collab|reviewer');
            Route::post('update/{id}', 'update')->role('admin|collab|reviewer');
            Route::post('mark-paid/{id}', 'markPaid')->role('admin|collab|reviewer');
            Route::get('export-csv/{id}', 'exportCsv')->role('admin|collab|reviewer');
            Route::get('export-pdf/{id}', 'exportPdf')->role('admin|collab|reviewer');
        });

        // Performances
        Route::prefix('performance')->group(function () {
            Route::post('collabs', [PerformanceController::class, 'collabSales'])->role('admin');
            Route::post('collabs-by-type', [PerformanceController::class, 'collabSalesByType'])->role('admin');
        });

        Route::prefix('decoder-orders')->controller(DecoderOrderController::class)->group(function () {
            Route::get('fetch/{id}', 'fetch')->can('view decoder_order');
            Route::get('fetch-by-code', 'fetchByCode')->can('view decoder_order');
            Route::post('store', 'store')->can('add decoder_order');
            Route::post('delete/{id}', 'destroy')->can('delete decoder_order');
            Route::post('list', 'list')->can('view decoder_order');
            Route::get('generate-bill/{id}', 'generateBill')->can('generate-bill decoder_order');
            Route::post('list-decoders', [DecoderController::class, 'list'])->can('view decoder_order');
        });

        Route::prefix('tickets')->controller(TicketController::class)->group(function () {
            Route::get('fetch/{id}', 'fetch')->can('view ticket');
            Route::post('store', 'store')->can('add ticket');
            Route::post('update/{id}', 'update')->can('edit ticket');
            Route::post('respond/{id}', 'respond')->can('respond ticket');
            Route::post('delete/{id}', 'destroy')->can('delete ticket');
            Route::post('list', 'list')->can('view ticket');
        });

        Route::prefix('balance-adjustments')->controller(BalanceAdjustmentController::class)->group(function () {
            Route::get('fetch/{id}', 'fetch')->can('view balance_adjustment');
            Route::post('store', 'store')->can('add balance_adjustment');
            Route::post('list', 'list')->can('view balance_adjustment');
        });
    });

    Route::get('scrolling-messages/fetch-visibles', [ScrollingMessageController::class, 'fetchVisibles']);

    Route::prefix('countries')->controller(CountryController::class)->group(function () {
        Route::get('/', 'all');
    });

    Route::prefix('public-files')->controller(PublicFileController::class)->group(function () {
        Route::get('{filetype}/{filename?}', 'getFile');
    });
});




// Demandes d'annulation d'opérations (unique bloc)
Route::prefix('v1/operations-cancel')->middleware('auth:sanctum')->group(function () {
    Route::post('request/{operationId}', [OperationCancellationRequestController::class, 'requestCancellation'])
        ->name('api.operations-cancel.request');
    Route::post('approve/{requestId}', [OperationCancellationRequestController::class, 'approveCancellation'])
        ->name('api.operations-cancel.approve');
    Route::post('reject/{requestId}', [OperationCancellationRequestController::class, 'rejectCancellation'])
        ->name('api.operations-cancel.reject');
    Route::get('list', [OperationCancellationRequestController::class, 'listRequests'])
        ->name('api.operations-cancel.list');
});

// Alias pour compatibilité avec l'ancien front: POST /api/collab/operations-cancel
Route::middleware('auth:sanctum')->post('collab/operations-cancel', [
    OperationCancellationRequestController::class,
    'requestFromBody',
])->name('api.collab.operations-cancel.request');











/*
Route::prefix('v1/operations-cancel')->middleware('auth:sanctum')->group(function () {
    // Demande d’annulation d’une opération (collaborateur)
    Route::post('request/{operationId}', [OperationCancellationRequestController::class, 'requestCancellation'])
        ->name('api.operations-cancel.request');

    // Approbation d’une demande (admin)
    Route::post('approve/{requestId}', [OperationCancellationRequestController::class, 'approveCancellation'])
        ->name('api.operations-cancel.approve');

    // Rejet d’une demande (admin)
    Route::post('reject/{requestId}', [OperationCancellationRequestController::class, 'rejectCancellation'])
        ->name('api.operations-cancel.reject');

    // Liste des demandes (admin)
    Route::get('list', [OperationCancellationRequestController::class, 'listRequests'])
        ->name('api.operations-cancel.list');
});

// Alias pour l’ancienne URL utilisée par le front : POST /api/collab/operations-cancel
Route::middleware('auth:sanctum')->post('collab/operations-cancel', [
    OperationCancellationRequestController::class,
    'requestCancellation',
])->name('api.collab.operations-cancel.request');
*/







// Groupe API versionné pour les demandes d'annulation (envoi, validation, refus, liste)
/*Route::prefix('v1/operations-cancel')->middleware('auth:sanctum')->group(function () {
    Route::post('request/{operationId}', [OperationCancellationRequestController::class, 'requestCancellation']);
    Route::post('approve/{requestId}', [OperationCancellationRequestController::class, 'approveCancellation']);
    Route::post('reject/{requestId}', [OperationCancellationRequestController::class, 'rejectCancellation']);
    Route::get('list', [OperationCancellationRequestController::class, 'listRequests']);
});*/



// (Anciennes duplications nettoyées)
