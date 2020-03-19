<?php
require_once('./logwriter.php');
require_once('./rest.php');

define('APP_ID', 'local.5e620e101c2904.07186687'); // take it from Bitrix24 after adding a new application
define('APP_SECRET_CODE', 'yVgnzpYEgZuYFJJwSNhMzYCuYJ2GTUiVFO1u8oE1iB1mVJVsC2'); // take it from Bitrix24 after adding a new application
define('APP_REG_URL', 'https://bitrixnamechanger.000webhostapp.com/'); // the same URL you should set when adding a new application in Bitrix24

$link = mysqli_connect("localhost", "id12457980_admin", "1231321", "id12457980_bitrix");

$domain = $_REQUEST['auth']['domain'];

$refresh_token = $link->query("SELECT REFRESH_TOKEN FROM PORTALS WHERE PORTAL = '$domain'")
    ->fetch_assoc()['REFRESH_TOKEN'];

writeToLog($_REQUEST, 'Добавлена новая сделка, данные: ');
    
$new_id = $_REQUEST['data']['FIELDS']['ID'];

$arAccessParams = requestAccessTokenFromRefresh($refresh_token, $domain);
$refresh_token = $arAccessParams['refresh_token'];
$access_token = $arAccessParams['access_token'];
$link->query("UPDATE PORTALS SET REFRESH_TOKEN = '$refresh_token', ACCESS_TOKEN = '$access_token' WHERE PORTAL = '$domain'");
$nowFields = $link->query("SELECT FIELDS FROM FIELDS WHERE PORTAL = '$domain'")->fetch_assoc()['FIELDS'];
$nowFields = json_decode($nowFields, 1);
$onlyFields = ['ID', 'TITLE'];
foreach($nowFields as $field) {
    array_push($onlyFields, $field[0]);
}
$deals = array(
    'filter' =>  ['ID' => $new_id], 
    'select' => $onlyFields);
$result = executeREST($arAccessParams['client_endpoint'], 'crm.deal.list', $deals,
    $arAccessParams['access_token']);
foreach($result['result'] as $deal) {
    $forLog = [];
    $forLog['ID'] = $deal['ID'];
    $forLog['portal'] = $domain;
    $text = [];
    foreach($nowFields as $field) {
        $targetField = $field[0];
        $selector = $field[1][0];
        $limit = (int)$field[1][1];
        $value = $deal[$targetField];
        switch ($selector) {
            case "WORDS":
                if (count(explode(' ', $value)) > $limit) {
                    $value = implode(' ', array_slice(explode(' ', $value), 0, $limit));
                }
                array_push($text, $value);
                break;
            case "SYMBOLS":
                if (count(str_split($value)) > $limit) {
                    $value = implode('', array_slice(str_split($value), 0, $limit));
                }
                array_push($text, $value);
                break;
        }
    }
    $text = implode(', ', $text);
    $forLog['changes'] = $text;
    writeChangesToLog($forLog);
    $updates = array(
        'id' =>  $deal['ID'], 
        'fields' => ['TITLE' => $text],
        'params' => ['REGISTER_SONET_EVENT' => 'Y']
    );
    executeREST($arAccessParams['client_endpoint'], 'crm.deal.update', $updates,
    $arAccessParams['access_token']);
};
?>