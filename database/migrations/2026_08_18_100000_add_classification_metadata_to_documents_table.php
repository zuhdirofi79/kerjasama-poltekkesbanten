<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->date('document_date')->nullable()->after('description');
            $table->string('cooperation_area', 64)->nullable()->after('document_date');
            $table->string('cooperation_subtype', 128)->nullable()->after('cooperation_area');
            $table->date('cooperation_start_date')->nullable()->after('cooperation_subtype');
            $table->date('cooperation_end_date')->nullable()->after('cooperation_start_date');

            $table->index('document_date');
            $table->index('cooperation_area');
            $table->index('cooperation_subtype');
            $table->index('cooperation_end_date');
        });
    }

    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropIndex(['document_date']);
            $table->dropIndex(['cooperation_area']);
            $table->dropIndex(['cooperation_subtype']);
            $table->dropIndex(['cooperation_end_date']);
            $table->dropColumn([
                'document_date',
                'cooperation_area',
                'cooperation_subtype',
                'cooperation_start_date',
                'cooperation_end_date',
            ]);
        });
    }
};
