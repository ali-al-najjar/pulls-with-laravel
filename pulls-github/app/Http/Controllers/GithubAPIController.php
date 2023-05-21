<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use Carbon\Carbon;
use Revolution\Google\Sheets\Facades\Sheets;


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
                'Accept' => 'application/vnd.github+json',
                'Authorization' => 'Bearer ' . $this->token,
            ])->get('https://api.github.com/search/issues?q=repo:woocommerce/woocommerce+is:open+is:pr+created:<'.$date);
            $data = $response->json();
            $prs = $data['items'];
            $prData = [];
            
            foreach ($prs as $pr) {
                $prData[] = [
                    $pr['id'],
                    $pr['title'],
                    $pr['state'],
                    $pr['html_url'],
                    $pr['created_at']
                ];
            }
            
            Sheets::spreadsheet(env('POST_SPREADSHEET_ID', ''))->sheet('DataSheet')->append($prData);
            
            return $data;
    }

    public function getPRsWithReview()
        {
            $response = Http::withHeaders([
                'Accept' => 'application/vnd.github+json',
                'Authorization' => 'Bearer ' . $this->token,
            ])->get('https://api.github.com/search/issues?q=repo:woocommerce/woocommerce+is:open+is:pr+review:required');
            $data = $response->json();
            return $data;

    }

    public function getPRsWithSuccess()
        {
            $response = Http::withHeaders([
                'Accept' => 'application/vnd.github+json',
                'Authorization' => 'Bearer ' . $this->token,
            ])->get('https://api.github.com/search/issues?q=repo:woocommerce/woocommerce+is:open+is:pr+status:success');
            $data = $response->json();
            return $data;

    }

    public function getunassignedPRs()
        {
            $response = Http::withHeaders([
                'Accept' => 'application/vnd.github+json',
                'Authorization' => 'Bearer ' . $this->token,
            ])->get('https://api.github.com/search/issues?q=repo:woocommerce/woocommerce+is:open+is:pr+no:assignee');
            $data = $response->json();
            return $data;

    }


}
    