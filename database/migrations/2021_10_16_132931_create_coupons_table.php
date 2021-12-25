<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCouponsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');
            $table->string('slug')->unique()->comment('代稱(網址)');
            $table->string('title')->comment('標題(代號)');
            $table->string('sub_title')->nullable()->comment('備註/說明');
            $table->string('image')->nullable()->comment('照片');
            $table->string('content')->nullable()->comment('詳細內容'); //券-內容物+數量
            $table->string('tag')->nullable()->comment('標籤');
            $table->decimal('old_price', 5, 0 )->nullable()->comment('原始金額');
            $table->decimal('new_price', 5, 0 )->nullable()->comment('最後金額');
            $table->decimal('discount', 5, 2)->nullable()->comment('折扣數');
            $table->integer('view_cou')->default('0')->comment('查看數');
            $table->integer('collect_cou')->default('0')->comment('收藏數');
            $table->integer('comment_cou')->default('0')->comment('留言數');
            $table->date('start_at')->nullable()->comment('起時間');
            $table->date('end_at')->nullable()->comment('訖時間');
            $table->boolean('status')->default('0')->comment('優惠券狀態');
            $table->softDeletes();
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
        Schema::dropIfExists('coupons');
    }
}
