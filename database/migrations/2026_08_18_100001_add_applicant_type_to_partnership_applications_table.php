<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('partnership_applications', function (Blueprint $table) {
            $table->string('applicant_type', 16)->nullable()->after('status');
            $table->index('applicant_type');
        });
    }

    public function down(): void
    {
        Schema::table('partnership_applications', function (Blueprint $table) {
            $table->dropIndex(['applicant_type']);
            $table->dropColumn('applicant_type');
        });
    }
};
