<?php

namespace Tests\Unit;

use App\Http\Clients\AnalyticsClient;
use App\Http\Clients\SlackWebhookClient;
use App\Http\Clients\SquareClient;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Log;

class ClientsTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testSquareClient()
    {
        $client = new SquareClient();
        $responseTransactions = $client->getMethod('locations/' . env('SQUARE_LOCATION_ID') . '/transactions',
            [
                'headers' => [
                    'Authorization' => 'Bearer ' . env('SQUARE_SANDBOX_ACCESS_TOKEN')
                ]
            ]);
        $transactions = json_decode($responseTransactions->getBody(), true);
        $this->assertTrue(array_key_exists('transactions', $transactions));
    }

    public function testAnalyticClient()
    {
        $client = new AnalyticsClient(); //
        $responseWaivers = $client->getMethod('/waivers',
            [
                'headers' => [
                    'Content-Type' => 'application/json'
                ]
            ]);

        $this->assertTrue(!is_null($responseWaivers));
        $this->assertTrue(array_key_exists('data', json_decode($responseWaivers->getBody(), true)));
    }

}
