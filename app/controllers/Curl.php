<?php
require_once 'vendor/autoload.php';
use Curl\MultiCurl;

class Curl extends Controller {

    function __construct()
    {
        $this->sleepMin = 2;
        $this->sleepMax = 5;
    }

    function setCache($content, $url, $date = null){
        //Сохраняем кеш
        if ($content == '') {
            return;
        }
        if($date == null){
            preg_match( '~fi/(.*)\.ru~',$url, $a);
            $fileName = 'cash/' . 'error ' . $a[1];
        } else {
            preg_match( '~fi/(.*)\.ru~',$url, $a);
            $fileName = 'cash/' . $date . ' ' . $a[1];
        }
        if (!file_exists('cash')) {
            mkdir('cash');
        }
        $f = fopen($fileName, 'w+');
        fwrite($f, $content);
        fclose($f);
    }

    function load($url, $cash=0){

        //Указываем заголовки
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt( $ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/89.0.4389.105 YaBrowser/21.3.3.234 Yowser/2.5 Safari/537.36');
        curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate');
        curl_setopt($ch, CURLOPT_COOKIEJAR, __DIR__ . '/cookie.txt');
        //curl_setopt($ch, CURLOPT_PROXY,'213.79.122.82:8080');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $content = curl_exec($ch);
        curl_close($ch);

        sleep(rand($this->sleepMin, $this->sleepMax));

        //Записываем логи
        $file = fopen('log.txt', 'a+');
        fwrite($file, "\n".date('Y-m-d H:i:s').' '.$url);
        fclose($file);

        return $content;
    }

    function multirequest($urls, $date = null){
        $multi = curl_multi_init();
        $handles = [];
        $htmls = [];

        foreach ($urls as $url) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_COOKIEFILE,__DIR__ . '/cookie.txt');
            //curl_setopt($ch, CURLOPT_PROXY,'213.79.122.82:8080');

            $headers = array(
                'User-Agent: Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/89.0.4389.105 YaBrowser/21.3.3.234 Yowser/2.5 Safari/537.36',
                'Accept-Language: ru,en;q=0.9,fi;q=0.8,la;q=0.7'
            );
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            //curl_setopt($ch, CURLOPT_HEADER, 1);

            //Записываем логи
            $file = fopen('log.txt', 'a+');
            fwrite($file, "\n".date('Y-m-d H:i:s').' '.$url);
            fclose($file);

            curl_multi_add_handle($multi, $ch);
            $handles[$url] = $ch;
        }

        do {
            $mrc = curl_multi_exec($multi, $active);
        } while ($mrc == CURLM_CALL_MULTI_PERFORM);

        while ($active && $mrc == CURLM_OK) {

            if (curl_multi_select($multi) == -1) {
                unsleep(100);
            }
            do {
                $mrc = curl_multi_exec($multi, $active);
            } while ($mrc == CURLM_CALL_MULTI_PERFORM);
        }

        $i = 0;
        foreach ($handles as $channel) {
            $html = curl_multi_getcontent($channel);
            //Сохраняем карточки в кеш
            $this->setCache($html, $urls[$i], $date);

            $htmls[] = $html;

            curl_multi_remove_handle($multi, $channel);
            $i++;
        }

        curl_multi_close($multi);

        return $htmls;
    }

    function loadCard($url){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_COOKIEFILE,__DIR__ . '/cookie.txt');
        //curl_setopt($ch, CURLOPT_PROXY,'213.79.122.82:8080');

        $headers = array(
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/89.0.4389.105 YaBrowser/21.3.3.234 Yowser/2.5 Safari/537.36',
            'Accept-Language: ru,en;q=0.9,fi;q=0.8,la;q=0.7'
        );
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $content = curl_exec($ch);
        curl_close($ch);

        sleep(rand($this->sleepMin, $this->sleepMax));

        //Записываем логи
        $file = fopen('log.txt', 'a+');
        fwrite($file, "\n".date('Y-m-d H:i:s'). 'loadCard ' .$url);
        fclose($file);

        //Сохраняем проблемную карточку в кеш
        $this->setCache($content, $url);

        return $content;
    }
}
