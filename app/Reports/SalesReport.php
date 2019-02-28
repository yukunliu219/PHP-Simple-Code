<?php
/**
 * Created by PhpStorm.
 * User: gliu
 * Date: 8/15/18
 * Time: 7:51 PM
 */

namespace App\Reports;

use App\Charts\SalesCharts;
use App\Repositories\Transactions;
use DateTime;
use Log;

class SalesReport extends BaseReport
{
    public function __construct($option_text)
    {
        $this->setDateOptions($option_text);
    }

    private function getVoidResultArray()
    {
        return [
            self::TEXT_SALES => 0,
            self::TEXT_FEE => 0,
            self::TEXT_NET_SALES => 0,
            'text_sales' => "$0",
            'text_fee' => "$0",
            "text_net_sales" => "$0",
        ];
    }

    private function getVoidSummaryResultArray()
    {
        return [
            self::TEXT_TOTAL_NET_SALES => 0,
            "max_sales" => 0,
            "min_sales" => 0,
            'date' => ""
        ];
    }

    public function getSalesReport()
    {
        if ($this->option_date == self::UNKNOWN) {
            return "Sorry, unknown input date.";
        }

        if ($this->option_date == self::LAST_WEEK) {
            return $this->getLastWeekSalesReport();
        } else {
            return $this->getDailySalesReport();
        }
    }

    private function getDailySalesReport()
    {
        $result_array = $this->getDailySalesDataArray($this->days_before_begin, $this->days_before_end, $this->option_date);
        return $this->getDailyResponseArray($result_array, $this->option_date);
    }

    public function getDailySalesDataArray($days_before_begin, $days_before_end)
    {
        $transactions = new Transactions();
        $transactionsList = $transactions->getTransactionsFromSquare($transactions->getDateQueryString($days_before_begin, $days_before_end));
        $result_array = $this->getVoidResultArray();

        if (!array_key_exists(0, $transactionsList) || !array_key_exists('transactions', $transactionsList[0])) {
            return $result_array;
        }

        $result_array = $this->processDailyData($transactionsList, $result_array);

        $result_array['text_sales'] = "$" . number_format($result_array[self::TEXT_SALES] / 100, 2, '.', ',');
        $result_array['text_fee'] = "$" . number_format($result_array[self::TEXT_FEE] / 100, 2, '.', ',');
        $result_array['text_net_sales'] = "$" . number_format($result_array[self::TEXT_NET_SALES] / 100, 2, '.', ',');

        return $result_array;
    }

    public function processDailyData($transactionsList, $result_array)
    {
        foreach ($transactionsList as $transactions) {
            foreach ($transactions['transactions'] as $transaction) {
                foreach ($transaction['tenders'] as $tender) {
                    $result_array[self::TEXT_SALES] += $tender['amount_money']['amount'];
                    $result_array[self::TEXT_FEE] += $tender['processing_fee_money']['amount'];
                    $result_array[self::TEXT_NET_SALES] += $tender['amount_money']['amount'] - $tender['processing_fee_money']['amount'];
                }
            }
        }

        return $result_array;
    }

    private function getLastWeekSalesReport()
    {
        $result_array = $this->getLastWeekSalesDataArray();
        return $this->getWeeklyReportResponseArray($result_array);
    }

    public function getLastWeekSalesDataArray()
    {
        $transactions = new Transactions();
        $weeklySalesDataList = $this->getVoidSummaryResultArray();
        $weeklySalesDataList['date'] = $this->getWeeklyDateTimeInReportFormat();
        for ($i = 7; $i >= 1; $i--) {
            $weeklyTransactionsList[8 - $i] = $transactions->getTransactionsFromSquare($transactions->getLastWeekDateQueryString($i, $i));
            $weeklySalesDataList[8 - $i] = $this->getVoidResultArray();
        }

        $weeklySalesDataList = $this->processWeeklyData($weeklyTransactionsList,$weeklySalesDataList);

        $weeklySalesDataList[self::TEXT_TOTAL_NET_SALES] = number_format($weeklySalesDataList[self::TEXT_TOTAL_NET_SALES] / 100, 2, '.', ',');
        $weeklySalesDataList['max_sales'] = number_format($weeklySalesDataList['max_sales'] / 100, 2, '.', ',');
        $weeklySalesDataList['min_sales'] = number_format($weeklySalesDataList['min_sales'] / 100, 2, '.', ',');

        return $weeklySalesDataList;
    }

    public function processWeeklyData($weeklyTransactionsList, $weeklySalesDataList)
    {
        for ($i = 1; $i <= 7; $i++) {
            if (!array_key_exists(0, $weeklyTransactionsList[$i]) || !array_key_exists('transactions', $weeklyTransactionsList[$i][0])) {
                $weeklySalesDataList[$i]['text'] = "$0";
                continue;
            }
            foreach ($weeklyTransactionsList[$i] as $weeklyTransactions) {
                foreach ($weeklyTransactions['transactions'] as $transaction) {
                    foreach ($transaction['tenders'] as $tender) {
                        $weeklySalesDataList[$i][self::TEXT_NET_SALES] += $tender['amount_money']['amount'] - $tender['processing_fee_money']['amount'];
                    }
                }

                $weeklySalesDataList[$i]['text'] = "$" . number_format($weeklySalesDataList[$i][self::TEXT_NET_SALES] / 100, 2, '.', ',');
            }

            $weeklySalesDataList[self::TEXT_TOTAL_NET_SALES] += $weeklySalesDataList[$i][self::TEXT_NET_SALES];

            if ($i == 1 || $weeklySalesDataList['max_sales'] < $weeklySalesDataList[$i][self::TEXT_NET_SALES]) {
                $weeklySalesDataList['max_sales'] = $weeklySalesDataList[$i][self::TEXT_NET_SALES];
            }

            if ($i == 1 || $weeklySalesDataList['min_sales'] > $weeklySalesDataList[$i][self::TEXT_NET_SALES]) {
                $weeklySalesDataList['min_sales'] = $weeklySalesDataList[$i][self::TEXT_NET_SALES];
            }
        }

        return $weeklySalesDataList;
    }

    private function getDailyResponseArray($result_array, $option)
    {
        if ($option == self::TODAY) {
            $title = "Sales Report: " . $this->getDailyDateTimeInReportFormat('today');
        } else if ($option == self::YESTERDAY) {
            $title = "Sales Report: " . $this->getDailyDateTimeInReportFormat('yesterday');
        }

        return array(
            "response_type" => "in_channel",
            "attachments" => array(
                array(
                    "color" => $this->color,
                    "author_name" => "Axearena Analytic Service",
                    "author_link" => "https://www.axearena.com/",
                    "author_icon" => "https://www.axearena.com/images/axearena-logo/axe_arena_logo_main.jpg",
                    "title" => $title,
                    "title_link" => "https://www.axearena.com/",
                    "fields" => array(
                        array(
                            "title" => self::TEXT_SALES,
                            "value" => $result_array['text_sales'],
                            "short" => true
                        ),
                        array(
                            "title" => self::TEXT_FEE,
                            "value" => $result_array['text_fee'],
                            "short" => true
                        ),
                        array(
                            "title" => self::TEXT_NET_SALES,
                            "value" => $result_array['text_net_sales'],
                            "short" => true
                        )
                    )
                )
            )
        );
    }

    private function getWeeklyReportResponseArray($result_array_list)
    {
        $sales_chart = new SalesCharts();
        $chart_url = env('CHART_IMG_BASIC_URL') . $sales_chart->generateSalesChartAndGetChartPath($result_array_list);
        return array(
            "response_type" => "in_channel",
            "attachments" => array(
                array(
                    "color" => $this->color,
                    "author_name" => "Axearena Analytic Service",
                    "author_link" => "https://www.axearena.com/",
                    "author_icon" => "https://www.axearena.com/images/axearena-logo/axe_arena_logo_main.jpg",
                    "title" => "Weekly Net Sales Report: " . $result_array_list['date'],
                    "fallback" => "S: sales; F: fee; N: net sales",
                    "title_link" => "https://www.axearena.com/",
                    "fields" => array(
                        array(
                            "title" => "Monday",
                            "value" => $result_array_list[1]['text'],
                            "short" => true
                        ),
                        array(
                            "title" => "Tuesday",
                            "value" => $result_array_list[2]['text'],
                            "short" => true
                        ),
                        array(
                            "title" => "Wednesday",
                            "value" => $result_array_list[3]['text'],
                            "short" => true
                        ),
                        array(
                            "title" => "Thursday",
                            "value" => $result_array_list[4]['text'],
                            "short" => true
                        ),
                        array(
                            "title" => "Friday",
                            "value" => $result_array_list[5]['text'],
                            "short" => true
                        ),
                        array(
                            "title" => "Saturday",
                            "value" => $result_array_list[6]['text'],
                            "short" => true
                        ),
                        array(
                            "title" => "Sunday",
                            "value" => $result_array_list[7]['text'],
                            "short" => true
                        )
                    )
                ),
                array(
                    "color" => $this->color,
                    "mrkdwn" => true,
                    "title" => "Chart",
                    "image_url" => $chart_url,
                )
            )
        );
    }
}