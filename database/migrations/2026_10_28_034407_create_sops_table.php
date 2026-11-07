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
        Schema::create('sops', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('sop_code')->unique();
            $table->string('department')->default('laboratory');
            $table->string('category')->nullable()->default('General');
            $table->string('version')->default('1.0');
            $table->string('file_path');
            $table->string('file_name');
            $table->string('file_type', 10)->default('docx');
            $table->unsignedBigInteger('file_size')->default(0);
            $table->boolean('is_downloadable')->default(true);
            $table->text('extracted_summary')->nullable();
            $table->longText('extracted_html')->nullable();
            $table->json('extracted_metadata')->nullable();
            $table->json('leaflet_images')->nullable();
            $table->date('effective_date')->nullable();
            $table->date('review_date')->nullable();
            $table->string('author_name')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status')->default('active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sops');
    }
};
