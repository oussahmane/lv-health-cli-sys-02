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
        Schema::create('queues', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->constrained()->onDelete('cascade');
            $table->string('patient_name');
            $table->string('patient_phone')->nullable();
            $table->integer('queue_number');
            $table->date('date');
            $table->enum('status', ['waiting', 'called', 'in_examination', 'done', 'cancelled', 'no_show'])->default('waiting');
            $table->enum('source', ['manual', 'qr_scan', 'online_link']);
            $table->boolean('priority')->default(false);
            $table->string('cancel_token')->unique();
            $table->timestamp('called_at')->nullable();
            $table->timestamp('examination_start_at')->nullable();
            $table->timestamp('done_at')->nullable();
            $table->timestamps();

            $table->index(['clinic_id', 'date', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('queues');
    }
};
