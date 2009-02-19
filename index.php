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
	<link rel="shortcut icon" href="favicon.ico" type="image/x-icon" />
	<link rel="start" href="/" />
	<title><? echo _NOMBRE_; ?> - Servicio publicitario</title>
	<meta name="keywords" content="MUPI, Publicidad, El Salvador" />
	<meta name="description" content="MUPI es un servicio publicitario." />
	<script src="include/jquery-1.3.1.min.js" type="text/javascript"></script>
	<script type="text/javascript" src="include/tooltip.js"></script>
	<script type="text/javascript" src="include/chromejs/chrome.js">
	/***********************************************
	* Chrome CSS Drop Down Menu- (c) Dynamic Drive DHTML code library (www.dynamicdrive.com)
	* This notice MUST stay intact for legal use
	* Visit Dynamic Drive at http://www.dynamicdrive.com/ for full source code
	***********************************************/
	</script>
	<script>
	function grayOut(vis, options) {
  // Pass true to gray out screen, false to ungray
  // options are optional.  This is a JSON object with the following (optional) properties
  // opacity:0-100         // Lower number = less grayout higher = more of a blackout 
  // zindex: #             // HTML elements with a higher zindex appear on top of the gray out
  // bgcolor: (#xxxxxx)    // Standard RGB Hex color code
  // grayOut(true, {'zindex':'50', 'bgcolor':'#0000FF', 'opacity':'70'});
  // Because options is JSON opacity/zindex/bgcolor are all optional and can appear
  // in any order.  Pass only the properties you need to set.
  var options = options || {}; 
  var zindex = options.zindex || 50;
  var opacity = options.opacity || 70;
  var opaque = (opacity / 100);
  var bgcolor = options.bgcolor || '#000000';
  var dark=document.getElementById('darkenScreenObject');
  if (!dark) {
    // The dark layer doesn't exist, it's never been created.  So we'll
    // create it here and apply some basic styles.
    // If you are getting errors in IE see: http://support.microsoft.com/default.aspx/kb/927917
    var tbody = document.getElementsByTagName("body")[0];
    var tnode = document.createElement('div');           // Create the layer.
        tnode.style.position='absolute';                 // Position absolutely
        tnode.style.top='0px';                           // In the top
        tnode.style.left='0px';                          // Left corner of the page
        tnode.style.overflow='hidden';                   // Try to avoid making scroll bars            
        tnode.style.display='none';                      // Start out Hidden
        tnode.id='darkenScreenObject';                   // Name it so we can find it later
    tbody.appendChild(tnode);                            // Add it to the web page
    dark=document.getElementById('darkenScreenObject');  // Get the object.
  }
  if (vis) {
    // Calculate the page width and height 
    if( document.body && ( document.body.scrollWidth || document.body.scrollHeight ) ) {
        var pageWidth = document.body.scrollWidth+'px';
        var pageHeight = document.body.scrollHeight+'px';
    } else if( document.body.offsetWidth ) {
      var pageWidth = document.body.offsetWidth+'px';
      var pageHeight = document.body.offsetHeight+'px';
    } else {
       var pageWidth='100%';
       var pageHeight='100%';
    }   
    //set the shader to cover the entire page and make it visible.
    dark.style.opacity=opaque;                      
    dark.style.MozOpacity=opaque;                   
    dark.style.filter='alpha(opacity='+opacity+')'; 
    dark.style.zIndex=zindex;        
    dark.style.backgroundColor=bgcolor;  
    dark.style.width= pageWidth;
    dark.style.height= pageHeight;
    dark.style.display='block';                          
  } else {
     dark.style.display='none';
  }
}
	</script>
	<!--[if IE 6]>
	<style type="text/css">
	/* some css fixes for IE browsers */
	html {overflow-y:hidden;}
	body {overflow-y:auto;}
	#bg {position:absolute; z-index:-1;}
	#container {position:static;}
	</style>
	<![endif]-->
</head>
<body>
<div id="cargando"><img src="loader.gif"> Por favor espere...<hr /></div>
<script>grayOut(true)</script>
<?php flush(); ?>
<?php ob_start(); ?>
<div id="container">
	<table style="border:0">
		 <tr>
			 <td style="border:0">
			 	<?php CONTENIDO_mostrar_logo(); ?>
			 </td>
			 <td width="100px" style="border:0">
			 	<?php echo IMAGEN ("ECO.gif","Eco Mupis", "200px", "150px"); ?>
			 </td>
		 </tr>
	 </table>
	<br />
	<?
	global $session;
	if ( $session->logged_in ) {
	echo INICIAR_MENUES();
	}
	?>
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
	</div>
	</body>
</html>
<?php ob_flush(); flush(); ?>
<script>
window.setTimeout(function() {
 $("#cargando").html("")
 grayOut(false)
}, 100);
</script>
