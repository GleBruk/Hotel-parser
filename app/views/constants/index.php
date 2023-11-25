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
        <h1>Константы</h1>
        <form class="form-inline avito-form" action="./constants/sendConstants" method="post">
            <hr>
            <div class="form-group">
                <label>Guesthouse - Kuin Kotonaan</label>
                <input type="text" class="form-control" name="Guesthouse_Kuin_Kotonaan" value="<?php $data['Guesthouse_Kuin_Kotonaan']?>" style="width:50px;">
                <br>
                <label>Hotel Leikari</label>
                <input type="text" class="form-control" name="Hotel_Leikari" value="<?php echo $data['Hotel_Leikari']?>" style="width:50px;">
                <br>
                <label>Leikari "Nature" Bungalows with Terrace</label>
                <input type="text" class="form-control" name='Leikari_Nature_Bungalows_with_Terrace' value="<?php echo $data['Leikari_Nature_Bungalows_with_Terrace']?>" style="width:50px;">
                <br>
                <label>Апартаменты</label>
                <input type="text" class="form-control" name='Apartments' value="<?php echo $data['Apartments']?>" style="width:50px;">
                <br>
                <label>Kotkan Residenssi Apartments</label>
                <input type="text" class="form-control" name='Kotkan_Residenssi_Apartments' value="<?php echo $data['Kotkan_Residenssi_Apartments']?>" style="width:50px;">
                <br>
                <label>Guest House Nina Art</label>
                <input type="text" class="form-control" name="Guest_House_Nina_Art" value="<?php echo $data['Guest_House_Nina_Art']?>" style="width:50px;">
                <br>
                <label>Guesthouse Lokinlaulu</label>
                <input type="text" class="form-control" name="Guesthouse_Lokinlaulu" value="<?php echo $data['Guesthouse_Lokinlaulu']?>" style="width:50px;">
                <br>
                <label>The Grand Karhu</label>
                <input type="text" class="form-control" name="The_Grand_Karhu" value="<?php echo $data['The_Grand_Karhu']?>" style="width:50px;">
                <br>
                <label>Kartanohotelli Karhulan Hovi</label>
                <input type="text" class="form-control" name="Kartanohotelli_Karhulan_Hovi" value="<?php echo $data['Kartanohotelli_Karhulan_Hovi']?>" style="width:50px;">
                <br>
                <label>Kartanohotelli Karhulan Hovi (воскресенье)</label>
                <input type="text" class="form-control" name="Kartanohotelli_Karhulan_Hovi_Sunday" value="<?php echo $data['Kartanohotelli_Karhulan_Hovi_Sunday']?>" style="width:50px;">
                <br>
                <label>Hotelli Merikotka</label>
                <input type="text" class="form-control" name="Hotelli_Merikotka" value="<?php echo $data['Hotelli_Merikotka']?>" style="width:50px;">
                <br>
                <label>Hotelli Kotola</label>
                <input type="text" class="form-control" name="Hotelli_Kotola" value="<?php echo $data['Hotelli_Kotola']?>" style="width:50px;">
                <br>
                <label>Kesähostelli Kärkisaari</label>
                <input type="text" class="form-control" name="Kesähostelli_Kärkisaari" value="<?php echo $data['Kesähostelli_Kärkisaari']?>" style="width:50px;">
                <br>
                <label>Hotel Villa Vanessa</label>
                <input type="text" class="form-control" name="Hotel_Villa_Vanessa" value="<?php echo $data['Hotel_Villa_Vanessa']?>" style="width:50px;">
                <br>
                <label>Beach Hotel Santalahti</label>
                <input type="text" class="form-control" name="Beach_Hotel_Santalahti" value="<?php echo $data['Beach_Hotel_Santalahti']?>" style="width:50px;">
                <hr>
                <button type="submit" class="btn btn-default" style="margin-bottom:30px;">Изменить</button>
                <br>
            </div>
        </form>
    </div>
</body>
