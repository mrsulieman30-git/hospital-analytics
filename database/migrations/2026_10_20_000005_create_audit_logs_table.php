<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action'); // CREATE, UPDATE, DELETE, SUBMIT, APPROVE, RETURN, EXPORT, LOGIN
            $table->string('entity_type')->nullable();
            $table->unsignedBigInteger('entity_id')->nullable();
            $table->string('department')->nullable();
            $table->string('description');
            $table->string('ip_address')->nullable();
            $table->json('diff')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['created_at', 'action']);
            $table->index(['department', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
