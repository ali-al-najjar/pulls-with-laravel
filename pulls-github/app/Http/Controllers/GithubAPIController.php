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
        public function getPRs()
        {
            $state = "open";
            $date = Carbon::today()->subDays(14)->format('Y-m-d');
            $page = 1;
            $perPage = 30;
            $hasNextPage = true;
            $data = [];

            $header = [
                'PR-ID',
                'PR#',
                'PR-Title',
                'State',
                'Link',
                'Created At'
            ];
            Sheets::spreadsheet(env('POST_SPREADSHEET_ID', ''))->sheet('Open PRs')->append([$header]);
            
            $values = Sheets::spreadsheet(env('POST_SPREADSHEET_ID', ''))->sheet('Open PRs')->all();
            $fields = $values[0];
            $sheetData = [];
            for ($i = 1; $i < count($values); $i++) {
                $row = $values[$i];
                $rowData = [];
                for ($j = 0; $j < count($row); $j++) {
                    $rowData[$fields[$j]] = $row[$j];
                }

                $sheetData[] = $rowData;
            }
            $jsonData = json_encode($sheetData);
            

            while ($hasNextPage) {
                $response = Http::withHeaders([
                    'Accept' => 'application/vnd.github+json',
                    'Authorization' => 'Bearer ' . $this->token,
                    'X-GitHub-Api-Version: 2022-11-28'
                ])
                    ->timeout(60)
                    ->get("https://api.github.com/repos/{$this->owner}/{$this->repo}/pulls", [
                        'state' => $state,
                        'per_page' => $perPage,
                        'page' => $page,
                        'direction' => 'desc',
                    ]);
        
                $responseData = $response->json();
                $filteredData = array_filter($responseData, function ($pr) use ($date) {
                    return $pr['created_at'] < $date;
                });
        
                $data = array_merge($data, $filteredData);

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
            }
            $prData = [];
            
            foreach ($data as $pr) {
                $prData[] = [
                    $pr['id'],
                    $pr['number'],
                    $pr['title'],
                    $pr['state'],
                    $pr['html_url'],
                    $pr['created_at']
                ];
            }
            if (count($values) <= 1) {
                Sheets::spreadsheet(env('POST_SPREADSHEET_ID', ''))->sheet('Open PRs')->append($prData);
            } else {
                $existingPRs = array_column($sheetData, 'PR-ID');
                $newData = array_filter($prData, function ($pr) use ($existingPRs) {
                    return !in_array($pr[0], $existingPRs);
                });
                if (!empty($newData)) {
                    Sheets::spreadsheet(env('POST_SPREADSHEET_ID', ''))->sheet('Open PRs')->append($newData);
                }
            }
            $count = count($data);
            echo $count;
            return $data;
        }


    public function getPRsWithReview()
        {
            $state = "open";
            $page = 1;
            $perPage = 30;
            $hasNextPage = true;
            $data = [];

            $header = [
                'PR-ID',
                'PR#',
                'PR-Title',
                'State',
                'Link',
                'Created At'
            ];
            Sheets::spreadsheet(env('POST_SPREADSHEET_ID', ''))->sheet('PRs with Reviews')->append([$header]);
            
            $values = Sheets::spreadsheet(env('POST_SPREADSHEET_ID', ''))->sheet('PRs with Reviews')->all();
            $fields = $values[0];
            $sheetData = [];
            for ($i = 1; $i < count($values); $i++) {
                $row = $values[$i];
                $rowData = [];
                for ($j = 0; $j < count($row); $j++) {
                    $rowData[$fields[$j]] = $row[$j];
                }

                $sheetData[] = $rowData;
            }
            $jsonData = json_encode($sheetData);
            

            while ($hasNextPage) {
                $response = Http::withHeaders([
                    'Accept' => 'application/vnd.github+json',
                    'Authorization' => 'Bearer ' . $this->token,
                    'X-GitHub-Api-Version: 2022-11-28'
                ])
                    ->timeout(60)
                    ->get("https://api.github.com/repos/{$this->owner}/{$this->repo}/pulls", [
                        'state' => $state,
                        'per_page' => $perPage,
                        'page' => $page,
                        'direction' => 'desc',
                    ]);
        
                $responseData = $response->json();
                $filteredData = array_filter($responseData, function ($pr) {
                    return !empty($pr['requested_reviewers']);
                });
        
                $data = array_merge($data, $filteredData);

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
            }
            $prData = [];
            
            foreach ($data as $pr) {
                $prData[] = [
                    $pr['id'],
                    $pr['number'],
                    $pr['title'],
                    $pr['state'],
                    $pr['html_url'],
                    $pr['created_at']
                ];
            }
            if (count($values) <= 1) {
                Sheets::spreadsheet(env('POST_SPREADSHEET_ID', ''))->sheet('PRs with Reviews')->append($prData);
            } else {
                $existingPRs = array_column($sheetData, 'PR-ID');
                $newData = array_filter($prData, function ($pr) use ($existingPRs) {
                    return !in_array($pr[0], $existingPRs);
                });
                if (!empty($newData)) {
                    Sheets::spreadsheet(env('POST_SPREADSHEET_ID', ''))->sheet('PRs with Reviews')->append($newData);
                }
            }
            $count = count($data);
            echo $count;
            return $data;
    }

    public function getPRsWithSuccess()
        {
            $response = Http::withHeaders([
                'Accept' => 'application/vnd.github+json',
                'Authorization' => 'Bearer ' . $this->token,
            ])->get("https://api.github.com/search/issues?q=repo:{$this->owner}/{$this->repo}+is:open+is:pr+status:success");
            $data = $response->json();
            $prs = $data['items'];
            $prData = [];
            
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
            return $data;

    }

    public function getunassignedPRs()
        {
            $response = Http::withHeaders([
                'Accept' => 'application/vnd.github+json',
                'Authorization' => 'Bearer ' . $this->token,
            ])->get("https://api.github.com/search/issues?q=repo:{$this->owner}/{$this->repo}+is:open+is:pr+no:assignee");
            $data = $response->json();
            $prs = $data['items'];
            $prData = [];
            
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
    