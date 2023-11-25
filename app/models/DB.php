<?php


class DB{
    private static $_db = null;

    //Подключаемся к БД
    public static function getInstance()
    {
        if (self::$_db == null){
            try {
                error_clear_last();
                self::$_db = new PDO('mysql:host=localhost;dbname=booking_parser', 'user', 'pass');
                $error = error_get_last();
                if ($error || self::$_db == null) {
                    throw new Exception("Ошибка подключения к БД");
                }
                return self::$_db;
            } catch (Exception $e) {
                print_r($error);
                echo $e->getMessage();
                self::getInstance();
            }
        }
    }



    private function __construct(){
    }
    private function __clone(){
    }
    private function __wakeup(){
    }
}
