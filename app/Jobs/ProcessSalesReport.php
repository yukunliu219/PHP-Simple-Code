<?php

namespace App\Jobs;

use App\Http\Clients\SlackWebhookClient;
use App\Reports\SalesReport;
use App\Repositories\Transactions;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class ProcessSalesReport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 5;
    protected $option;
    protected $slackWebhoodURL;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($option, $slackWebhoodURL)
    {
        $this->option = $option;
        $this->slackWebhoodURL = $slackWebhoodURL;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $slackClient = new SlackWebhookClient($this->slackWebhoodURL);
        $salesReport = new SalesReport($this->option);
        $slackClient->postMethod([
            'headers' => ['Content-Type' => 'application/json'],
            'body' => json_encode($salesReport->getSalesReport())
        ]);
    }
}
