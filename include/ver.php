<?php
error_reporting(E_STRICT | E_ALL);
date_default_timezone_set ('America/El_Salvador');
require_once('const.php');
require_once('sesion.php');

function retornar($texto) {
	exit ('<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />' . $texto . '<br />');
}
if ( !isset($_GET['id']) ) retornar ("¡Ups!, parece que esta utilizando mal este script");

global $session, $database;
/*
if ( !$session->logged_in  ) {
	// Denegar la búsqueda
	retornar ("¡Ups!, ¡parece que no podemos mostrarte nada a menos que estes registrado!");
}
*/

	// Búscar y mostrar la imagen
	$link = @mysql_connect(DB_SERVER, DB_USER, DB_PASS) or die('Por favor revise sus datos, puesto que se produjo el siguiente error:<br /><pre>' . mysql_error() . '</pre>');
	mysql_select_db(DB_NAME, $link) or die('!->La base de datos seleccionada "'.$DB_base.'" no existe');
	$q = "SELECT mime, data from ".TBL_IMG." WHERE id_imagen=".addslashes($_GET['id']).";";
	$result = @mysql_query($q, $link) or retornar ('!->Ocurrió un error mientras se procesaba la búsqueda del Id "'.$_GET['id'].'" solicitado.');
	$num_rows = mysql_numrows($result);
   
	if(!$result || ($num_rows < 0)){
	  retornar ("Error mostrando la información");
	}

	if($num_rows == 0){
	  retornar ("¡No hay un recurso con ese Id.!");
	}
	ob_start();
	header("Content-Type: " . mysql_result($result,0,"mime"));
	echo mysql_result($result,0,"data");
	ob_end_flush();
?>
