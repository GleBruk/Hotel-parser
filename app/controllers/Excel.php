<?php
require_once 'vendor/autoload.php';

class Excel extends Controller {
    function __construct() {
        $this->days_val = $_POST['days_val'];
    }

    public function getToExcel($newVals){
        $oldVals = [];
        $xls = PHPExcel_IOFactory::load( 'table.xls');
        $sheet = $xls->getActiveSheet();

        //Считываем старые значения
        $highestColumn = $sheet->getHighestColumn();
        $i = 0;
        foreach($sheet->getRowIterator() as $row) {
            $range = 'A' . $row->getRowIndex() . ':' . $highestColumn . $row->getRowIndex();
            $rowData = $sheet->rangeToArray($range, NULL, TRUE, TRUE, TRUE);
            $rowData = $rowData[$row->getRowIndex()];
            if (implode("", $rowData) != "") {
                $j = 0;
                foreach ($rowData as $column => $value) {
                    $oldVals[$i][$j] = $value;
                    $j++;
                }
            }
            $i++;
        }
        //print_r($oldVals);

        //Переписываем старые значения
        for($i = 1; $i < count($oldVals); $i++){
            if($newVals[1] == $oldVals[$i][1]){
                //echo 'Bingo!';
                $roomNumColumnIndex = [8, 13, 18, 23, 28, 33, 38, 43, 48, 53, 58, 63, 68];// Координаты комнат в наличии
                $loadIndex = [6, 9, 14, 19, 24, 29, 34, 39, 44, 49, 54, 59, 64, 69];// Координаты Загрузка
                $changesColumnNums = [10, 15, 20, 25, 30, 35, 40, 45, 50, 55, 60, 65, 70];// Координаты Изменения
                $maColumnsNums = [7, 11, 16, 21, 26, 31, 36, 41, 46, 51, 56, 61, 66, 71];// Координаты МА 30
                $changesMaColumnIndex = [12, 17, 22, 27, 32, 37, 42, 47, 52, 57, 62, 67, 72];// Координаты Разность МА 30


                for ($j = 0; $j < count($maColumnsNums); $j++) {
                    $loadArr = [];
                    if ($i < 31) {
                        for($k = $i - 1; $k > 0; $k--) {
                            preg_match('~\d+~', $oldVals[$k][$loadIndex[$j]], $a);
                            if ($a[0] != '') {
                                $loadArr[] = $a[0];
                            }
                        }
                    } else {
                        $n = 0;
                        for ($k = $i - 1; $n < 30; $k--) {
                            preg_match('~\d+~', $oldVals[$k][$loadIndex[$j]], $a);
                            if ($a[0] != '') {
                                $loadArr[] = $a[0];
                            }
                            $n++;
                        }
                    }
                    //print_r($loadArr);

                    if($j == 0){
                        //Переписываем данные Guesthouse Kuin Kotonaan
                        $oldVals[$i][0] = $newVals[0];
                        $oldVals[$i][2] = $newVals[2];
                        $oldVals[$i][3] = $newVals[3];
                        $oldVals[$i][4] = $newVals[4];
                        $oldVals[$i][5] = $newVals[5];
                    }

                    if($j > 0){
                        //Переписываем номера в наличии
                        $oldVals[$i][$roomNumColumnIndex[$j - 1]] = $newVals[$roomNumColumnIndex[$j - 1]];
                    }
                    //Переписываем загрузку
                    $oldVals[$i][$loadIndex[$j]] = $newVals[$loadIndex[$j]];


                    preg_match('~\d+~', $newVals[$loadIndex[$j]], $a);
                    $currentLoad = $a[0];
                    $ma = ($currentLoad + array_sum($loadArr)) / (count($loadArr) + 1);
                    $oldVals[$i][$maColumnsNums[$j]] = round($ma, 2) . '%';

                    if ($i > 1 || $j > 0) {
                        //Изменения
                        $oldVals[$i][$changesColumnNums[$j - 1]] = $newVals[$roomNumColumnIndex[$j - 1]] - $oldVals[$i - 1][$roomNumColumnIndex[$j - 1]];

                        //Разность МА 30
                        preg_match('~\d+~', $oldVals[$i][7], $myMa);
                        preg_match('~\d+~', $oldVals[$i][$maColumnsNums[$j]], $otherMa);
                        $oldVals[$i][$changesMaColumnIndex[$j - 1]] = $myMa[0] - $otherMa[0];
                    }
                }

                //Рынок и выборка

                //print_r($newVals);

                //Загрузка рынка
                $oldVals[$i][73] = $newVals[73];

                //Загрузка выборки
                $oldVals[$i][76] = $newVals[76];

                $lArr = [73, 76];
                $maArr = [74, 77];
                $changesArr = [75, 78];


                for($j = 0; $j < count($lArr); $j++){
                    if($i < 31) {
                        for ($k = $i - 1; $k > 0; $k--) {
                            preg_match('~\d+~',$oldVals[$k][$lArr[$j]], $a);
                            if($a[0] != '') {
                                $loadArr[] = $a[0];
                            }
                        }
                    } else{
                        $n = 0;
                        for ($k = $i - 1; $n < 30; $k--) {
                            preg_match('~\d+~',$oldVals[$k][$lArr[$j]], $a);
                            if($a[0] != '') {
                                $loadArr[] = $a[0];
                            }
                            $n++;
                        }
                    }
                    preg_match('~\d+~', $newVals[$lArr[$j]], $a);
                    $currentLoad = $a[0];
                    $ma = ($currentLoad + array_sum($loadArr)) / (count($loadArr) + 1);
                    $oldVals[$i][$maArr[$j]] = round($ma, 2) . '%';

                    //$newVals[70] = //Разность МА 30
                    preg_match('~\d+~',$oldVals[$i][7], $myMa);
                    preg_match('~\d+~',$oldVals[$i][$maArr[$j]], $otherMa);
                    $oldVals[$i][$changesArr[$j]] = $myMa[0] - $otherMa[0];
                }
            }
        }

        // Создаем объект класса PHPExcel
        $xls = new PHPExcel();
        // Получаем активный лист
        $sheet = $xls->getActiveSheet();
        // Подписываем лист
        $sheet->setTitle('Букинг');

        /* Если нужно заполнить таблицу
        $oldVals[11][0] = null;
        $oldVals[11][1] = '11.08.2021';
        $d = 12;
        for($i = 0; $i < 366; $i++) {
            $t = strtotime('+' . $i . ' day 00:00:00');
            $сheckin_year = date('y', $t);
            $сheckin_month = date('m', $t);
            $сheckin_monthday = date('d', $t);
            $date = $сheckin_monthday . '.' . $сheckin_month . '.20' . $сheckin_year;
            $oldVals[$d][0] = null;
            $oldVals[$d][1] = $date;
            $d++;
        }

       //print_r($oldVals);*/
        $i = count($oldVals);
        for($j = 0; $j < 366; $j++){
            $t = strtotime('+1 year -' . $j . 'day 00:00:00');
            $сheckin_year = date('y', $t);
            $сheckin_month = date('m', $t);
            $сheckin_monthday = date('d', $t);
            $date = $сheckin_monthday . '.' . $сheckin_month . '.20' . $сheckin_year;
            if($oldVals[$i - 1][1] == $date){
                for($n = 0; $n <= $j; $n++) {
                    $t = strtotime('+1 year -' . $j. ' day +' . $n . ' day 00:00:00');
                    $сheckin_year = date('y', $t);
                    $сheckin_month = date('m', $t);
                    $сheckin_monthday = date('d', $t);
                    $date = $сheckin_monthday . '.' . $сheckin_month . '.20' . $сheckin_year;
                    $oldVals[$i - 1 + $n][0] = null;
                    $oldVals[$i - 1 + $n][1] = $date;
                }
                break(1);
            }
        }



        //Записываем старые значения
        for($i = 0; $i < count($oldVals); $i++) {
            for($j = 0; $j < 256; $j++){
                $sheet->setCellValueByColumnAndRow(
                    $j,
                    $i + 1,
                    $oldVals[$i][$j]);
                $sheet->getStyleByColumnAndRow($j, $i + 1)->getAlignment()->
                setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            }
        }

        /*$t = strtotime('+1 year 00:00:00');
        $сheckin_year = date('y', $t);
        $сheckin_month = date('m', $t);
        $сheckin_monthday = date('d', $t);
        $date = $сheckin_monthday . '.' . $сheckin_month . '.20' . $сheckin_year;

        $i = count($oldVals);
        if($oldVals[$i - 1][1] != $date){
            $sheet->setCellValueByColumnAndRow(
                1,
                $i + 1,
                $date);
            $sheet->getStyleByColumnAndRow(1, $i + 1)->getAlignment()->
            setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        }*/
        //Стили
        for($i = 0; $i < count($oldVals[0]); $i++){
            $sheet->setCellValueByColumnAndRow(
                $i,
                1,
                $oldVals[0][$i]);
            $sheet->getStyleByColumnAndRow($i, 1)->getAlignment()->
            setTextRotation(90)->setWrapText(true);
            $sheet->getStyleByColumnAndRow($i, 1)->getFont()->setSize(9);
            if($i == 1 || $i == 2 || $i == 4 || $i == 5){
                $sheet->getColumnDimensionByColumn($i)->setWidth(10);
            } else{
                $sheet->getColumnDimensionByColumn($i)->setWidth(6);
            }
            //$sheet->getColumnDimensionByColumn($i, 10)
        }
        $sheet->getRowDimension('1')->setRowHeight(100);

        $objWriter = new PHPExcel_Writer_Excel5($xls);
        $objWriter->save('table.xls');
    }

    public function getToChart($data, $limitedLoad, $load, $fileName){
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
            new PHPExcel_Chart_DataSeriesValues('String', 'Цены!$A$2:$A$'.($this->days_val + 1), null, 6),
        );//Ось X. Указывает координаты для её значения


        $dsv = array(
            new PHPExcel_Chart_DataSeriesValues('Number', 'Цены!$B$2:$B$'.($this->days_val + 1), null, $this->days_val),
            new PHPExcel_Chart_DataSeriesValues('Number', 'Цены!$C$2:$C$'.($this->days_val + 1), null, $this->days_val),
            new PHPExcel_Chart_DataSeriesValues('Number', 'Цены!$D$2:$D$'.($this->days_val + 1), null, $this->days_val),
            new PHPExcel_Chart_DataSeriesValues('Number', 'Цены!$E$2:$E$'.($this->days_val + 1), null, $this->days_val),
            new PHPExcel_Chart_DataSeriesValues('Number', 'Цены!$F$2:$F$'.($this->days_val + 1), null, $this->days_val),
            new PHPExcel_Chart_DataSeriesValues('Number', 'Цены!$G$2:$G$'.($this->days_val + 1), null, $this->days_val),
            new PHPExcel_Chart_DataSeriesValues('Number', 'Цены!$H$2:$H$'.($this->days_val + 1), null, $this->days_val),
            new PHPExcel_Chart_DataSeriesValues('Number', 'Цены!$I$2:$I$'.($this->days_val + 1), null, $this->days_val),
            new PHPExcel_Chart_DataSeriesValues('Number', 'Цены!$J$2:$J$'.($this->days_val + 1), null, $this->days_val),
            new PHPExcel_Chart_DataSeriesValues('Number', 'Цены!$K$2:$K$'.($this->days_val + 1), null, $this->days_val),
            new PHPExcel_Chart_DataSeriesValues('Number', 'Цены!$L$2:$L$'.($this->days_val + 1), null, $this->days_val),
            new PHPExcel_Chart_DataSeriesValues('Number', 'Цены!$M$2:$M$'.($this->days_val + 1), null, $this->days_val),
            new PHPExcel_Chart_DataSeriesValues('Number', 'Цены!$N$2:$N$'.($this->days_val + 1), null, $this->days_val),
            new PHPExcel_Chart_DataSeriesValues('Number', 'Цены!$O$2:$O$'.($this->days_val + 1), null, $this->days_val),
            new PHPExcel_Chart_DataSeriesValues('Number', 'Цены!$P$2:$P$'.($this->days_val + 1), null, $this->days_val),
            new PHPExcel_Chart_DataSeriesValues('Number', 'Цены!$Q$2:$Q$'.($this->days_val + 1), null, $this->days_val),
        );


        $ds = new PHPExcel_Chart_DataSeries( PHPExcel_Chart_DataSeries::TYPE_LINECHART,
            PHPExcel_Chart_DataSeries::GROUPING_STANDARD, range(0, count($dsv) - 1),
            $dsl, $xal, $dsv);//макет диаграммы

        $pa = new \PHPExcel_Chart_PlotArea(NULL, array($ds));
        $legend = new \PHPExcel_Chart_Legend(\PHPExcel_Chart_Legend::POSITION_RIGHT, NULL, false);
        $title = new \PHPExcel_Chart_Title('Цены');

        $chart = new \PHPExcel_Chart( 'chart1', $title, $legend, $pa, true,
            0, NULL, NULL );
        $chart->setTopLeftPosition('R1');
        $chart->setBottomRightPosition('BP31');
        $ews->addChart($chart);




        $hotelList2 = ['Отели','Guesthouse - Kuin Kotonaan', 'Рынок', 'Рынок без KK и Лейкари', 'Выборка'];
        $arr2[0] = $hotelList2;
        for($i = 0; $i < count($limitedLoad); $i++){
            $arr2[$i + 1] = $limitedLoad[$i];
        }

        //print_r($hotelList);
        //print_r($data);

        $ea->createSheet();
        $ews = $ea->setActiveSheetIndex(1);
        //$ews = $ea->getActiveSheet();
        $ews->setTitle('Ограниченная_загрузка');
        $ews->fromArray($arr2);


        $dsl2 = array(
            new PHPExcel_Chart_DataSeriesValues('String', 'Ограниченная_загрузка!$B$1', null, 1),
            new PHPExcel_Chart_DataSeriesValues('String', 'Ограниченная_загрузка!$C$1', null, 1),
            new PHPExcel_Chart_DataSeriesValues('String', 'Ограниченная_загрузка!$D$1', null, 1),
            new PHPExcel_Chart_DataSeriesValues('String', 'Ограниченная_загрузка!$E$1', null, 1),
            new PHPExcel_Chart_DataSeriesValues('String', 'Ограниченная_загрузка!$F$1', null, 1),
        );

        $xal2 = array(
            new PHPExcel_Chart_DataSeriesValues('String', 'Ограниченная_загрузка!$A$2:$A$'.($this->days_val + 1), null, 6),
        );//Ось X. Указывает координаты для её значения


        $dsv2 = array(
            new PHPExcel_Chart_DataSeriesValues('Number', 'Ограниченная_загрузка!$B$2:$B$'.($this->days_val + 1), null, $this->days_val),
            new PHPExcel_Chart_DataSeriesValues('Number', 'Ограниченная_загрузка!$C$2:$C$'.($this->days_val + 1), null, $this->days_val),
            new PHPExcel_Chart_DataSeriesValues('Number', 'Ограниченная_загрузка!$D$2:$D$'.($this->days_val + 1), null, $this->days_val),
            new PHPExcel_Chart_DataSeriesValues('Number', 'Ограниченная_загрузка!$E$2:$E$'.($this->days_val + 1), null, $this->days_val),
            new PHPExcel_Chart_DataSeriesValues('Number', 'Ограниченная_загрузка!$F$2:$F$'.($this->days_val + 1), null, $this->days_val),
        );


        $ds2 = new PHPExcel_Chart_DataSeries( PHPExcel_Chart_DataSeries::TYPE_BARCHART,
            PHPExcel_Chart_DataSeries::GROUPING_STANDARD, range(0, count($dsv2) - 1),
            $dsl2, $xal2, $dsv2);//макет диаграммы

        $pa2 = new \PHPExcel_Chart_PlotArea(NULL, array($ds2));
        $legend2 = new \PHPExcel_Chart_Legend(\PHPExcel_Chart_Legend::POSITION_RIGHT, NULL, false);
        $title2 = new \PHPExcel_Chart_Title('Ограниченная_загрузка');

        $chart2 = new \PHPExcel_Chart( 'chart1', $title2, $legend2, $pa2, true,
            0, NULL, NULL );
        $chart2->setTopLeftPosition('F1');
        $chart2->setBottomRightPosition('BD26');
        $ews->addChart($chart2);




        $hotelList3 = ['Отели','Guesthouse - Kuin Kotonaan','Hotel Leikari','Leikari "Nature" Bungalows with Terrace',
            'Апартаменты', 'Kotkan Residenssi Apartments', 'Guest House Nina Art', 'Guesthouse Lokinlaulu', 'The Grand Karhu',
            'Kartanohotelli Karhulan Hovi', 'Hotelli Merikotka', 'Hotelli Kotola', 'Kesähostelli Kärkisaari', 'Hotel Villa Vanessa',
            'Beach Hotel Santalahti', 'Рынок', 'Выборка'];
        $arr3[0] = $hotelList3;
        for($i = 0; $i < count($load); $i++){
            $arr3[$i + 1] = $load[$i];
        }

        //print_r($hotelList);
        //print_r($data);

        $ea->createSheet();
        $ews = $ea->setActiveSheetIndex(2);
        //$ews = $ea->getActiveSheet();
        $ews->setTitle('Загрузка');
        $ews->fromArray($arr3);


        $dsl3 = array(
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

        $xal3 = array(
            new PHPExcel_Chart_DataSeriesValues('String', 'Загрузка!$A$2:$A$'.($this->days_val + 1), null, 6),
        );//Ось X. Указывает координаты для её значения


        $dsv3 = array(
            new PHPExcel_Chart_DataSeriesValues('Number', 'Загрузка!$B$2:$B$'.($this->days_val + 1), null, $this->days_val),
            new PHPExcel_Chart_DataSeriesValues('Number', 'Загрузка!$C$2:$C$'.($this->days_val + 1), null, $this->days_val),
            new PHPExcel_Chart_DataSeriesValues('Number', 'Загрузка!$D$2:$D$'.($this->days_val + 1), null, $this->days_val),
            new PHPExcel_Chart_DataSeriesValues('Number', 'Загрузка!$E$2:$E$'.($this->days_val + 1), null, $this->days_val),
            new PHPExcel_Chart_DataSeriesValues('Number', 'Загрузка!$F$2:$F$'.($this->days_val + 1), null, $this->days_val),
            new PHPExcel_Chart_DataSeriesValues('Number', 'Загрузка!$G$2:$G$'.($this->days_val + 1), null, $this->days_val),
            new PHPExcel_Chart_DataSeriesValues('Number', 'Загрузка!$H$2:$H$'.($this->days_val + 1), null, $this->days_val),
            new PHPExcel_Chart_DataSeriesValues('Number', 'Загрузка!$I$2:$I$'.($this->days_val + 1), null, $this->days_val),
            new PHPExcel_Chart_DataSeriesValues('Number', 'Загрузка!$J$2:$J$'.($this->days_val + 1), null, $this->days_val),
            new PHPExcel_Chart_DataSeriesValues('Number', 'Загрузка!$K$2:$K$'.($this->days_val + 1), null, $this->days_val),
            new PHPExcel_Chart_DataSeriesValues('Number', 'Загрузка!$L$2:$L$'.($this->days_val + 1), null, $this->days_val),
            new PHPExcel_Chart_DataSeriesValues('Number', 'Загрузка!$M$2:$M$'.($this->days_val + 1), null, $this->days_val),
            new PHPExcel_Chart_DataSeriesValues('Number', 'Загрузка!$N$2:$N$'.($this->days_val + 1), null, $this->days_val),
            new PHPExcel_Chart_DataSeriesValues('Number', 'Загрузка!$O$2:$O$'.($this->days_val + 1), null, $this->days_val),
            new PHPExcel_Chart_DataSeriesValues('Number', 'Загрузка!$P$2:$P$'.($this->days_val + 1), null, $this->days_val),
            new PHPExcel_Chart_DataSeriesValues('Number', 'Загрузка!$Q$2:$Q$'.($this->days_val + 1), null, $this->days_val),
        );


        $ds3 = new PHPExcel_Chart_DataSeries( PHPExcel_Chart_DataSeries::TYPE_BARCHART,
            PHPExcel_Chart_DataSeries::GROUPING_STANDARD, range(0, count($dsv3) - 1),
            $dsl3, $xal3, $dsv3);//макет диаграммы

        $pa3 = new \PHPExcel_Chart_PlotArea(NULL, array($ds3));
        $legend3 = new \PHPExcel_Chart_Legend(\PHPExcel_Chart_Legend::POSITION_RIGHT, NULL, false);
        $title3 = new \PHPExcel_Chart_Title('Загрузка');

        $chart3 = new \PHPExcel_Chart( 'chart1', $title3, $legend3, $pa3, true,
            0, NULL, NULL );
        $chart3->setTopLeftPosition('R1');
        $chart3->setBottomRightPosition('BP26');
        $ews->addChart($chart3);
        /**/
        $writer = \PHPExcel_IOFactory::createWriter($ea, 'Excel2007');
        $writer->setIncludeCharts(true);
        $writer->save($fileName . '.xlsx');
    }
}