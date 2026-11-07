<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('executive_insights', function (Blueprint $table) {
            $table->id();
            $table->string('report_type')->default('daily'); // daily, weekly, monthly, quarterly, yearly
            $table->date('period_date');
            $table->string('title');
            $table->text('executive_summary');
            $table->json('key_findings')->nullable();
            $table->json('bottlenecks_and_root_causes')->nullable();
            $table->json('action_plan')->nullable();
            $table->json('medical_benchmarks')->nullable();
            $table->string('status')->default('published');
            $table->foreignId('published_by')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('is_ai_generated')->default(false);
            $table->timestamps();

            $table->index(['report_type', 'period_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('executive_insights');
    }
};
