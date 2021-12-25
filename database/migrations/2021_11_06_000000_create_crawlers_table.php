<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCrawlersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('crawlers', function (Blueprint $table) {
            $table->id();
            $table->string('cls')->comment('所屬看板');
            $table->string('slug')->unique()->comment('文章Slug');
            $table->string('title')->nullable()->comment('文章標題');
            $table->timestamp('last_at')->nullable()->comment('最後一則回覆的時間');
            $table->boolean('status')->default('1')->comment('1:啟用 2:停用 3:刪除');
            $table->timestamps();
        });
        Schema::create('crawler_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('crawler_id');
            $table->boolean('status')->default('0')->comment('是否已發布');
            $table->string('ptt_id')->comment('回覆者PttID');
            $table->string('content')->comment('回覆內容');
            $table->timestamp('created_at')->comment('回覆時間');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('crawlers');
        Schema::dropIfExists('crawler_items');
    }
}
