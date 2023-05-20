<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class GithubAPIController extends Controller
{
        public function getPullRequests()
        {
            $date = Carbon::today()->subDays(14)->format('Y-m-d');
            $token = env('GITHUB_TOKEN');

            $response = Http::withHeaders([
                'Accept' => 'application/vnd.github.v3+json',
                'Authorization' => 'Bearer ' . $token,
            ])->get('https://api.github.com/search/issues?q=repo:woocommerce/woocommerce+is:open+is:pr+created:<'.$date);
            $data = $response->json();
            return $data;
            $filePath = storage_path('app/public/old_pull_requests.txt');
            file_put_contents($filePath, $data);

    }
}
    