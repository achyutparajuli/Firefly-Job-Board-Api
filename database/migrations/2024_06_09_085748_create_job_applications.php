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
        Schema::create('job_applications', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('job_id')->constrained('job_listings')->onDelete('cascade');
            $table->string('slug', 70)->unique()->index();
            $table->foreignId('employee_id')->constrained('users')->onDelete('cascade');
            $table->string('cv', 100);
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->string('cover_letter_file', 100)->nullable(); // file
            $table->longText('cover_letter_content')->nullable(); // content
            $table->string('experience', 70)->nullable();
            $table->string('skills', 200)->nullable();
            $table->string('remarks', 200)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_applications');
    }
};
