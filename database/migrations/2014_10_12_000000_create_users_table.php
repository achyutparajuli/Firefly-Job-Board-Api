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
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 70)->index();
            $table->string('email', 100)->unique()->index();
            $table->string('mobile', 10)->unique()->index();
            $table->string('password');
            $table->boolean('status')->default(1);
            $table->longtext('api_token')->nullable();
            $table->enum('user_type', ['employee', 'employeer']);
            $table->string('job_title');
            $table->rememberToken();
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamps();

            // We can add other fields as per required.
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
