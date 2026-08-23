<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('planters', function (Blueprint $table) {
            $table->string('rdd_division')->nullable()->after('district');
            $table->string('farm_name')->nullable()->after('rdd_division');
            $table->string('whatsapp', 30)->nullable()->after('phone');
            $table->string('fax', 30)->nullable()->after('whatsapp');
            $table->string('business_type', 30)->nullable()->after('address');
            $table->boolean('already_certified')->nullable()->after('business_type');
            $table->string('certification_standard')->nullable()->after('already_certified');
            $table->string('prior_certificate_document')->nullable()->after('certification_standard');
            $table->boolean('certification_rejected_or_suspended')->nullable()->after('prior_certificate_document');
            $table->text('certification_issue_reason')->nullable()->after('certification_rejected_or_suspended');
            $table->json('crops_products')->nullable()->after('certification_issue_reason');
            $table->boolean('aware_of_certification')->nullable()->after('crops_products');
            $table->boolean('has_certification_leaflet')->nullable()->after('aware_of_certification');
            $table->boolean('processes_rubber_on_farm')->nullable()->after('has_certification_leaflet');
            $table->string('has_process_plan', 10)->nullable()->after('processes_rubber_on_farm');
            $table->string('group_name')->nullable()->after('has_process_plan');
            $table->text('group_address')->nullable()->after('group_name');
        });
    }

    public function down(): void
    {
        Schema::table('planters', function (Blueprint $table) {
            $table->dropColumn([
                'rdd_division',
                'farm_name',
                'whatsapp',
                'fax',
                'business_type',
                'already_certified',
                'certification_standard',
                'prior_certificate_document',
                'certification_rejected_or_suspended',
                'certification_issue_reason',
                'crops_products',
                'aware_of_certification',
                'has_certification_leaflet',
                'processes_rubber_on_farm',
                'has_process_plan',
                'group_name',
                'group_address',
            ]);
        });
    }
};
