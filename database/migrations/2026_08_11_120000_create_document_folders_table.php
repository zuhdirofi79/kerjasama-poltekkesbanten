<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_folders', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('year_folder_id')->constrained('year_folders')->restrictOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('document_folders')->nullOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('legacy_institution_folder_id')
                ->nullable()
                ->unique()
                ->constrained('institution_folders')
                ->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['year_folder_id', 'parent_id', 'is_active']);
            $table->index(['year_folder_id', 'sort_order']);
        });

        Schema::table('documents', function (Blueprint $table) {
            $table->foreignId('document_folder_id')
                ->nullable()
                ->after('institution_folder_id')
                ->constrained('document_folders')
                ->nullOnDelete();

            $table->index(['document_folder_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropConstrainedForeignId('document_folder_id');
        });

        Schema::dropIfExists('document_folders');
    }
};
