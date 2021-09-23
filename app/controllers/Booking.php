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
        $this->days_val = $_POST['days_val'];
    }

    public function index(){
        $this->view('home\index');
    }

    public function parse(){
        $conModel = $this->model('ConstantsModel');
        $constants = $conModel->getConstants();

        for ($i = 0; $i < $this->days_val; $i++) {
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

            $data = $this->parseAll($url, $date);

            if($this->group_adults == 1 && $this->checkoutIndex == 1){
                $excelData = $this->getExcelData($data, $date, $constants, $this->sample);
                //print_r($excelData);
                //echo "<br/>";
                $this->excel->getToExcel($excelData);
            }

            $chartData[] = $this->getChartData($date, $data, $this->sample);

            $chartData = $this->getLoad($date, $data, $constants, $this->sample);
            $limitedLoad = $chartData[0];
            $load = $chartData[1];

            $dataAll[] = $data;
        }
        //$this->view('home/index', $data);
        //print_r($chartData);
        //print_r($load);
        $chartName = $this->group_adults . 'adults-' . $this->checkoutIndex . 'days-chart';
        $this->excel->getToChart($chartData, $limitedLoad, $load, $chartName);
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
            //print_r($error);
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
            if($ei > 20){
                echo 'Ошибка селекторов в ' . $hotelName;
                return $row;
            }
            $cardContent = new Document($content);

            $nameStr = @$cardContent->find('h2.hp__hotel-name')[0];
            if($nameStr == null){
                throw new Exception("Ошибка курла");
            }
            $nameStr = $nameStr->text();
            preg_match('~\w.*~', $nameStr, $a);
            $cardName = $a[0];

            if($cardName != $hotelName){
                echo $cardName . "<br>";
                echo $url . "<br>";
                echo $hotelName . "<br>";
                throw new Exception("Ошибка. Не та карточка");
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
                preg_match('~(\d+)~', $capacity, $a);
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
                        if ($room['price'] < $chartData[$i + 1] && $room['capacity'] >= $this->group_adults || $chartData[$i + 1] == null && $room['capacity'] >= $this->group_adults) {
                            $chartData[$i + 1] = $room['price'];
                        }
                    }
                }
            }

            for($i = 0; $i < count($apartmentsList); $i++){
                if($hotel['hotel_name'] == $apartmentsList[$i]){
                    $apartmentPrice = null;
                    foreach ($hotel['rooms'] as $room) {
                        if ($room['price'] < $apartmentPrice && $room['capacity'] >= $this->group_adults || $apartmentPrice == null && $room['capacity'] >= $this->group_adults) {
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
                        if ($room['price'] < $kraPrice && $room['capacity'] >= $this->group_adults || $kraPrice == null && $room['capacity'] >= $this->group_adults) {
                            $kraPrice = $room['price'];
                        }
                    }
                }
                if($chartData[5] > $kraPrice || $chartData[5] == null){
                    $chartData[5] = $kraPrice;
                }
            }
        }

        $marketPrice = round(array_sum($chartData)/(count($chartData) - 1), 0);
        $chartData[15] =  $marketPrice;

        for($i = 0; $i < 15; $i++){
            if($chartData[$i] == null){
                $chartData[$i] = '0';
            }
        }

        ksort($chartData);

        $hotelsSample = explode(', ', $sample);
        for ($i = 0; $i < count($hotelsSample); $i++) {
            for ($j = 0; $j < count($hotelList); $j++){
                //$priceArr[$j] = $chartData[$j + 1];
                if ($hotelsSample[$i] == $hotelList[$j]){
                    $samplePrice[] = $chartData[$j + 1];
                }
            }
        }
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

        $hotelsSample = explode(', ', $sample);
        $sampleRoomsSum = 0;
        $sampleRoomsConstants = 0;

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

                    for ($j = 0; $j < count($hotelsSample); $j++) {
                        if ($hotelsSample[$j] == $hotel['hotel_name']){
                            $sampleRoomsSum = $sampleRoomsSum + $freeRooms;
                            $sampleRoomsConstants = $sampleRoomsConstants + $conArr[$i];
                        }
                    }

                    if($hotel['hotel_name'] != 'Kotkan Residenssi Apartments'){
                        $marketRoomSum = $marketRoomSum + $freeRooms;
                    }

                    if($hotel['hotel_name'] != 'Hotel Leikari' && $hotel['hotel_name'] != 'Guesthouse - Kuin Kotonaan' && $hotel['hotel_name'] != 'Kotkan Residenssi Apartments'){
                        $marketRoomSumLimited = $marketRoomSumLimited + $freeRooms;
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
                        $load[$i + 1] = round((($conArr[$i] - $freeRooms) / $conArr[$i]), 2) * 100;//Загрузка

                        echo "<br/>" . $date;
                        echo "<br/><a href='https://" . $hotel['url'] . "'>" . $hotel['hotel_name'] . "</a>";
                        echo "<br/>Константа " . $conArr[$i] . 'Число комнат ' . $freeRooms;
                    }
                }
            }
            for($i = 0; $i < count($apartmentsList); $i++) {
                if ($hotel['hotel_name'] == $apartmentsList[$i]) {
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

            for($i = 0; $i < count($kraHotels); $i++){
                if ($hotel['hotel_name'] == $kraHotels[$i]){
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
        $marketRoomSum = $marketRoomSum + $apartmentsSum + $kraSum;
        $marketRoomSumLimited = $marketRoomSumLimited + $apartmentsSum + $kraSum;

        for ($i = 0; $i < count($hotelsSample); $i++) {
            if ($hotelsSample[$i] == 'Апартаменты'){
                $sampleRoomsSum = $sampleRoomsSum + $apartmentsSum;
                $sampleRoomsConstants = $sampleRoomsConstants + $conArr[3];
            }
            if ($hotelsSample[$i] == 'Kotkan Residenssi Apartments'){
                $sampleRoomsSum = $sampleRoomsSum + $kraSum;
                $sampleRoomsConstants = $sampleRoomsConstants + $conArr[4];
            }
        }

        echo "<br/>Апартаменты";
        echo "<br/>Константа " . $conArr[3] . 'Число комнат ' . $apartmentsSum;
        echo "<br/>KRA";
        echo "<br/>Константа " . $conArr[4] . 'Число комнат ' . $kraSum;

        for($i = 0; $i < 15; $i++){
            if($load[$i] == null){
                $load[$i] = '100';
            }
        }

        ksort($load);

        if($hotelsSample[0] != null){
            if($sampleRoomsSum == 0){
                $load[16] = '100';
            } else {
                $load[16] = round(($sampleRoomsConstants - $sampleRoomsSum)/$sampleRoomsConstants, 2) * 100;
            }
        }else{
            $load[16] = '0';
        }

        $load[15] = round((array_sum($conArr) - $marketRoomSum) / array_sum($conArr), 2) * 100;

        $loadLimited[0] = $load[0];
        $loadLimited[1] = $load[1];
        $loadLimited[2] = $load[15];
        array_splice($conArr, 0, 2);
        $loadLimited[3] = round((array_sum($conArr) - $marketRoomSumLimited)/array_sum($conArr), 2) * 100;
        $loadLimited[4] = $load[16];

        $chartData[0] = $loadLimited;
        $chartData[1] = $load;

        return $chartData;
    }

    public function getExcelData($data, $date, $constants, $sample = null){
        $hotelList = ['Hotel Leikari','Leikari "Nature" Bungalows with Terrace',
            'Апартаменты', 'Kotkan Residenssi Apartments', 'Guest House Nina Art', 'Guesthouse Lokinlaulu', 'The Grand Karhu',
            'Kartanohotelli Karhulan Hovi', 'Hotelli Merikotka', 'Hotelli Kotola',
            'Kesähostelli Kärkisaari', 'Hotel Villa Vanessa', 'Beach Hotel Santalahti'];
        $apartmentsList = ['Homely Apartment MILA', 'Ilona Apartment - Home Away From Home', 'Apartments N & P',
            'Apartments in Finland N & P', 'Comfortable Apartment MILA at a good location', 'Apartments ”Enkeli”',
            'Scandinavian Sun Apartment', 'Scandinavian City Apartment'];
        $kraHotels = ['Kotkan Residenssi Apartments', 'Stunning 2-Bed Apartment in Kotka',
            'Superior 2-Bed Apartment in Kotka', 'Inviting 2-Bed & Sauna Royal Apartment in Kotka',
            'Captivating 4-Bed Apartment in Kotka'];

        $roomNumColumnIndex = [8, 13, 18, 23, 28, 33, 38, 43, 48, 53, 58, 63, 68];// Координаты комнат в наличии
        $loadIndex = [9, 14, 19, 24, 29, 34, 39, 44, 49, 54, 59, 64, 69];// Координаты Загрузка
        $changesColumnNums = [10, 15, 20, 25, 30, 35, 40, 45, 50, 55, 60, 65, 70];// Координаты Изменения
        $maColumnsNums = [11, 16, 21, 26, 31, 36, 41, 46, 51, 56, 61, 66, 71];// Координаты МА 30
        $changesMaColumnIndex = [12, 17, 22, 27, 32, 37, 42, 47, 52, 57, 62, 67, 72];// Координаты Разность МА 30

        $newData = [];

        $newData[1] = $date;
        $days = [
            'Воскресенье', 'Понедельник', 'Вторник', 'Среда', 'Четверг', 'Пятница', 'Суббота'
        ];

        $newData[2] = $days[date("w", strtotime($date))];
        $newData[4] = 'Константа';
        $newData[5] = 'Константа';

        $gKKcon = $constants[0];
        array_splice($constants, 0, 1);

        if($newData[2] == 'Воскресенье'){
            $kar = $constants[9];
        } else{
            $kar = $constants[8];
        }
        array_splice($constants, 8, 1);


        //Kuin Kotonaan
        $newData[0] = 'Всё сдано';
        $newData[3] = 0;
        $newData[6] = '100%';
        $newData[7] = null;

        //Апартаменты
        $newData[18] = '0';
        $newData[19] = '100%';
        $newData[20] = null;
        $newData[21] = null;
        $newData[22] = null;

        //Kotkan Residenssi Apartments
        $newData[23] = '0';
        $newData[24] = '100%';//Загрузка
        $newData[25] = null;//Изменения
        $newData[26] = null;//Ма30
        $newData[27] = null;//Разность Ма30

        //Kartanohotelli Karhulan Hovi
        $newData[43] = '0';
        $newData[44] = '100%';
        $newData[45] = null;
        $newData[46] = null;
        $newData[47] = null;

        //print_r($constants);
        //echo "<br/>";

        $hotelsSample = explode(', ', $sample);
        $sampleRoomsSum = 0;
        $sampleRoomsConstants = 0;

        foreach($data as $hotel) {
            for ($i = 0; $i < count($hotelList); $i++) {
                if ($hotel['hotel_name'] == $hotelList[$i]){
                    $newData[$roomNumColumnIndex[$i]] = null;
                    $previousRoomType = null;
                    foreach ($hotel['rooms'] as $room) {
                        if ($room['room_type'] != $previousRoomType) {
                            $previousRoomType = $room['room_type'];
                            $newData[$roomNumColumnIndex[$i]] = $newData[$roomNumColumnIndex[$i]] + $room['roomNum'];//Комнаты в наличии
                        }
                    }

                    for ($j = 0; $j < count($hotelsSample); $j++) {
                        if ($hotelsSample[$j] == $hotel['hotel_name']){
                            $sampleRoomsSum = $sampleRoomsSum + $newData[$roomNumColumnIndex[$i]];
                            $sampleRoomsConstants = $sampleRoomsConstants + $constants[$i];
                        }
                    }

                    $newData[$loadIndex[$i]] = round((($constants[$i] - $newData[$roomNumColumnIndex[$i]]) / $constants[$i]), 2) * 100 . '%';//Загрузка
                    $newData[$changesColumnNums[$i]] = null;//Изменения
                    $newData[$maColumnsNums[$i]] = null;//Ма30
                    $newData[$changesMaColumnIndex[$i]] = null;//Разность Ма30
                }
            }

            if ($hotel['hotel_name'] == 'Guesthouse - Kuin Kotonaan') {
                $newData[0] = null;
                $availableRoomsNum = null;
                $previousRoomType = null;
                foreach ($hotel['rooms'] as $room) {
                    if ($room['price'] < $newData[0] || $newData[0] == null) {
                        $newData[0] = $room['price'];
                    }
                    if ($room['room_type'] != $previousRoomType) {
                        $previousRoomType = $room['room_type'];
                        $availableRoomsNum = $availableRoomsNum + $room['roomNum'];
                    }
                }

                for ($j = 0; $j < count($hotelsSample); $j++) {
                    if ($hotelsSample[$j] == $hotel['hotel_name']){
                        $sampleRoomsSum = $sampleRoomsSum + $availableRoomsNum;
                        $sampleRoomsConstants = $sampleRoomsConstants + $gKKcon;
                    }
                }

                $newData[3] = $availableRoomsNum;
                $newData[6] = round((($gKKcon - $newData[3]) / $gKKcon), 2) * 100 . '%';
                $newData[7] = null;
            }

            if ($hotel['hotel_name'] == 'Kartanohotelli Karhulan Hovi') {
                $availableRoomsNum = null;
                $previousRoomType = null;
                foreach ($hotel['rooms'] as $room) {
                    if ($room['room_type'] != $previousRoomType) {
                        $previousRoomType = $room['room_type'];
                        $availableRoomsNum = $availableRoomsNum + $room['roomNum'];
                    }
                }

                for ($j = 0; $j < count($hotelsSample); $j++) {
                    if ($hotelsSample[$j] == $hotel['hotel_name']){
                        $sampleRoomsSum = $sampleRoomsSum + $availableRoomsNum;
                        $sampleRoomsConstants = $sampleRoomsConstants + $kar;
                    }
                }

                $newData[43] = $availableRoomsNum;
                $newData[44] = round((($kar - $newData[43]) / $kar), 2) * 100 . '%';
                $newData[45] = null;
                $newData[46] = null;
                $newData[47] = null;

                //echo 'Kartanohotelli Karhulan Hovi ' . $newData[43] . "<br>";
            }

            for($i = 0; $i < count($apartmentsList); $i++) {
                if ($hotel['hotel_name'] == $apartmentsList[$i]) {
                    $previousRoomType = null;
                    $apartments = null;
                    foreach ($hotel['rooms'] as $room) {
                        if ($room['room_type'] != $previousRoomType) {
                            $previousRoomType = $room['room_type'];
                            $apartments = $apartments + $room['roomNum'];
                        }
                    }

                    $apartmentsSum = $apartmentsSum + $apartments;
                    $newData[18] = $apartmentsSum;
                    $newData[19] = round((($constants[2] - $apartmentsSum) / $constants[2]), 2) * 100;//Загрузка
                    $newData[20] = null;
                    $newData[21] = null;
                    $newData[22] = null;
                }
            }

            for($i = 0; $i < count($kraHotels); $i++){
                if ($hotel['hotel_name'] == $kraHotels[$i]){
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
                    $newData[23] = $kraSum;
                    $newData[24] = round((($constants[3] - $kraSum) / $constants[3]), 2) * 100;//Загрузка
                    $newData[25] = null;//Изменения
                    $newData[26] = null;//Ма30
                    $newData[27] = null;//Разность Ма30
                }
            }

            $checkedHotels[] = $hotel['hotel_name'];
        }

        for ($i = 0; $i < count($hotelsSample); $i++) {
            if ($hotelsSample[$i] == 'Апартаменты'){
                $sampleRoomsSum = $sampleRoomsSum + $apartmentsSum;
                $sampleRoomsConstants = $sampleRoomsConstants + $constants[2];
            }
            if ($hotelsSample[$i] == 'Kotkan Residenssi Apartments'){
                $sampleRoomsSum = $sampleRoomsSum + $kraSum;
                $sampleRoomsConstants = $sampleRoomsConstants + $constants[4];
            }
        }

        //Рынок
        array_splice($constants, 8, 1);
        $conSum = array_sum($constants) + $gKKcon + $kar;

        $availableRoomsSum = $newData[3];
        for($i = 0; $i < count($roomNumColumnIndex); $i++){
            $availableRoomsSum = $availableRoomsSum + $newData[$roomNumColumnIndex[$i]];
        }

        $newData[73] = round(($conSum - $availableRoomsSum)/$conSum, 2) * 100 . '%';

        if($hotelsSample[0] != null){
            if($sampleRoomsSum == 0){
                $newData[76] = '100';
            } else {
                $newData[76] = round(($sampleRoomsConstants - $sampleRoomsSum)/$sampleRoomsConstants, 2) * 100;
            }
        }else{
            $newData[76] = '0';
        }

        $this->getMissingHotels($checkedHotels, $newData);
        return $newData;
    }

    private function getMissingHotels($hotelList, &$newData){
        $setHotelsList = ['Hotel Leikari', 'Leikari "Nature" Bungalows with Terrace', /*'Апартаменты',*/
            'Kotkan Residenssi Apartments', 'Guest House Nina Art', 'Guesthouse Lokinlaulu', 'The Grand Karhu',
            'Kartanohotelli Karhulan Hovi', 'Hotelli Merikotka', 'Hotelli Kotola', 'Kesähostelli Kärkisaari',
            'Hotel Villa Vanessa', 'Beach Hotel Santalahti'];

        $roomNumColumnIndex = [8, 13, /*18,*/ 23, 28, 33, 38, 43, 48, 53, 58, 63, 68];// Координаты комнат в наличии
        $loadIndex = [9, 14, /*19,*/ 24, 29, 34, 39, 44, 49, 54, 59, 64, 69];// Координаты Загрузка
        $changesColumnNums = [10, 15, /*20,*/ 25, 30, 35, 40, 45, 50, 55, 60, 65, 70];// Координаты Изменения
        $maColumnsNums = [11, 16, /*21,*/ 26, 31, 36, 41, 46, 51, 56, 61, 66, 71];// Координаты МА 30
        $changesMaColumnIndex = [12, 17, /*22,*/ 27, 32, 37, 42, 47, 52, 57, 62, 67, 72];// Координаты Разность МА 30

        for($i = 0; $i < count($setHotelsList); $i++) {
            if (in_array($setHotelsList[$i], $hotelList) == false) {
                $newData[$roomNumColumnIndex[$i]] = 0;
                $newData[$loadIndex[$i]] = 100 .'%';//Загрузка
                $newData[$changesColumnNums[$i]] = null;//Изменения
                $newData[$maColumnsNums[$i]] = null;//Ма30
                $newData[$changesMaColumnIndex[$i]] = null;//Разность Ма30
            }
        }
    }
}