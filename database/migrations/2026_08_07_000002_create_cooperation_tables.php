<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cooperation_sections', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('type', 32); // domestic | international
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('cooperation_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('section_id')->constrained('cooperation_sections')->restrictOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->string('icon')->nullable();
            $table->text('description')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['section_id', 'slug']);
            $table->index(['section_id', 'is_active', 'sort_order']);
        });

        Schema::create('year_folders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('cooperation_categories')->restrictOnDelete();
            $table->unsignedSmallInteger('year');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['category_id', 'year']);
            $table->index(['category_id', 'is_active', 'year']);
        });

        Schema::create('institution_folders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('year_folder_id')->constrained('year_folders')->restrictOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->string('logo')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['year_folder_id', 'slug']);
            $table->index(['year_folder_id', 'is_active', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('institution_folders');
        Schema::dropIfExists('year_folders');
        Schema::dropIfExists('cooperation_categories');
        Schema::dropIfExists('cooperation_sections');
    }
};
