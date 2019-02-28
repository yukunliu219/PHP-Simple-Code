<?php
/**
 * Created by PhpStorm.
 * User: gliu
 * Date: 8/7/18
 * Time: 10:27 AM
 */

namespace App\Repositories;

use App\Http\Clients\AnalyticsClient;

class Bookings extends BaseRepository
{
    public function __construct()
    {

    }

    public function getDataFromBookingService($query)
    {
        $client = new AnalyticsClient(); //
        $responseWaivers = $client->getMethod('/bookings' . $query,
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