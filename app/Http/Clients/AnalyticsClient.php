<?php
/**
 * Created by PhpStorm.
 * User: gliu
 * Date: 8/7/18
 * Time: 10:15 AM
 */

namespace App\Http\Clients;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Log;

class AnalyticsClient extends Client
{
    public $basicURL;

    public function __construct(array $config = [])
    {
        $this->basicURL = env('BASIC_URL_ANALYTIC') . '/api';
        parent::__construct($config);
    }

    public function getMethod($relativeURL, $config)
    {
        $client = new Client();
        $response = null;
        try{
            $response = $client->get($this->basicURL . $relativeURL, $config);
        }
        catch (RequestException $e) {
            Log::debug(__FILE__ . " line:" . __LINE__ . " AnalyticsClient: receive 404.");
        }
        return $response;
    }
}