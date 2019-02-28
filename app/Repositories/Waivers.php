<?php
/**
 * Created by PhpStorm.
 * User: gliu
 * Date: 8/7/18
 * Time: 10:34 AM
 */

namespace App\Repositories;

use DateTime;
use App\Http\Clients\AnalyticsClient;
use Log;

class Waivers extends BaseRepository
{
    public function __construct()
    {

    }

    public function getDataFromWaiverService($query)
    {
        $client = new AnalyticsClient(); //
        $responseWaivers = $client->getMethod('/waivers' . $query,
            [
                'headers' => [
                    'Content-Type' => 'application/json'
                ]
            ]);

        if (is_null($responseWaivers)) {
            return null;
        }
        return json_decode($responseWaivers->getBody(), true);
    }
}