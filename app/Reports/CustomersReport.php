<?php
/**
 * Created by PhpStorm.
 * User: gliu
 * Date: 8/15/18
 * Time: 9:07 PM
 */

namespace App\Reports;

use App\Repositories\Bookings;
use App\Repositories\Waivers;
use DateTime;
use Log;

class CustomersReport extends BaseReport
{
    public function __construct($option_text)
    {
        $this->setDateOptions($option_text);
    }

    private function getVoidCustomersResultArray()
    {
        return [
            'waivers_count' => 0,
            'bookings_count' => 0,
            'customers_count' => 0,
            'waiver_customer_rate' => 'Not Available',
            'customers_booking_rate' => 'Not Available',
            'text' => "Waivers: 0, Bookings: 0, Waivers/Customers: 0, Customers/Bookings: 0"
        ];
    }

    private function getVoidSummaryResultArray()
    {
        return [
            'total_waivers' => 0,
            'total_bookings' => 0,
            'total_customers' => 0,
            'max_customers' => 0,
            'min_customers' => 0,
            'max_day' => "",
            'min_day' => "",
            'customers_per_booking' => 'Not Available',
        ];
    }

    private function convertNumberToDay($number)
    {
        if ($number == 1) return "Monday";
        if ($number == 2) return "Tuesday";
        if ($number == 3) return "Wednesday";
        if ($number == 4) return "Thursday";
        if ($number == 5) return "Friday";
        if ($number == 6) return "Saturday";
        if ($number == 7) return "Sunday";
    }


    public function getCustomersReport()
    {
        if ($this->option_date == self::UNKNOWN) {
            return "Sorry, unknown input date.";
        }

        if ($this->option_date == self::LAST_WEEK) {
            return $this->getLastWeekCustomersReport();
        } else {
            return $this->getDailyCustomersReport();
        }
    }

    private function getDailyCustomersReport()
    {
        $result_array = $this->getDailyCustomersDataArray($this->days_before_begin, $this->days_before_end);
        return $this->getDailyCustomersReportResponseArray($result_array, $this->option_date);
    }

    public function getDailyCustomersDataArray($days_before_begin, $days_before_end)
    {
        $result_array = $this->getVoidCustomersResultArray();

        // get raw waivers and bookings data
        $waiversRepo = new Waivers();
        $waivers = $waiversRepo->getDataFromWaiverService($waiversRepo->getDateQueryString($days_before_begin, $days_before_end));

        $bookingsRepo = new Bookings();
        $bookings = $bookingsRepo->getDataFromBookingService($bookingsRepo->getDateQueryString($days_before_begin, $days_before_end));

        $result_array = $this->processDailyCustomersData($waivers, $bookings, $result_array);

        return $result_array;
    }

    public function processDailyCustomersData($waivers, $bookings, $result_array)
    {
        $result_array['waivers_count'] = count($waivers);

        if (!is_null($bookings) && count($bookings) != 0) {
            $result_array['bookings_count'] = count($bookings);

            // get customers count
            foreach ($bookings as $booking) {
                $result_array['customers_count'] += $booking['group_size'];
            }

            if ($result_array['customers_count'] != 0) {
                $result_array['waiver_customer_rate'] = number_format(($result_array['waivers_count'] / $result_array['customers_count']) * 100, 2);
                $emoji = $this->getWaiverCustomerEmojiByStandard($result_array['waiver_customer_rate']);
                $result_array['waiver_customer_rate'] = $result_array['waiver_customer_rate'] . '%' . $emoji;
                $result_array['customers_booking_rate'] = number_format(($result_array['customers_count'] / $result_array['bookings_count']), 2);
            }
        }

        return $result_array;
    }

    private function getLastWeekCustomersReport()
    {
        $result_array = $this->getLastWeekCustomersDataArray();
        return $this->getLastWeekCustomersReportResponseArray($result_array, $this->option_date);
    }

    public function getLastWeekCustomersDataArray()
    {
        $waiversRepo = new Waivers();
        $bookingsRepo = new Bookings();
        $weeklyCustomersDataList = $this->getVoidSummaryResultArray();
        $weeklyCustomersDataList['date'] = $this->getWeeklyDateTimeInReportFormat();

        // get raw waivers and bookings data
        for ($i = 7; $i >= 1; $i--) {
            $weeklyCustomersList[8 - $i]['waivers'] = $waiversRepo->getDataFromWaiverService($waiversRepo->getLastWeekDateQueryString($i, $i));
            $weeklyCustomersList[8 - $i]['bookings'] = $bookingsRepo->getDataFromBookingService($bookingsRepo->getLastWeekDateQueryString($i, $i));
            $weeklyCustomersDataList[8 - $i] = $this->getVoidCustomersResultArray();
        }

        $weeklyCustomersDataList = $this->processLastWeekCustomersData($weeklyCustomersList, $weeklyCustomersDataList);

        return $weeklyCustomersDataList;
    }

    public function processLastWeekCustomersData($weeklyCustomersList, $weeklyCustomersDataList)
    {
        for ($i = 1; $i <= 7; $i++) {
            $weeklyCustomersDataList[$i]['waivers_count'] = count($weeklyCustomersList[$i]['waivers']);
            $weeklyCustomersDataList[$i]['bookings_count'] = count($weeklyCustomersList[$i]['bookings']);

            $weeklyCustomersDataList['total_waivers'] += $weeklyCustomersDataList[$i]['waivers_count'];
            $weeklyCustomersDataList['total_bookings'] += $weeklyCustomersDataList[$i]['bookings_count'];

            if ($weeklyCustomersDataList[$i]['bookings_count'] == 0) {
                continue;
            }

            foreach ($weeklyCustomersList[$i]['bookings'] as $booking) {
                $weeklyCustomersDataList[$i]['customers_count'] += $booking['group_size'];
            }

            $weeklyCustomersDataList['total_customers'] += $weeklyCustomersDataList[$i]['customers_count'];

            if ($i == 1 || $weeklyCustomersDataList['max_customers'] < $weeklyCustomersDataList[$i]['customers_count']) {
                $weeklyCustomersDataList['max_customers'] = $weeklyCustomersDataList[$i]['customers_count'];
                $weeklyCustomersDataList['max_day'] = $this->convertNumberToDay($i);
            }

            if ($i == 1 || $weeklyCustomersDataList['min_customers'] > $weeklyCustomersDataList[$i]['customers_count']) {
                $weeklyCustomersDataList['min_customers'] = $weeklyCustomersDataList[$i]['customers_count'];
                $weeklyCustomersDataList['min_day'] = $this->convertNumberToDay($i);
            }

            if ($weeklyCustomersDataList[$i]['customers_count'] != 0 && $weeklyCustomersDataList[$i]['bookings_count'] != 0) {
                $weeklyCustomersDataList[$i]['waiver_customer_rate'] = number_format(($weeklyCustomersDataList[$i]['waivers_count'] / $weeklyCustomersDataList[$i]['customers_count']) * 100, 2);
                $emoji = $this->getWaiverCustomerEmojiByStandard($weeklyCustomersDataList[$i]['waiver_customer_rate']);
                $weeklyCustomersDataList[$i]['waiver_customer_rate'] = $weeklyCustomersDataList[$i]['waiver_customer_rate'] . '%' . $emoji;
                $weeklyCustomersDataList[$i]['customers_booking_rate'] = number_format(($weeklyCustomersDataList[$i]['customers_count'] / $weeklyCustomersDataList[$i]['bookings_count']), 2);
            }

            $weeklyCustomersDataList[$i]['text'] =
                "Bookings: " . $weeklyCustomersDataList[$i]['bookings_count'] .
                ", Customers: " . $weeklyCustomersDataList[$i]['customers_count'] .
                ", Waivers: " . $weeklyCustomersDataList[$i]['waivers_count'] .
                ", Waivers/Customers: " . $weeklyCustomersDataList[$i]['waiver_customer_rate'] .
                ", Customers per Booking: " . $weeklyCustomersDataList[$i]['customers_booking_rate'];
        }

        if ($weeklyCustomersDataList['total_bookings'] != 0) {
            $weeklyCustomersDataList['customers_per_booking'] = number_format(($weeklyCustomersDataList['total_customers'] / $weeklyCustomersDataList['total_bookings']), 2);
        }

        return $weeklyCustomersDataList;
    }

    private function getDailyCustomersReportResponseArray($result_array, $option)
    {
        if ($option == self::TODAY) {
            $title = "Customers Report: " . $this->getDailyDateTimeInReportFormat('today');
        } else if ($option == self::YESTERDAY) {
            $title = "Customers Report: " . $this->getDailyDateTimeInReportFormat('yesterday');
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
                            "title" => "Customers per Booking:",
                            "value" => $result_array['customers_booking_rate'],
                            "short" => true
                        ),
                        array(
                            "title" => "Waivers/Customers:",
                            "value" => $result_array['waiver_customer_rate'],
                            "short" => true
                        )
                    ),
                )
            )
        );
    }

    private function getLastWeekCustomersReportResponseArray($result_array_list)
    {
        $dateTime = New DateTime("now");
        return array(
            "response_type" => "in_channel",
            "attachments" => array(
                array(
                    "color" => $this->color,
                    "author_name" => "Axearena Analytic Service",
                    "author_link" => "https://www.axearena.com/",
                    "author_icon" => "https://www.axearena.com/images/axearena-logo/axe_arena_logo_main.jpg",
                    "title" => "Weekly Customers Report: " . $result_array_list['date'],
                    "fallback" => "S: sales; F: fee; N: net sales",
                    "title_link" => "https://www.axearena.com/",
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

                    )
                )
            )
        );
    }
}