<?php
/**
 * Created by PhpStorm.
 * User: gliu
 * Date: 8/7/18
 * Time: 4:43 PM
 */

namespace App\Repositories;

use App\Http\Clients\SquareClient;
use Log;

class Transactions extends BaseRepository
{

    public function __construct()
    {

    }

    public function getTransactionsFromSquare($query)
    {
        $client = new SquareClient();
        $responseTransactions = $client->getMethod('locations/' . env('SQUARE_LOCATION_ID') . '/transactions' . $query,
            [
                'headers' => [
                    'Authorization' => 'Bearer ' . env('SQUARE_SANDBOX_ACCESS_TOKEN')
                ]
            ]);

        $transactions = json_decode($responseTransactions->getBody(), true);

        $transactionsList = array($transactions);

        // check if the transactions are over 50, if so, sending following request to get the rest transactions.
        while (array_key_exists("cursor", $transactions)) {
            $responseTransactions = $client->getMethod('locations/' . env('SQUARE_LOCATION_ID') . '/transactions?cursor=' . $transactions["cursor"],
                [
                    'headers' => [
                        'Authorization' => 'Bearer ' . env('SQUARE_SANDBOX_ACCESS_TOKEN')
                    ]
                ]);
            $transactions = json_decode($responseTransactions->getBody(), true);
            array_push($transactionsList, $transactions);
        }

        return $transactionsList;
    }
}