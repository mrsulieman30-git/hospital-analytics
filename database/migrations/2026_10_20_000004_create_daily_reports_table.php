<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_reports', function (Blueprint $table) {
            $table->id();
            $table->string('department'); // pharmacy, laboratory, emergency, inpatient, reception
            $table->date('report_date');
            $table->string('shift')->default('24-Hour Daily Summary');
            $table->string('form_code');
            $table->string('lead_staff_name')->nullable();
            $table->string('status')->default('draft'); // draft, submitted, approved, returned
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('submitted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('audited_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('audited_at')->nullable();
            $table->text('audit_feedback')->nullable();
            $table->json('form_data')->nullable();
            $table->json('summary_metrics')->nullable();
            $table->timestamps();

            $table->index(['department', 'report_date']);
            $table->index(['report_date', 'status']);
            $table->unique(['department', 'report_date', 'shift']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_reports');
    }
};
