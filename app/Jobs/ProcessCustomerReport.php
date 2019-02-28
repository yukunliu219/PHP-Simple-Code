<?php

namespace App\Jobs;

use App\Http\Clients\SlackWebhookClient;
use App\Reports\CustomersReport;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class ProcessCustomerReport implements ShouldQueue
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
        $customersReport = new CustomersReport($this->option);
        $slackClient->postMethod([
            'headers' => ['Content-Type' => 'application/json'],
            'body' => json_encode($customersReport->getCustomersReport())
        ]);
    }
}
