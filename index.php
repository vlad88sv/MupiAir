<?php
/*-----------------------INICIALIZACIÓN-------------------*/
error_reporting(E_STRICT | E_ALL);
/* Activar compresión de salida */
ob_start("ob_gzhandler"); 
/* Para los mapas de google */
require_once('include/maps/GoogleMapAPI.class.php');
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
/*Para manejo de catorcenas */
require_once ('include/fecha.php');
/* CONTENIDO */
/*Constructores de Menús, etc.*/
require_once("contenido/sub.php");
require_once("contenido/usuario.php");
require_once("contenido/ayuda+contacto.php");
require_once("contenido/usuario+recuperar_clave.php");
require_once("contenido/usuario+info.php");
require_once("contenido/usuario+ingresar.php");
require_once("contenido/usuario+registrar.php");
require_once("contenido/usuario+editar.php");
require_once("contenido/global+404.php");
require_once("contenido/global+estadisticas.php");
require_once("contenido/admin+reportes.php");
require_once("contenido/mupis+ubicaciones.php");
require_once("contenido/mupis+mupis.php");
require_once("contenido/mupis+pantallas.php");
require_once("contenido/mupis+pedidos.php");
require_once("contenido/mupis+calles.php");
require_once("contenido/mupis+eventos.php");
require_once("contenido/admin+comentarios.php");
require_once("contenido/mapa+referencias.php");
require_once("contenido/cargar+pantallas.php");
?>
<!-- Este comentario activa "quirks mode" en Internet Explorer ;) - Vlad.-->
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" xml:lang="es" lang="es">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<meta http-equiv="Content-Style-type" content="text/css" />
	<meta http-equiv="Content-Script-type" content="text/javascript" />
	<meta http-equiv="Content-Language" content="es" />
	<link rel="stylesheet" type="text/css" href="estilo.css" />
	<link rel="stylesheet" type="text/css" href="include/chrometheme/chromestyle.css" />
	<link rel="stylesheet" type="text/css" href="include/jquery.jgrowl.css" />
	<link rel="shortcut icon" href="favicon.ico" type="image/x-icon" />
	<link rel="start" href="/" />
	<title><? echo _NOMBRE_; ?> - Servicio publicitario</title>
	<meta name="keywords" content="MUPI, Publicidad, El Salvador" />
	<meta name="description" content="MUPI es un servicio publicitario." />
	<script src="include/jquery-1.3.1.min.js" type="text/javascript"></script>
	<script src="include/jquery.blockUI.js" type="text/javascript"></script>
	<script src="include/jquery.jgrowl.js" type="text/javascript"></script>
	<script src="include/jquery.toggleval.js" type="text/javascript"></script>
	<script type="text/javascript" src="include/tooltip.js"></script>
	<script type="text/javascript" src="include/chromejs/chrome.js">
	/***********************************************
	* Chrome CSS Drop Down Menu- (c) Dynamic Drive DHTML code library (www.dynamicdrive.com)
	* This notice MUST stay intact for legal use
	* Visit Dynamic Drive at http://www.dynamicdrive.com/ for full source code
	***********************************************/
	</script>
	<script>
	$().ajaxStart(function(){$.blockUI( { message: '<h1><img src="loader.gif" /> Su petición esta siendo procesada...</h1>' } );}).ajaxStop($.unblockUI);
	</script>
	<!--[if gte IE 5.5]>
	<![if lt IE 7]>
	<style type="text/css">
	#alImg1 img, #alImg2 img { filter:progid:DXImageTransform.Microsoft.Alpha(opacity=0); }
	#alImg1, #alImg2 { display: inline-block; }
	#alImg1 { filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(src='logo.png'); }
	#alImg2 { filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(src='ECO.png'); }
	</style>
	<![endif]>
	<![endif]-->
	<style>
	div.jGrowl div.aviso {background-color: #FF0000;color: #FFFFFF;}
	div.jGrowl div.smoke {background-color: #000000;color: #FFFFFF;-moz-border-radius:0px;-webkit-border-radius:0px;width:600px;overflow:hidden;}
	div.jGrowl div.globoclientes {background-color: #FFFA73;color: #000000;-moz-border-radius:0px;-webkit-border-radius:0px;width:600px;overflow:hidden;border:2px solid #000000}
	</style>
</head>
<body>
	<script>
		// Esto por alguna razón es lento en FF3.0/LNX - Shiretoko/LNX no tiene problemas, ni Opera/LNX.
		$.blockUI.defaults.applyPlatformOpacityRules = true;
        $.blockUI(
		{ message: '<img src="loader-white.gif" />Cargando, espere por favor...', css: { border: 'none', padding: '15px', backgroundColor: '#000', '-webkit-border-radius': '10px', '-moz-border-radius': '10px', opacity: '.5', color: '#fff', 'font-size': '15pt' }
        });
	</script>
	<div style="height:80px; margin-top:5px; margin-left:10px; margin-right:10px">
			<div style="float:left">
				<span id="alImg1" style="width:327px;height:75px;">
			 	<img src="logo.png" width="327" height="75" alt="CEPASA de C.V."/>
				</span>
			 </div>
			 <div style="float:right">
				<span id="alImg2" style="width:78px;height:75px;">
			 	<img src="ECO.png" width="78" height="75" alt="Eco Mupis" />
				</span>
			 </div>
	 </div>
	<div style="clear:both; margin:0">
	<?
	global $session;
	if ( $session->logged_in ) {
	echo INICIAR_MENUES();
	}
	?>
	</div>
	<div id="container">
		<div id="content">
			<?php CONTENIDO_mostrar_principal(); ?>
		</div>
		<div class="clear"></div>
		<?php 
		global $session; 
		if ( $session->logged_in && $session->isAdmin() ) {
		echo '<div id="abajo">';
		CONTENIDO_en_linea();
		echo '</div>';
		}
		?>
		<div class="clear"></div>
	</div>
	</div>
	</body>
</html>
<?php
ob_flush();
flush();
?>
<script>
setTimeout($.unblockUI, 10);
</script>
