#!/usr/bin/php
<?php

define('DEBUG', false);

## Main
$config = array();
try {
    $config = parse_input_and_config($config);
}
catch(Exception $e){
    echo $e->getMessage() . "\n";
    usage();
    exit(1);
}

$identity_uri = $config['identity_uri'];
$username = $config['username'];
$api_key = $config['api_key'];
$service = $config['service'];
$region = $config['region'];
$uri = $config['uri'];
$method = $config['method'];
$data = isset($config['data']) ? $config['data'] : false;

$auth_info = get_auth_info($identity_uri, $username, $api_key);
$token = extract_token($auth_info);

$default_headers = array(
    "Accept: application/json",
    "Content-Type: application/json",
    "X-Auth-Token: $token",
    "Accept-Language: en-US,en"
);

$endpoint = extract_endpoint($auth_info, $service, $region);
$request_uri = $endpoint . $uri;

list($result,$http_status) = curl_request($request_uri, $method, $data, $default_headers);
echo $result;
exit(map_http_status_to_exit_code($http_status));

## Functions

function usage(){
    $script_name = basename(__FILE__);
    echo "usage: {$script_name} [-s service] [-r region] [-u uri] [-m http-method] [-d data]\n";
    echo "options:\n";
    echo " -s\tThe service you're contacting. Ex: cloudFiles\n";
    echo " -r\tThe region you would like to execute this operation against. Ex: DFW\n";
    echo " -u\tThe *relative* URI for the endpoint you're trying to reach. Ex: /loadbalancers\n";
    echo " -m\tThe HTTP method, GET or POST\n";
    echo " -d\tAn optional block of data that will be sent with your request. This parameter should be a valid JSON string\n";
}

function map_http_status_to_exit_code($status){
    if($status >= 200 && $status < 300)
        return 0;
    elseif($status >= 300 && $status < 400)
        return 3;
    elseif($status >= 400 && $status < 500)
        return 4;
    else
        return 5;
}

function parse_input_and_config(){

    $default_config = array(
        "method" => "GET",
        "region" => "DFW"
    );

    $raw_cli_config = getopt("s:r:u:m:d:");
    $cli_config = convert_short_params_to_long($raw_cli_config);

    $file_config = get_file_config('.simplerackspacecfg', 'default');

    $config = array_merge($default_config, $file_config, $cli_config);
    validate_config($config);

    return $config;
}

function validate_config($config){

    $required_params = array(
        'identity_uri','username','api_key',
        'service', 'region', 'uri', 'method'
    );
    $supplied_params = array_keys($config);
    $missing_params = array_diff($required_params, $supplied_params);
    if(count($missing_params) > 0)
        throw new Exception("The following required parameters is missing: " . implode(", ",$missing_params));
}

function convert_short_params_to_long($config){

    $short_to_long = array(
        "s" => "service",
        "r" => "region",
        "u" => "uri",
        "m" => "method",
        "d" => "data"
    );

    foreach($short_to_long as $short_param => $long_param){
        if(isset($config[$short_param])){
            $config[$long_param] = $config[$short_param];
            unset($config[$short_param]);
        }
    }

    return $config;
}

function get_file_config($filename, $section){

    $config = parse_ini_file($filename, true);
    if(!isset($config[$section]))
        throw new Exception('Section does not exist');

    return $config[$section];
}

function extract_endpoint($auth_info, $service, $region){
    $valid_service_names = array();
    $service_catelog = $auth_info['access']['serviceCatalog'];
    foreach($service_catelog as $service_details){
        $service_name = $service_details['name'];
        $valid_service_names[] = $service_name;
        if($service == $service_name){
            foreach($service_details['endpoints'] as $endpoint){
                if($endpoint['region'] == $region)
                    return $endpoint['publicURL'];
            }
        }
    }
    $message = "Could not locate service catelog entry for $service. Try one of these: " .
        implode(", ", $valid_service_names);
    throw new Exception($message);
}

function extract_token($auth_info){
    return $auth_info['access']['token']['id'];
}

function get_auth_info($identity_uri, $username, $apiKey){

    $token_uri = $identity_uri . "tokens";

    $auth_data = array(
        "auth" => array(
            "RAX-KSKEY:apiKeyCredentials" => array(
                "username" => $username,
                "apiKey" => $apiKey 
            )
        )
    );

    list($auth_info,$code) = curl_request($token_uri,
                            "POST",
                            json_encode($auth_data),
                            array(
                                'Content-Type: application/json'
                            ));
    
    if($code != 200)
        throw new Exception($auth_info);

    $auth_info = json_decode($auth_info, true);
    return $auth_info;
}

function curl_request($url, $method='GET', $data=false, $headers=array()){

    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    if($data !== false)
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    if(DEBUG)
        curl_setopt($ch, CURLOPT_VERBOSE, true);

    $output = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    curl_close($ch);

    return array($output, $code);
}
?>
