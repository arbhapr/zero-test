<?php

namespace App\Commands;

use Illuminate\Support\Facades\Cache;
use LaravelZero\Framework\Commands\Command;

class ReviewSummaryCommand extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'review:summary';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Summary list of review';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $cacheKey = 'all-products';

        if (Cache::has($cacheKey)) {
            dd('from-cache', Cache::get($cacheKey));
        }

        $ratings = [
            1 => 0,
            2 => 0,
            3 => 0,
            4 => 0,
            5 => 0,
        ];
        $rawReviews = json_decode(file_get_contents(storage_path() . "/reviews.json"), true);
        $rawProducts = json_decode(file_get_contents(storage_path() . "/products.json"));

        $reviews = collect($rawReviews);

        foreach ($reviews as $n => $item) {
            if ($item['rating'] == 1) $ratings[1] += 1;
            elseif ($item['rating'] == 2) $ratings[2] += 1;
            elseif ($item['rating'] == 3) $ratings[3] += 1;
            elseif ($item['rating'] == 4) $ratings[4] += 1;
            elseif ($item['rating'] == 5) $ratings[5] += 1;
        }

        $total_review = ($ratings[1] * 1) + ($ratings[2] * 2) + ($ratings[3] * 3) + ($ratings[4] * 4) + ($ratings[5] * 5);

        $response = (object) [
            'avg_reviews' => (count($reviews) > 0) ? number_format($total_review / count($reviews), 2) : 0,
            'total_reviews' => count($reviews),
            'review_per_rating' => $ratings,
        ];
        Cache::put($cacheKey, $response, 180);
        dd('not-from-cache', $response);
    }
}
