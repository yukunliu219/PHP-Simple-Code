<?php
/**
 * Created by PhpStorm.
 * User: gliu
 * Date: 8/20/18
 * Time: 11:47 AM
 */

namespace App\Charts;

use CpChart\Data;
use CpChart\Image;
use Log;
use DateTime;

class SalesCharts
{
    public function __construct()
    {

    }

    public function generateSalesChartAndGetChartPath($result_array)
    {
        $SalesData = new Data();
        for ($i = 1; $i <= 7; $i++) {
            Log::debug("### sales = " . $result_array[$i]['Net Sales']);
            $SalesData->addPoints($result_array[$i]['Net Sales'] / 100, "Net Sales");
        }

        $SalesData->setPalette("Net Sales", array("R" => 0, "G" => 0, "B" => 245, "Alpha" => 70));
        $SalesData->setSerieDescription("Net Sales", "dollar");
        $SalesData->setScatterSerie(1000, 1000, "Net Sales");

        $SalesData->addPoints(array("Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday"), "Labels");
        $SalesData->setSerieDescription("Labels", "Days");
        $SalesData->setAbscissa("Labels");

        /* Create the pChart object */
        $chart = new Image(700, 500, $SalesData);

        /* Set the default font properties */
        $chart->setFontProperties(array("FontName" => "calibri.ttf", "FontSize" => 12));
        $chart->drawText(360, 50, "Net Sales:" . $result_array['date'], ["FontSize" => 20, "Align" => TEXT_ALIGN_BOTTOMMIDDLE]);
        /* Draw the scale and the chart */
        $chart->setGraphArea(50, 80, 680, 420);
        $chart->drawScale(array("DrawSubTicks" => TRUE, "Mode" => SCALE_MODE_ADDALL_START0, "GridR" => 0, "GridG" => 0, "GridB" => 0, "GridTicks" => 2));
        $chart->setShadow(FALSE);
        $chart->drawStackedBarChart(array("Surrounding" => -15, "InnerSurrounding" => 15, "DisplayValues" => true, "DisplayR" => 255, "DisplayG" => 255, "DisplayB" => 255));

        /* Write the chart legend */

        $chart->drawLegend(600, 60, array("Style" => LEGEND_NOBORDER, "Mode" => LEGEND_HORIZONTAL));

        /* Render the picture (choose the best way) */

        $datetime = new DateTime;
        $chart_name = env('CHART_IMG_NAME') . "-" . $datetime->format('Y-m-d-h-i-s'). ".png";
        $chart->render(env('CHART_IMG_PATH').$chart_name);

        return $chart_name;
    }
}