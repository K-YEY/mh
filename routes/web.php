<?php

use App\Http\Controllers\Accounting\AccountController;
use App\Http\Controllers\Accounting\ExchangeRateController;
use App\Http\Controllers\Accounting\FinancialReportController;
use App\Http\Controllers\Accounting\InvoiceController;
use App\Http\Controllers\Accounting\LedgerReportController;
use App\Http\Controllers\Accounting\PaymentVoucherController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\IntegrationSettingController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Documents\DocumentVaultController;
use App\Http\Controllers\Operations\ContainerTrackingController;
use App\Http\Controllers\Operations\CustomsBrokerController;
use App\Http\Controllers\Operations\HouseShipmentController;
use App\Http\Controllers\Operations\MasterShipmentController;
use App\Http\Controllers\Operations\TruckerController;
use App\Http\Controllers\Settings\CurrencyController;
use App\Http\Controllers\Settings\PartyController;
use App\Http\Controllers\Settings\PortController;
use App\Http\Controllers\Settings\UserController;
use Illuminate\Support\Facades\Route;

// Guest Auth Routes
Route::middleware('guest')->group(function () {
    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store']);
});

// Authenticated Locked ERP Routes
Route::middleware('auth')->group(function () {
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    // Dashboard Overview
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Operations Domain
    Route::prefix('operations')->group(function () {
        Route::resource('master-shipments', MasterShipmentController::class);
        Route::post('master-shipments/{masterShipment}/add-container', [MasterShipmentController::class, 'addContainer'])->name('master-shipments.add-container');
        Route::post('master-shipments/{masterShipment}/upload-document', [MasterShipmentController::class, 'uploadDocument'])->name('master-shipments.upload-document');
        Route::post('containers/{container}/events', [MasterShipmentController::class, 'addTrackingEvent'])->name('containers.add-event');

        Route::post('house-shipments', [HouseShipmentController::class, 'store'])->name('house-shipments.store');
        Route::post('house-shipments/{houseShipment}/release-do', [HouseShipmentController::class, 'releaseDeliveryOrder'])->name('house-shipments.release-do');

        Route::resource('customs-brokers', CustomsBrokerController::class)->only(['index', 'store']);
        Route::resource('truckers', TruckerController::class)->only(['index', 'store']);

        Route::get('tracking', [ContainerTrackingController::class, 'index'])->name('tracking.index');
        Route::post('tracking/event', [ContainerTrackingController::class, 'addEvent'])->name('tracking.add-event');
        Route::delete('tracking/history/{id}', [ContainerTrackingController::class, 'deleteHistory'])->name('tracking.delete-history');
        Route::delete('tracking/clear-history', [ContainerTrackingController::class, 'clearAllHistory'])->name('tracking.clear-all-history');
        Route::redirect('tariffs', '/operations/tracking')->name('tariffs.index');
    });

    // Financial Domain
    Route::prefix('accounting')->group(function () {
        Route::get('accounts', [AccountController::class, 'index'])->name('accounts.index');
        Route::post('accounts', [AccountController::class, 'store'])->name('accounts.store');

        Route::resource('invoices', InvoiceController::class);
        Route::resource('vouchers', PaymentVoucherController::class);

        Route::redirect('deposits', '/accounting/invoices');

        Route::get('exchange-rates', [ExchangeRateController::class, 'index'])->name('exchange-rates.index');
        Route::post('exchange-rates', [ExchangeRateController::class, 'store'])->name('exchange-rates.store');

        Route::get('journal-entries', [LedgerReportController::class, 'journalEntries'])->name('ledger.journals');
        Route::get('journal-entries/create', [LedgerReportController::class, 'createJournalEntry'])->name('ledger.journals.create');
        Route::post('journal-entries', [LedgerReportController::class, 'storeJournalEntry'])->name('ledger.journals.store');
        Route::get('sub-ledger', [LedgerReportController::class, 'subLedgerStatement'])->name('ledger.statement');

        // Financial Reports
        Route::get('reports/trial-balance', [FinancialReportController::class, 'trialBalance'])->name('accounting.reports.trial-balance');
        Route::get('reports/income-statement', [FinancialReportController::class, 'incomeStatement'])->name('accounting.reports.income-statement');
        Route::get('reports/general-ledger', [FinancialReportController::class, 'generalLedger'])->name('accounting.reports.general-ledger');
    });

    // Document Vault
    Route::prefix('documents')->group(function () {
        Route::get('vault', [DocumentVaultController::class, 'index'])->name('documents.vault');
        Route::post('vault/{document}/verify', [DocumentVaultController::class, 'verify'])->name('documents.verify');
        Route::post('vault/{document}/reject', [DocumentVaultController::class, 'reject'])->name('documents.reject');
    });

    // Settings Domain
    Route::prefix('settings')->group(function () {
        Route::get('users', [UserController::class, 'index'])->name('users.index');
        Route::post('users', [UserController::class, 'store'])->name('users.store');

        Route::get('parties', [PartyController::class, 'index'])->name('parties.index');
        Route::post('parties', [PartyController::class, 'store'])->name('parties.store');

        Route::get('ports', [PortController::class, 'index'])->name('ports.index');
        Route::post('ports', [PortController::class, 'store'])->name('ports.store');

        Route::get('currencies', [CurrencyController::class, 'index'])->name('currencies.index');
        Route::post('currencies', [CurrencyController::class, 'store'])->name('currencies.store');
    });

    // Admin Integrations Settings
    Route::get('admin/settings/integrations', [IntegrationSettingController::class, 'index'])->name('admin.settings.integrations.index');
    Route::post('admin/settings/integrations', [IntegrationSettingController::class, 'update'])->name('admin.settings.integrations.update');
    Route::post('admin/settings/integrations/test/{provider}', [IntegrationSettingController::class, 'testConnection'])->name('admin.settings.integrations.test');

    // Audit Trail Logs
    Route::get('admin/audit-logs', [AuditLogController::class, 'index'])->name('admin.audit-logs.index');
});
