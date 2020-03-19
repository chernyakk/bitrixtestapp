<?php

$link = mysqli_connect("localhost", "id12457980_admin", "1231321", "id12457980_bitrix");

function redirect($url) {
    Header("HTTP 302 Found");
    Header("Location: ".$url);
    die();
}

if (!$_POST) {
    redirect('https://reformat.biz/');
}

$portal = $_REQUEST['DOMAIN'];
$accessToken = $_REQUEST['AUTH_ID'];
$refreshToken = $_REQUEST['REFRESH_ID'];
$expires = $_REQUEST['AUTH_EXPIRES'];
$member = $_REQUEST['member_id'];
$checkIn = $link->query("SELECT FIELDS FROM FIELDS WHERE PORTAL = '$portal'")->fetch_assoc();

$link->query("UPDATE PORTALS SET REFRESH_TOKEN = '$refreshToken', ACCESS_TOKEN = '$accessToken',
    MEMBER_ID = '$member', EXPIRES_IN = '$expires' WHERE PORTAL = '$portal'");
$checkIn = $link->query("SELECT FIELDS FROM FIELDS WHERE PORTAL = '$portal'")->fetch_assoc();
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
<?php } ?>
