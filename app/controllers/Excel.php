<?php
require_once 'vendor/autoload.php';

class Excel extends Controller {
    function __construct() {
        $this->days_val = $_POST['days_val'];
    }

    public function getToExcel($newVals){
        //Массив для старых данных таблицы
        $oldVals = [];
        //Присваиваем старые данные таблицы
        $xls = PHPExcel_IOFactory::load( 'table.xls');
        //Делаем активный лист
        $sheet = $xls->getActiveSheet();

        //Считываем старые значения

        //Берём последнюю колонну в файле
        $highestColumn = $sheet->getHighestColumn();
        //Перебираем все строки
        $i = 0;
        foreach($sheet->getRowIterator() as $row) {
            //Указываем координаты промежутка данные которого мы хотим взять
            $range = 'A' . $row->getRowIndex() . ':' . $highestColumn . $row->getRowIndex();
            //Получаем данные строки в указанном промежутке
            $rowData = $sheet->rangeToArray($range, NULL, TRUE, TRUE, TRUE);
            //Т.к. данные возвращаются в виде двумерного массива под индексом строки, то указываем номер строки
            //print_r($rowData);
            $rowData = $rowData[$row->getRowIndex()];
            //Если строка была абсолютно пуста, то игнорируем её
            if (implode("", $rowData) != "") {
                $j = 0;
                //Заполняем массив для старых данных
                foreach ($rowData as $column => $value) {
                    $oldVals[$i][$j] = $value;
                    $j++;
                }
            }
            $i++;
        }
        //print_r($oldVals);

        //Перебираем старые значения
        for($i = 1; $i < count($oldVals); $i++){
            //Когда доходим до даты на которую собирал данные парсер, то начинаем вычислять нужные нам значения
            if($newVals[1] == $oldVals[$i][1]){
                //Индексы значений. Т.к. значения Guesthouse Kuin Kotonaan имеют нестандартные координаты, то в ряде
                //массивов значений отсутствуют его индексы. Поэтому массивы $loadIndex и $maColumnsNums длиннее других
                $roomNumColumnIndex = [8, 13, 18, 23, 28, 33, 38, 43, 48, 53, 58, 63, 68];// Индексы комнат в наличии
                $loadIndex = [6, 9, 14, 19, 24, 29, 34, 39, 44, 49, 54, 59, 64, 69];// Индексы Загрузка
                $changesColumnNums = [10, 15, 20, 25, 30, 35, 40, 45, 50, 55, 60, 65, 70];// Индексы Изменения
                $maColumnsNums = [7, 11, 16, 21, 26, 31, 36, 41, 46, 51, 56, 61, 66, 71];// Индексы МА 30
                $changesMaColumnIndex = [12, 17, 22, 27, 32, 37, 42, 47, 52, 57, 62, 67, 72];// Индексы Разность МА 30

                //Перебираем массив с индексами значения МА30, чтобы заполнить все значения каждого отеля
                for ($j = 0; $j < count($maColumnsNums); $j++) {
                    //Массив для загрузки
                    $loadArr = [];
                    //Если в таблице меньше 31 строки, то собираем загрузку за имеющийся период времени
                    if ($i < 31) {
                        /*
                         * Собираем загрузку начиная с последней строки в порядке убывания. Т.к. первая строка это
                         * заголовки, то она нам не нужна. $k является номером рассматриваемой строки.
                        */
                        for($k = $i - 1; $k > 0; $k--) {
                            //Берём данные нужной нам строки в соответствии с индексом загрузки отеля
                            preg_match('~\d+~', $oldVals[$k][$loadIndex[$j]], $a);
                            //Если ячейка пустая, то её значение не учитывается
                            if ($a[0] != '') {
                                //Сохраняем значение загрузки в массив
                                $loadArr[] = $a[0];
                            }
                        }
                    } else {
                        //Если в таблице более 31 строки, то собираем данные за 30 дней. $n - число итераций,
                        //$k - номер строки
                        $n = 0;
                        for ($k = $i - 1; $n < 30; $k--) {
                            preg_match('~\d+~', $oldVals[$k][$loadIndex[$j]], $a);
                            if ($a[0] != '') {
                                $loadArr[] = $a[0];
                            }
                            $n++;
                        }
                    }

                    if($j == 0){
                        /*
                         * Прописываем данные Guesthouse Kuin Kotonaan. Заметьте, что у этого отеля нет
                         * значения Изменения. Из-за этого массивы с индексами имеют разную длину
                        */
                        $oldVals[$i][0] = $newVals[0];//Цена
                        $oldVals[$i][2] = $newVals[2];//День недели
                        $oldVals[$i][3] = $newVals[3];//Число номеров в наличии
                        $oldVals[$i][4] = $newVals[4];//Брони
                        $oldVals[$i][5] = $newVals[5];//Выручка
                    }

                    //Если мы прописывали данные не Guesthouse Kuin Kotonaan, то прописываем номера в наличии
                    if($j > 0){
                        $oldVals[$i][$roomNumColumnIndex[$j - 1]] = $newVals[$roomNumColumnIndex[$j - 1]];
                    }
                    //Прописываем загрузку
                    $oldVals[$i][$loadIndex[$j]] = $newVals[$loadIndex[$j]];


                    /*
                     * Вычисляем МА30. МА30 это среднее арифметическое значений загрузки за 30 дней.
                     * Берём загрузку на выбранную дату и суммируем с загрузкой на остальные даты.
                     * Затем делим сумму на число слагаемых
                    */
                    preg_match('~\d+~', $newVals[$loadIndex[$j]], $a);
                    $currentLoad = $a[0];
                    $ma = ($currentLoad + array_sum($loadArr)) / (count($loadArr) + 1);
                    $oldVals[$i][$maColumnsNums[$j]] = round($ma, 2) . '%';

                    if ($i > 1 || $j > 0) {
                        //Изменения. Отнимаем от числа комнат в наличии на выбранную даты число комнат на предыдущую дату
                        $oldVals[$i][$changesColumnNums[$j - 1]] = $newVals[$roomNumColumnIndex[$j - 1]] - $oldVals[$i - 1][$roomNumColumnIndex[$j - 1]];

                        //Аналогично с МА 30
                        preg_match('~\d+~', $oldVals[$i][7], $myMa);
                        preg_match('~\d+~', $oldVals[$i][$maColumnsNums[$j]], $otherMa);
                        $oldVals[$i][$changesMaColumnIndex[$j - 1]] = $myMa[0] - $otherMa[0];
                    }
                }

                //Рынок и выборка

                //Загрузка рынка
                $oldVals[$i][73] = $newVals[73];

                //Загрузка выборки
                $oldVals[$i][76] = $newVals[76];

                //Индексы загрузки рынка и выборки
                $lArr = [73, 76];
                //Индексы МА30 рынка и выборки
                $maArr = [74, 77];
                //Индексы изменений рынка и выборки
                $changesArr = [75, 78];


                //Аналогичным образом вычисляем МА30 и изменения МА30 рынка и выборки
                for($j = 0; $j < count($lArr); $j++){
                    //Собираем загрузку
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
                    //Вычисляем МА30
                    preg_match('~\d+~', $newVals[$lArr[$j]], $a);
                    $currentLoad = $a[0];
                    $ma = ($currentLoad + array_sum($loadArr)) / (count($loadArr) + 1);
                    $oldVals[$i][$maArr[$j]] = round($ma, 2) . '%';

                    //Разность МА 30
                    preg_match('~\d+~',$oldVals[$i][7], $myMa);
                    preg_match('~\d+~',$oldVals[$i][$maArr[$j]], $otherMa);
                    $oldVals[$i][$changesArr[$j]] = $myMa[0] - $otherMa[0];
                }
            }
        }


        /*
         * Т.к. парсер может не использоваться довольно длительный срок, то возникнет необходимость заполнить даты,
         * чтобы строки шли последовательно в порядке возрастания дат. Поэтому в данном цикле мы заполняем
         * таблицу на год вперёд пустыми строками. Если парсер не использовался более 10000 дней (более 27 лет),
         * то придётся заполнять недостающие строки вручную или другим способом
        */
        //Определяем число строк в таблице
        $i = count($oldVals);
        /*
         * В первой итерации мы получим дату ровно через год. Если парсер сегодня не использовался, то условие
         * не сработает и $j увеличится на один и в следующей итерации мы получим дату предыдущего дня. Таким образом
         * мы опустимся на 10000 дней.
        */
        for($j = 0; $j < 10000; $j++){
            //Определяем дату
            $t = strtotime('+1 year -' . $j . 'day 00:00:00');
            $сheckin_year = date('y', $t);
            $сheckin_month = date('m', $t);
            $сheckin_monthday = date('d', $t);
            $date = $сheckin_monthday . '.' . $сheckin_month . '.20' . $сheckin_year;

            /*
             * Сравниваем полученную дату с датой последней строки в таблице. В случае совпадения запускаем цикл,
             * который заполнит все вышестоящие строки, пока не достигнет даты, которая наступит через два года.
            */
            if($oldVals[$i - 1][1] == $date){
                for($n = 0; $n <= $j; $n++) {
                    //Определяем дату
                    $t = strtotime('+1 year -' . $j. ' day +' . $n . ' day 00:00:00');
                    $сheckin_year = date('y', $t);
                    $сheckin_month = date('m', $t);
                    $сheckin_monthday = date('d', $t);
                    $date = $сheckin_monthday . '.' . $сheckin_month . '.20' . $сheckin_year;

                    //Определяем номер строки и заполняем таблицу
                    $oldVals[$i - 1 + $n][0] = null;
                    $oldVals[$i - 1 + $n][1] = $date;
                }
                //Заполнив таблицу выходим из цикла, т.к. нижестоящие строки проверять нет смысла
                break(1);
            }
        }


        //Приступаем к созданию таблицы

        $xls = new PHPExcel();
        //Получаем активный лист
        $sheet = $xls->getActiveSheet();
        //Подписываем лист
        $sheet->setTitle('Букинг');

        //Записываем значения
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

        //Сохраняем таблицу в файл table.xls
        $objWriter->save('table.xls');
    }

    public function getToChart($prices, $limitedLoad, $load, $fileName){
        $ea = new PHPExcel();
        //Наименования линий графика
        $hotelList = ['Отели','Guesthouse - Kuin Kotonaan','Hotel Leikari','Leikari "Nature" Bungalows with Terrace',
            'Апартаменты', 'Kotkan Residenssi Apartments', 'Guest House Nina Art', 'Guesthouse Lokinlaulu', 'The Grand Karhu',
            'Kartanohotelli Karhulan Hovi', 'Hotelli Merikotka', 'Hotelli Kotola', 'Kesähostelli Kärkisaari', 'Hotel Villa Vanessa',
            'Beach Hotel Santalahti', 'Рынок', 'Выборка'];
        /*
         * PhpExcel рисует таблицу для графика из массива. Каждый массив внутри общего массива будет отдельной
         * строкой в таблице. Поэтому первым элементом делаем массив с наименованиями, а остальными элементами
         * массивы с ценами
        */
        $arr[0] = $hotelList;
        for($i = 0; $i < count($prices); $i++){
            $arr[$i + 1] = $prices[$i];
        }

        //Рисуем первый график Цены

        //Устанавливаем номер листа
        $ea->setActiveSheetIndex(0);
        //Делаем лист активным
        $ews = $ea->getActiveSheet();
        $ews->setTitle('Цены');
        //Рисуем таблицу для графика на основе созданного массива
        $ews->fromArray($arr);

        //Указываем координаты для наименований графика
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

        //Указываем координаты дат. По ним будет построена ось X
        $xal = array(
            new PHPExcel_Chart_DataSeriesValues('String', 'Цены!$A$2:$A$'.($this->days_val + 1), null, 6),
        );

        //Указываем координаты цен. По ним будет построен график
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


        //Делаем макет диаграммы
        $ds = new PHPExcel_Chart_DataSeries( PHPExcel_Chart_DataSeries::TYPE_LINECHART,
            PHPExcel_Chart_DataSeries::GROUPING_STANDARD, range(0, count($dsv) - 1),
            $dsl, $xal, $dsv);

        //Делаем легенду графика и указываем заголовок
        $pa = new \PHPExcel_Chart_PlotArea(NULL, array($ds));
        $legend = new \PHPExcel_Chart_Legend(\PHPExcel_Chart_Legend::POSITION_RIGHT, NULL, false);
        $title = new \PHPExcel_Chart_Title('Цены');

        //Добавляем график и указываем его координаты
        $chart = new \PHPExcel_Chart( 'chart1', $title, $legend, $pa, true,
            0, NULL, NULL );
        $chart->setTopLeftPosition('R1');
        $chart->setBottomRightPosition('BP31');
        $ews->addChart($chart);


        //Аналогичным образом делаем второй график - Ограниченная загрузка


        //Указываем наименования
        $hotelList2 = ['Отели','Guesthouse - Kuin Kotonaan', 'Рынок', 'Рынок без KK и Лейкари', 'Выборка'];
        //Формируем массив
        $arr2[0] = $hotelList2;
        for($i = 0; $i < count($limitedLoad); $i++){
            $arr2[$i + 1] = $limitedLoad[$i];
        }

        //Делаем следующий лист
        $ea->createSheet();
        //Указываем номер листа
        $ews = $ea->setActiveSheetIndex(1);
        $ews->setTitle('Ограниченная_загрузка');
        //Делаем таблицу
        $ews->fromArray($arr2);


        //Координаты наименований
        $dsl2 = array(
            new PHPExcel_Chart_DataSeriesValues('String', 'Ограниченная_загрузка!$B$1', null, 1),
            new PHPExcel_Chart_DataSeriesValues('String', 'Ограниченная_загрузка!$C$1', null, 1),
            new PHPExcel_Chart_DataSeriesValues('String', 'Ограниченная_загрузка!$D$1', null, 1),
            new PHPExcel_Chart_DataSeriesValues('String', 'Ограниченная_загрузка!$E$1', null, 1),
            new PHPExcel_Chart_DataSeriesValues('String', 'Ограниченная_загрузка!$F$1', null, 1),
        );

        //Координаты дат
        $xal2 = array(
            new PHPExcel_Chart_DataSeriesValues('String', 'Ограниченная_загрузка!$A$2:$A$'.($this->days_val + 1), null, 6),
        );

        //Координаты значений загрузки
        $dsv2 = array(
            new PHPExcel_Chart_DataSeriesValues('Number', 'Ограниченная_загрузка!$B$2:$B$'.($this->days_val + 1), null, $this->days_val),
            new PHPExcel_Chart_DataSeriesValues('Number', 'Ограниченная_загрузка!$C$2:$C$'.($this->days_val + 1), null, $this->days_val),
            new PHPExcel_Chart_DataSeriesValues('Number', 'Ограниченная_загрузка!$D$2:$D$'.($this->days_val + 1), null, $this->days_val),
            new PHPExcel_Chart_DataSeriesValues('Number', 'Ограниченная_загрузка!$E$2:$E$'.($this->days_val + 1), null, $this->days_val),
            new PHPExcel_Chart_DataSeriesValues('Number', 'Ограниченная_загрузка!$F$2:$F$'.($this->days_val + 1), null, $this->days_val),
        );


        //Макет диаграммы
        $ds2 = new PHPExcel_Chart_DataSeries( PHPExcel_Chart_DataSeries::TYPE_BARCHART,
            PHPExcel_Chart_DataSeries::GROUPING_STANDARD, range(0, count($dsv2) - 1),
            $dsl2, $xal2, $dsv2);

        //Легенда и заголовок
        $pa2 = new \PHPExcel_Chart_PlotArea(NULL, array($ds2));
        $legend2 = new \PHPExcel_Chart_Legend(\PHPExcel_Chart_Legend::POSITION_RIGHT, NULL, false);
        $title2 = new \PHPExcel_Chart_Title('Ограниченная_загрузка');

        //Добавляем график и указываем его координаты
        $chart2 = new \PHPExcel_Chart( 'chart1', $title2, $legend2, $pa2, true,
            0, NULL, NULL );
        $chart2->setTopLeftPosition('F1');
        $chart2->setBottomRightPosition('BD26');
        $ews->addChart($chart2);


        //Теперь последний график - Загрузка


        //Наименования
        $hotelList3 = ['Отели','Guesthouse - Kuin Kotonaan','Hotel Leikari','Leikari "Nature" Bungalows with Terrace',
            'Апартаменты', 'Kotkan Residenssi Apartments', 'Guest House Nina Art', 'Guesthouse Lokinlaulu', 'The Grand Karhu',
            'Kartanohotelli Karhulan Hovi', 'Hotelli Merikotka', 'Hotelli Kotola', 'Kesähostelli Kärkisaari', 'Hotel Villa Vanessa',
            'Beach Hotel Santalahti', 'Рынок', 'Выборка'];
        //Формируем массив
        $arr3[0] = $hotelList3;
        for($i = 0; $i < count($load); $i++){
            $arr3[$i + 1] = $load[$i];
        }

        //Создаём третий лист
        $ea->createSheet();
        $ews = $ea->setActiveSheetIndex(2);
        //Указываем заголовок
        $ews->setTitle('Загрузка');
        //Делаем таблицу
        $ews->fromArray($arr3);


        //Координаты наименований
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

        //Координаты дат
        $xal3 = array(
            new PHPExcel_Chart_DataSeriesValues('String', 'Загрузка!$A$2:$A$'.($this->days_val + 1), null, 6),
        );

        //Координаты значений загрузки
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


        //Макет диаграммы
        $ds3 = new PHPExcel_Chart_DataSeries( PHPExcel_Chart_DataSeries::TYPE_BARCHART,
            PHPExcel_Chart_DataSeries::GROUPING_STANDARD, range(0, count($dsv3) - 1),
            $dsl3, $xal3, $dsv3);

        //Легенда
        $pa3 = new \PHPExcel_Chart_PlotArea(NULL, array($ds3));
        $legend3 = new \PHPExcel_Chart_Legend(\PHPExcel_Chart_Legend::POSITION_RIGHT, NULL, false);
        $title3 = new \PHPExcel_Chart_Title('Загрузка');

        //Добавляем график и указываем его координаты
        $chart3 = new \PHPExcel_Chart( 'chart1', $title3, $legend3, $pa3, true,
            0, NULL, NULL );
        $chart3->setTopLeftPosition('R1');
        $chart3->setBottomRightPosition('BP26');
        $ews->addChart($chart3);

        //Теперь рисуем созданные графики и сохраняем в указанный файл
        $writer = \PHPExcel_IOFactory::createWriter($ea, 'Excel2007');
        $writer->setIncludeCharts(true);
        $writer->save($fileName . '.xlsx');
    }
}