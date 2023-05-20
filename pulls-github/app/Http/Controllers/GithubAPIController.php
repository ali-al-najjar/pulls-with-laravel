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
            $pull_requests = [];
            $date = Carbon::today()->subDays(14)->format('Y-m-d');
            $token = env('GITHUB_TOKEN');
            $page= 1;
            $per_page= 30;
            $next_page=true;

            while ($next_page) {
            $response = Http::withHeaders([
                'Authorization' => "Bearer $token"

            ])
            ->timeout(60)
            ->get("https://api.github.com/repos/{$owner}/{$repo}/pulls", [
                'state' => $state,
                'per_page' => $per_page,
                'page' => $page
            ],
            );

            $data = json_decode($response->getBody(), true);
            foreach ($data as $pr) {
                $created_date = date('Y-m-d', strtotime($pr['created_at']));
                if ($created_date <= $date) {
                    $pull_requests[] = $pr;
                }
            }

            $linkHeader = $response->header('Link');
            if ($linkHeader) {
                $links = $this->parseLinkHeader($linkHeader);
                if (isset($links['next'])) {
                    $page++;
                } else {
                    $next_page = false;
                }
            } else {
                $next_page = false;
            }
    }
            $result = response()->json($pull_requests);
            $json_data = $result->getContent();
            $data_array = json_decode($json_data, true);
            $count = count($data_array);
            echo $count;
            return $data_array;


        }
        private function parseLinkHeader($header)
        {
            $links = [];
            $matches = [];
            $pattern = '/<([^>]+)>;\s*rel="([^"]+)"/';
    
            preg_match_all($pattern, $header, $matches);
    
            foreach ($matches[2] as $index => $rel) {
                $links[$rel] = $matches[1][$index];
            }
    
            return $links;
        }
    
    }
    