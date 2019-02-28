<?php
/**
 * Created by PhpStorm.
 * User: gliu
 * Date: 8/15/18
 * Time: 7:48 PM
 */

namespace App\Reports;


class BaseReport
{
    public $modelsList;

    protected $option_date;
    protected $days_before_begin;
    protected $days_before_end;
    protected $color = Self::COLOR_GREEN;

    // options
    const UNKNOWN = 0;
    const TODAY = 1;
    const YESTERDAY = 2;
    const LAST_WEEK = 3;

    const SALES = 4;
    const FEE = 5;
    const NET_SALES = 6;

    // texts
    const TEXT_TODAY = "today";
    const TEXT_YESTERDAY = "yesterday";
    const TEXT_LAST_WEEK = "last week";
    const TEXT_LASTWEEK = "lastweek";

    const TEXT_SALES = "Sales";
    const TEXT_FEE = "Processing Fee";
    const TEXT_NET_SALES = "Net Sales";
    const TEXT_TOTAL_NET_SALES = "Total Net Sales";

    // colors
    const COLOR_GREEN = "#33cc33";
    const COLOR_YELLOW = "#ffd700";
    const COLOR_RED = "#ff0000";

    // emojis
    const EMOJI_THUMBSUP = ":thumbsup::thumbsup::thumbsup:";
    const EMOJI_WOMAN_NO = ":woman-gesturing-no::woman-gesturing-no::woman-gesturing-no:";
    const EMOJI_THINKING_FACE = ":thinking_face::thinking_face::thinking_face:";
    const EMOJI_RAGE = ":rage::rage::rage:";
    const EMOJI_TADA = ":tada::tada::tada:";
    const EMOJI_CLAP = ":clap::clap::clap:";
    const EMOJI_EXCLAMATION = ":exclamation::exclamation::exclamation:";
    const EMOJI_WARNING = ":warning::warning::warning:";
    const EMOJI_CHART = ":chart:";


    protected function setDateOptions($option_text)
    {
        if (strcmp($option_text, self::TEXT_TODAY) == 0) {
            $this->option_date = self::TODAY;
            $this->days_before_begin = 0;
            $this->days_before_end = 0;
        } else if (strcmp($option_text, self::TEXT_YESTERDAY) == 0) {
            $this->option_date = self::YESTERDAY;
            $this->days_before_begin = 1;
            $this->days_before_end = 1;
        } else if (strcmp($option_text, self::TEXT_LAST_WEEK) == 0 ||
            strcmp($option_text, self::TEXT_LASTWEEK) == 0
        ) {
            $this->option_date = self::LAST_WEEK;
        } else {
            $this->option_date = self::UNKNOWN;
        }
    }

    public function isOptionsExist($option_text)
    {
        if (strcmp($option_text, self::TEXT_TODAY) == 0 ||
            strcmp($option_text, self::TEXT_YESTERDAY) == 0 ||
            strcmp($option_text, self::TEXT_LAST_WEEK) == 0 ||
            strcmp($option_text, self::TEXT_LASTWEEK) == 0
        ) {
            return true;
        } else {
            return false;
        }
    }

    protected function getDateOption()
    {
        return $this->option_date;
    }

    protected function get_Option_Today()
    {
        return self::TODAY;
    }

    protected function get_Option_Yesterday()
    {
        return self::YESTERDAY;
    }

    protected function get_Option_LastWeek()
    {
        return self::LAST_WEEK;
    }

    protected function get_Option_Unknown()
    {
        return self::UNKNOWN;
    }

    protected function getWaiverCustomerEmojiByStandard($rate)
    {
        $diff = abs(100.00 - $rate);
        if ($diff == env('STANDARD_WAIVER_CUSTOMER_EXCELLENT')) {
            return $this::EMOJI_TADA;
        } else if ($diff <= env('STANDARD_WAIVER_CUSTOMER_GOOD')) {
            return $this::EMOJI_THUMBSUP;
        } else if ($diff <= env('STANDARD_WAVIER_CUSTOMER_DANGER')) {
            return $this::EMOJI_WARNING;
        } else if ($diff > env('STANDARD_WAVIER_CUSTOMER_DANGER')) {
            return $this::EMOJI_EXCLAMATION;
        }
    }

    protected function getPostEmojiByStandard($count)
    {
        if ($count > 0) {
            return $this::EMOJI_CLAP;
        } else {
            return $this::EMOJI_RAGE;
        }
    }

    protected function getUnreadMessageEmojiByStandard($count)
    {
        if ($count == env('STANDARD_UNREAD_MESSAGE_GOOD')) {
            return $this::EMOJI_THUMBSUP;
        } else if ($count <= env('STANDARD_UNREAD_MESSAGE_WARNING')) {
            return $this::EMOJI_THINKING_FACE;
        } else if ($count > env('STANDARD_UNREAD_MESSAGE_WARNING')) {
            return $this::EMOJI_RAGE;
        }
    }

    protected function getDailyDateTimeInReportFormat($day)
    {
        return date("M-d, Y", strtotime($day));
    }

    protected function getWeeklyDateTimeInReportFormat()
    {
        return date("M-d", strtotime('monday this week -7 day')) . " ~ " .
        date("M-d, Y", strtotime('monday this week -1 day'));
    }
}