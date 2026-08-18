<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cooperation_monitorings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained('documents')->cascadeOnDelete();
            $table->date('monitoring_date');
            $table->string('implementation_status', 64);
            $table->unsignedTinyInteger('progress_percentage')->nullable();
            $table->text('notes')->nullable();
            $table->text('follow_up')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['document_id', 'monitoring_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cooperation_monitorings');
    }
};
