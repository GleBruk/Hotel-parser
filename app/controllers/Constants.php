<?php

class Constants extends Controller{
    private $model;

    public function __construct(){
        // Подключаемся к БД
        $this->model = $this->model('ConstantsModel');
    }

    public function index(){
        //Получаем константы
        $constants = $this->model->getConstantsAssoc();
        //Переходим в шаблон
        $this->view('constants/index', $constants);
    }
    public function sendConstants(){
        //Устанавливаем поля в классе
        $this->model->setData();
        //Меняем константы
        $this->model->changeConstants();
        //Возвращаемся в шаблон
        $this->index();
    }
}