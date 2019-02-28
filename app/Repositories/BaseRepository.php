<?php
/**
 * Created by PhpStorm.
 * User: gliu
 * Date: 8/7/18
 * Time: 10:29 AM
 */

namespace App\Repositories;

use DateTime;
use Log;

class BaseRepository
{
    public function all()
    {
        return $this->modelsList;
    }

    public function isModelsListEmpty()
    {
        return !isset($this->modelsList);
    }

    public function sortByDate()
    {
        if (!$this->isModelsListEmpty()) {
            usort($this->modelsList, function ($a, $b) {
                $ad = new DateTime($a['created_at']);
                $bd = new DateTime($b['created_at']);

                if ($ad == $bd) {
                    return 0;
                }

                return $ad < $bd ? -1 : 1;
            });
        }

        return $this->modelsList;
    }

    public function sortByDateDesc()
    {
        if (!$this->isModelsListEmpty()) {
            usort($this->modelsList, function ($a, $b) {
                $ad = new DateTime($a['created_at']);
                $bd = new DateTime($b['created_at']);

                if ($ad == $bd) {
                    return 0;
                }

                return $ad > $bd ? -1 : 1;
            });
        }

        return $this->modelsList;
    }

    public function getDateQueryString($day_before_begin, $day_before_end)
    {
        $begin_time = new DateTime($day_before_begin . ' days ago');
        $begin_time->setTime(0, 0, 0);
        $str_begin_time = "begin_time=" . $begin_time->format(DateTime::RFC3339);

        $end_time = new DateTime($day_before_end . ' days ago');
        $end_time->setTime(23, 59, 59);
        $str_end_time = "end_time=" . $end_time->format(DateTime::RFC3339);

        return "?" . $str_begin_time . "&" . $str_end_time;
    }

    public function getLastWeekDateQueryString($day_before_begin, $day_before_end)
    {
        $begin_time = new DateTime('monday this week -' . $day_before_begin . ' day');
        $begin_time->setTime(0, 0, 0);
        $str_begin_time = "begin_time=" . $begin_time->format(\DateTime::RFC3339);

        $end_time = new DateTime('monday this week -' . $day_before_end . ' day');
        $end_time->setTime(23, 59, 59);
        $str_end_time = "end_time=" . $end_time->format(\DateTime::RFC3339);

        return "?" . $str_begin_time . "&" . $str_end_time;
    }
}