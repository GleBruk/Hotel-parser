<?php
require_once 'app/controllers/Curl.php';
require_once 'app/controllers/Excel.php';
require_once 'vendor/autoload.php';
use DiDom\Document;

class Booking extends Controller{
    function __construct() {
        $this->curl = new Curl;
        $this->excel = new Excel;
        //Особая выборка отелей, указанная пользователем в форме, для дальнейшей работе с ней в Excel
        $this->sample = $_POST['sample'];
        //Число дней, которое планируется провести в отеле
        $this->checkoutIndex = $_POST['checkout_index'];
        //Число взрослых
        $this->group_adults = $_POST['group_adults'];
        //Период (число дней), на который просматриваются данные
        $this->days_val = $_POST['days_val'];
    }

    public function index(){
        $this->view('home/index');
    }

    public function parse(){
        //Получаем константы
        $conModel = $this->model('ConstantsModel');
        $constants = $conModel->getConstants();


        //Определяем текущий день недели
        $day = date( "l" );
        //По понедельникам и четвергам очищаем логи и кэш
        if($day == 'Monday' || $day == 'Thursday'){
            unlink('./././log.txt');

            $includes = glob('./././cash/*');
            foreach ($includes as $include){
                unlink($include);
            }
            rmdir('./././cash');
        }

        /*
         * Перебираем контент на каждую дату, начиная с сегодняшнего дня.
         * Число итераций зависит от значения прописанного в форме, в поле Период
        */
        for ($i = 0; $i < $this->days_val; $i++) {
            //Определяем год, месяц и день заезда
            $t = strtotime('+' . $i . ' day 00:00:00');
            $сheckin_year = date('y', $t);
            $сheckin_month = date('m', $t);
            $сheckin_monthday = date('d', $t);

            //Определяем год, месяц и день выезда
            $t = strtotime('+' . ($i + $this->checkoutIndex) . ' day 00:00:00');
            $checkout_year = date('y', $t);
            $checkout_month = date('m', $t);
            $checkout_monthday = date('d', $t);

            //Формируем url для запроса
            $url = 'https://www.booking.com/searchresults.ru.html?label=gen173nr-1FCAEoggI46AdIM1gEaEiIAQGYASG4ARfIAQzYAQHoAQH4AQuIAgGoAgO4AsPX34QGwAIB0gIkMjAyZjAwYzItNDVhYS00YTY0LWJiNjYtZjJkM2U3YjE4ZWZk2AIG4AIB&sid=f3857b8c77f93c413cb4587518473e0c&sb=1&src=searchresults&src_elem=sb&error_url=https%3A%2F%2Fwww.booking.com%2Fsearchresults.ru.html%3Flabel%3Dgen173nr-1FCAEoggI46AdIM1gEaEiIAQGYASG4ARfIAQzYAQHoAQH4AQuIAgGoAgO4AsPX34QGwAIB0gIkMjAyZjAwYzItNDVhYS00YTY0LWJiNjYtZjJkM2U3YjE4ZWZk2AIG4AIB%3Bsid%3Df3857b8c77f93c413cb4587518473e0c%3Btmpl%3Dsearchresults%3Bac_click_type%3Db%3Bac_position%3D0%3Bclass_interval%3D1%3Bdest_id%3D-1372348%3Bdest_type%3Dcity%3Bdtdisc%3D0%3Bfrom_sf%3D1%3Bgroup_adults%3D2%3Bgroup_children%3D0%3Binac%3D0%3Bindex_postcard%3D0%3Blabel_click%3Dundef%3Bno_rooms%3D1%3Boffset%3D0%3Bpostcard%3D0%3Braw_dest_type%3Dcity%3Broom1%3DA%252CA%3Bsb_price_type%3Dtotal%3Bsearch_selected%3D1%3Bshw_aparth%3D1%3Bslp_r_match%3D0%3Bsrc%3Dindex%3Bsrc_elem%3Dsb%3Bsrpvid%3Dedf75d0156180246%3Bss%3D%25D0%259A%25D0%25BE%25D1%2582%25D0%25BA%25D0%25B0%252C%2520%25D0%25AE%25D0%25B6%25D0%25BD%25D0%25B0%25D1%258F%2520%25D0%25A4%25D0%25B8%25D0%25BD%25D0%25BB%25D1%258F%25D0%25BD%25D0%25B4%25D0%25B8%25D1%258F%252C%2520%25D0%25A4%25D0%25B8%25D0%25BD%25D0%25BB%25D1%258F%25D0%25BD%25D0%25B4%25D0%25B8%25D1%258F%3Bss_all%3D0%3Bss_raw%3D%25D0%259A%25D0%25BE%25D1%2582%25D0%25BA%25D0%25B0%3Bssb%3Dempty%3Bsshis%3D0%3Btop_ufis%3D1%26%3B&ss=%D0%9A%D0%BE%D1%82%D0%BA%D0%B0&is_ski_area=0&ssne=%D0%9A%D0%BE%D1%82%D0%BA%D0%B0&ssne_untouched=%D0%9A%D0%BE%D1%82%D0%BA%D0%B0&city=-1372348&checkin_year=20' . $сheckin_year . '&checkin_month=' . $сheckin_month . '&checkin_monthday=' . $сheckin_monthday . '&checkout_year=20' . $checkout_year . '&checkout_month=' . $checkout_month . '&checkout_monthday=' . $checkout_monthday . '&group_adults=' . $this->group_adults . '&group_children=0&no_rooms=1&sb_changed_dates=1&from_sf=1';

            //Определяем дату
            $date = $сheckin_monthday . '.' . $сheckin_month . '.20' . $сheckin_year;

            //Начинаем парсить страницу по заданному url и устанавливаем дату
            $data = $this->parseAll($url, $date);

            //Если в форме указан 1 взрослый и 1 день, то заполняем Excel таблицу собранными данными
            if($this->group_adults == 1 && $this->checkoutIndex == 1){
                //Подготавливаем данные для Excel таблицы
                $excelData = $this->getExcelData($data, $date, $constants, $this->sample);

                //Делаем новую таблицу
                $this->excel->getToExcel($excelData);
            }

            //Формируем из собранных данных данные для графика цен
            $prices[] = $this->getPrices($date, $data, $this->sample);

            //Аналогично для графика загрузки
            $loadData = $this->getLoad($date, $data, $constants, $this->sample);
            $limitedLoad[] = $loadData[0];
            $load[] = $loadData[1];

            //Кладём собранные данные за эту дату в общий массив
            $dataAll[] = $data;
        }
        //Формируем название файла с графиками
        $fileName = $this->group_adults . 'adults-' . $this->checkoutIndex . 'days-chart';
        //Рисуем графики
        $this->excel->getToChart($prices, $limitedLoad, $load, $fileName);
        //Переходим в шаблон и передаём собранные данные, чтобы вывести их на экран
        $this->view('home/index', $dataAll);
    }

    public function parseAll($url, $date, $ei = null){
        /*
            * Т.к. парсится большой объём данных с применением Multicurl, то неизбежны различные погрешности.
            * Как правило не загружается страница из-за плохого интернета или по иной причине, или
            * загрузилась не та страница. Для этого здесь используются исключения и в случае ошибки
            * запрос на страницу отправляется по новой. Если счетчик исключений ($ei) превышает
            * определённое кол-во, то функция как правило возвращает пустое значение и парсинг продолжается
            * дальше. Если это случилось, то это могут быть проблемы с интернетом, DOM, curl, JSON.
        */
        try {
            error_clear_last();
            //Начинаем парсить страницу с отелями
            $dataAll = @$this->parsePage($url);

            //Проверяем наличие ошибок
            $error = error_get_last();
            if($ei > 10){
                error_clear_last();
                $error = error_get_last();
            }
            if ($error || $dataAll == null && $ei <= 10) {
                throw new Exception("Ошибка страницы");
            } elseif ($dataAll == null && $ei > 10){
                return $dataAll;
            }

            //print_r($dataAll);

            //Формируем массив из собранных url карточек отелей
            $urls = [];
            for($i = 0; $i < count($dataAll); $i++){
                $urls[$i] = $dataAll[$i]['url'];
            }
            //Делаем массив двумерным, по пять url в каждом массиве
            $urls = array_chunk($urls, 5);

            //Запускаем по пять параллельных потоков с паузой в несколько секунд
            foreach ($urls as $chunk){
                $htmls[] = $this->curl->multirequest($chunk, $date);
                sleep(rand($this->sleepMin, $this->sleepMax));
            }
            //print_r($htmls);

            //Перебираем загруженные карточки и парсим их
            $cardIndex = 0;
            for($i = 0; $i < count($htmls); $i++){
                for ($j = 0; $j < count($htmls[$i]); $j++){
                    $cardsContent[] = $this->parseCard($htmls[$i][$j], $dataAll[$cardIndex]['url'], $dataAll[$cardIndex]['hotel_name']);
                    $cardIndex++;
                }
            }

            //Перебираем собранные данные. Проверяем, чтобы были данные со всех карточек
            for($i = 0; $i < count($dataAll); $i++) {
                if($cardsContent[$i]['rooms'] == []){
                    echo 'Ошибка';
                    $html = '';
                    $j = 0;
                    while ($html == ''){
                        $html = $this->curl->loadCard($dataAll[$i]['url']);
                        if ($j == 50){
                            echo "<h1>ОШИБКА. КАРТОЧКА НЕ ЗАГРУЖАЕТСЯ. ПРОВЕРЬТЕ ПОДКЛЮЧЕНИЕ К ИНТЕРНЕТУ ИЛИ ПОЗОВИТЕ РАЗРАБОТЧИКА</h1>";
                            die();
                        }
                        $j++;
                    }
                    $rooms = $this->parseCard($html, $dataAll[$i]['url'], $dataAll[$i]['hotel_name']);
                    $dataAll[$i]['rooms'] = $rooms['rooms'];
                } else
                    $dataAll[$i]['rooms'] = $cardsContent[$i]['rooms'];
            }

            return $dataAll;
        } catch (Exception $e) {
            //print_r($error);
            echo $e->getMessage();
            $ei = $ei + 1;
            return @$this->parseAll($url, $date, $ei);
        }
    }

    function parsePage($url){
        $data = null;
        //Загружаем страницу через curl
        $content = $this->curl->load($url, $cash=3600);
        //Убеждаемся, что страница загрузилась
        if($content == ''){
            $i = 0;
            while($content == ''){
                echo 'Осечка';
                $content = $this->curl->load($url, $cash=3600);
                $i++;
                if($i == 50){
                    echo "<h1>ОШИБКА. СТРАНИЦА НЕ ЗАГРУЖАЕТСЯ. ПРОВЕРЬТЕ ПОДКЛЮЧЕНИЕ К ИНТЕРНЕТУ ИЛИ ПОЗОВИТЕ РАЗРАБОТЧИКА</h1>";
                    die();
                }
            }
        }

        //Извлекаем нужные данные из json
        preg_match('~type="application/json">({"BasicPropertyData:.*}}}).*window\.Promise~', $content, $a);
        $json = json_decode($a[1]);
        //print_r($json);
        $hotelQuery = serialize($json->{'ROOT_QUERY'});
        preg_match('~search\({"input":{.*"useSearchParamsFromSession":true}}\)~',$hotelQuery, $a);
        $hotelArr = $json->{'ROOT_QUERY'}->{'searchQueries'}->{$a[0]}->{'results'};

        $i = 0;
        $pageContent = new Document($content);
        $items = $pageContent->find('div > div[data-testid="property-card"]');
        foreach ($hotelArr as $hotel ){
            $row['hotel_name'] = $hotel->{'displayName'}->{'text'};
            $row['price'] = $hotel->{'blocks'}[0]->{'finalPrice'}->{'amount'};

            //Т.к. в json нету url карточки отеля, то его мы извлекаем из DOM
            $url = $items[$i]->find('a[data-testid="title-link"]')[0]->attr('href');
            //preg_match('~\n(.*)\n~', $url, $a);
            $row['url'] = $url;
            $i++;
            $data[]= $row;
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

            //Получаем название загруженной нами карточки
            preg_match("~b_hotel_name:\s'(.*)',~", $content, $a);
            $cardName = $a[1];
            $cardName = str_replace( '\\', '', $cardName);

            if($cardName == null){
                throw new Exception("Ошибка курла");
            }

            //Убеждаемся, что загрузили нужную карточку
            if($cardName != $hotelName){
                throw new Exception("Ошибка. Не та карточка");
            }
            //Извлекаем нужные данные из json
            preg_match('~b_rooms_available_and_soldout:(.*),~', $content, $a);
            $json = json_decode($a[1]);
            $i = 0;
            foreach ($json as $room_data){

                foreach ($room_data->{'b_blocks'} as $block){
                    $row['rooms'][$i]['room_type'] = $room_data->{'b_name'};

                    if($block->b_max_persons == null){
                        throw new Exception("Ошибка вместимости");
                    }
                    preg_match('~(\d+)~', $block->b_max_persons, $a);
                    $row['rooms'][$i]['capacity'] = $a[1];

                    if($block->b_price == null){
                        throw new Exception("Ошибка цены");
                    }
                    preg_match('~\d+\s*\d+~', $block->b_price, $a);
                    $row['rooms'][$i]['price'] = $a[0];

                    $row['rooms'][$i]['breakfast'] = $block->b_mealplan_included_name;

                    $row['rooms'][$i]['payment'] = $block->b_cancellation_type;

                    if($block->b_nr_stays == null){
                        throw new Exception("Ошибка числа комнат");
                    }
                    $row['rooms'][$i]['roomNum'] = $block->b_nr_stays;

                    foreach($row['rooms'][$i] as $el => $val){
                        if($val == '' && $el != 'breakfast'){
                            throw new Exception("Ошибка карточки");
                        }
                    }

                    $i++;
                }
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

    public function getPrices($date, $data, $sample){
        //Основной список отелей
        $hotelList = ['Guesthouse - Kuin Kotonaan','Hotel Leikari','Leikari "Nature" Bungalows with Terrace',
            'Апартаменты', 'Kotkan Residenssi Apartments', 'Guest House Nina Art', 'Guesthouse Lokinlaulu', 'The Grand Karhu',
            'Kartanohotelli Karhulan Hovi', 'Hotelli Merikotka', 'Hotelli Kotola', 'Kesähostelli Kärkisaari', 'Hotel Villa Vanessa',
            'Beach Hotel Santalahti'];
        //Список апартаментов
        $apartmentsList = ['Homely Apartment MILA', 'Ilona Apartment - Home Away From Home', 'Apartments N & P',
            'Apartments in Finland N & P', 'Comfortable Apartment MILA at a good location', 'Apartments ”Enkeli”',
            'Scandinavian Sun Apartment', 'Scandinavian City Apartment'];
        //Список отелей группы Kotkan Residenssi Apartments
        $kraHotels = ['Kotkan Residenssi Apartments', 'Stunning 2-Bed Apartment in Kotka',
            'Superior 2-Bed Apartment in Kotka', 'Inviting 2-Bed & Sauna Royal Apartment in Kotka',
            'Captivating 4-Bed Apartment in Kotka'];

        $chartData[0] = $date;
        //Перебираем все собранные парсером отели
        foreach($data as $hotel) {
            /*
             * Если данный отель есть в основном списке отелей, то добавляем в массив цену за самый дешёвый
             * номер с подходящей вместимостью комнаты
            */
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

            /*
             * Действуем аналогичным образом для апартаментов
             * Цену для апартаментов записываем под заранее известным индексом
            */
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

            //Аналогично для Kotkan Residenssi Apartments
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

        //Вычисляем среднюю арифметическую рыночную цену
        $marketPrice = round(array_sum($chartData)/(count($chartData) - 1), 0);
        $chartData[15] =  $marketPrice;

        //Если парсер не нашёл данных какого-то отеля, то пишем ноль
        for($i = 0; $i < 15; $i++){
            if($chartData[$i] == null){
                $chartData[$i] = '0';
            }
        }

        ksort($chartData);

        //Получаем названия отелей из выборки указанной пользователем
        $hotelsSample = explode(', ', $sample);
        //Собираем цены отелей из выборки
        for ($i = 0; $i < count($hotelsSample); $i++) {
            for ($j = 0; $j < count($hotelList); $j++){
                if ($hotelsSample[$i] == $hotelList[$j]){
                    $samplePrice[] = $chartData[$j + 1];
                }
            }
        }
        //Вычисляем среднюю арифметическую цену выборку
        if($samplePrice != null){
            $chartData[16] = round(array_sum($samplePrice)/count($samplePrice), 0);
        }else{
            $chartData[16] = '0';
        }
        return $chartData;
    }

    public function getLoad($date, $data, $conArr, $sample){
        //Основной список отелей. На основе порядка элементов в данном массиве будет сформирован массив с данными
        //для графика с загрузкой
        $hotelList = ['Guesthouse - Kuin Kotonaan','Hotel Leikari','Leikari "Nature" Bungalows with Terrace',
            'Апартаменты', 'Kotkan Residenssi Apartments', 'Guest House Nina Art', 'Guesthouse Lokinlaulu', 'The Grand Karhu',
            'Kartanohotelli Karhulan Hovi', 'Hotelli Merikotka', 'Hotelli Kotola',
            'Kesähostelli Kärkisaari', 'Hotel Villa Vanessa', 'Beach Hotel Santalahti'];
        //Список апартаментов
        $apartmentsList = ['Homely Apartment MILA', 'Ilona Apartment - Home Away From Home', 'Apartments N & P',
            'Apartments in Finland N & P', 'Comfortable Apartment MILA at a good location', 'Apartments ”Enkeli”',
            'Scandinavian Sun Apartment', 'Scandinavian City Apartment'];
        //Список отелей группы Kotkan Residenssi Apartments
        $kraHotels = ['Kotkan Residenssi Apartments', 'Stunning 2-Bed Apartment in Kotka',
            'Superior 2-Bed Apartment in Kotka', 'Inviting 2-Bed & Sauna Royal Apartment in Kotka',
            'Captivating 4-Bed Apartment in Kotka'];
        $load[0] = $date;

        /*
         * Т.к. у Kartanohotelli Karhulan Hovi в воскресенье константа отличается от константы в другие дни недели,
         * то для него сделано две константы. Это создаёт проблему при заполнении загрузки. Поэтому мы присваиваем
         * значение константы в воскресенье переменной и удаляем константу из массива. Затем выводим массив констант
         * на экран для отладки
        */
        $karSunCon = $conArr[9];
        array_splice($conArr, 9, 1);
        print_r($conArr);

        $hotelsSample = explode(', ', $sample);
        $sampleRoomsSum = 0;
        $sampleRoomsConstants = 0;

        //Перебираем все собранные парсером отели
        foreach($data as $hotel) {
            for ($i = 0; $i < count($hotelList); $i++) {
                /*
                 * Если данный отель есть в основном списке отелей, то определяем число свободных комнат
                 * Отдельно определяем число комнат у выборки отелей, рынка и ограниченного рынка
                 * Также выводим на экран ссылку на отель с указанными константой и числом свободных комнат
                 * для отладки
                */
                if ($hotel['hotel_name'] == $hotelList[$i]) {
                    //Перебираем все собранные комнаты и берём их кол-во. Т.к. они повторяются,
                    //то убеждаемся, что смотрим разные комнаты
                    $freeRooms = null;
                    $previousRoomType = null;
                    foreach ($hotel['rooms'] as $room) {
                        if ($room['room_type'] != $previousRoomType) {
                            $previousRoomType = $room['room_type'];
                            $freeRooms = $freeRooms + $room['roomNum'];
                        }
                    }

                    //Если отель из выборки, то добавляем число его комнат к числу комнат выборки
                    //Аналогично с константами
                    for ($j = 0; $j < count($hotelsSample); $j++) {
                        if ($hotelsSample[$j] == $hotel['hotel_name']){
                            $sampleRoomsSum = $sampleRoomsSum + $freeRooms;
                            $sampleRoomsConstants = $sampleRoomsConstants + $conArr[$i];
                        }
                    }

                    //Не учитываем Kotkan Residenssi Apartments в рыночную загрузку, т.к. он будет считаться отдельно
                    if($hotel['hotel_name'] != 'Kotkan Residenssi Apartments'){
                        $marketRoomSum = $marketRoomSum + $freeRooms;
                    }

                    //Считаем число комнат у ограниченного рынка
                    if($hotel['hotel_name'] != 'Hotel Leikari' && $hotel['hotel_name'] != 'Guesthouse - Kuin Kotonaan' && $hotel['hotel_name'] != 'Kotkan Residenssi Apartments'){
                        $marketRoomSumLimited = $marketRoomSumLimited + $freeRooms;
                    }

                    /*
                     * Считаем загрузку. Если это Kartanohotelli, то смотрим день недели.
                     * Также выводим информацию для отладки. Чтобы посчитать загрузку, мы вычитаем из константы число
                     * свободных комнат и делим разницу на константу, затем частное умножаем на 100 и округляем.
                    */
                    if($hotel['hotel_name'] == 'Kartanohotelli Karhulan Hovi'){
                        $days = [
                            'Воскресенье', 'Понедельник', 'Вторник', 'Среда', 'Четверг', 'Пятница', 'Суббота'
                        ];

                        $day = $days[date("w", strtotime($date))];
                        echo "<br/>" . $date;
                        echo "<br/><a href='" . $hotel['url'] . "'>" . $hotel['hotel_name'] . "</a>";
                        if($day == 'Воскресенье'){
                            $load[$i + 1] = strval(round((($karSunCon - $freeRooms) / $karSunCon), 2) * 100);
                            echo "<br/>Константа " . $karSunCon . 'Число комнат ' . $freeRooms;
                        } else{
                            $load[$i + 1] = strval(round((($conArr[$i] - $freeRooms) / $conArr[$i]), 2) * 100);
                            echo "<br/>Константа " . $conArr[$i] . 'Число комнат ' . $freeRooms;

                        }
                    } else{
                        $load[$i + 1] = strval(round((($conArr[$i] - $freeRooms) / $conArr[$i]), 2) * 100);//Загрузка

                        echo "<br/>" . $date;
                        echo "<br/><a ='" . $hotel['url'] . "'>" . $hotel['hotel_name'] . "</a>";
                        echo "<br/>Константа " . $conArr[$i] . 'Число комнат ' . $freeRooms;
                    }
                }
            }
            //Аналогично считаем загрузку апартаментов
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
                    $load[4] = strval(round((($conArr[3] - $apartmentsSum) / $conArr[3]), 2) * 100);//Загрузка
                }
            }

            //Загрузка группы отелей Kotkan Residenssi Apartments
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
                    $load[5] = strval(round((($conArr[4] - $kraSum) / $conArr[4]), 2) * 100);
                }
            }
        }
        //Т.к. апартаментов и Kotkan Residenssi Apartments нет в общем списке отелей, их число комнат нужно добавить
        //к числу комнат рынка, ограниченного рынка и к выборке
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

        //Информация для отладки
        echo "<br/>Апартаменты";
        echo "<br/>Константа " . $conArr[3] . 'Число комнат ' . $apartmentsSum;
        echo "<br/>KRA";
        echo "<br/>Константа " . $conArr[4] . 'Число комнат ' . $kraSum;

        //Для отелей, которых не собрал парсер пишем ноль
        for($i = 0; $i < 15; $i++){
            if($load[$i] == null){
                $load[$i] = '100';
            }
        }

        ksort($load);

        //Считаем загрузку рынка
        $load[15] = strval(round((array_sum($conArr) - $marketRoomSum) / array_sum($conArr), 2) * 100);

        //Считаем загрузку выборки
        if($hotelsSample[0] != null){
            if($sampleRoomsSum == 0){
                $load[16] = '100';
            } else {
                $load[16] = strval(round(($sampleRoomsConstants - $sampleRoomsSum)/$sampleRoomsConstants, 2) * 100);
            }
        }else{
            $load[16] = '0';
        }

        //Т.к. для загрузки ограниченного рынка рисуется отдельный график, то создаём отдельный массив куда кладём
        //нужные нам данные

        //Устанавливаем дату
        $loadLimited[0] = $load[0];
        //Загрузка Guesthouse - Kuin Kotonaan
        $loadLimited[1] = $load[1];
        //Загрузка рынка
        $loadLimited[2] = $load[15];
        //Убираем лишние константы и считаем загрузку ограниченного рынка
        array_splice($conArr, 0, 2);
        $loadLimited[3] = strval(round((array_sum($conArr) - $marketRoomSumLimited)/array_sum($conArr), 2) * 100);
        //Загрузка выборки
        $loadLimited[4] = $load[16];

        //Кладём данные двух графиков в общий массив
        $chartData[0] = $loadLimited;
        $chartData[1] = $load;

        return $chartData;
    }

    public function getExcelData($data, $date, $constants, $sample = null){
        /*
         * При создании таблицы нам нужно будет знать в какую ячейку записывать конкретное значение.
         * Для этого в массиве с данными для таблицы соблюдается строжайший порядок. Поэтому в данном массиве
         * для каждого значения каждого отеля прописан свой индекс. Чтобы заполнить массив в соответствии
         * с порядком ниже прописаны список отелей и индексы различных значений.
        */

        /*
         * Списки названий отелей. Т.к. апартаменты и Kotkan Residenssi Apartments являются группами других отелей, то
         * они имеют свои массивы. Но при они этом прописаны в общем списке, т.к. в зависимости от индекса отеля в
         * общем списке отелей будет найден его индекс в массиве индексов нужного нам значения. Это значит, что группы
         * отелей должны иметь свои индексы в списках, чтобы на месте их значений не было значений других отелей
        */

        //Общий список названий отелей.
        $hotelList = ['Hotel Leikari','Leikari "Nature" Bungalows with Terrace',
            'Апартаменты', 'Kotkan Residenssi Apartments', 'Guest House Nina Art', 'Guesthouse Lokinlaulu',
            'The Grand Karhu', 'Kartanohotelli Karhulan Hovi', 'Hotelli Merikotka', 'Hotelli Kotola',
            'Kesähostelli Kärkisaari', 'Hotel Villa Vanessa', 'Beach Hotel Santalahti'];
        //Список апартаментов
        $apartmentsList = ['Homely Apartment MILA', 'Ilona Apartment - Home Away From Home', 'Apartments N & P',
            'Apartments in Finland N & P', 'Comfortable Apartment MILA at a good location', 'Apartments ”Enkeli”',
            'Scandinavian Sun Apartment', 'Scandinavian City Apartment'];
        //Группа отелей Kotkan Residenssi Apartments
        $kraHotels = ['Kotkan Residenssi Apartments', 'Stunning 2-Bed Apartment in Kotka',
            'Superior 2-Bed Apartment in Kotka', 'Inviting 2-Bed & Sauna Royal Apartment in Kotka',
            'Captivating 4-Bed Apartment in Kotka'];

        /*
         * Массивы индексов значений. Значение индекса зависит от номера столбца данного значения в таблице.
         * Это можно лучше понять посмотрев файл с таблицей table.xls
        */

        //Индексы комнат в наличии
        $roomNumColumnIndex = [8, 13, 18, 23, 28, 33, 38, 43, 48, 53, 58, 63, 68];
        //Индексы Загрузка
        $loadIndex = [9, 14, 19, 24, 29, 34, 39, 44, 49, 54, 59, 64, 69];
        //Индексы Изменения
        $changesColumnNums = [10, 15, 20, 25, 30, 35, 40, 45, 50, 55, 60, 65, 70];
        //Индексы МА 30
        $maColumnsNums = [11, 16, 21, 26, 31, 36, 41, 46, 51, 56, 61, 66, 71];
        //Индексы Разность МА 30
        $changesMaColumnIndex = [12, 17, 22, 27, 32, 37, 42, 47, 52, 57, 62, 67, 72];

        //Массив в котором мы будем хранить данные для таблицы
        $newData = [];

        //Во втором столбце таблицы пишется дата
        $newData[1] = $date;
        $days = [
            'Воскресенье', 'Понедельник', 'Вторник', 'Среда', 'Четверг', 'Пятница', 'Суббота'
        ];

        //Пишем день недели
        $newData[2] = $days[date("w", strtotime($date))];
        $newData[4] = 'Константа';
        $newData[5] = 'Константа';

        //Получаем константу Guesthouse Kuin Kotonaan и удаляем её из общего массива констант, т.к. данные этого
        //отеля прописываются отдельно от других
        $gKKcon = $constants[0];
        array_splice($constants, 0, 1);

        //Определяем константу Kartanohotelli Karhulan Hovi, она зависит от дня недели
        if($newData[2] == 'Воскресенье'){
            $kar = $constants[9];
        } else{
            $kar = $constants[8];
        }
        //Удаляем её из общего списка констант
        array_splice($constants, 8, 1);

        //Для начала заполняем значения отелей, которых нет в общем списке. Позже они будут переписаны

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

        //Определяем список отелей из выборки
        $hotelsSample = explode(', ', $sample);
        $sampleRoomsSum = 0;
        $sampleRoomsConstants = 0;

        //Перебираем собранные отели
        foreach($data as $hotel) {
            //Перебираем общий список отелей
            for ($i = 0; $i < count($hotelList); $i++) {
                /*
                 * Если отель есть в общем списке, то определив его индекс мы добавляем его данные
                 * в массив для таблицы под индексом взятым из массива индексов значений
                */
                if ($hotel['hotel_name'] == $hotelList[$i]){
                    //Сначала пишем, что значение комнат в наличии равно нулю
                    $newData[$roomNumColumnIndex[$i]] = null;
                    //Перебираем все комнаты в отеле. Убеждаемся, что ещё не смотели данные этой комнаты и
                    //добавляем кол-во свободных комнат в массив под нужным индексом
                    $previousRoomType = null;
                    foreach ($hotel['rooms'] as $room) {
                        if ($room['room_type'] != $previousRoomType) {
                            $previousRoomType = $room['room_type'];
                            $newData[$roomNumColumnIndex[$i]] = $newData[$roomNumColumnIndex[$i]] + $room['roomNum'];//Комнаты в наличии
                        }
                    }

                    //Если отель есть в выборке, то добавляем его число комнат и консанту
                    for ($j = 0; $j < count($hotelsSample); $j++) {
                        if ($hotelsSample[$j] == $hotel['hotel_name']){
                            $sampleRoomsSum = $sampleRoomsSum + $newData[$roomNumColumnIndex[$i]];
                            $sampleRoomsConstants = $sampleRoomsConstants + $constants[$i];
                        }
                    }

                    //Вычисляем по формуле загрузку отеля и добавляем её в массив по соответствующему индексу
                    $newData[$loadIndex[$i]] = round((($constants[$i] - $newData[$roomNumColumnIndex[$i]]) / $constants[$i]), 2) * 100 . '%';
                    /*
                     * Также заполняем значения массива по соответствующим индексам. В данном методе они будут
                     * равны нулю, т.к. их невозможно посчитать здесь. Соответственно они будут позже вычислены
                     * в другом методе, а здесь они будут установлены для удобства
                    */
                    $newData[$changesColumnNums[$i]] = null;//Изменения
                    $newData[$maColumnsNums[$i]] = null;//Ма30
                    $newData[$changesMaColumnIndex[$i]] = null;//Разность Ма30
                }
            }

            //Т.к. отеля Guesthouse - Kuin Kotonaan нет в общем списке, то его значения заполняем отдельно от других
            if ($hotel['hotel_name'] == 'Guesthouse - Kuin Kotonaan') {
                $newData[0] = null;
                $availableRoomsNum = null;
                $previousRoomType = null;
                //Перебираем все комнаты
                foreach ($hotel['rooms'] as $room) {
                    //Находим самую низкую цену
                    if ($room['price'] < $newData[0] || $newData[0] == null) {
                        $newData[0] = $room['price'];
                    }
                    //Определяем число комнат в наличии
                    if ($room['room_type'] != $previousRoomType) {
                        $previousRoomType = $room['room_type'];
                        $availableRoomsNum = $availableRoomsNum + $room['roomNum'];
                    }
                }

                //Если отель есть в выборке, то добавляем его число комнат и консанту
                for ($j = 0; $j < count($hotelsSample); $j++) {
                    if ($hotelsSample[$j] == $hotel['hotel_name']){
                        $sampleRoomsSum = $sampleRoomsSum + $availableRoomsNum;
                        $sampleRoomsConstants = $sampleRoomsConstants + $gKKcon;
                    }
                }

                //Заполняем значения отеля Guesthouse - Kuin Kotonaan
                $newData[3] = $availableRoomsNum;//Число комнат в наличии
                $newData[6] = round((($gKKcon - $newData[3]) / $gKKcon), 2) * 100 . '%';//Загрузка
                $newData[7] = null;//МА30
            }

            //Т.к. у отеля Kartanohotelli Karhulan Hovi константа зависит от дня недели, то его значения тоже
            //записываются отдельно
            if ($hotel['hotel_name'] == 'Kartanohotelli Karhulan Hovi') {
                $availableRoomsNum = null;
                $previousRoomType = null;
                foreach ($hotel['rooms'] as $room) {
                    if ($room['room_type'] != $previousRoomType) {
                        $previousRoomType = $room['room_type'];
                        $availableRoomsNum = $availableRoomsNum + $room['roomNum'];
                    }
                }

                //Смотрим, есть ли он в выборке
                for ($j = 0; $j < count($hotelsSample); $j++) {
                    if ($hotelsSample[$j] == $hotel['hotel_name']){
                        $sampleRoomsSum = $sampleRoomsSum + $availableRoomsNum;
                        $sampleRoomsConstants = $sampleRoomsConstants + $kar;
                    }
                }

                $newData[43] = $availableRoomsNum;//Комнаты в наличии
                $newData[44] = round((($kar - $newData[43]) / $kar), 2) * 100 . '%';//Загрузка
                $newData[45] = null;//Изменения
                $newData[46] = null;//МА30
                $newData[47] = null;//Разность МА30
            }

            //Перебираем список апартаментов
            for($i = 0; $i < count($apartmentsList); $i++) {
                //Если отель есть в списке апартаментов, то заполняем его значения
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
                    $newData[18] = $apartmentsSum;//Комнаты в наличии
                    $newData[19] = round((($constants[2] - $apartmentsSum) / $constants[2]), 2) * 100;//Загрузка
                    $newData[20] = null;//Изменения
                    $newData[21] = null;//МА30
                    $newData[22] = null;//Разность МА30
                }
            }

            //Аналогично с группой Kotkan Residenssi Apartments
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

                    //Если хотя бы один отель из группы Kotkan Residenssi Apartments, то добавляем эту группу
                    //в список проверенных отелей
                    $checkedHotels[] = 'Kotkan Residenssi Apartments';
                }
            }

            //Добавляем отель в массив просмотренных отелей
            $checkedHotels[] = $hotel['hotel_name'];
        }

        //Перебрав все отели, смотрим, есть ли в выборке апартаменты и Kotkan Residenssi Apartments
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

        //Удаляем из массива константу Kartanohotelli Karhulan Hovi
        array_splice($constants, 8, 1);
        //Считаем сумму констант рынка, добавив к сумме элементов массива переменные с константами недостающих отелей
        $conSum = array_sum($constants) + $gKKcon + $kar;

        //Считаем сумму комнат в наличии у рынка
        //Сперва добавляем число комнат в наличии отеля Guesthouse Kuin Kotonaan, т.к. его значения нет в списке
        //индексов значений
        $availableRoomsSum = $newData[3];
        //Затем перебираем значения других отелей согласно списку и суммируем
        for($i = 0; $i < count($roomNumColumnIndex); $i++){
            $availableRoomsSum = $availableRoomsSum + $newData[$roomNumColumnIndex[$i]];
        }

        //Вычисляем загрузку рынка
        $newData[73] = round(($conSum - $availableRoomsSum)/$conSum, 2) * 100 . '%';

        //Если есть выборка, то вычисляем её загрузку
        if($hotelsSample[0] != null){
            if($sampleRoomsSum == 0){
                $newData[76] = '100';
            } else {
                $newData[76] = round(($sampleRoomsConstants - $sampleRoomsSum)/$sampleRoomsConstants, 2) * 100;
            }
        }else{
            $newData[76] = '0';
        }

        //У отелей, данные которых не собрал парсер, прописываем в значениях, что нет мест
        $this->getMissingHotels($checkedHotels, $newData);
        //Возвращаем массив с данными для таблицы
        return $newData;
    }

    private function getMissingHotels($hotelList, &$newData){
        //Список отелей
        $setHotelsList = ['Hotel Leikari', 'Leikari "Nature" Bungalows with Terrace', /*'Апартаменты',*/
            'Kotkan Residenssi Apartments', 'Guest House Nina Art', 'Guesthouse Lokinlaulu', 'The Grand Karhu',
            'Kartanohotelli Karhulan Hovi', 'Hotelli Merikotka', 'Hotelli Kotola', 'Kesähostelli Kärkisaari',
            'Hotel Villa Vanessa', 'Beach Hotel Santalahti'];

        $roomNumColumnIndex = [8, 13, /*18,*/ 23, 28, 33, 38, 43, 48, 53, 58, 63, 68];// Индексы комнат в наличии
        $loadIndex = [9, 14, /*19,*/ 24, 29, 34, 39, 44, 49, 54, 59, 64, 69];// Индексы Загрузка
        $changesColumnNums = [10, 15, /*20,*/ 25, 30, 35, 40, 45, 50, 55, 60, 65, 70];// Индексы Изменения
        $maColumnsNums = [11, 16, /*21,*/ 26, 31, 36, 41, 46, 51, 56, 61, 66, 71];// Индексы МА 30
        $changesMaColumnIndex = [12, 17, /*22,*/ 27, 32, 37, 42, 47, 52, 57, 62, 67, 72];// Индексы Разность МА 30

        //Перебираем все отели из списка
        for($i = 0; $i < count($setHotelsList); $i++) {
            //Если отеля из списка нет, среди просмотренных, то записываем его значения
            if($hotelList == null){
            	return;
            }
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
