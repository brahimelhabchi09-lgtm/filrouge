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
        Schema::create('meetings', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->timestamp('date');
            $table->string('link');
            $table->string('pdf_path');
            $table->unsignedBigInteger('admin_id');
            $table->unsignedBigInteger('request_meeting_id');
            $table->foreign('admin_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('request_meeting_id')->references('id')->on('request_meetings')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meetings');
    }
};
