<?php
/*-----------------------INICIALIZACIÓN-------------------*/
error_reporting(E_STRICT | E_ALL);
/* Activar compresión de salida */
ob_start("ob_gzhandler"); 
/* Para los mapas de google */
require_once('include/maps/GoogleMapAPI.class.php');
$map = new GoogleMapAPI('map');
/* Constantes */
require_once("include/const.php");
/* Controlador principal de la sesión */
require_once("include/sesion.php");
/* Hacer disponible a todos mi ubicación*/
$sURL_INDEX = $_SERVER['PHP_SELF'];
date_default_timezone_set ('America/El_Salvador');
ini_set("memory_limit","128M");

/*-----------------------INCLUSIONES-------------------*/
/* CODIGO */
/*Para procesar PDF's*/
require_once ('include/dompdf/dompdf_config.inc.php');

/* CONTENIDO */
/*Constructores de Menús, etc.*/
require_once("contenido/sub.php");
require_once("contenido/admin.php");
require_once("contenido/ayuda+contacto.php");
require_once("contenido/usuario+recuperar_clave.php");
require_once("contenido/usuario+info.php");
require_once("contenido/usuario+mupi.php");
require_once("contenido/usuario+ingresar.php");
require_once("contenido/usuario+registrar.php");
require_once("contenido/usuario+editar.php");
require_once("contenido/global+404.php");
require_once("contenido/global+info.php");
require_once("contenido/global+estadisticas.php");
require_once("contenido/admin+reportes.php");
require_once("contenido/mupis+ubicaciones.php");
require_once("contenido/mupis+contacto.php");
require_once("contenido/mupis+creativo.php");
require_once("contenido/mupis+detalle.php");
require_once("contenido/mupis+eventos.php");
require_once("contenido/mupis+info.php");
require_once("contenido/mupis+precio.php");
require_once("contenido/mupis+servicios.php");
require_once("contenido/mupis+ubicaciones.php");
?>
<!-- Este comentario activa "quirks mode" en Internet Explorer ;) - Vlad.-->
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="es" lang="es">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<meta http-equiv="Content-Style-type" content="text/css" />
	<meta http-equiv="Content-Script-type" content="text/javascript" />
	<meta http-equiv="Content-Language" content="es" />
	<link rel="stylesheet" type="text/css" href="estilo.css" />
	<link rel="stylesheet" type="text/css" href="include/chrometheme/chromestyle3.css" />
	<link rel="shortcut icon" href="favicon.ico" type="image/x-icon" />
	<link rel="start" href="/" />
	<title><? echo _NOMBRE_; ?> - Servicio publicitario</title>
	<meta name="keywords" content="MUPI, Publicidad, El Salvador" />
	<meta name="description" content="MUPI es un servicio publicitario." />
	<script type="text/javascript" src="include/chromejs/chrome.js">
	/***********************************************
	* Chrome CSS Drop Down Menu- (c) Dynamic Drive DHTML code library (www.dynamicdrive.com)
	* This notice MUST stay intact for legal use
	* Visit Dynamic Drive at http://www.dynamicdrive.com/ for full source code
	***********************************************/
	</script>
	</head>
<body>
	<?php CONTENIDO_mostrar_logo(); ?>
	<?php echo INICIAR_MENUES() ?>
	<div id="container">
		<div id="content">
			<?php CONTENIDO_mostrar_principal(); ?>
		</div>
		<div class="clear"></div>
		<div id="abajo">
			<?php  CONTENIDO_en_linea(); ?>
		</div>
		<div class="clear"></div>
	</div>
	</div>	
	<img class="fija" src="hoja.gif" />
	</body>
</html>
