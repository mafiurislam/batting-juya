<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('otp_verifications', function (Blueprint $table) {
            $table->id();
            $table->string('phone', 30)->index();
            $table->string('country_code', 10)->default('+91');
            $table->string('username', 60)->nullable();
            $table->string('purpose', 30)->default('signup');
            $table->string('otp_code', 10);
            $table->string('provider', 30)->default('msg91');
            $table->json('provider_response')->nullable();
            $table->unsignedSmallInteger('attempts')->default(0);
            $table->timestamp('verified_at')->nullable();
            $table->timestamp('expires_at');
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();
        });

        Schema::create('sports', function (Blueprint $table) {
            $table->id();
            $table->string('sport_id', 30)->unique();
            $table->string('sport_name', 80);
            $table->integer('sequence')->default(1);
            $table->boolean('popular')->default(true);
            $table->boolean('highlight')->default(false);
            $table->boolean('others')->default(false);
            $table->boolean('status')->default(true);
            $table->string('icon', 80)->nullable();
            $table->timestamps();
        });

        Schema::create('exchange_events', function (Blueprint $table) {
            $table->id();
            $table->string('event_id', 60)->unique();
            $table->string('market_id', 60)->index();
            $table->string('market_name', 80)->default('Match Odds');
            $table->string('sport_id', 30)->index();
            $table->string('sport_name', 80);
            $table->string('tournament_id', 60)->nullable();
            $table->string('tournament_name', 120)->nullable();
            $table->string('event_name', 200);
            $table->string('event_time', 60)->nullable();
            $table->boolean('in_play')->default(false)->index();
            $table->string('status', 30)->default('OPEN');
            $table->boolean('is_fancy')->default(false);
            $table->boolean('is_bookmakers')->default(false);
            $table->boolean('is_sportsbook')->default(false);
            $table->boolean('is_streaming')->default(false);
            $table->boolean('is_virtual')->default(false);
            $table->decimal('total_matched', 16, 2)->default(0);
            $table->json('runners_json')->nullable();
            $table->json('bookmaker_json')->nullable();
            $table->json('fancy_json')->nullable();
            $table->json('sportsbook_json')->nullable();
            $table->json('score_json')->nullable();
            $table->string('winner_selection', 120)->nullable();
            $table->timestamps();
        });

        Schema::create('casino_games', function (Blueprint $table) {
            $table->id();
            $table->string('event_id', 80)->unique();
            $table->string('event_name', 150);
            $table->string('slug', 150)->index();
            $table->string('menu_id', 30)->nullable()->index();
            $table->string('menu_name', 80)->nullable();
            $table->string('category_tab', 80)->default('OUR CASINO')->index();
            $table->string('company_name', 80)->nullable();
            $table->string('group_event_name', 80)->nullable();
            $table->string('room', 40)->nullable();
            $table->string('game_type', 40)->default('teenpatti')->index();
            $table->string('image_url', 500)->nullable();
            $table->string('local_image', 255)->nullable();
            $table->boolean('popular')->default(false)->index();
            $table->integer('sequence')->default(10);
            $table->decimal('min_bet', 12, 2)->default(10);
            $table->decimal('max_bet', 12, 2)->default(100000);
            $table->decimal('rtp', 5, 2)->default(97.00);
            $table->boolean('status')->default(true);
            $table->json('config_json')->nullable();
            $table->timestamps();
        });

        Schema::create('bets', function (Blueprint $table) {
            $table->id();
            $table->string('bet_ref', 40)->unique();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('category', 30)->default('exchange')->index();
            $table->string('sport_id', 30)->nullable();
            $table->string('sport_name', 80)->nullable();
            $table->string('event_id', 80)->nullable()->index();
            $table->string('event_name', 200);
            $table->string('market_id', 80)->nullable();
            $table->string('market_name', 120)->default('Match Odds');
            $table->string('selection_id', 80)->nullable();
            $table->string('selection_name', 150);
            $table->string('bet_type', 20)->default('BACK');
            $table->decimal('odds', 10, 2)->default(2.00);
            $table->decimal('run_line', 10, 2)->nullable();
            $table->decimal('stake', 16, 2);
            $table->decimal('potential_profit', 16, 2)->default(0);
            $table->decimal('liability', 16, 2)->default(0);
            $table->decimal('profit_loss', 16, 2)->default(0);
            $table->string('status', 20)->default('MATCHED')->index();
            $table->string('result_summary', 255)->nullable();
            $table->json('game_details_json')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('settled_at')->nullable();
            $table->timestamps();
        });

        Schema::create('account_statements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('credit', 16, 2)->default(0);
            $table->decimal('debit', 16, 2)->default(0);
            $table->decimal('balance', 16, 2)->default(0);
            $table->string('remark', 255);
            $table->string('from_to', 180)->default('System -> User');
            $table->string('ref_type', 40)->default('system')->index();
            $table->string('ref_id', 80)->nullable();
            $table->timestamps();
        });

        Schema::create('user_bank_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('holder_name', 120);
            $table->string('bank_name', 120);
            $table->string('account_number', 60);
            $table->string('ifsc_code', 30);
            $table->string('upi_id', 100)->nullable();
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });

        Schema::create('banking_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('tx_ref', 40)->unique();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('type', 20)->index(); // deposit, withdraw, transfer, reward
            $table->string('method', 40)->default('UPI');
            $table->decimal('amount', 16, 2);
            $table->decimal('charges', 16, 2)->default(0);
            $table->decimal('final_amount', 16, 2);
            $table->string('utr_number', 80)->nullable();
            $table->string('receipt_path', 255)->nullable();
            $table->unsignedBigInteger('bank_account_id')->nullable();
            $table->json('bank_details_json')->nullable();
            $table->string('status', 20)->default('PENDING')->index();
            $table->string('admin_note', 255)->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('user_multi_markets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('event_id', 60)->index();
            $table->string('sport_id', 30)->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'event_id']);
        });

        Schema::create('password_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('changed_by', 60)->default('Self');
            $table->string('remark', 255)->default('Password Changed Successfully');
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();
        });

        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('login_status', 60)->default('Login Success');
            $table->string('ip_address', 45)->default('127.0.0.1');
            $table->string('isp', 120)->default('Broadband Telecom');
            $table->string('city_state_country', 150)->default('Kolkata / West Bengal / India');
            $table->string('user_agent', 255)->nullable();
            $table->timestamps();
        });

        Schema::create('site_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key', 100)->unique();
            $table->longText('value')->nullable();
            $table->string('group', 40)->default('general')->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_settings');
        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('password_histories');
        Schema::dropIfExists('user_multi_markets');
        Schema::dropIfExists('banking_transactions');
        Schema::dropIfExists('user_bank_accounts');
        Schema::dropIfExists('account_statements');
        Schema::dropIfExists('bets');
        Schema::dropIfExists('casino_games');
        Schema::dropIfExists('exchange_events');
        Schema::dropIfExists('sports');
        Schema::dropIfExists('otp_verifications');
    }
};

