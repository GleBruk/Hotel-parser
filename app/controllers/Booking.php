<?php
require_once 'app/controllers/Curl.php';
require_once 'app/controllers/Excel.php';
require_once 'vendor/autoload.php';
use DiDom\Document;

class Booking extends Controller{
    function __construct() {
        $this->curl = new Curl;
        $this->excel = new Excel;
        $this->sample = $_POST['sample'];
        $this->checkoutIndex = $_POST['checkout_index'];
        $this->group_adults = $_POST['group_adults'];
    }

    public function index(){
        $this->view('home\index');
    }

    public function parse(){
        $conModel = $this->model('ConstantsModel');
        $constants = $conModel->getConstants();
        for ($i = 0; $i < 14; $i++) {
            if ($i < 14) {//смотрим на 2 недели
                $t = strtotime('+' . $i . ' day 00:00:00');
                $сheckin_year = date('y', $t);
                $сheckin_month = date('m', $t);
                $сheckin_monthday = date('d', $t);
                $t = strtotime('+' . ($i + $this->checkoutIndex) . ' day 00:00:00');
                $checkout_year = date('y', $t);
                $checkout_month = date('m', $t);
                $checkout_monthday = date('d', $t);
                $url = 'https://www.booking.com/searchresults.ru.html?label=gen173nr-1FCAEoggI46AdIM1gEaEiIAQGYASG4ARfIAQzYAQHoAQH4AQuIAgGoAgO4AsPX34QGwAIB0gIkMjAyZjAwYzItNDVhYS00YTY0LWJiNjYtZjJkM2U3YjE4ZWZk2AIG4AIB&sid=f3857b8c77f93c413cb4587518473e0c&sb=1&src=searchresults&src_elem=sb&error_url=https%3A%2F%2Fwww.booking.com%2Fsearchresults.ru.html%3Flabel%3Dgen173nr-1FCAEoggI46AdIM1gEaEiIAQGYASG4ARfIAQzYAQHoAQH4AQuIAgGoAgO4AsPX34QGwAIB0gIkMjAyZjAwYzItNDVhYS00YTY0LWJiNjYtZjJkM2U3YjE4ZWZk2AIG4AIB%3Bsid%3Df3857b8c77f93c413cb4587518473e0c%3Btmpl%3Dsearchresults%3Bac_click_type%3Db%3Bac_position%3D0%3Bclass_interval%3D1%3Bdest_id%3D-1372348%3Bdest_type%3Dcity%3Bdtdisc%3D0%3Bfrom_sf%3D1%3Bgroup_adults%3D2%3Bgroup_children%3D0%3Binac%3D0%3Bindex_postcard%3D0%3Blabel_click%3Dundef%3Bno_rooms%3D1%3Boffset%3D0%3Bpostcard%3D0%3Braw_dest_type%3Dcity%3Broom1%3DA%252CA%3Bsb_price_type%3Dtotal%3Bsearch_selected%3D1%3Bshw_aparth%3D1%3Bslp_r_match%3D0%3Bsrc%3Dindex%3Bsrc_elem%3Dsb%3Bsrpvid%3Dedf75d0156180246%3Bss%3D%25D0%259A%25D0%25BE%25D1%2582%25D0%25BA%25D0%25B0%252C%2520%25D0%25AE%25D0%25B6%25D0%25BD%25D0%25B0%25D1%258F%2520%25D0%25A4%25D0%25B8%25D0%25BD%25D0%25BB%25D1%258F%25D0%25BD%25D0%25B4%25D0%25B8%25D1%258F%252C%2520%25D0%25A4%25D0%25B8%25D0%25BD%25D0%25BB%25D1%258F%25D0%25BD%25D0%25B4%25D0%25B8%25D1%258F%3Bss_all%3D0%3Bss_raw%3D%25D0%259A%25D0%25BE%25D1%2582%25D0%25BA%25D0%25B0%3Bssb%3Dempty%3Bsshis%3D0%3Btop_ufis%3D1%26%3B&ss=%D0%9A%D0%BE%D1%82%D0%BA%D0%B0&is_ski_area=0&ssne=%D0%9A%D0%BE%D1%82%D0%BA%D0%B0&ssne_untouched=%D0%9A%D0%BE%D1%82%D0%BA%D0%B0&city=-1372348&checkin_year=20' . $сheckin_year . '&checkin_month=' . $сheckin_month . '&checkin_monthday=' . $сheckin_monthday . '&checkout_year=20' . $checkout_year . '&checkout_month=' . $checkout_month . '&checkout_monthday=' . $checkout_monthday . '&group_adults=' . $this->group_adults . '&group_children=0&no_rooms=1&sb_changed_dates=1&from_sf=1';
                $date = $сheckin_monthday . '.' . $сheckin_month . '.20' . $сheckin_year;
            }

            $data = $this->parseAll($url, $date);

            //$excelData = $this->getExcelData($data, $date, $constants, $this->sample);
            //print_r($excelData);
            //echo "<br/>";
            //$tableName = $checkoutIndex . 'days-'  . $group_adults . 'adults-table';
            //$this->excel->getToExcel($excelData, $tableName);

            $chartData[] = $this->getChartData($date, $data, $this->sample);

            $load[] = $this->getLoad($date, $data, $constants, $this->sample);

            $dataAll[] = $data;
        }
        //$this->view('home/index', $data);
        //print_r($chartData);
        //print_r($load);
        $chartName = $this->checkoutIndex . 'days-'  . $this->group_adults . 'adults-chart';
        $this->excel->getToChart($chartData, $load, $chartName);
        //$this->view('home/index', $data);
        //print_r($chartData);
        //print_r($load);
        $this->view('home\index', $dataAll);
    }

    public function parseAll($url, $date, $ei = null){

        /*$dataAll = $this->parsePage($url);*/
        try {
            error_clear_last();
            $dataAll = @$this->parsePage($url);
            $error = error_get_last();
            if($ei > 10){
                error_clear_last();
            }
            if ($error || $dataAll == null) {
                throw new Exception("Ошибка страницы");
            }

            //print_r($dataAll);
            //$this->parseCard($urls, $row);
            $urls = [];
            for($i = 0; $i < count($dataAll); $i++){
                $urls[$i] = $dataAll[$i]['url'];
            }
            $urls = array_chunk($urls, 5);
            //print_r($urls);

            foreach ($urls as $chunk){
                $htmls[] = $this->curl->multirequest($chunk, $date);
                sleep(rand($this->sleepMin, $this->sleepMax));
            }
            $cardIndex = 0;
            for($i = 0; $i < count($htmls); $i++){
                for ($j = 0; $j < count($htmls[$i]); $j++){
                    $cardsContent[] = $this->parseCard($htmls[$i][$j], $dataAll[$cardIndex]['url'], $dataAll[$cardIndex]['hotel_name']);
                    $cardIndex++;
                }
            }
            //$cardsContent = $this->parseCard($htmls[0][2]);
            //print_r($cardsContent);
            for($i = 0; $i < count($dataAll); $i++) {
                if($cardsContent[$i]['rooms'] == []){
                    echo 'Ошибка';
                    $html = '';
                    while ($html == ''){
                        $html = $this->curl->loadCard($dataAll[$i]['url']);
                    }
                    $rooms = $this->parseCard($html, $dataAll[$i]['url'], $dataAll[$i]['hotel_name']);
                    $dataAll[$i]['rooms'] = $rooms['rooms'];
                } else
                    $dataAll[$i]['rooms'] = $cardsContent[$i]['rooms'];
            }
            return $dataAll;
        } catch (Exception $e) {
            // код который может обработать исключение
            print_r($error);
            echo $e->getMessage();
            $ei = $ei + 1;
            return @$this->parseAll($url, $date, $ei);
        }
    }

    function parsePage($url){
        $data = null;
        $content = $this->curl->load($url, $cash=3600);
        if($content == ''){
            while($content == ''){
                echo 'Осечка';
                $content = $this->curl->load($url, $cash=3600);
            }
        }

        $pageContent = new Document($content);
        $items = $pageContent->find('div#hotellist_inner div.sr_item');
        foreach($items as $item) {
            $row = [];
            $hotelName = $item->find('a.js-sr-hotel-link > span')[0]->text();
            preg_match('~\n(.*)\n~', $hotelName, $a);
            $hotelName = $a[1];
            $row['hotel_name'] = $hotelName;

            $price = $item->find('div.bui-price-display__value')[0]->text();
            preg_match('~\d+~', $price, $a);
            $row['price'] = $a[0];

            $url = $item->find('a.js-sr-hotel-link')[0]->attr('href');
            preg_match('~\n(.*)\n~', $url, $a);
            $row['url'] = 'www.booking.com' . $a[1];

            $data []= $row;
            //
            /*$i++;
            if($i > 2){
                return $data;
            }*/
            //return $data;
        }
        return $data;
    }

    public function parseCard($content, $url, $hotelName, $ei = null){
        try {
            error_clear_last();
            $cardContent = new Document($content);

            $nameStr = @$cardContent->find('h2.hp__hotel-name')[0]->text();
            preg_match('~\w.*~', $nameStr, $a);
            $cardName = $a[0];

            if($cardName != $hotelName){
                echo $cardName . "<br>";
                echo $url . "<br>";
                echo $hotelName . "<br>";
                throw new Exception("Не та карточка");
            }

            $cardContent = @$cardContent->find('tr.e2e-hprt-table-row');
            $i = 0;
            foreach($cardContent as $roomContent){
                $room_type = '';
                if(@$roomContent->has('span.hprt-roomtype-icon-link')) {
                    $room_type = @$roomContent->find('span.hprt-roomtype-icon-link')[0]->text();
                    preg_match('~\n(.*)\n~', $room_type, $a);
                    $room_type = $a[1];
                } else{
                    for ($k = 1; $room_type == ''; $k++){
                        if($i - $k >= 0){
                            $room_type = $row['rooms'][$i - $k]['room_type'];
                        } else{
                            break(1);
                        }
                    }
                }
                $row['rooms'][$i]['room_type'] = $room_type;

                $capacity = @$roomContent->find('span.bui-u-sr-only')[0];
                if($capacity == null){
                    throw new Exception("Ошибка вместимости");
                }
                $capacity = $capacity->text();
                preg_match('~\n(.*)\n~', $capacity, $a);
                $row['rooms'][$i]['capacity'] = $a[1];

                $price = @$roomContent->find('div.bui-price-display__value')[0];
                if($price == null){
                    throw new Exception("Ошибка цены");
                }
                $price = $price->text();
                preg_match('~\d+\s*\d+~', $price, $a);
                $row['rooms'][$i]['price'] = $a[0];

                $conditions = @$roomContent->find('ul.hprt-conditions > li');
                if($conditions == null){
                    throw new Exception("Ошибка условий");
                }
                foreach($conditions as $condition){
                    $conditionText = @$condition->text();

                    /*if(strpos($conditionText, 'завтрак') || strpos($conditionText, 'Завтрак')){
                        preg_match('~\n+(.*)\n~', $conditionText, $a);
                        $row['rooms'][$i]['breakfast'] = $a[1];
                    }*/

                    if(strpos($conditionText, 'бронирования') || strpos($conditionText, 'Оплата не возвращается') || strpos($conditionText, 'Стоимость возвращается частично')){
                        preg_match('~\n(.*)\n~', $conditionText, $a);
                        $row['rooms'][$i]['payment'] = $a[1];
                    }
                }

                $nums = @$roomContent->find('select.hprt-nos-select > option');
                if($nums == null){
                    throw new Exception("Ошибка числа комнат");
                }
                foreach($nums as $num){
                    $num = @$num->text();
                    preg_match('~\n(.*)\n~', $num, $a);
                    $row['rooms'][$i]['roomNum'] = $a[1];
                }

                foreach($row['rooms'][$i] as $el => $val){
                    if($val == ''){
                        throw new Exception("Ошибка карточки");
                    }
                }

                $i++;
            }
            if($ei > 20){
                error_clear_last();
            }
            $error = error_get_last();
            if ($error) {
                throw new Exception("Ошибка карточки");
            }
            return $row;
        } catch (Exception $e) {
            $ei = $ei + 1;
            echo $e->getMessage();
            $content = $this->curl->loadCard($url);
            return @$this->parseCard($content, $url, $hotelName, $ei);
        }
    }

    public function getChartData($date, $data, $sample){
        $hotelList = ['Guesthouse - Kuin Kotonaan','Hotel Leikari','Leikari "Nature" Bungalows with Terrace',
            'Апартаменты', 'Kotkan Residenssi Apartments', 'Guest House Nina Art', 'Guesthouse Lokinlaulu', 'The Grand Karhu',
            'Kartanohotelli Karhulan Hovi', 'Hotelli Merikotka', 'Hotelli Kotola', 'Kesähostelli Kärkisaari', 'Hotel Villa Vanessa',
            'Beach Hotel Santalahti'];
        $apartmentsList = ['Homely Apartment MILA', 'Ilona Apartment - Home Away From Home', 'Apartments N & P',
            'Apartments in Finland N & P', 'Comfortable Apartment MILA at a good location', 'Apartments ”Enkeli”',
            'Scandinavian Sun Apartment', 'Scandinavian City Apartment'];
        $kraHotels = ['Kotkan Residenssi Apartments', 'Stunning 2-Bed Apartment in Kotka',
            'Superior 2-Bed Apartment in Kotka', 'Inviting 2-Bed & Sauna Royal Apartment in Kotka',
            'Captivating 4-Bed Apartment in Kotka'];

        $chartData[0] = $date;
        foreach($data as $hotel) {
            for($i = 0; $i < count($hotelList); $i++){
                if ($hotel['hotel_name'] == $hotelList[$i]) {
                    $chartData[$i + 1] = null;
                    foreach ($hotel['rooms'] as $room) {
                        if ($room['price'] < $chartData[$i + 1] || $chartData[$i + 1] == null) {
                            $chartData[$i + 1] = $room['price'];
                        }
                    }
                }
            }

            for($i = 0; $i < count($apartmentsList); $i++){
                if ($hotel['hotel_name'] == $apartmentsList[$i]){
                    $apartmentPrice = null;
                    foreach ($hotel['rooms'] as $room) {
                        if ($room['price'] < $apartmentPrice || $apartmentPrice == null) {
                            $apartmentPrice = $room['price'];
                        }
                    }
                }
                if($chartData[4] > $apartmentPrice || $chartData[4] == null){
                    $chartData[4] = $apartmentPrice;
                }
            }

            for($i = 0; $i < count($kraHotels); $i++){
                if ($hotel['hotel_name'] == $kraHotels[$i]){
                    $kraPrice = null;
                    foreach ($hotel['rooms'] as $room) {
                        if ($room['price'] < $kraPrice || $kraPrice == null) {
                            $kraPrice = $room['price'];
                        }
                    }
                }
                if($chartData[5] > $kraPrice || $chartData[5] == null){
                    $chartData[5] = $kraPrice;
                }
            }
        }

        for($i = 0; $i < 15; $i++){
            if($chartData[$i] == null){
                $chartData[$i] = '0';
            }
        }

        ksort($chartData);

        $hotelsSample = explode(', ', $sample);
        for ($i = 0; $i < count($hotelsSample); $i++) {
            for ($j = 0; $j < count($hotelList); $j++){
                $priceArr[$j] = $chartData[$j + 1];
                if ($hotelsSample[$i] == $hotelList[$j]){
                    $samplePrice[] = $chartData[$j + 1];
                }
            }
        }
        $marketPrice = round(array_sum($priceArr)/count($priceArr), 0);
        $chartData[15] =  $marketPrice;
        if($samplePrice != null){
            $chartData[16] = round(array_sum($samplePrice)/count($samplePrice), 0);
        }else{
            $chartData[16] = '0';
        }
        return $chartData;
    }

    public function getLoad($date, $data, $conArr, $sample){
        $hotelList = ['Guesthouse - Kuin Kotonaan','Hotel Leikari','Leikari "Nature" Bungalows with Terrace',
            'Апартаменты', 'Kotkan Residenssi Apartments', 'Guest House Nina Art', 'Guesthouse Lokinlaulu', 'The Grand Karhu',
            'Kartanohotelli Karhulan Hovi', 'Hotelli Merikotka', 'Hotelli Kotola',
            'Kesähostelli Kärkisaari', 'Hotel Villa Vanessa', 'Beach Hotel Santalahti'];
        $apartmentsList = ['Homely Apartment MILA', 'Ilona Apartment - Home Away From Home', 'Apartments N & P',
            'Apartments in Finland N & P', 'Comfortable Apartment MILA at a good location', 'Apartments ”Enkeli”',
            'Scandinavian Sun Apartment', 'Scandinavian City Apartment'];
        $kraHotels = ['Kotkan Residenssi Apartments', 'Stunning 2-Bed Apartment in Kotka',
            'Superior 2-Bed Apartment in Kotka', 'Inviting 2-Bed & Sauna Royal Apartment in Kotka',
            'Captivating 4-Bed Apartment in Kotka'];
        $load[0] = $date;

        $karSunCon = $conArr[9];
        array_splice($conArr, 9, 1);
        print_r($conArr);

        foreach($data as $hotel) {
            //$apartmentsSum = null;
            for ($i = 0; $i < count($hotelList); $i++) {
                if ($hotel['hotel_name'] == $hotelList[$i]) {
                    $freeRooms = null;
                    $previousRoomType = null;
                    foreach ($hotel['rooms'] as $room) {
                        if ($room['room_type'] != $previousRoomType) {
                            $previousRoomType = $room['room_type'];
                            $freeRooms = $freeRooms + $room['roomNum'];
                        }
                    }
                    if($hotel['hotel_name'] == 'Kartanohotelli Karhulan Hovi'){
                        $days = [
                            'Воскресенье', 'Понедельник', 'Вторник', 'Среда', 'Четверг', 'Пятница', 'Суббота'
                        ];

                        $day = $days[date("w", strtotime($date))];
                        echo "<br/>" . $date;
                        echo "<br/><a href='https://" . $hotel['url'] . "'>" . $hotel['hotel_name'] . "</a>";
                        if($day == 'Воскресенье'){
                            $load[$i + 1] = round((($karSunCon - $freeRooms) / $karSunCon), 2) * 100;
                            echo "<br/>Константа " . $karSunCon . 'Число комнат ' . $freeRooms;
                        } else{
                            $load[$i + 1] = round((($conArr[$i] - $freeRooms) / $conArr[$i]), 2) * 100;
                            echo "<br/>Константа " . $conArr[$i] . 'Число комнат ' . $freeRooms;

                        }
                    } else{
                        echo "<br/>" . $date;
                        echo "<br/><a href='https://" . $hotel['url'] . "'>" . $hotel['hotel_name'] . "</a>";
                        $load[$i + 1] = round((($conArr[$i] - $freeRooms) / $conArr[$i]), 2) * 100;//Загрузка
                        echo "<br/>Константа " . $conArr[$i] . 'Число комнат ' . $freeRooms;
                    }
                }
            }
            for($k = 0; $k < count($apartmentsList); $k++) {
                if ($hotel['hotel_name'] == $apartmentsList[$k]) {
                    $previousRoomType = null;
                    $apartments = null;
                    foreach ($hotel['rooms'] as $room) {
                        if ($room['room_type'] != $previousRoomType) {
                            $previousRoomType = $room['room_type'];
                            $apartments = $apartments + $room['roomNum'];
                        }
                    }
                    $apartmentsSum = $apartmentsSum + $apartments;
                    $load[4] = round((($conArr[3] - $apartmentsSum) / $conArr[3]), 2) * 100;//Загрузка
                }
            }

            for($k = 0; $k < count($kraHotels); $k++){
                if ($hotel['hotel_name'] == $kraHotels[$k]){
                    $previousRoomType = null;
                    $kraRooms = null;
                    foreach ($hotel['rooms'] as $room) {
                        if ($room['room_type'] != $previousRoomType) {
                            $previousRoomType = $room['room_type'];
                            $kraRooms = $kraRooms + $room['roomNum'];
                            //echo $kraRooms;
                        }
                    }
                    $kraSum = $kraSum + $kraRooms;
                    $load[5] = round((($conArr[4] - $kraSum) / $conArr[4]), 2) * 100;//Загрузка
                }
            }
        }

        echo "<br/>Апартаменты";
        echo "<br/>Константа " . $conArr[3] . 'Число комнат ' . $apartmentsSum;
        echo "<br/>KRA";
        echo "<br/>Константа " . $conArr[4] . 'Число комнат ' . $kraSum;

        for($i = 0; $i < 15; $i++){
            if($load[$i] == null){
                $load[$i] = '0';
            }
        }

        ksort($load);

        $hotelsSample = explode(', ', $sample);
        for ($i = 0; $i < count($hotelsSample); $i++) {
            for ($j = 0; $j < count($hotelList); $j++){
                $loadArr[$j] = $load[$j + 1];
                if ($hotelsSample[$i] == $hotelList[$j]){
                    $sampleLoad[] = $load[$j + 1];
                }
            }
        }
        $marketLoad = round(array_sum($loadArr)/count($loadArr), 0);
        $load[15] = $marketLoad;
        if($sampleLoad != null){
            $load[16] = round(array_sum($sampleLoad)/count($sampleLoad), 0);
        }else{
            $load[16] = '0';
        }
        return $load;
    }
}