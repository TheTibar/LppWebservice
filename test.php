<?php
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

/*
$strtest = '/f_mediam/fo/tips/LPPTOT622.zip">Télécharger le fichier';
$strpost_test = strripos($strtest, 'LPPTOT');
echo(nl2br("chaine : " . $strtest . "\n"));
echo(nl2br("pos LPP : " . $strpost_test . "\n"));
*/
?>