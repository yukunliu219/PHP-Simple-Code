<?php
/**
 * Created by PhpStorm.
 * User: gliu
 * Date: 8/7/18
 * Time: 4:45 PM
 */

namespace App\Http\Clients;

use GuzzleHttp\Client;
use Log;

class SquareClient extends Client
{
    public $basicURL;

    public function __construct(array $config = [])
    {
        $this->basicURL = env('BASED_URL_SQUARE');
        parent::__construct($config);
    }

    public function getMethod($relativeURL, $config)
    {
        $client = new Client();
        return $client->get($this->basicURL.$relativeURL, $config);
    }
}