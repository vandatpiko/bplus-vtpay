<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBplusVTPaysTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bplus_vtpays', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id');
            $table->string('username', 12)->primary();
            $table->string('password',10)->nullable();
            $table->string('imei');
            $table->string('display_name', 50)->nullable();
            $table->text('client_private_key');
            $table->text('viettel_public_key');
            $table->string('token_notification', 255);
            $table->text('access_token')->nullable();
            $table->text('refresh_token')->nullable();
            $table->text('session_id')->nullable();
            $table->string('account_id')->nullable();
            $table->string('acc_no')->nullable();
            $table->bigInteger('balance')->nullable();
            $table->json('extra_data')->nullable();
            $table->dateTime('login_at')->nullable();
            $table->dateTime('refresh_at')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references(app()->make(config('bplusvtpay.model_user'))->getKeyName())->on(app()->make(config('bplusvtpay.model_user'))->getTable())
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bplus_vtpays');
    }
}
