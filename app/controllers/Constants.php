<?php


class Constants extends Controller{
    private $model;

    public function __construct(){
        // Подключаемся к БД
        $this->model = $this->model('ConstantsModel');
    }

    public function index(){
        $constants = $this->model->getConstantsAssoc();
        $this->view('constants/index', $constants);
    }
    public function sendConstants(){
        $this->model->setData();
        $this->model->changeConstants();
        $this->index();
    }
}