<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('department_forms', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name_en');
            $table->string('name_so');
            $table->string('form_code')->unique();
            $table->string('cadence')->default('shift_based'); // 'shift_based' or '24_hour_daily'
            $table->json('allowed_shifts')->nullable();
            $table->json('dropdown_options')->nullable();
            $table->json('field_settings')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('department_forms');
    }
};
