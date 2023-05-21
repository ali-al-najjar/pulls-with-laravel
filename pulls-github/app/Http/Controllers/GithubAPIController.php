<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use Carbon\Carbon;



class GithubAPIController extends Controller
{
    public $token;

    public function __construct()
    {
        $this->token = env('GITHUB_TOKEN');
    } 
        public function getPRs()
        {
            $date = Carbon::today()->subDays(14)->format('Y-m-d');
            $response = Http::withHeaders([
                'Accept' => 'application/vnd.github.v3+json',
                'Authorization' => 'Bearer ' . $this->token,
            ])->get('https://api.github.com/search/issues?q=repo:woocommerce/woocommerce+is:open+is:pr+created:<'.$date);
            $data = $response->json();
            return $data;


    }

    public function getPRsWithReview()
        {
            $response = Http::withHeaders([
                'Accept' => 'application/vnd.github.v3+json',
                'Authorization' => 'Bearer ' . $this->token,
            ])->get('https://api.github.com/search/issues?q=repo:woocommerce/woocommerce+is:open+is:pr+review:required');
            $data = $response->json();
            return $data;

    }

    public function getPRsWithSuccess()
        {
            $response = Http::withHeaders([
                'Accept' => 'application/vnd.github.v3+json',
                'Authorization' => 'Bearer ' . $this->token,
            ])->get('https://api.github.com/search/issues?q=repo:woocommerce/woocommerce+is:open+is:pr+status:success');
            $data = $response->json();
            return $data;

    }


}
    