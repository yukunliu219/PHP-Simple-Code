<?php
/**
 * Created by PhpStorm.
 * User: gliu
 * Date: 8/17/18
 * Time: 7:35 PM
 */

namespace App\Reports;


use App\Charts\SalesCharts;
use App\Repositories\Messages;
use App\Repositories\Posts;
use DateTime;
use Log;

class SummaryReport extends BaseReport
{
    public function __construct($option_text)
    {
        $this->setDateOptions($option_text);
    }

    public function getSummaryReport()
    {
        if ($this->option_date == self::UNKNOWN) {
            return "Sorry, unknown input date.";
        }

        if ($this->option_date == self::LAST_WEEK) {
            return $this->getLastWeekSummaryReport();
        } else {
            return $this->getDailySummaryReport();
        }
    }

    private function getDailySummaryReport()
    {
        $salesReport = new SalesReport($this->option_date);
        $result_array_sales = $salesReport->getDailySalesDataArray($this->days_before_begin, $this->days_before_end, $this->option_date);

        $customersReport = new CustomersReport($this->option_date);
        $result_array_customers = $customersReport->getDailyCustomersDataArray($this->days_before_begin, $this->days_before_end);

        $result_array = array_merge($result_array_sales, $result_array_customers);

        return $this->getDailySummaryReportResponseArray($result_array, $this->option_date);
    }

    private function getLastWeekSummaryReport()
    {
        $salesReport = new SalesReport($this->option_date);
        $result_array_sales = $salesReport->getLastWeekSalesDataArray();

        $customersReport = new CustomersReport($this->option_date);
        $result_array_customers = $customersReport->getLastWeekCustomersDataArray();

        $posts = new Posts();
        $result_array['posts'] = $posts->getPostCountsByWeeks(2);

        $messages = new Messages();
        $result_array['unread_messages'] = $messages->getUnreadMessagesLast7daysCount();

        $result_array = $this->processLastWeekSummaryData($result_array_sales, $result_array_customers, $result_array);

        $sales_chart = new SalesCharts();
        $chart_url = env('CHART_IMG_BASIC_URL') . $sales_chart->generateSalesChartAndGetChartPath($result_array_sales);

        return $this->getLastWeekSummaryReportResponseArray($result_array, $chart_url);
    }

    public function processLastWeekSummaryData($result_array_sales, $result_array_customers, $result_array)
    {
        $result_array['date'] = $result_array_sales['date'];

        // combine the sales array and customers array
        for ($i = 1; $i <= 7; $i++) {
            $result_array[$i]['text'] = "Net Sales: " . $result_array_sales[$i]['text'] . "; " . $result_array_customers[$i]['text'];
        }

        // total
        $result_array['total_net_sales'] = $result_array_sales[self::TEXT_TOTAL_NET_SALES];
        $result_array['total_customers'] = $result_array_customers['total_customers'];
        $result_array['total_waivers'] = $result_array_customers['total_waivers'];

        // max and min
        $result_array['max_customers'] = $result_array_customers['max_customers'];
        $result_array['min_customers'] = $result_array_customers['min_customers'];
        $result_array['max_sales'] = $result_array_sales['max_sales'];
        $result_array['min_sales'] = $result_array_sales['min_sales'];

        $result_array['max_day'] = $result_array_customers['max_day'];
        $result_array['min_day'] = $result_array_customers['min_day'];

        // emoji
        if ($result_array['total_waivers'] != 0) {
            $result_array['emoji_customer_waiver'] = $this->getWaiverCustomerEmojiByStandard(($result_array['total_customers'] / $result_array['total_waivers'] * 100));
        }
        $result_array['emoji_posts'] = $this->getPostEmojiByStandard($result_array['posts']);
        $result_array['emoji_unread_messages'] = $this->getUnreadMessageEmojiByStandard($result_array['unread_messages']);

        return $result_array;
    }

    private function getDailySummaryReportResponseArray($result_array, $option)
    {
        if ($option == self::TODAY) {
            $title = "Summary Report: " . $this->getDailyDateTimeInReportFormat('today');
        } else if ($option == self::YESTERDAY) {
            $title = "Summary Report: " . $this->getDailyDateTimeInReportFormat('yesterday');
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
                            "short" => false
                        ),
                        array(
                            "title" => "Total Waivers:",
                            "value" => $result_array['waivers_count'],
                            "short" => true
                        ),
                        array(
                            "title" => "Total Bookings:",
                            "value" => $result_array['bookings_count'],
                            "short" => true
                        ),
                        array(
                            "title" => "Total Customers:",
                            "value" => $result_array['customers_count'],
                            "short" => true
                        ),
                        array(
                            "title" => "Waivers/Customers:",
                            "value" => $result_array['waiver_customer_rate'],
                            "short" => true
                        ),
                        array(
                            "title" => "Customers/Bookings:",
                            "value" => $result_array['customers_booking_rate'],
                            "short" => true
                        )
                    ),
                )
            )
        );
    }

    private function getLastWeekSummaryReportResponseArray($result_array_list, $chart_url)
    {
        $dateTime = New DateTime("now");
        return array(
            "response_type" => "in_channel",
            "text" => "*Weekly Summary Report: " . $result_array_list['date'] . "* \nLast week, the total net sales was $`" . $result_array_list['total_net_sales'] . "` and the total customers was `" . $result_array_list['total_customers'] . "` (with `" . $result_array_list['total_waivers'] . "` waivers signed " . $result_array_list['emoji_customer_waiver'] . "). Of those, the *peak* day is `" . $result_array_list['max_day'] . "` (sales: $`" . $result_array_list['max_sales'] . "`, customers: `" . $result_array_list['max_customers'] . "`) and the *slowest* day is `" . $result_array_list['min_day'] . "` (sales: $`" . $result_array_list['min_sales'] . "`; customers: `" . $result_array_list['min_customers'] . "`). You posted `" . $result_array_list['posts'] . "` news in the last two weeks." . $result_array_list['emoji_posts'] . " , and your total unread message is `" . $result_array_list['unread_messages'] . "`" . $result_array_list['emoji_unread_messages'] . ".",
            "attachments" => array(
                array(
                    "color" => $this->color,
                    "mrkdwn" => true,
                    "title" => "Chart",
                    "image_url" => $chart_url,
                ),
                array(
                    "color" => $this->color,
                    "mrkdwn" => true,
                    "title" => "Detail",
                    "fields" => array(
                        array(
                            "title" => "Monday",
                            "value" => $result_array_list[1]['text'],
                            "short" => false
                        ),
                        array(
                            "title" => "Tuesday",
                            "value" => $result_array_list[2]['text'],
                            "short" => false
                        ),
                        array(
                            "title" => "Wednesday",
                            "value" => $result_array_list[3]['text'],
                            "short" => false
                        ),
                        array(
                            "title" => "Thursday",
                            "value" => $result_array_list[4]['text'],
                            "short" => false
                        ),
                        array(
                            "title" => "Friday",
                            "value" => $result_array_list[5]['text'],
                            "short" => false
                        ),
                        array(
                            "title" => "Saturday",
                            "value" => $result_array_list[6]['text'],
                            "short" => false
                        ),
                        array(
                            "title" => "Sunday",
                            "value" => $result_array_list[7]['text'],
                            "short" => false
                        ),
                        array(
                            "title" => "New Posts for last two weeks",
                            "value" => $result_array_list['posts'] . $result_array_list['emoji_posts'],
                            "short" => false
                        ),
                        array(
                            "title" => "Unread Messages Last seven days",
                            "value" => $result_array_list['unread_messages'] . $result_array_list['emoji_unread_messages'],
                            "short" => false
                        )

                    )
                )
            )
        );
    }
}