<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class GithubAPIController extends Controller
{
        public function getPRs()
        {
            $date = Carbon::today()->subDays(14)->format('Y-m-d');
            $token = env('GITHUB_TOKEN');

            $response = Http::withHeaders([
                'Accept' => 'application/vnd.github.v3+json',
                'Authorization' => 'Bearer ' . $token,
            ])->get('https://api.github.com/search/issues?q=repo:woocommerce/woocommerce+is:open+is:pr+created:<'.$date);
            $data = $response->json();
            return $data;


    }

    public function getPRsWithReview()
        {
            $date = Carbon::today()->subDays(14)->format('Y-m-d');
            $token = env('GITHUB_TOKEN');

            $response = Http::withHeaders([
                'Accept' => 'application/vnd.github.v3+json',
                'Authorization' => 'Bearer ' . $token,
            ])->get('https://api.github.com/search/issues?q=repo:woocommerce/woocommerce+is:open+is:pr+review:required');
            $data = $response->json();
            return $data;

    }
}
    