<?php
//обработчик установки и удаления приложения
define('APPTOKEN', 'af71d9299be978df905e406727bd0a97');
$link = mysqli_connect("localhost", "id12457980_admin", "1231321", "id12457980_bitrix");
function writeToLogAppEvents($data, $event) {
    $events = [['Новая установка: ', 'В БД добавлен новый портал '],
    ['Удаление приложения: ', 'Из БД удалены данные портала ']];
    if ($event == 'install') $events = $events[0];
    if ($event == 'uninstall') $events = $events[1];
    $title = $events[0];
    $text = $events[1];
    $log = "\n------------------------\n";
    $log .= date("Y.m.d G:i:s") . "\n";
    $log .= $title . "\n";
    $log .= $text . $data;
    $log .= "\n------------------------\n";
    file_put_contents(getcwd() . '/hook.log', $log, FILE_APPEND);
    return true;
}
if (isset($_REQUEST['auth']['application_token']) and ($_REQUEST['auth']['application_token'] == APPTOKEN)) {
    $portal = $_REQUEST['auth']['domain'];
    switch ($_REQUEST['event']) {
        case 'ONAPPINSTALL':
            if (!$link->query("INSERT INTO PORTALS (PORTAL) VALUES ('$portal')")) {
                printf("Errormessage: %s\n", mysqli_error($link));
            }
            if (!$link->query("INSERT INTO FIELDS (PORTAL) VALUES ('$portal')")) {
                printf("Errormessage: %s\n", mysqli_error($link));
            }
            writeToLogAppEvents($portal, 'install');
            break;
        case 'ONAPPUNINSTALL':
            if (!$link->query("DELETE FROM PORTALS WHERE PORTAL = '$portal'")) {
                printf("Errormessage: %s\n", mysqli_error($link));
            }
            if (!$link->query("DELETE FROM FIELDS WHERE PORTAL = '$portal'")) {
                printf("Errormessage: %s\n", mysqli_error($link));
            }
            writeToLogAppEvents($portal, 'uninstall');
            break;
        }
    }
?>
