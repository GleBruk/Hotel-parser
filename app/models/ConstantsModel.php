<?php
require 'DB.php';

//В константах хранится кол-во номеров в конкретном отеле. Пользователь может изменять их значение
class ConstantsModel extends Controller{
    //Поля
    private $guesthouse_Kuin_Kotonaan;
    private $hotel_Leikari;
    private $leikari_Nature_Bungalows_with_Terrace;
    private $apartments;
    private $kotkan_Residenssi_Apartments;
    private $guest_House_Nina_Art;
    private $guesthouse_Lokinlaulu;
    private $the_Grand_Karhu;
    private $kartanohotelli_Karhulan_Hovi;
    private $kartanohotelli_Karhulan_Hovi_Sunday;
    private $hotelli_Merikotka;
    private $hotelli_Kotola;
    private $kesähostelli_Kärkisaari;
    private $hotel_Villa_Vanessa;
    private $beach_Hotel_Santalahti;

    private $_db = null;

    public function __construct(){
        // Подключаемся к БД
        $this->_db = DB::getInstance();
    }

    public function setData(){
        //Устанавливаем поля
        $this->guesthouse_Kuin_Kotonaan = $_POST['Guesthouse_Kuin_Kotonaan'];
        $this->hotel_Leikari = $_POST['Hotel_Leikari'];
        $this->leikari_Nature_Bungalows_with_Terrace = $_POST['Leikari_Nature_Bungalows_with_Terrace'];
        $this->apartments = $_POST['Apartments'];
        $this->kotkan_Residenssi_Apartments = $_POST['Kotkan_Residenssi_Apartments'];
        $this->guest_House_Nina_Art = $_POST['Guest_House_Nina_Art'];
        $this->guesthouse_Lokinlaulu = $_POST['Guesthouse_Lokinlaulu'];
        $this->the_Grand_Karhu = $_POST['The_Grand_Karhu'];
        $this->kartanohotelli_Karhulan_Hovi = $_POST['Kartanohotelli_Karhulan_Hovi'];
        $this->kartanohotelli_Karhulan_Hovi_Sunday = $_POST['Kartanohotelli_Karhulan_Hovi_Sunday'];
        $this->hotelli_Merikotka = $_POST['Hotelli_Merikotka'];
        $this->hotelli_Kotola = $_POST['Hotelli_Kotola'];
        $this->kesähostelli_Kärkisaari = $_POST['Kesähostelli_Kärkisaari'];
        $this->hotel_Villa_Vanessa = $_POST['Hotel_Villa_Vanessa'];
        $this->beach_Hotel_Santalahti = $_POST['Beach_Hotel_Santalahti'];
    }

    public function changeConstants(){
        // Удаляем старые константы
        $sql = 'DELETE FROM `constants`';
        $query = $this->_db->prepare($sql);
        $query->execute();

        //Добавляем новые
        $sql = 'INSERT INTO constants(Guesthouse_Kuin_Kotonaan, Hotel_Leikari, Leikari_Nature_Bungalows_with_Terrace,
        Apartments, Kotkan_Residenssi_Apartments,Guest_House_Nina_Art, Guesthouse_Lokinlaulu, The_Grand_Karhu,
        Kartanohotelli_Karhulan_Hovi, Kartanohotelli_Karhulan_Hovi_Sunday, Hotelli_Merikotka, Hotelli_Kotola,
        Kesähostelli_Kärkisaari, Hotel_Villa_Vanessa, Beach_Hotel_Santalahti) VALUES(:guesthouse_Kuin_Kotonaan,
        :hotel_Leikari, :leikari_Nature_Bungalows_with_Terrace, :apartments, :kotkan_Residenssi_Apartments,
        :guest_House_Nina_Art, :guesthouse_Lokinlaulu, :the_Grand_Karhu, :kartanohotelli_Karhulan_Hovi, 
        :kartanohotelli_Karhulan_Hovi_Sunday, :hotelli_Merikotka, :hotelli_Kotola, :keshostelli_Krkisaari,
        :hotel_Villa_Vanessa, :beach_Hotel_Santalahti)';
        $query = $this->_db->prepare($sql);

        $query->execute(['guesthouse_Kuin_Kotonaan' => $this->guesthouse_Kuin_Kotonaan,
            'hotel_Leikari' => $this->hotel_Leikari,
            'leikari_Nature_Bungalows_with_Terrace' => $this->leikari_Nature_Bungalows_with_Terrace,
            'apartments' => $this->apartments, 'kotkan_Residenssi_Apartments' => $this->kotkan_Residenssi_Apartments,
            'guest_House_Nina_Art' => $this->guest_House_Nina_Art, 'guesthouse_Lokinlaulu' => $this->guesthouse_Lokinlaulu,
            'the_Grand_Karhu' => $this->the_Grand_Karhu, 'kartanohotelli_Karhulan_Hovi' => $this->kartanohotelli_Karhulan_Hovi,
            'kartanohotelli_Karhulan_Hovi_Sunday' => $this->kartanohotelli_Karhulan_Hovi_Sunday,
            'hotelli_Merikotka' => $this->hotelli_Merikotka, 'hotelli_Kotola' => $this->hotelli_Kotola,
            'keshostelli_Krkisaari' => $this->kesähostelli_Kärkisaari, 'hotel_Villa_Vanessa' => $this->hotel_Villa_Vanessa,
            'beach_Hotel_Santalahti' => $this->beach_Hotel_Santalahti]);
    }

    public function getConstants(){
        //Получаем константы из БД. На случай ошибки оборачиваем это в исключения
        try{
            error_clear_last();
            //var_dump($this->_db);
            $result = $this->_db->query("SELECT * FROM `constants`");
            $this->_db = null;
            $error = error_get_last();
            if ($error){
                throw new Exception("Ошибка MySQL");
            }
            return $result->fetch(PDO::FETCH_NUM);
        } catch (Exception $e) {
            echo $e->getMessage();
            $this->getConstants();
        }
    }

    public function getConstantsAssoc(){
        //Получаем константы из БД и убеждаемся в отсутствии ошибок
        try{
            error_clear_last();
            $result = $this->_db->query("SELECT * FROM `constants`");
            $this->_db = null;
            $error = error_get_last();
            if ($error){
                throw new Exception("Ошибка MySQL");
            }
            return $result->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            echo $e->getMessage();
            $this->getConstantsAssoc();
        }
    }
}