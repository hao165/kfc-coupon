<?php

namespace App\Console\Commands;

use App\Constant\CouponStatusType;
use App\Models\Comment;
use App\Models\Coupon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\URL;
use Spatie\Sitemap\SitemapGenerator;

class GenerateSitemap extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sitemap:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '建立新的Sitemap.xml';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $sitemap = App::make("sitemap");

        $sitemap->add(route('coupons.index'), date("Y-m-d"), '1.0', 'daily');
        $sitemap->add(route('coupons.expired'), date("Y-m-d"), '0.9', 'daily');
        $sitemap->add(route('collect.rank'), date("Y-m-d"), '0.9', 'daily');

        $coupons = Coupon::where('status', CouponStatusType::USABLE)
            ->orWhere('status', CouponStatusType::EXPIRED)
            ->get();

        $coupons->each(function ($item, $key) use ($sitemap) {
            $sitemap->add(route('coupons.show', $item->slug), $item->created_at, '0.8', 'weekly');
        });

        $chatPageCount = (int) ceil((Comment::count())/15);
        for ($i=1; $i<$chatPageCount; $i++) {
            $sitemap->add(route('chat.index', $i), date("Y-m-d"), '0.6', 'weekly');
        }

        $sitemap->store('xml', 'sitemap');
    }
}
