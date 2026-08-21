
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('website_name')->nullable();
            $table->string('website_currency')->nullable();
            $table->string('website_color')->default('#4c45e0');
            $table->string('timezone')->default('UTC');
            $table->string('copyright_text')->nullable();
            $table->tinyInteger('user_registration')->default(1);
            $table->tinyInteger('recaptcha')->default(0);
            $table->tinyInteger('user_email_notification')->default(1);
            $table->tinyInteger('admin_email_notification')->default(1);
            $table->tinyInteger('user_payment')->default(1);
            $table->tinyInteger('user_login')->default(1);
            $table->tinyInteger('buy_number')->default(1);
            $table->tinyInteger('boost_social')->default(1);
            $table->tinyInteger('social_logs')->default(1);
            $table->tinyInteger('referral_enabled')->default(0);
            $table->decimal('referral_commission_percent', 5, 2)->default(0);
            $table->tinyInteger('referral_first_deposit_only')->default(1);
            $table->text('seo_description')->nullable();
            $table->text('live_chat_code')->nullable();
            $table->string('logo_white')->nullable();
            $table->string('logo_dark')->nullable();
            $table->string('favicon_white')->nullable();
            $table->string('favicon_dark')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('settings');
    }
};
