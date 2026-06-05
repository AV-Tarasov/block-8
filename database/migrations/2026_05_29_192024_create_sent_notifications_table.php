<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        Schema::create('sent_notifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('task_id');
            $table->string('type');
            $table->timestamps();

            $table->unique(['task_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sent_notifications');
    }
};
