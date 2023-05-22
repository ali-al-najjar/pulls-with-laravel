<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use Carbon\Carbon;
use Revolution\Google\Sheets\Facades\Sheets;

class GithubAPIController extends Controller
{
    public $token;
    public $owner;
    public $repo;

    public function __construct()
    {
        $this->token = env('GITHUB_TOKEN');
        $this->owner = "woocommerce";
        $this->repo = "woocommerce";
        
    } 

    public function getOpenPRs()
    {
        $date = Carbon::today()->subDays(14)->format('Y-m-d');
        $page = 1;
        $hasNextPage = true;
        $header = [
            'PR-ID',
            'PR#',
            'PR-Title',
            'State',
            'Link',
            'Created At'
        ];
        Sheets::spreadsheet(env('POST_SPREADSHEET_ID', ''))->sheet('Open PRs')->append([$header]);
        while ($hasNextPage) {
            $response = Http::withHeaders([
                'Accept' => 'application/vnd.github+json',
                'Authorization' => 'Bearer ' . $this->token,
            ])->get("https://api.github.com/search/issues?page=$page&q=repo:{$this->owner}/{$this->repo}+is:open+is:pr+created:<$date");
    
            $data = $response->json();
            $prs = $data['items'];
            $prData = [];
            $linkHeader = $response->header('Link');
            if ($linkHeader) {
                $links = $this->parseLinkHeader($linkHeader);
                if (isset($links['next'])) {
                    $page++;
                } else {
                    $hasNextPage = false;
                }
            } else {
                $hasNextPage = false;
            }
            
            foreach ($prs as $pr) {
                $prData[] = [
                    $pr['id'],
                    $pr['number'],
                    $pr['title'],
                    $pr['state'],
                    $pr['html_url'],
                    $pr['created_at']
                ];
            }
            
            Sheets::spreadsheet(env('POST_SPREADSHEET_ID', ''))->sheet('Open PRs')->append($prData);
        }
        
        return $data;
    }

    public function getPRsWithSuccess()
    {
        $page = 1;
        $hasNextPage = true;
        $header = [
            'PR-ID',
            'PR#',
            'PR-Title',
            'State',
            'Link',
            'Created At'
        ];
        Sheets::spreadsheet(env('POST_SPREADSHEET_ID', ''))->sheet('PRs with Success')->append([$header]);
        while ($hasNextPage) {
            $response = Http::withHeaders([
                'Accept' => 'application/vnd.github+json',
                'Authorization' => 'Bearer ' . $this->token,
            ])->get("https://api.github.com/search/issues?page=$page&q=repo:{$this->owner}/{$this->repo}+is:open+is:pr+status:success");
    
            $data = $response->json();
            $prs = $data['items'];
            $prData = [];
            $linkHeader = $response->header('Link');
            if ($linkHeader) {
                $links = $this->parseLinkHeader($linkHeader);
                if (isset($links['next'])) {
                    $page++;
                } else {
                    $hasNextPage = false;
                }
            } else {
                $hasNextPage = false;
            }
            
            foreach ($prs as $pr) {
                $prData[] = [
                    $pr['id'],
                    $pr['number'],
                    $pr['title'],
                    $pr['state'],
                    $pr['html_url'],
                    $pr['created_at']
                ];
            }
            
            Sheets::spreadsheet(env('POST_SPREADSHEET_ID', ''))->sheet('PRs with Success')->append($prData);
        }
        
        return $data;
    }

    public function getUnassignedPRs()
    {
        $page = 1;
        $hasNextPage = true;
        $header = [
            'PR-ID',
            'PR#',
            'PR-Title',
            'State',
            'Link',
            'Created At'
        ];
        Sheets::spreadsheet(env('POST_SPREADSHEET_ID', ''))->sheet('PRs without assignees')->append([$header]);
        while ($hasNextPage) {
            $response = Http::withHeaders([
                'Accept' => 'application/vnd.github+json',
                'Authorization' => 'Bearer ' . $this->token,
            ])->get("https://api.github.com/search/issues?page=$page&q=repo:{$this->owner}/{$this->repo}+is:open+is:pr+no:assignee");
    
            $data = $response->json();
            $prs = $data['items'];
            $prData = [];
            $linkHeader = $response->header('Link');
            if ($linkHeader) {
                $links = $this->parseLinkHeader($linkHeader);
                if (isset($links['next'])) {
                    $page++;
                } else {
                    $hasNextPage = false;
                }
            } else {
                $hasNextPage = false;
            }
            
            foreach ($prs as $pr) {
                $prData[] = [
                    $pr['id'],
                    $pr['number'],
                    $pr['title'],
                    $pr['state'],
                    $pr['html_url'],
                    $pr['created_at']
                ];
            }
            
            Sheets::spreadsheet(env('POST_SPREADSHEET_ID', ''))->sheet('PRs without assignees')->append($prData);
        }
        
        return $data;
    }

    public function getPRsRequired()
    {
        $page = 1;
        $hasNextPage = true;
        $header = [
            'PR-ID',
            'PR#',
            'PR-Title',
            'State',
            'Link',
            'Created At'
        ];
        Sheets::spreadsheet(env('POST_SPREADSHEET_ID', ''))->sheet('PRs Required')->append([$header]);
        while ($hasNextPage) {
            $response = Http::withHeaders([
                'Accept' => 'application/vnd.github+json',
                'Authorization' => 'Bearer ' . $this->token,
            ])->get("https://api.github.com/search/issues?page=$page&q=repo:{$this->owner}/{$this->repo}+is:open+is:pr+review:required");
    
            $data = $response->json();
            $prs = $data['items'];
            $prData = [];
            $linkHeader = $response->header('Link');
            if ($linkHeader) {
                $links = $this->parseLinkHeader($linkHeader);
                if (isset($links['next'])) {
                    $page++;
                } else {
                    $hasNextPage = false;
                }
            } else {
                $hasNextPage = false;
            }
            
            foreach ($prs as $pr) {
                $prData[] = [
                    $pr['id'],
                    $pr['number'],
                    $pr['title'],
                    $pr['state'],
                    $pr['html_url'],
                    $pr['created_at']
                ];
            }
            
            Sheets::spreadsheet(env('POST_SPREADSHEET_ID', ''))->sheet('PRs Required')->append($prData);
        }
        
        return $data;
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
    