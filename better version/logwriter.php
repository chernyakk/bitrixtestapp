<?php

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

function writeToLog($data, $title = '') {
    $log = "\n------------------------\n";
    $log .= date("Y.m.d G:i:s e")  . "\n";
    $log .= (strlen($title) > 0 ? $title : 'DEBUG') . "\n";
    $log .= print_r($data, 1);
    $log .= "\n------------------------\n";
    file_put_contents(getcwd() . '/hook.log', $log, FILE_APPEND);
    return true;
}

function writeChangesToLog($data) {
    $title = 'Внесены изменения: ';
    $log = "\n------------------------\n";
    $log .= date("Y.m.d G:i:s e")  . "\n";
    $log .= $title . "\n";
    $log .= "На портале " . $data['portal'] . " у сделки с ID: " . $data['ID'];
    $log .= " название изменено на: " . "\n" . $data['changes'];
    $log .= "\n------------------------\n";
    file_put_contents(getcwd() . '/hook.log', $log, FILE_APPEND);
    return true;
}

?>