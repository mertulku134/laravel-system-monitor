<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('user_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable();
            $table->string('session_id');
            $table->string('type');
            $table->string('url')->nullable();
            $table->string('method')->nullable();
            $table->string('ip')->nullable();
            $table->string('user_agent')->nullable();
            $table->float('duration')->nullable();
            $table->integer('status')->nullable();
            $table->json('details')->nullable();
            $table->timestamp('created_at');

            $table->index('user_id');
            $table->index('session_id');
            $table->index('type');
            $table->index('created_at');
        });

        Schema::create('user_activity_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_activity_id');
            $table->string('event_type');
            $table->json('event_data');
            $table->timestamp('created_at');

            $table->index('user_activity_id');
            $table->index('event_type');
            $table->index('created_at');

            $table->foreign('user_activity_id')
                ->references('id')
                ->on('user_activities')
                ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('user_activity_events');
        Schema::dropIfExists('user_activities');
    }
}; 