<?php


include_once __DIR__ .  '/../Classes/db_connect.php';
include_once __DIR__ .  '/../Classes/Log.php';
include_once __DIR__ .  '/../Classes/Process.php';
use Classes\Log;
use Classes\Process;



$stillValid = TRUE;
$Log = new Log();
$Process = new Process();

$instance = \ConnectDB::getInstance();
$conn = $instance->getConnection();


/* A REACTIVER */

$create_process = $Process->newProcess('CRON_Import_LPP_Complet', 0);


if ($stillValid && $create_process == 0)
{
    $stillValid = TRUE;
    $process_id = $Process->__get('process_id');
    $Log->writeLog("Création du process : " . $process_id, $process_id);
}
else
{
    $Log->writeLog("Erreur création du process", -1);
    $stillValid = FALSE;
    die();
}


echo("step1");
//  
/* A REACTIVER */
if ($stillValid && $Log->writeLog("Début récupération chemin LPPFiles", $process_id))
{
    $sql = "SELECT param_value 
            FROM lpp_param 
            WHERE param_name = 'tmp_lpp_path'";
    $sql_result = mysqli_query($conn, $sql);
    $data = mysqli_fetch_assoc($sql_result);
    
    if($data['param_value'] != "") 
    {
        $target_directory = __DIR__ . $data['param_value'];
        $Log->writeLog("Fin récupération chemin LPPFiles : " . $target_directory, $process_id);
        $stillValid = TRUE;
    }
    else 
    {
        $Log->writeLog("Erreur récupération chemin LPPFiles", $process_id);
        $stillValid = FALSE;
        die();
    }
}
else
{
    $Log->writeLog("Erreur récupération chemin LPPFiles", $process_id);
    $stillValid = FALSE;
    die();
}



echo("step2");
/* A REACTIVER */
if ($stillValid && $Log->writeLog("Début nettoyage répertoire LPPFiles", $process_id))
{
    $files = glob($target_directory . '*'); // get all file names
    foreach($files as $file){ // iterate files
        if(is_file($file))
            unlink($file); // delete file
    }
    $Log->writeLog("Fin nettoyage répertoire LPPFiles", $process_id);
    $stillValid = TRUE;
}
else
{
    $Log->writeLog("Erreur nettoyage répertoire LPPFiles", $process_id);
    $stillValid = FALSE;
    die();
}


echo("step3");

/* A REACTIVER */
if ($stillValid && $Log->writeLog("Début récupération url LPP", $process_id))
{
    //On récupère l'emplacement et le nom du fichier à télécharger
    $page = file_get_contents('http://www.codage.ext.cnamts.fr/codif/tips//telecharge/index_tele.php?p_site=AMELI');
    $url_base = 'http://www.codage.ext.cnamts.fr/f_mediam/fo/tips/';
    
    $doc = new DOMDocument;
    libxml_use_internal_errors(true);
    $doc->loadHTML($page);
    //var_dump($page);
    //echo(nl2br("page length : " . strlen($page) . "\n"));
    $pos_link = strripos($page, '/f_mediam/fo/tips/LPPTOT');
    //echo(nl2br("pos_/f_mediam/fo/tips/LPPTOT : " . $pos_link . "\n"));
    $page_fin = substr($page, $pos_link, strlen($page));
    //echo(nl2br("page_fin length : " . strlen($page_fin) . "\n"));
    //echo(nl2br("page_fin : " . $page_fin . "\n"));
    $filename_deb = strpos($page_fin, 'LPPTOT');
    //echo(nl2br("\n" . "filename_deb : " . $filename_deb . "\n"));
    $pos_ZIP = strpos($page_fin, '.zip');
    //echo(nl2br("pos_ZIP : " . $pos_ZIP . "\n"));
    $filename_length = $pos_ZIP - $filename_deb + 4;
    //echo(nl2br("filename_length : " . $filename_length . "\n"));
    $file_name = substr($page_fin, $filename_deb, $filename_length);
    //echo(nl2br("filename: " . $file_name . "\n"));
    $url = $url_base . $file_name;
    //echo(nl2br("lien complet : " . $url . "\n"));
    
    
    $Log->writeLog("Fin récupération url LPP : " . $url, $process_id);
    $stillValid = TRUE;
}
else
{
    $Log->writeLog("Erreur récupération url LPP", $process_id);
    $stillValid = FALSE;
    die();
}


echo("step4");

        
/* A REACTIVER */
if($stillValid && $Log->writeLog("Début téléchargement LPP", $process_id) && file_put_contents($target_directory . $file_name, file_get_contents($url)))
{
    $Log->writeLog("Fin téléchargement LPP : " . $file_name, $process_id);
    $stillValid = TRUE;
}
else 
{
    $Log->writeLog("Erreur téléchargement LPP", $process_id);
    $stillValid = FALSE;
    die();
}


echo("step5");
/* A REACTIVER */
if($stillValid && $Log->writeLog("Début dezippage LPP", $process_id))
{
    $zip = new ZipArchive;
    $Log->writeLog("Fichier : " . $target_directory . $file_name, $process_id);
    if ($zip->open($target_directory . $file_name) === TRUE) 
    {
        $zip->extractTo($target_directory);
        $zip->close();
        $Log->writeLog("Fin dezippage LPP", $process_id);
    }
    else 
    {
        $Log->writeLog("Erreur dezippage LPP", $process_id);
        $stillValid = FALSE;
        die();
    }
}
else
{
    $Log->writeLog("Erreur dezippage LPP", $process_id);
    die();
}

echo("step6");
/* A REACTIVER */ 
if($stillValid && $Log->writeLog('Début sauvegarde nom fichier', $process_id))
{
    $sql = "INSERT INTO lpp_file_history (file_name, process_id) 
            VALUES ('$file_name', $process_id)";
    if(mysqli_query($conn, $sql))
    {
        $Log->writeLog("Fin sauvegarde nom fichier : " . $file_name, $process_id);
        $stillValid = TRUE;
    }
    else
    {
        $Log->writeLog("Erreur sauvegarde nom fichier", $process_id);
        $stillValid = FALSE;
        die();
    }
}
else
{
    $Log->writeLog("Erreur sauvegarde nom fichier", $process_id);
    $stillValid = FALSE;
    die();
}

echo("step7");
/* TRANSFORMATION NX EN PLUSIEURS LIGNE */
$file_named_unzipped = substr($file_name, 0, strpos($file_name, '.'));
if ($stillValid && $Log->writeLog("Début transformation fichier : " . $file_named_unzipped, $process_id))
{
    //read the entire string
    $file_named_unzipped = substr($file_name, 0, strpos($file_name, '.'));
    $str=file_get_contents($target_directory . $file_named_unzipped);
    
    
    //replace something in the file string - this is a VERY simple example
    $str=str_replace("1010101", "\n", $str);
    $str=utf8_encode($str);
    //write the entire string
    file_put_contents($target_directory . $file_named_unzipped, $str);
}
else
{
    $Log->writeLog("Erreur suppression table temporaire", $process_id);
    $stillValid = FALSE;
    die();
}


echo("step8");
$newDataTableName = 'lpp_current_data'; //ne pas commenter pour les tests


if($stillValid && $Log->writeLog("Début chargement fichier en table temporaire", $process_id))
{
    /* NE FONCTIONNE PAS CHEZ OVH MUTUALISE
     $sql = "LOAD DATA LOCAL INFILE '$finalFile'
     INTO TABLE $newDataTableName
     FIELDS TERMINATED BY '|'
     LINES TERMINATED BY '\\r\\n'
     IGNORE 1 LINES";
     */
    $j = 0;
    //echo(nl2br("j debut : " . $j . "\n"));
    //echo(nl2br("fichier : " . $finalFile . "\n"));
    $finalFile = $target_directory . $file_named_unzipped;
    $handle = fopen($finalFile, 'r');
    
    
    
    
    if($handle)
    {
        //echo(nl2br("handle ok" . "\n"));
        //Première ligne : entête
        $data = fgets($handle);
        echo(nl2br($data));
        //echo(nl2br($data[0]  . "\n"));
        
        
        //while(($data = fgetcsv($handle, 0, "|")) !== FALSE && $j < 50) //limite à 50 pour tests
        while(($data = fgets($handle)) !== FALSE) //sans limite
        {
            //echo(nl2br("Dans le while \n"));
            //echo(nl2br($data));
            //echo(nl2br("j : " . $j . "\n"));
            //echo(nl2br(str_replace("|", ",", $data[0])  . "\n"));
            //var_dump($data);
            
            //echo(nl2br("num : " . strval($num) . "\n"));
            
            $code_lpp = mysqli_real_escape_string($conn, mb_substr($data, 0, 7, 'UTF-8'));
            $label = mysqli_real_escape_string($conn, mb_substr($data, 13, 108, 'UTF-8'));
            //$label = mb_substr($data, 13, 108, 'UTF-8');
            print(nl2br($label . "\n"));
            $prix = mysqli_real_escape_string($conn, mb_substr($data, 292, 11, 'UTF-8'));
            
            $debut_validite =  mysqli_real_escape_string($conn, mb_substr($data, 256, 8, 'UTF-8'));
            $maj_971 = mysqli_real_escape_string($conn, mb_substr($data, 303, 4, 'UTF-8'));
            
            $maj_972 = mysqli_real_escape_string($conn, mb_substr($data, 307, 4, 'UTF-8'));
            
            $maj_973 = mysqli_real_escape_string($conn, mb_substr($data, 311, 4, 'UTF-8'));
            
            $maj_974 = mysqli_real_escape_string($conn, mb_substr($data, 315, 4, 'UTF-8'));
            
            
            $prix = $prix / 100;
            $maj_971 = $maj_971 / 1000;
            $maj_972 = $maj_972 / 1000;
            $maj_973 = $maj_973 / 1000;
            $maj_974 = $maj_974 / 1000;
            
            $sql = "INSERT INTO $newDataTableName VALUES ($code_lpp, '$label', $prix, '$debut_validite', $maj_971, $maj_972, $maj_973, $maj_974, $process_id)" ;
            echo(nl2br($sql . "\n"));
            
            $j = $j + 1; //Le fichier commence ligne 1 pour notepad ++
            $stepLog = 1000;
            
            //echo(nl2br($sql . "\n"));
            
            if(mysqli_query($conn, $sql))
            {
                if (fmod($j, $stepLog) == 0 || $j == 1)
                {
                    $Log->writeLog("Chargement des lignes : " . strval($j) . " à " . strval($j + $stepLog - 1) . " (max)", $process_id);
                }
                $sql = NULL;
            }
            else
            {
                $Log->writeLog("Erreur sur la ligne : " . strval($j) . ", requête : " . $sql, $process_id);
                $stillValid = FALSE;
                die();
            }
        }
        //echo("eof");
        $Log->writeLog("Fin chargement fichier en table temporaire : " . $j . " lignes", $process_id);
        fclose($handle);
        $stillValid = TRUE;
    }
    else
    {
        //echo("erreur handle");
        $Log->writeLog("Erreur chargement fichier en table temporaire", $process_id);
        $stillValid = FALSE;
        die();
    }
}
else
{
    $stillValid = FALSE;
    $Log->writeLog("Erreur chargement fichier en table temporaire", $process_id);
    die();
}



/* A REACTIVER */
/* on supprime toutes les données qui sont plus anciennes que le process en cours */
if ($stillValid && $Log->writeLog("Début suppression table LPP : " . $newDataTableName, $process_id))
{
    $sql = "DELETE FROM $newDataTableName WHERE process_id <> $process_id";
    $sql_result = mysqli_query($conn, $sql);
    
    if (mysqli_query($conn, $sql))
    {
        $Log->writeLog("Fin suppression table LPP : " . $newDataTableName, $process_id);
        $stillValid = TRUE;
    }
    else
    {
        $Log->writeLog("Erreur suppression table LPP : " . $newDataTableName, $process_id);
        $stillValid = FALSE;
        die();
    }
}
else
{
    $Log->writeLog("Erreur suppression table LPP", $process_id);
    $stillValid = FALSE;
    die();
}

?>