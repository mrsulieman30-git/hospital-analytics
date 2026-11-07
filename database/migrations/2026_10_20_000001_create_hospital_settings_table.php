<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hospital_settings', function (Blueprint $table) {
            $table->id();
            $table->string('hospital_name')->default('Kaafi Hospitals');
            $table->string('tagline')->nullable()->default('Specialized Hospital & Clinical Analytics');
            $table->string('logo_path')->nullable();
            $table->string('primary_color', 10)->default('#124E78');
            $table->string('secondary_color', 10)->default('#1E293B');
            $table->string('accent_color', 10)->default('#C81D25');
            $table->string('currency', 10)->default('$');
            $table->string('contact_email')->nullable()->default('info@kaafihospital.so');
            $table->string('contact_phone')->nullable()->default('+252 61 555 0100');
            $table->string('address')->nullable()->default('Wadajir District, Mogadishu, Somalia');
            $table->string('submission_deadline')->default('08:30');
            $table->string('default_locale', 5)->default('en');
            $table->json('extra_metadata')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hospital_settings');
    }
};
