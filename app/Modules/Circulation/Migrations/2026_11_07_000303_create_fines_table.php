<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loan_id')->constrained()->cascadeOnDelete();
            $table->foreignId('member_id')->constrained('members')->cascadeOnDelete();
            $table->integer('days_late');
            $table->decimal('amount', 12, 2);
            $table->string('status', 20)->default('unpaid');
            $table->dateTime('paid_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'member_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fines');
    }
};
