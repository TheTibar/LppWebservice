<?php
header("Content-Type:application/json;charset=utf-8", false);
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Origin, Content-Type, Autorization, X-Auth-Token');
header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, HEAD, OPTIONS');

include_once dirname(__FILE__) .  '/../Classes/db_connect.php';


function response($status, $status_message, $data, $data_filter)
{
    /*
    header("HTTP/1.1 ".$status);
    //header("Content-Type:application/json;charset=utf-8", false);
    $response['status']=$status;
    $response['status_message']=$status_message;
    $response['data']=$data;
    $json_response = json_encode($response, JSON_UNESCAPED_UNICODE);
    */
    //var_dump($data);
    echo $data[$data_filter];
    die();
}

$instance = \ConnectDB::getInstance();
$conn = $instance->getConnection();

$code_lpp = isset($_GET['code_lpp']) ? $_GET['code_lpp'] : "";
$data_filter = isset($_GET['data_filter']) ? $_GET['data_filter'] : "";



if (! empty($code_lpp)  && ! empty($data_filter))
{
    $code_lpp = mysqli_real_escape_string($conn, $code_lpp);
    $sql = "SELECT 
                code_lpp, 
                label, 
                prix, 
                debut_validite, 
                maj_971, 
                maj_972, 
                maj_973, 
                maj_974, 
                file_name as version
            FROM lpp_current_data
            INNER JOIN lpp_file_history ON lpp_file_history.process_id = lpp_current_data.process_id
            WHERE code_lpp = $code_lpp";
    //echo($sql);
    
    try
    {
        if ($result = mysqli_query($conn, $sql))
        {
            $data = mysqli_fetch_assoc($result);
            if($data === NULL)
            {
                $msg = 'error_retrieving_data_from_current_data_for_this_lpp_code';
                $msg_array["ERROR"] = $msg;
                $data_filter = "ERROR";
                response(200, $msg, $msg_array, $data_filter);
            }
            else
            {
                $data["prix"] = str_replace(".", ",", $data["prix"]);
                $data["maj_971"] = str_replace(".", ",", $data["maj_971"]);
                $data["maj_972"] = str_replace(".", ",", $data["maj_972"]);
                $data["maj_973"] = str_replace(".", ",", $data["maj_973"]);
                $data["maj_974"] = str_replace(".", ",", $data["maj_974"]);
                $data["debut_validite"] = substr($data["debut_validite"], 6, 2) . "/" . substr($data["debut_validite"], 4, 2) . "/" . substr($data["debut_validite"], 0, 4);
                response(200, 'LPP_Data', $data, $data_filter);
                
            }
        }
        else
        {
            $msg = 'error_retrieving_data_from_current_data_for_this_lpp_code';
            $msg_array["ERROR"] = $msg;
            $data_filter = "ERROR";
            response(200, $msg, $msg_array, $data_filter);
        }
    }
    catch (Exception $e)
    {
        echo ("Erreur : " . $e);
    }
}
else 
{
    $msg='ERREUR';
    $sep='|';
    if (empty($code_lpp)) {
        $msg = $msg . $sep . 'code_lpp_non_renseigne';
    }
    if (empty($data_filter)) {
        $msg = $msg . $sep . 'filtre_donne_non_renseigne';
    }
    $msg_array["ERROR"] = $msg;
    $data_filter = "ERROR";
    response(200, $msg, $msg_array, $data_filter);
}

?>