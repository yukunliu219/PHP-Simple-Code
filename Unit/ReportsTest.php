<?php

namespace Tests\Unit;

use App\Reports\BaseReport;
use App\Reports\CustomersReport;
use App\Reports\SalesReport;
use App\Reports\SummaryReport;
use Tests\TestCase;
use Log;

class ReportsTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testSalesReport()
    {
        $config = new BaseReport();
        $salesReport = new SalesReport("yesterday");

        // test calculateDaily method
        $transactionsList = array(
            array(
                "transactions" => array(
                    array(
                        "id" => "a",
                        "tenders" => array(
                            array(
                                "id" => 1,
                                "amount_money" => array(
                                    "amount" => 150,
                                ),
                                "processing_fee_money" => array(
                                    "amount" => 15,
                                )
                            )
                        )
                    ),
                    array(
                        "id" => "b",
                        "tenders" => array(
                            array(
                                "id" => 1,
                                "amount_money" => array(
                                    "amount" => 100,
                                ),
                                "processing_fee_money" => array(
                                    "amount" => 10,
                                )
                            )
                        )
                    ),
                    array(
                        "id" => "a",
                        "tenders" => array(
                            array(
                                "id" => 1,
                                "amount_money" => array(
                                    "amount" => 200,
                                ),
                                "processing_fee_money" => array(
                                    "amount" => 20,
                                )
                            )
                        )
                    )
                )
            )
        );
        $result_array = array(
            $config::TEXT_SALES => 0,
            $config::TEXT_FEE => 0,
            $config::TEXT_NET_SALES => 0,
            'text_sales' => "$0",
            'text_fee' => "$0",
            "text_net_sales" => "$0"
        );
        $result_array = $salesReport->processDailyData($transactionsList,$result_array);
        $this->assertTrue($result_array[$config::TEXT_SALES] == 450);
        $this->assertTrue($result_array[$config::TEXT_FEE] == 45);
        $this->assertTrue($result_array[$config::TEXT_NET_SALES] == 405);

        //test calculateWeekly
        $weeklySalesDataList = [
            $config::TEXT_TOTAL_NET_SALES => 0,
            "max_sales" => 0,
            "min_sales" => 0,
            'date' => ""
        ];

        for ($i = 7; $i >= 1; $i--) {
            $weeklyTransactionsList[8 - $i] = array(
                array(
                    "transactions" => array(
                        array(
                            "id" => "a",
                            "tenders" => array(
                                array(
                                    "id" => 1,
                                    "amount_money" => array(
                                        "amount" => 100 * $i,
                                    ),
                                    "processing_fee_money" => array(
                                        "amount" => 10 * $i,
                                    )
                                )
                            )
                        ),
                        array(
                            "id" => "b",
                            "tenders" => array(
                                array(
                                    "id" => 1,
                                    "amount_money" => array(
                                        "amount" => 200 * $i,
                                    ),
                                    "processing_fee_money" => array(
                                        "amount" => 20 * $i,
                                    )
                                )
                            )
                        ),
                        array(
                            "id" => "a",
                            "tenders" => array(
                                array(
                                    "id" => 1,
                                    "amount_money" => array(
                                        "amount" => 300 * $i,
                                    ),
                                    "processing_fee_money" => array(
                                        "amount" => 30 * $i,
                                    )
                                )
                            )
                        )
                    )
                )
            );
            $weeklySalesDataList[8 - $i] = [
                $config::TEXT_SALES => 0,
                $config::TEXT_FEE => 0,
                $config::TEXT_NET_SALES => 0,
                'text_sales' => "$0",
                'text_fee' => "$0",
                "text_net_sales" => "$0",
            ];
        }

        $weeklySalesDataList = $salesReport->processWeeklyData($weeklyTransactionsList, $weeklySalesDataList);
        $this->assertTrue($weeklySalesDataList['max_sales'] == 3780);
        $this->assertTrue($weeklySalesDataList['min_sales'] == 540);
        $this->assertTrue($weeklySalesDataList[2][$config::TEXT_NET_SALES] == 3240);
        $this->assertTrue($weeklySalesDataList[5][$config::TEXT_NET_SALES] == 1620);
    }

    public function testCustomersReport()
    {
        $customersReport = new CustomersReport("yesterday");

        // test processDailyCustomersData
        $result_array = [
            'waivers_count' => 0,
            'bookings_count' => 0,
            'customers_count' => 0,
            'waiver_customer_rate' => 'Not Available',
            'customers_booking_rate' => 'Not Available',
            'text' => "Waivers: 0, Bookings: 0, Waivers/Customers: 0, Customers/Bookings: 0"
        ];

        $waivers = array(
            array(
                "id" => 1
            ),
            array(
                "id" => 2
            ),
            array(
                "id" => 3
            ),
            array(
                "id" => 4
            ),
            array(
                "id" => 5
            )
        );

        $bookings = array(
            array(
                "id" => 1,
                "group_size" => 2
            ),
            array(
                "id" => 2,
                "group_size" => 4
            )
        );
        $result_array = $customersReport->processDailyCustomersData($waivers, $bookings, $result_array);
        $this->assertTrue($result_array['waivers_count'] == 5);
        $this->assertTrue($result_array['bookings_count'] == 2);
        $this->assertTrue($result_array['customers_count'] == 6);
        $this->assertTrue(strcmp($result_array['customers_booking_rate'], "3.00") == 0);
        $this->assertTrue(strcmp($result_array['waiver_customer_rate'], "83.33%:thumbsup::thumbsup::thumbsup:") == 0 );

        // test processLastWeekCustomersData
        $weeklyCustomersDataList = [
            'total_waivers' => 0,
            'total_bookings' => 0,
            'total_customers' => 0,
            'max_customers' => 0,
            'min_customers' => 0,
            'max_day' => "",
            'min_day' => "",
            'customers_per_booking' => 'Not Available'
        ];

        for ($i = 1; $i <= 7; $i++) {
            $weeklyCustomersList[$i]['waivers'] = array(
                array(
                    'id' => 1
                ),
                array(
                    'id' => 2
                ),
                array(
                    'id' => 3
                ),
                array(
                    'id' => 4
                ),
                array(
                    'id' => 5
                )
            );

            $weeklyCustomersList[$i]['bookings'] = array(
                array(
                    'id' => 1 * $i,
                    'group_size' => 2 * $i
                ),
                array(
                    'id' => 2 * $i,
                    'group_size' => 4 * $i
                ),
            );
            $weeklyCustomersDataList[8 - $i] = [
                'waivers_count' => 0,
                'bookings_count' => 0,
                'customers_count' => 0,
                'waiver_customer_rate' => 'Not Available',
                'customers_booking_rate' => 'Not Available',
                'text' => "Waivers: 0, Bookings: 0, Waivers/Customers: 0, Customers/Bookings: 0"
            ];
        }
        $customersReport = new CustomersReport("last week");
        $weeklyCustomersDataList = $customersReport->processLastWeekCustomersData($weeklyCustomersList, $weeklyCustomersDataList);

        $this->assertTrue(strcmp($weeklyCustomersDataList[2]['text'],"Bookings: 2, Customers: 12, Waivers: 5, Waivers/Customers: 41.67%:exclamation::exclamation::exclamation:, Customers per Booking: 6.00") == 0);
        $this->assertTrue($weeklyCustomersDataList['total_waivers'] == 35);
        $this->assertTrue($weeklyCustomersDataList['total_bookings'] == 14);
        $this->assertTrue($weeklyCustomersDataList['max_customers'] == 42);
        $this->assertTrue($weeklyCustomersDataList['min_customers'] == 6);
        $this->assertTrue(strcmp($weeklyCustomersDataList['max_day'],"Sunday") == 0);
        $this->assertTrue(strcmp($weeklyCustomersDataList['min_day'], "Monday") == 0);
        $this->assertTrue($weeklyCustomersDataList['customers_per_booking'] == 12.00);
    }

    public function testSummaryReport()
    {
        $config = new BaseReport();

        // test processLastWeekSummaryData
        $summaryReport = new SummaryReport("last week");
        $result_array_sales = [
            $config::TEXT_TOTAL_NET_SALES => 10000,
            'max_sales' => 2000 ,
            'min_sales' => 500,
            'date' => '2018-08-08T00:00:00-06:00'
        ];
        $result_array_customers = [
            'total_customers' => 400,
            'total_waivers' => 380,
            'max_customers' => 80,
            'min_customers' => 30,
            'max_day' => 'Saturday',
            'min_day' => 'Monday'
        ];
        for ($i = 1; $i <= 7; $i++) {
            $result_array_sales[$i]['text'] = "Net Sales: $" . $i * 100.5;
            $result_array_customers[$i]['text'] = "Bookings: ". ($i * 3) .", Customers: ". ($i * 7) .", Waivers: ". ($i * 8) .", Waivers/Customers: " . 90.00 . "%:exclamation::exclamation::exclamation:, Customers per Booking: " . $i * 3.5  ."";
        }
        $result_array = ['posts' => 2, 'unread_messages' => 1];
        $result_array = $summaryReport->processLastWeekSummaryData($result_array_sales, $result_array_customers, $result_array);

        $this->assertTrue($result_array['total_net_sales'] == 10000);
        $this->assertTrue($result_array['total_customers'] == 400);
        $this->assertTrue($result_array['total_waivers'] == 380);
        $this->assertTrue($result_array['max_customers'] == 80);
        $this->assertTrue($result_array['min_customers'] == 30);
        $this->assertTrue($result_array['max_sales'] == 2000);
        $this->assertTrue($result_array['min_sales'] == 500);
        $this->assertTrue(strcmp($result_array['max_day'], 'Saturday')  == 0);
        $this->assertTrue(strcmp($result_array['min_day'], 'Monday') == 0);
    }
}
