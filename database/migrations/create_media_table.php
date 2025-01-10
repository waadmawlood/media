<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create(config('media.table_name', 'media'), function (Blueprint $table) {
            $table->id();
            $table->morphs('mediable');
            $table->nullableMorphs('user');
            $table->string('basename');
            $table->string('filename')->nullable();
            $table->string('path')->nullable();
            $table->integer('index')->default(1);
            $table->string('label')->nullable();
            $table->string('collection')->default('default');
            $table->string('disk')->nullable();
            $table->string('bucket')->nullable();
            $table->string('mimetype')->nullable();
            $table->unsignedBigInteger('filesize')->default(0);
            $table->boolean('approved')->default(config('media.default_approved', true));
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists(config('media.table_name', 'media'));
    }
};
