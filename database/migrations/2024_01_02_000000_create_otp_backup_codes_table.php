<?php

use Skywalker\Support\Database\Migration;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration
{
    protected $table = 'otp_backup_codes';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $this->createSchema(function (Blueprint $table) {
            $table->id();
            $table->string('identifier'); // email or phone
            $table->string('code');
            $table->timestamp('used_at')->nullable();
            $table->timestamps();
            
            $table->index(['identifier']);
        });
    }
};
