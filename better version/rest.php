<?

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

function requestAccessTokenFromRefresh ($refresh_token, $server_domain) {
    $url = 'https://' . $server_domain . '/oauth/token/?' .
        'grant_type=refresh_token'.
        '&client_id='.urlencode(APP_ID).
        '&client_secret='.urlencode(APP_SECRET_CODE).
        '&refresh_token='.urlencode($refresh_token);
    return executeHTTPRequest($url);
}

function executeREST ($rest_url, $method, $params, $access_token) {
    $url = $rest_url.$method.'.json';
    return executeHTTPRequest($url, array_merge($params, array("auth" => $access_token)));
}

?>