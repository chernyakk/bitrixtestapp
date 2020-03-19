<?php
// здесь объявляются константы для верификации, при добавлении в Маркетплейс, насколько я знаю, выдаются другие, позволяющие
// не иметь привязки к конкретному порталу
define('APP_ID', 'local.5e620e101c2904.07186687'); // take it from Bitrix24 after adding a new application
define('APP_SECRET_CODE', 'yVgnzpYEgZuYFJJwSNhMzYCuYJ2GTUiVFO1u8oE1iB1mVJVsC2'); // take it from Bitrix24 after adding a new application
define('APP_REG_URL', 'https://bitrixtesting1.000webhostapp.com/'); // the same URL you should set when adding a new application in Bitrix24
// сразу в лог записывается полученный запрос
writeToLog($_REQUEST, 'Входные параметры');
// подключаемся к БД, которая нужна в трёх из четырёх возможных случаев
$link = mysqli_connect("localhost", "id12457980_admin", "1231321", "id12457980_bitrix");
// функция для редиректа в случае, если сайт просто открыт из браузера
function redirect($url) {
    Header("HTTP 302 Found");
    Header("Location: ".$url);
    die();
}
// функция для отправки POST-запроса
function executeHTTPRequest ($queryUrl, array $params = array()) {
    $result = array();
    $queryData = http_build_query($params);

    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_SSL_VERIFYPEER => 0,
        CURLOPT_POST => 1,
        CURLOPT_HEADER => 0,
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_URL => $queryUrl,
        CURLOPT_POSTFIELDS => $queryData,
    ));
    $curlResult = curl_exec($curl);
    curl_close($curl);
    if ($curlResult != '') $result = json_decode($curlResult, true);
    return $result;
}
//функция для получения свежего обновлемого токена
function requestAccessTokenFromRefresh ($refresh_token, $server_domain) {
    $url = 'https://' . $server_domain . '/oauth/token/?' .
        'grant_type=refresh_token'.
        '&client_id='.urlencode(APP_ID).
        '&client_secret='.urlencode(APP_SECRET_CODE).
        '&refresh_token='.urlencode($refresh_token);
    return executeHTTPRequest($url);
}
//функция для формирования запроса к REST
function executeREST ($rest_url, $method, $params, $access_token) {
    $url = $rest_url.$method.'.json';
    return executeHTTPRequest($url, array_merge($params, array("auth" => $access_token)));
}
//три функции для записи различных событий в лог
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
function writeToLogNew($data) {
    $title = 'Новая установка';
    $log = "\n------------------------\n";
    $log .= date("Y.m.d G:i:s") . "\n";
    $log .= (strlen($title) > 0 ? $title : 'DEBUG') . "\n";
    $log .= 'В БД добавлен новый портал: ' . $data;
    $log .= "\n------------------------\n";
    file_put_contents(getcwd() . '/hook.log', $log, FILE_APPEND);
    return true;
}
//часть кода, которая должна обарабатывать случай, когда появилась новая сделка
if ((isset($_REQUEST['event']) and ($_REQUEST['event']) == 'ONCRMDEALADD')) {
    
    writeToLog($_REQUEST, 'Добавлена новая сделка, данные: ');
    
    $domain = $_REQUEST['auth']['domain'];
    
    $new_id = $_REQUEST['data']['FIELDS']['ID'];
    //LAST_ID был нужен в предыдущей версии приложения, но на всякий случай пока оставил
    $link->query("UPDATE FIELDS SET LAST_ID = '$new_id' WHERE PORTAL = '$domain'");
    
    $refresh_token = $link->query("SELECT REFRESH_TOKEN FROM PORTALS WHERE PORTAL = '$domain'")
    ->fetch_assoc()['REFRESH_TOKEN'];
    
    $arAccessParams = requestAccessTokenFromRefresh($refresh_token, $domain);
    
    $refresh_token = $arAccessParams['refresh_token'];
    $access_token = $arAccessParams['access_token'];
    
    $link->query("UPDATE PORTALS SET REFRESH_TOKEN = '$refresh_token', ACCESS_TOKEN = '$access_token' WHERE PORTAL = '$domain'");
    //получение обрабатываемых полей из БД
    $nowFields = $link->query("SELECT FIELDS FROM FIELDS WHERE PORTAL = '$domain'")->fetch_assoc()['FIELDS'];
    $nowFields = json_decode($nowFields, 1);
    $onlyFields = ['ID', 'TITLE'];
    //поля ID и TITLE недоступны для того, чтобы их можно было вносить в название, во избежание конфликтов
    
    foreach($nowFields as $field) {
        array_push($onlyFields, $field[0]);
    }
    
    $deals = array(
        'filter' =>  ['ID' => $new_id], 
    	'select' => $onlyFields);
    //получение из CRM сделок, в которых необходимо изменить название
    $result = executeREST($arAccessParams['client_endpoint'], 'crm.deal.list', $deals,
        $arAccessParams['access_token']);
    //смена названия в сделках
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
}
// обработчик на тот случай, если в приложение зашли из интерфейса Битрикс24
elseif (isset($_REQUEST['DOMAIN'])) {
    $portal = $_REQUEST['DOMAIN'];
    $accessToken = $_REQUEST['AUTH_ID'];
    $refreshToken = $_REQUEST['REFRESH_ID'];
    $expires = $_REQUEST['AUTH_EXPIRES'];
    $member = $_REQUEST['member_id'];
    // проверка на то, существует ли запись данного портала в БД
    $checkIn = $link->query("SELECT FIELDS FROM FIELDS WHERE PORTAL = '$portal'")->fetch_assoc();
    if(!$checkIn) {
        if (!$link->query("INSERT INTO PORTALS (PORTAL, ACCESS_TOKEN, EXPIRES_IN, REFRESH_TOKEN, MEMBER_ID)
        VALUES ('$portal', '$accessToken', '$expires', '$refreshToken', '$member')")) {
            printf("Errormessage: %s\n", mysqli_error($link));
        }
        if (!$link->query("INSERT INTO FIELDS (PORTAL, TYPE, FIELDS) VALUES ('$portal', 'deals', 'default')")) {
            printf("Errormessage: %s\n", mysqli_error($link));
        }
    }
    $link->query("UPDATE PORTALS SET REFRESH_TOKEN = '$refreshToken', ACCESS_TOKEN = '$accessToken'
        WHERE PORTAL = '$portal'");
    // случай, когда поля для портала ещё не заданы, тот самый интерфейс выбора полей
    if ($checkIn['FIELDS'] == 'default') {?>
        <!doctype html>
        <html lang="ru">
            <head>
                <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
                <meta http-equiv="X-UA-Compatible" content="IE=edge">
                <meta name="viewport" content="width=device-width, initial-scale=1">
                <title>Список новых сделок</title>
                <script type="text/javascript" src="js/bitrix.js"></script>
                <script src="https://code.jquery.com/jquery-3.4.1.min.js" crossorigin="anonymous"></script>
                <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.11.0/umd/popper.min.js" crossorigin="anonymous"></script>
                <script type="text/javascript" src="js/application.js"></script>
                <link rel="stylesheet" href="css/bootstrap.min.css" type="text/css"/>
                <script type="text/javascript" src="js/bootstrap.min.js"></script>
                <script type="text/javascript" src="js/bootstrap-multiselect.js"></script>
                <link rel="stylesheet" href="css/bootstrap-multiselect.css" type="text/css"/>
                <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js"></script>
                <script src="//api.bitrix24.com/api/v1/"></script>
                
            
            </head>
            <body>
                <div id="fields">
                    <script>
                        let portal = '<?php echo($_REQUEST['DOMAIN']) ?>';
                        $(document).ready(function () {
                    		BX24.init(function(){
                    			app.AllFields();
                    		});
                        });
                    </script>
                </div>
                <div id="fieldsButton">
                </div>
                <div>
                    <table id="maintable" class='table table-responsive'>
                        <thead>
                            <th>Поле</th>
                            <th>Селектор</th>
                            <th>Количество</th>
                        </thead>
                    </table>
                    <input id="butt1" type='button' class='btn-default' value='Задать поля' onclick="butt1()" disabled></input>
                </div>
            </body>
        </html>
    <?php }
    //случай, когда поля уже заданы, отображение выбранных полей,
    //однако тут отображается для пользователя не привычное название, а название на уровне API, это не дорабатывал ещё
    else {?>
        <html>
            <head>
                <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
                <meta http-equiv="X-UA-Compatible" content="IE=edge">
                <meta name="viewport" content="width=device-width, initial-scale=1">
                <title>Список новых сделок</title>
                <script type="text/javascript" src="js/bitrix.js"></script>
                <script src="https://code.jquery.com/jquery-3.4.1.min.js" crossorigin="anonymous"></script>
                <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.11.0/umd/popper.min.js" crossorigin="anonymous"></script>
                <script type="text/javascript" src="js/application.js"></script>
                <link rel="stylesheet" href="css/bootstrap.min.css" type="text/css"/>
                <script type="text/javascript" src="js/bootstrap.min.js"></script>
                <script type="text/javascript" src="js/bootstrap-multiselect.js"></script>
                <link rel="stylesheet" href="css/bootstrap-multiselect.css" type="text/css"/>
                <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js"></script>
                <script src="//api.bitrix24.com/api/v1/"></script>
            </head>
            <body>
                <div>
                    <h3>На данный момент у Вас установлены следующие поля:</h3>
                    <ul>
                    <?php
                    $fields = json_decode($checkIn['FIELDS'], 1);
                    if ($checkIn['FIELDS'] != 'default') {
                        ?><script>
                        BX24.callBind('OnCrmDealAdd', 'https://bitrixtesting1.000webhostapp.com/');
                        BX24.callBind('OnAppUninstall', 'https://bitrixtesting1.000webhostapp.com/handler.php');
                        </script><?php
                    }
                    foreach($fields as $field) {?>
                        <li><?php echo ($field[0])?></li>
                    <?php } ?>
                    </ul>
                    <script>
                        let portal = '<?php echo($_REQUEST['DOMAIN']) ?>';
                    </script>
                    <input id="reset" type='button' class='btn-default'
                    value='Обнулить поля' onclick="reset()"></input>
                </div>
            </body>
        </html>            
<?php        }}
elseif (!$_POST) {
    redirect('https://google.com/');
}
?>
