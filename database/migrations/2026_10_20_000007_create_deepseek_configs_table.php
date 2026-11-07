<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deepseek_configs', function (Blueprint $table) {
            $table->id();
            $table->text('api_key')->nullable();
            $table->string('selected_model')->default('deepseek-chat'); // deepseek-chat, deepseek-reasoner
            $table->text('custom_system_prompt')->nullable();
            $table->boolean('enable_web_search')->default(true);
            $table->decimal('temperature', 3, 2)->default(0.70);
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deepseek_configs');
    }
};
