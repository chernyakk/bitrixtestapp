<?php
// здесь в БД записываются выбранные поля
$link = mysqli_connect("localhost", "id12457980_admin", "1231321", "id12457980_bitrix");
$fields = $_POST['result'];
$portal = $_POST['portal'];
$type = 'deals';
$link->query("UPDATE FIELDS SET TYPE = '$type', FIELDS = '$fields'
    WHERE PORTAL = '$portal'");
