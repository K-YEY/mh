<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Currencies
        Schema::create('currencies', function (Blueprint $table) {
            $table->string('code', 3)->primary();
            $table->string('name', 50);
            $table->string('symbol', 10);
            $table->smallInteger('decimal_places')->default(2);
            $table->boolean('is_base_currency')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 2. Exchange Rates
        Schema::create('exchange_rates', function (Blueprint $table) {
            $table->id();
            $table->string('currency_code', 3);
            $table->decimal('rate_to_base', 18, 6);
            $table->date('effective_date');
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('currency_code')->references('code')->on('currencies')->cascadeOnDelete();
            $table->unique(['currency_code', 'effective_date'], 'uq_currency_date');
        });

        // 3. Ports
        Schema::create('ports', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->unique();
            $table->string('name', 150);
            $table->string('country_code', 2);
            $table->boolean('is_active')->default(true);
            $table->timestamp('created_at')->useCurrent();
        });

        // 4. Parties
        Schema::create('parties', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('legal_name', 255);
            $table->string('trade_name', 255)->nullable();
            $table->string('tax_number', 100)->nullable();
            $table->string('commercial_registry', 100)->nullable();
            $table->string('email', 150)->nullable();
            $table->string('phone', 50)->nullable();
            $table->text('address')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 5. Party Roles
        Schema::create('party_roles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('party_id')->constrained('parties')->cascadeOnDelete();
            $table->enum('role', [
                'CUSTOMER',
                'SHIPPING_LINE',
                'TRUCKER',
                'CUSTOMS_BROKER',
                'OVERSEAS_AGENT',
                'PACKHOUSE',
                'EMPLOYEE',
            ]);
            $table->timestamp('created_at')->useCurrent();
            $table->unique(['party_id', 'role'], 'uq_party_role');
        });

        // 6. Ocean Freight Tariffs
        Schema::create('ocean_freight_tariffs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipping_line_id')->constrained('parties');
            $table->foreignId('pol_id')->constrained('ports');
            $table->foreignId('pod_id')->constrained('ports');
            $table->enum('container_type', ['20GP', '40GP', '40HC', '20RF', '40RF', '40HR']);
            $table->string('currency_code', 3);
            $table->decimal('buy_rate', 15, 2);
            $table->integer('transit_time_days')->nullable();
            $table->date('valid_from');
            $table->date('valid_to');
            $table->integer('free_days_demurrage')->default(14);
            $table->text('notes')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('currency_code')->references('code')->on('currencies');
            $table->index(['pol_id', 'pod_id', 'container_type', 'valid_to'], 'idx_tariff_lookup');
        });

        // 7. Master Shipments
        Schema::create('master_shipments', function (Blueprint $table) {
            $table->id();
            $table->string('file_number', 50)->unique();
            $table->enum('direction', ['EXPORT', 'IMPORT']);
            $table->enum('shipment_mode', ['FCL', 'LCL']);

            $table->foreignId('shipping_line_id')->constrained('parties');
            $table->string('booking_reference', 100)->nullable();
            $table->string('master_bl_number', 100)->unique()->nullable();

            $table->string('vessel_name', 150);
            $table->string('voyage_number', 50);
            $table->foreignId('pol_id')->constrained('ports');
            $table->foreignId('pod_id')->constrained('ports');
            $table->date('etd');
            $table->date('eta');
            $table->date('actual_departure')->nullable();
            $table->date('actual_arrival')->nullable();

            $table->enum('status', [
                'BOOKING_CONFIRMED',
                'CONTAINER_GATED_IN',
                'SAILED_IN_TRANSIT',
                'DISCHARGED_PORT',
                'CUSTOMS_PROCESSING',
                'DELIVERED',
                'CLOSED_FINANCIALLY',
            ])->default('BOOKING_CONFIRMED');

            $table->foreignId('created_by_user_id')->constrained('users');
            $table->timestamps();
        });

        // 8. Master Containers (Reefer Controls & Cold Treatment Probes)
        Schema::create('master_containers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('master_shipment_id')->constrained('master_shipments')->cascadeOnDelete();
            $table->string('container_number', 20);
            $table->enum('container_type', ['20RF', '40RF', '40HR', '20GP', '40GP', '40HC']);
            $table->string('seal_number', 50)->nullable();
            $table->boolean('is_reefer')->default(true);

            // Temperature, Ventilation, Humidity
            $table->decimal('target_temperature', 4, 1)->nullable();
            $table->integer('ventilation_flow')->nullable();
            $table->integer('humidity_percent')->nullable();
            $table->string('genset_number', 50)->nullable();
            $table->string('data_logger_serial', 100)->nullable();

            // Cold Treatment Probes
            $table->boolean('requires_cold_treatment')->default(false);
            $table->string('ct_probe_1', 50)->nullable();
            $table->string('ct_probe_2', 50)->nullable();
            $table->string('ct_probe_3', 50)->nullable();

            $table->decimal('tare_weight_kg', 10, 2)->nullable();
            $table->decimal('max_payload_kg', 10, 2)->nullable();
            $table->timestamp('discharged_at')->nullable();
            $table->integer('free_days')->default(14);
            $table->timestamp('returned_empty_at')->nullable();
            $table->string('current_status', 50)->default('ASSIGNED');

            $table->timestamp('created_at')->useCurrent();
            $table->unique(['master_shipment_id', 'container_number'], 'uq_container_job');
        });

        // 9. Container Tracking Events
        Schema::create('container_tracking_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('master_container_id')->constrained('master_containers')->cascadeOnDelete();
            $table->string('event_code', 50);
            $table->string('event_name', 150);
            $table->string('location_name', 150)->nullable();
            $table->timestamp('occurred_at');
            $table->enum('source', ['MANUAL', 'FINDTEU', 'SHIPSGO', 'VIZION', 'SEARATES'])->default('MANUAL');
            $table->json('raw_payload')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['master_container_id', 'occurred_at'], 'idx_tracking_time');
        });

        // 10. House Shipments (Crop Details, Packhouse, W/M Engine)
        Schema::create('house_shipments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('master_shipment_id')->constrained('master_shipments')->cascadeOnDelete();
            $table->string('house_file_number', 50)->unique();
            $table->string('house_bl_number', 100)->unique()->nullable();

            $table->foreignId('shipper_id')->constrained('parties');
            $table->foreignId('consignee_id')->constrained('parties');
            $table->foreignId('notify_party_id')->nullable()->constrained('parties');
            $table->foreignId('packhouse_id')->nullable()->constrained('parties');
            $table->foreignId('customs_broker_id')->nullable()->constrained('parties');
            $table->foreignId('trucker_id')->nullable()->constrained('parties');

            // Agricultural Traceability
            $table->string('crop_type', 100);
            $table->string('variety', 100)->nullable();
            $table->string('lot_number', 100)->nullable();

            // Customs Release & Delivery Order
            $table->string('acid_number', 50)->nullable();
            $table->string('customs_declaration_no', 100)->nullable();
            $table->date('customs_release_date')->nullable();
            $table->enum('delivery_order_status', ['HOLD_FINANCIAL', 'RELEASED'])->default('HOLD_FINANCIAL');

            // Packages, Weights & Volume
            $table->integer('total_packages')->default(1);
            $table->string('package_type', 50)->default('TELESCOPIC_CARTONS');
            $table->decimal('gross_weight_kg', 12, 3);
            $table->decimal('net_weight_kg', 12, 3)->nullable();
            $table->decimal('volume_cbm', 12, 4);

            // Computed W/M Revenue Tons Column (Compatible with SQLite & MySQL)
            $table->decimal('revenue_tons', 12, 4)->storedAs('CASE WHEN (gross_weight_kg / 1000.0) > volume_cbm THEN (gross_weight_kg / 1000.0) ELSE volume_cbm END');

            $table->enum('status', ['PLANNED', 'CLEARED', 'DELIVERED', 'COMPLETED'])->default('PLANNED');
            $table->timestamps();
        });

        // 11. House Shipment Containers (LCL Allocation)
        Schema::create('house_shipment_containers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('house_shipment_id')->constrained('house_shipments')->cascadeOnDelete();
            $table->foreignId('master_container_id')->constrained('master_containers')->cascadeOnDelete();
            $table->decimal('allocated_weight_kg', 10, 2);
            $table->decimal('allocated_cbm', 10, 3);
            $table->timestamp('created_at')->useCurrent();
        });

        // 12. Document Types
        Schema::create('document_types', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name_ar', 150);
            $table->string('name_en', 150);
            $table->boolean('requires_expiry_date')->default(false);
            $table->boolean('requires_reference_no')->default(true);
            $table->boolean('is_active')->default(true);
        });

        // 13. Shipment Documents
        Schema::create('shipment_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_type_id')->constrained('document_types');
            $table->string('documentable_type', 100);
            $table->unsignedBigInteger('documentable_id');

            $table->string('reference_number', 100)->nullable();
            $table->string('issuing_authority', 255)->nullable();
            $table->date('issue_date')->nullable();
            $table->date('expiry_date')->nullable();

            $table->string('file_path', 500);
            $table->string('file_name', 255);
            $table->integer('file_size_bytes');
            $table->string('mime_type', 100);

            $table->enum('status', ['PENDING_REVIEW', 'VERIFIED', 'REJECTED'])->default('PENDING_REVIEW');
            $table->text('rejection_notes')->nullable();
            $table->foreignId('verified_by_user_id')->nullable()->constrained('users');
            $table->timestamp('verified_at')->nullable();

            $table->foreignId('uploaded_by_user_id')->constrained('users');
            $table->timestamps();

            $table->index(['documentable_type', 'documentable_id'], 'idx_doc_poly');
            $table->index('expiry_date', 'idx_doc_expiry');
        });

        // 14. Accounts (Chart of Accounts)
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name_ar', 150);
            $table->string('name_en', 150);
            $table->enum('type', ['ASSET', 'LIABILITY', 'EQUITY', 'REVENUE', 'EXPENSE']);
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->boolean('is_active')->default(true);

            $table->foreign('parent_id')->references('id')->on('accounts');
        });

        // 15. Charge Items
        Schema::create('charge_items', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name_ar', 150);
            $table->string('name_en', 150);
            $table->foreignId('revenue_account_id')->constrained('accounts');
            $table->foreignId('expense_account_id')->constrained('accounts');
        });

        // 16. Journal Entries
        Schema::create('journal_entries', function (Blueprint $table) {
            $table->id();
            $table->string('entry_number', 50)->unique();
            $table->date('entry_date');
            $table->enum('source_type', ['OPERATIONS_INVOICE', 'VENDOR_BILL', 'PAYMENT_VOUCHER', 'RECEIPT_VOUCHER', 'MANUAL']);
            $table->unsignedBigInteger('source_id')->nullable();
            $table->text('narration');
            $table->boolean('is_posted')->default(false);
            $table->foreignId('posted_by_user_id')->nullable()->constrained('users');
            $table->timestamp('posted_at')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        // 17. Journal Entry Lines (Double-Entry Base FX calculation)
        Schema::create('journal_entry_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('journal_entry_id')->constrained('journal_entries')->cascadeOnDelete();
            $table->foreignId('account_id')->constrained('accounts');
            $table->foreignId('party_id')->nullable()->constrained('parties');

            $table->string('description', 255)->nullable();
            $table->string('currency_code', 3);
            $table->decimal('exchange_rate_to_base', 18, 6);

            $table->decimal('debit', 18, 2)->default(0.00);
            $table->decimal('credit', 18, 2)->default(0.00);

            $table->decimal('debit_base', 18, 2)->storedAs('debit * exchange_rate_to_base');
            $table->decimal('credit_base', 18, 2)->storedAs('credit * exchange_rate_to_base');

            $table->foreign('currency_code')->references('code')->on('currencies');
            $table->index(['party_id', 'account_id'], 'idx_subledger_party');
        });

        // 18. Shipment Invoices
        Schema::create('shipment_invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number', 50)->unique();
            $table->enum('invoice_type', ['CUSTOMER_INVOICE', 'VENDOR_BILL']);
            $table->foreignId('party_id')->constrained('parties');

            $table->foreignId('master_shipment_id')->nullable()->constrained('master_shipments');
            $table->foreignId('house_shipment_id')->nullable()->constrained('house_shipments');

            $table->string('currency_code', 3);
            $table->decimal('exchange_rate', 18, 6);
            $table->date('issue_date');
            $table->date('due_date');

            $table->decimal('subtotal', 18, 2);
            $table->decimal('tax_total', 18, 2)->default(0.00);
            $table->decimal('total_amount', 18, 2);

            $table->enum('status', ['DRAFT', 'POSTED', 'PARTIALLY_PAID', 'PAID', 'VOID'])->default('DRAFT');
            $table->foreignId('journal_entry_id')->nullable()->unique()->constrained('journal_entries');

            $table->timestamp('created_at')->useCurrent();
            $table->foreign('currency_code')->references('code')->on('currencies');
        });

        // 19. Shipment Invoice Items
        Schema::create('shipment_invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipment_invoice_id')->constrained('shipment_invoices')->cascadeOnDelete();
            $table->foreignId('charge_item_id')->constrained('charge_items');
            $table->string('description', 255)->nullable();
            $table->decimal('quantity', 10, 2)->default(1.00);
            $table->decimal('unit_price', 18, 2);
            $table->decimal('line_total', 18, 2);
        });

        // 20. Payment Vouchers (Cash/Bank Vouchers)
        Schema::create('payment_vouchers', function (Blueprint $table) {
            $table->id();
            $table->string('voucher_number', 50)->unique();
            $table->enum('voucher_type', ['RECEIPT', 'PAYMENT']);
            $table->foreignId('party_id')->constrained('parties');
            $table->enum('payment_method', ['CASH', 'BANK_TRANSFER', 'CHEQUE']);
            $table->foreignId('account_id')->constrained('accounts'); // Cash/Bank GL Account
            $table->decimal('amount', 18, 2);
            $table->string('currency_code', 3);
            $table->decimal('exchange_rate', 18, 6);
            $table->date('payment_date');
            $table->string('reference_number', 100)->nullable();
            $table->foreignId('journal_entry_id')->nullable()->unique()->constrained('journal_entries');
            $table->foreignId('created_by_user_id')->constrained('users');
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('currency_code')->references('code')->on('currencies');
        });

        // 21. Container Deposits
        Schema::create('container_deposits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('master_container_id')->constrained('master_containers');
            $table->foreignId('party_id')->constrained('parties');
            $table->enum('deposit_type', ['PAID_TO_LINE', 'COLLECTED_FROM_CUSTOMER']);
            $table->decimal('amount', 15, 2);
            $table->string('currency_code', 3);
            $table->enum('status', ['HELD', 'REFUNDED_FULL', 'PARTIALLY_DEDUCTED', 'FORFEITED'])->default('HELD');
            $table->decimal('deducted_amount', 15, 2)->default(0.00);
            $table->string('deduction_reason', 255)->nullable();
            $table->date('refund_date')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('container_deposits');
        Schema::dropIfExists('payment_vouchers');
        Schema::dropIfExists('shipment_invoice_items');
        Schema::dropIfExists('shipment_invoices');
        Schema::dropIfExists('journal_entry_lines');
        Schema::dropIfExists('journal_entries');
        Schema::dropIfExists('charge_items');
        Schema::dropIfExists('accounts');
        Schema::dropIfExists('shipment_documents');
        Schema::dropIfExists('document_types');
        Schema::dropIfExists('house_shipment_containers');
        Schema::dropIfExists('house_shipments');
        Schema::dropIfExists('container_tracking_events');
        Schema::dropIfExists('master_containers');
        Schema::dropIfExists('master_shipments');
        Schema::dropIfExists('ocean_freight_tariffs');
        Schema::dropIfExists('party_roles');
        Schema::dropIfExists('parties');
        Schema::dropIfExists('ports');
        Schema::dropIfExists('exchange_rates');
        Schema::dropIfExists('currencies');
    }
};
