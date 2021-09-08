<?php
require_once 'vendor/autoload.php';

class Excel extends Controller {
    public function getToChart($data, $load, $fileName){
        $ea = new PHPExcel();
        $hotelList = ['Отели','Guesthouse - Kuin Kotonaan','Hotel Leikari','Leikari "Nature" Bungalows with Terrace',
            'Апартаменты', 'Kotkan Residenssi Apartments', 'Guest House Nina Art', 'Guesthouse Lokinlaulu', 'The Grand Karhu',
            'Kartanohotelli Karhulan Hovi', 'Hotelli Merikotka', 'Hotelli Kotola', 'Kesähostelli Kärkisaari', 'Hotel Villa Vanessa',
            'Beach Hotel Santalahti', 'Рынок', 'Выборка'];
        $arr[0] = $hotelList;
        for($i = 0; $i < count($data); $i++){
            $arr[$i + 1] = $data[$i];
        }

        //print_r($hotelList);
        //print_r($data);

        $ea->setActiveSheetIndex(0);
        $ews = $ea->getActiveSheet();
        $ews->setTitle('Цены');
        $ews->fromArray($arr);


        $dsl = array(
            new PHPExcel_Chart_DataSeriesValues('String', 'Цены!$B$1', null, 1),
            new PHPExcel_Chart_DataSeriesValues('String', 'Цены!$C$1', null, 1),
            new PHPExcel_Chart_DataSeriesValues('String', 'Цены!$D$1', null, 1),
            new PHPExcel_Chart_DataSeriesValues('String', 'Цены!$E$1', null, 1),
            new PHPExcel_Chart_DataSeriesValues('String', 'Цены!$F$1', null, 1),
            new PHPExcel_Chart_DataSeriesValues('String', 'Цены!$G$1', null, 1),
            new PHPExcel_Chart_DataSeriesValues('String', 'Цены!$H$1', null, 1),
            new PHPExcel_Chart_DataSeriesValues('String', 'Цены!$I$1', null, 1),
            new PHPExcel_Chart_DataSeriesValues('String', 'Цены!$J$1', null, 1),
            new PHPExcel_Chart_DataSeriesValues('String', 'Цены!$K$1', null, 1),
            new PHPExcel_Chart_DataSeriesValues('String', 'Цены!$L$1', null, 1),
            new PHPExcel_Chart_DataSeriesValues('String', 'Цены!$M$1', null, 1),
            new PHPExcel_Chart_DataSeriesValues('String', 'Цены!$N$1', null, 1),
            new PHPExcel_Chart_DataSeriesValues('String', 'Цены!$O$1', null, 1),
            new PHPExcel_Chart_DataSeriesValues('String', 'Цены!$P$1', null, 1),
            new PHPExcel_Chart_DataSeriesValues('String', 'Цены!$Q$1', null, 1),
        );

        $xal = array(
            new PHPExcel_Chart_DataSeriesValues('String', 'Цены!$A$2:$A$15', null, 6),
        );//Ось X. Указывает координаты для её значения


        $dsv = array(
            new PHPExcel_Chart_DataSeriesValues('Number', 'Цены!$B$2:$B$15', null, 14),
            new PHPExcel_Chart_DataSeriesValues('Number', 'Цены!$C$2:$C$15', null, 14),
            new PHPExcel_Chart_DataSeriesValues('Number', 'Цены!$D$2:$D$15', null, 14),
            new PHPExcel_Chart_DataSeriesValues('Number', 'Цены!$E$2:$E$15', null, 14),
            new PHPExcel_Chart_DataSeriesValues('Number', 'Цены!$F$2:$F$15', null, 14),
            new PHPExcel_Chart_DataSeriesValues('Number', 'Цены!$G$2:$G$15', null, 14),
            new PHPExcel_Chart_DataSeriesValues('Number', 'Цены!$H$2:$H$15', null, 14),
            new PHPExcel_Chart_DataSeriesValues('Number', 'Цены!$I$2:$I$15', null, 14),
            new PHPExcel_Chart_DataSeriesValues('Number', 'Цены!$J$2:$J$15', null, 14),
            new PHPExcel_Chart_DataSeriesValues('Number', 'Цены!$K$2:$K$15', null, 14),
            new PHPExcel_Chart_DataSeriesValues('Number', 'Цены!$L$2:$L$15', null, 14),
            new PHPExcel_Chart_DataSeriesValues('Number', 'Цены!$M$2:$M$15', null, 14),
            new PHPExcel_Chart_DataSeriesValues('Number', 'Цены!$N$2:$N$15', null, 14),
            new PHPExcel_Chart_DataSeriesValues('Number', 'Цены!$O$2:$O$15', null, 14),
            new PHPExcel_Chart_DataSeriesValues('Number', 'Цены!$P$2:$P$15', null, 14),
            new PHPExcel_Chart_DataSeriesValues('Number', 'Цены!$Q$2:$Q$15', null, 14),
        );


        $ds = new PHPExcel_Chart_DataSeries( PHPExcel_Chart_DataSeries::TYPE_LINECHART,
            PHPExcel_Chart_DataSeries::GROUPING_STANDARD, range(0, count($dsv) - 1),
            $dsl, $xal, $dsv);//макет диаграммы

        $pa = new \PHPExcel_Chart_PlotArea(NULL, array($ds));
        $legend = new \PHPExcel_Chart_Legend(\PHPExcel_Chart_Legend::POSITION_RIGHT, NULL, false);
        $title = new \PHPExcel_Chart_Title('Цены');

        $chart = new \PHPExcel_Chart( 'chart1', $title, $legend, $pa, true,
            0, NULL, NULL );
        $chart->setTopLeftPosition('S1');
        $chart->setBottomRightPosition('AD25');
        $ews->addChart($chart);

        $hotelList2 = ['Отели','Guesthouse - Kuin Kotonaan','Hotel Leikari','Leikari "Nature" Bungalows with Terrace',
            'Апартаменты', 'Kotkan Residenssi Apartments', 'Guest House Nina Art', 'Guesthouse Lokinlaulu', 'The Grand Karhu',
            'Kartanohotelli Karhulan Hovi', 'Hotelli Merikotka', 'Hotelli Kotola', 'Kesähostelli Kärkisaari', 'Hotel Villa Vanessa',
            'Beach Hotel Santalahti', 'Рынок', 'Выборка'];
        $arr2[0] = $hotelList2;
        for($i = 0; $i < count($load); $i++){
            $arr2[$i + 1] = $load[$i];
        }

        //print_r($hotelList);
        //print_r($data);

        $ea->createSheet();
        $ews = $ea->setActiveSheetIndex(1);
        //$ews = $ea->getActiveSheet();
        $ews->setTitle('Загрузка');
        $ews->fromArray($arr2);


        $dsl2 = array(
            new PHPExcel_Chart_DataSeriesValues('String', 'Загрузка!$B$1', null, 1),
            new PHPExcel_Chart_DataSeriesValues('String', 'Загрузка!$C$1', null, 1),
            new PHPExcel_Chart_DataSeriesValues('String', 'Загрузка!$D$1', null, 1),
            new PHPExcel_Chart_DataSeriesValues('String', 'Загрузка!$E$1', null, 1),
            new PHPExcel_Chart_DataSeriesValues('String', 'Загрузка!$F$1', null, 1),
            new PHPExcel_Chart_DataSeriesValues('String', 'Загрузка!$G$1', null, 1),
            new PHPExcel_Chart_DataSeriesValues('String', 'Загрузка!$H$1', null, 1),
            new PHPExcel_Chart_DataSeriesValues('String', 'Загрузка!$I$1', null, 1),
            new PHPExcel_Chart_DataSeriesValues('String', 'Загрузка!$J$1', null, 1),
            new PHPExcel_Chart_DataSeriesValues('String', 'Загрузка!$K$1', null, 1),
            new PHPExcel_Chart_DataSeriesValues('String', 'Загрузка!$L$1', null, 1),
            new PHPExcel_Chart_DataSeriesValues('String', 'Загрузка!$M$1', null, 1),
            new PHPExcel_Chart_DataSeriesValues('String', 'Загрузка!$N$1', null, 1),
            new PHPExcel_Chart_DataSeriesValues('String', 'Загрузка!$O$1', null, 1),
            new PHPExcel_Chart_DataSeriesValues('String', 'Загрузка!$P$1', null, 1),
            new PHPExcel_Chart_DataSeriesValues('String', 'Загрузка!$Q$1', null, 1)
        );

        $xal2 = array(
            new PHPExcel_Chart_DataSeriesValues('String', 'Загрузка!$A$2:$A$15', null, 6),
        );//Ось X. Указывает координаты для её значения


        $dsv2 = array(
            new PHPExcel_Chart_DataSeriesValues('Number', 'Загрузка!$B$2:$B$15', null, 14),
            new PHPExcel_Chart_DataSeriesValues('Number', 'Загрузка!$C$2:$C$15', null, 14),
            new PHPExcel_Chart_DataSeriesValues('Number', 'Загрузка!$D$2:$D$15', null, 14),
            new PHPExcel_Chart_DataSeriesValues('Number', 'Загрузка!$E$2:$E$15', null, 14),
            new PHPExcel_Chart_DataSeriesValues('Number', 'Загрузка!$F$2:$F$15', null, 14),
            new PHPExcel_Chart_DataSeriesValues('Number', 'Загрузка!$G$2:$G$15', null, 14),
            new PHPExcel_Chart_DataSeriesValues('Number', 'Загрузка!$H$2:$H$15', null, 14),
            new PHPExcel_Chart_DataSeriesValues('Number', 'Загрузка!$I$2:$I$15', null, 14),
            new PHPExcel_Chart_DataSeriesValues('Number', 'Загрузка!$J$2:$J$15', null, 14),
            new PHPExcel_Chart_DataSeriesValues('Number', 'Загрузка!$K$2:$K$15', null, 14),
            new PHPExcel_Chart_DataSeriesValues('Number', 'Загрузка!$L$2:$L$15', null, 14),
            new PHPExcel_Chart_DataSeriesValues('Number', 'Загрузка!$M$2:$M$15', null, 14),
            new PHPExcel_Chart_DataSeriesValues('Number', 'Загрузка!$N$2:$N$15', null, 14),
            new PHPExcel_Chart_DataSeriesValues('Number', 'Загрузка!$O$2:$O$15', null, 14),
            new PHPExcel_Chart_DataSeriesValues('Number', 'Загрузка!$P$2:$P$15', null, 14),
            new PHPExcel_Chart_DataSeriesValues('Number', 'Загрузка!$Q$2:$Q$15', null, 14),
        );


        $ds2 = new PHPExcel_Chart_DataSeries( PHPExcel_Chart_DataSeries::TYPE_BARCHART,
            PHPExcel_Chart_DataSeries::GROUPING_STANDARD, range(0, count($dsv2) - 1),
            $dsl2, $xal2, $dsv2);//макет диаграммы

        $pa2 = new \PHPExcel_Chart_PlotArea(NULL, array($ds2));
        $legend2 = new \PHPExcel_Chart_Legend(\PHPExcel_Chart_Legend::POSITION_RIGHT, NULL, false);
        $title2 = new \PHPExcel_Chart_Title('Загрузка');

        $chart2 = new \PHPExcel_Chart( 'chart1', $title2, $legend2, $pa2, true,
            0, NULL, NULL );
        $chart2->setTopLeftPosition('S1');
        $chart2->setBottomRightPosition('AM25');
        $ews->addChart($chart2);

        $writer = \PHPExcel_IOFactory::createWriter($ea, 'Excel2007');
        $writer->setIncludeCharts(true);
        $writer->save($fileName . '.xlsx');
    }
}