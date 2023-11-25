<?php
require_once 'vendor/autoload.php';
?>
    <!DOCTYPE html>
    <html lang="ru">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
        <title>Парсер Букинг</title>

        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css" integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous">
        <link rel="stylesheet" href="./public/css/main.css" charset="utf-8">
    </head>
<body>
<div class="container-fluid">
    <?php require 'public/blocks/header.php'?>
    <h1>Парсер Букинг</h1>
    <form class="form-inline" action="./booking/parse" method="post">
        <label>Период (в днях)</label>
        <input type="text" class="form-control" name="days_val" value="30" style="width:100px">
        <br>
        <br>
        <label>Число дней</label>
        <input type="text" class="form-control" name="checkout_index" value="1" style="width:100px;">
        <br>
        <br>
        <label>Число взрослых</label>
        <input type="text" class="form-control" name="group_adults" value="1" style="width:100px;">
        <br>
        <br>
        <label>Выборка</label>
        <input type="text" class="form-control" name="sample" value="" style="width:1250px;">
        <br>
        <hr>
        <button type="submit" class="btn btn-default">Выполнить</button>
    </form>

    <hr />

    <div class="row">
        <div class="col-md-6">
<?php

if ($data) {
    echo '<pre>';
    print_r($data);
    echo '</pre>';
}
