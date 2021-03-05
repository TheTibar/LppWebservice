<?php
header("Content-Type:application/json;charset=utf-8", false);
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Origin, Content-Type, Autorization, X-Auth-Token');
header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, HEAD, OPTIONS');

include_once dirname(__FILE__) .  '/../Classes/db_connect.php';


function response($status, $status_message, $data)
{
    header("HTTP/1.1 ".$status);
    //header("Content-Type:application/json;charset=utf-8", false);
    $response['status']=$status;
    $response['status_message']=$status_message;
    $response['data']=$data;
    $json_response = json_encode($response, JSON_UNESCAPED_UNICODE);
    echo $json_response;
    die();
}

$instance = \ConnectDB::getInstance();
$conn = $instance->getConnection();

$code_lpp = isset($_GET['code_lpp']) ? $_GET['code_lpp'] : "";



if (! empty($code_lpp))
{
    $code_lpp = mysqli_real_escape_string($conn, $code_lpp);
    $sql = "SELECT code_lpp, label, prix, debut_validite, maj_971, maj_972, maj_973, maj_974
            FROM lpp_current_data
            WHERE code_lpp = $code_lpp";
    //echo($sql);
    
    try
    {
        if ($result = mysqli_query($conn, $sql))
        {
            $data = mysqli_fetch_assoc($result);
            if($data === NULL)
            {
                response(200, 'error_retrieving_data_from_current_data', NULL);
            }
            else
            {
                response(200, 'LPP_Data', $data);
                
            }
        }
        else
        {
            response(200, 'error_retrieving_data_from_current_data', NULL);
        }
    }
    catch (Exception $e)
    {
        echo ("Erreur : " . $e);
    }
}
else 
{
    $msg='error';
    $sep='|';
    if (empty($code_lpp)) {
        $msg = $msg . $sep . 'empty_region_id';
    }
    response(200, $msg, NULL);
}

?>