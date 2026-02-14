<?php

use Skywalker\Support\Database\Migration;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration
{
    protected $table = 'otps';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $this->createSchema(function (Blueprint $table) {
            $table->id();
            $table->string('identifier'); // email or phone
            $table->string('token');
            $table->timestamp('expires_at');
            $table->string('ip_address')->nullable();
            $table->timestamps();
            
            $table->index(['identifier', 'token']);
        });
    }
};
