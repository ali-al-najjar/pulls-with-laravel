<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class GithubAPIController extends Controller
{
        public function getPullRequests()
        {
            $owner = "woocommerce";
            $repo = "woocommerce";
            $state = "open";
            $date = Carbon::today()->subDays(1)->format('Y-m-d');
    
            $token = env('GITHUB_TOKEN');
            
            $response = Http::withHeaders([
                'Authorization' => "Bearer $token"

            ])
            ->timeout(60)
            ->get("https://api.github.com/repos/{$owner}/{$repo}/pulls", [
                'state' => $state,
                'per_page' => 50,
            ]);
    
            $data = $response->json();
            $count = count($data);
            echo$count;
            return $data;
        }
    }
    
