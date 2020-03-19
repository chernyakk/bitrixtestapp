<?php
require_once('./logwriter.php');
require_once('./rest.php');
define('APPTOKEN', 'af71d9299be978df905e406727bd0a97');
$link = mysqli_connect("localhost", "id12457980_admin", "1231321", "id12457980_bitrix");

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
            $install = 'https://bitrixnamechanger.000webhostapp.com/handler.php';
            $rewriter = 'https://bitrixnamechanger.000webhostapp.com/method.php';
            $events = array(
                'OnCrmDealAdd' => $rewriter,
                'OnAppInstall' => $install,
                'OnAppUninstall' => $install
            );
            $domain = $_REQUEST['auth']['domain'];
            foreach ($events as $event => $handler) {
                $refresh_token = $link->query("SELECT REFRESH_TOKEN FROM PORTALS WHERE PORTAL = '$domain'")
                ->fetch_assoc()['REFRESH_TOKEN'];
                $arAccessParams = requestAccessTokenFromRefresh($refresh_token, $domain);
                $refresh_token = $arAccessParams['refresh_token'];
                $access_token = $arAccessParams['access_token'];
                $link->query("UPDATE PORTALS SET REFRESH_TOKEN = '$refresh_token', ACCESS_TOKEN = '$access_token' WHERE PORTAL = '$domain'");
                $params = array(
                    'event' => $event,
                    'handler' => $handler
                );
                executeREST($arAccessParams['client_endpoint'], 'event.unbind', $params, $access_token);
            }
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