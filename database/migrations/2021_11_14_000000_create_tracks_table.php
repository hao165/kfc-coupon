<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTracksTable extends Migration
{
    /**
     * 追蹤PTT並轉發Line Notify
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tracks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');
            $table->string('cls')->comment('追蹤看板');
            $table->tinyInteger('page')->default('5')->comment('追蹤頁數');
            $table->json('keyword')->comment('關鍵字(陣列)');
            $table->tinyInteger('push')->comment('推數 0:不追蹤');
            $table->boolean('status')->default('1')->comment('1:啟用 2:停用 3:刪除');
            $table->timestamps();

            $table->unique(['user_id', 'cls']);
        });

        Schema::create('track_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');
            $table->foreignId('track_id');
            $table->string('type')->comment('類型：keyword、push');
            $table->string('title')->comment('文章標題');
            $table->string('url')->comment('文章網址');
            $table->boolean('status')->default('0')->comment('是否已發布');
            $table->timestamp('created_at')->nullable()->comment('建立時間');

            $table->unique(['user_id', 'url']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('line_notify')->nullable()->after('social');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tracks');
        Schema::dropIfExists('track_items');
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('line_notify');
        });
    }
}
