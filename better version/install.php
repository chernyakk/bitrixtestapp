<!doctype html>
    <html lang="ru">
        <head>
            <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
            <meta http-equiv="X-UA-Compatible" content="IE=edge">
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <title>Установка названий новых сделок</title>
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
            <script>BX24.callBind('OnAppInstall', 'https://bitrixnamechanger.000webhostapp.com/handler.php');</script>
        </head>
        <body>
            <div id="fields">
                <script>
                    let portal = '<?php echo($_REQUEST['DOMAIN']) ?>';
                    $(document).ready(function () {
                        BX24.init(function(){
                            app.AllFields();
                        });
                        BX24.callBind('OnCrmDealAdd', 'https://bitrixnamechanger.000webhostapp.com/method.php');
                        BX24.callBind('OnAppUninstall', 'https://bitrixnamechanger.000webhostapp.com/handler.php');
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