<?php
/**
 * Created by PhpStorm.
 * User: gliu
 * Date: 8/9/18
 * Time: 9:25 PM
 */

namespace App\Http\Clients;

use GuzzleHttp\Client;
use Log;

class SlackWebhookClient extends Client
{
    public $basicURL;

    public function __construct($basic_url)
    {
        $this->basicURL = $basic_url;
        parent::__construct();
    }

    public function postMethod($config)
    {
        $client = new Client();
        return $client->post($this->basicURL, $config);
    }

}