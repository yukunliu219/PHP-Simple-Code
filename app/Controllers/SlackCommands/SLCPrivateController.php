<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessCustomerReport;
use App\Jobs\ProcessSalesReport;
use App\Jobs\ProcessSummaryReport;
use App\Reports\BaseReport;
use Illuminate\Http\Request;
use Log;


class SLCPrivateController extends Controller
{
    private function getSalckWebhookURL ()
    {
        return env('SLACK_URL_WEBHOOK_SLC_PRIVATE');
    }

    private function getUnknownResponse()
    {
        return response()->json(
            array(
                "response_type" => "ephemeral",
                "text" => "Sorry, unknown option. Please pick 'today', 'yesterday', or 'last week' ."
            ),
            200
        );
    }

    public function salesSLCPrivate(Request $request)
    {
        $config = new BaseReport();

        // get command
        $command = $request->input('command');
        $text = strtolower($request->input('text'));

        // sales command
        if (strcmp($command, "/sales") == 0) {
            if (!isset($text) || $text == "") {
                ProcessSalesReport::dispatch($config::TEXT_TODAY, $this->getSalckWebhookURL())->delay(now()->addSecond(3));
            } else {
                if ($config->isOptionsExist($text)) {
                    // dispatch get sales report 3 second later
                    ProcessSalesReport::dispatch($text, $this->getSalckWebhookURL())->delay(now()->addSecond(3));
                } else {
                    return $this->getUnknownResponse();
                }
            }

            // immediately reply received message
            return response()->json(
                array(
                    "response_type" => "ephemeral",
                    "text" => "Loading your data, Please wait."
                ),
                200
            );
        }

        return response()->json('received command = ' . $command, 200);
    }

    public function customersSLCPrivate(Request $request)
    {
        $config = new BaseReport();

        // get command
        $command = $request->input('command');
        $text = strtolower($request->input('text'));

        // sales command
        if (strcmp($command, "/customers") == 0) {
            if (!isset($text) || $text == "") {
                ProcessCustomerReport::dispatch($config::TEXT_TODAY, $this->getSalckWebhookURL())->delay(now()->addSecond(3));
            } else {
                if ($config->isOptionsExist($text)) {
                    ProcessCustomerReport::dispatch($text, $this->getSalckWebhookURL())->delay(now()->addSecond(3));
                } else {
                    return $this->getUnknownResponse();
                }
            }

            // immediately reply received message
            return response()->json(
                array(
                    "response_type" => "ephemeral",
                    "text" => "Loading your data, Please wait."
                ),
                200
            );
        }

        return response()->json('received command = ' . $command, 200);
    }

    public function summarySLCPrivate(Request $request)
    {
        $config = new BaseReport();

        // get command
        $command = $request->input('command');
        $text = strtolower($request->input('text'));

        // sales command
        if (strcmp($command, "/summary") == 0) {
            if (!isset($text) || $text == "") {
                ProcessSummaryReport::dispatch($config::TEXT_TODAY, $this->getSalckWebhookURL())->delay(now()->addSeconds(3));
            } else {
                if ($config->isOptionsExist($text)) {
                    ProcessSummaryReport::dispatch($text, $this->getSalckWebhookURL())->delay(now()->addSeconds(3));
                } else {
                    return $this->getUnknownResponse();
                }
            }

            // immediately reply received message
            return response()->json(
                array(
                    "response_type" => "ephemeral",
                    "text" => "Loading your data, Please wait."
                ),
                200
            );
        }

        return response()->json('received command = ' . $command, 200);
    }
}
