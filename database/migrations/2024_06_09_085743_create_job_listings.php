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
        Schema::create('job_listings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('slug', 70)->unique()->index();
            $table->string('title', 70)->index();
            $table->string('company_name', 100)->index();
            $table->string('location', 70)->index();
            $table->longtext('description'); // has more of jd
            $table->longtext('instruction'); // has more of hiring process
            $table->boolean('status')->default(1);
            $table->date('deadline')->nullable();
            $table->longtext('keywords');
            $table->string('salary', 30)->nullable(); // Assuming its a salary range.
            $table->foreignId('employer_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_listings');
    }
};
