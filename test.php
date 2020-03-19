<?php
/** * Write data to log file. *
* @param mixed $data * @param string $title *
* @return bool */ 
writeToLog($_REQUEST, 'incoming');
function writeToLog($data, $title = '') {
    $log = "\n------------------------\n";
    $log .= date("Y.m.d G:i:s e")  . "\n";
    $log .= (strlen($title) > 0 ? $title : 'DEBUG') . "\n";
    $log .= print_r($data, 1);
    $log .= "\n------------------------\n";
    file_put_contents(getcwd() . '/hook1.log', $log, FILE_APPEND);
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
    file_put_contents(getcwd() . '/hook1.log', $log, FILE_APPEND);
    return true;
}
$link = mysqli_connect("localhost", "id12457980_admin", "1231321", "id12457980_bitrix");
$portal = $_REQUEST['auth']['domain'];
$webhook = 'https://reformat.bitrix24.ru/rest/49/33plg1fyqm8psub3/';
$nowFields = json_decode($nowFields, 1);
$onlyFields = ['ID', 'TITLE'];
foreach($nowFields as $field) {
    array_push($onlyFields, $field[0]);
}
print_r($onlyFields);
echo('<br>');
$queryUrl = $webhook . 'crm.deal.list/';
$queryData = http_build_query(array(
    'filter' =>  ['ID' => $_REQUEST['data']['FIELDS']['ID']], 
	'select' => $onlyFields));

$curl = curl_init();
curl_setopt_array($curl, array(
CURLOPT_SSL_VERIFYPEER => 0,
CURLOPT_POST => 1,
CURLOPT_HEADER => 0,
CURLOPT_RETURNTRANSFER => 1,
CURLOPT_URL => $queryUrl,
CURLOPT_POSTFIELDS => $queryData,
));

$result = curl_exec($curl);
$result = json_decode($result, 1);
curl_close($curl);

foreach($result['result'] as $deal) {
    $forLog = [];
    $forLog['ID'] = $deal['ID'];
    $forLog['portal'] = $_REQUEST['auth']['domain'];
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
    $updates = http_build_query(array(
	    'id' =>  $deal['ID'], 
	    'fields' => ['TITLE' => $text],
	    'params' => ['REGISTER_SONET_EVENT' => 'Y']
    ));
    $curl = curl_init();
    curl_setopt_array($curl, array(
    CURLOPT_SSL_VERIFYPEER => 0,
    CURLOPT_POST => 1,
    CURLOPT_HEADER => 0,
    CURLOPT_RETURNTRANSFER => 1,
    CURLOPT_URL => $webhook . 'crm.deal.update',
    CURLOPT_POSTFIELDS => $updates,
    ));

    curl_exec($curl);
    curl_close($curl);
};
?>