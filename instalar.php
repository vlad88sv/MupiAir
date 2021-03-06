<?php
if( !file_exists("include/data.php") ) {
	if ( @!chmod("include/", 0777) ) exit ("woops!, 'include/data.php' no existe e include/ no pudo ser Chmodeado");
	if ( @!touch ("include/data.php") ) exit ("woops!, 'include/data.php' no existe y no pudo ser creado");
}
require_once("include/const.php");
error_reporting(E_STRICT | E_ALL);

function CREAR_TBL($TBL,$QUERY) {
global $link;
if ( isset($_POST['reiniciar']) ) {
@mysql_query("DROP TABLE IF EXISTS $TBL;", $link) or die('!->No se pudo eliminar la tabla "'.$TBL.'".<br /><pre>' . mysql_error() . '</pre>');
}
$QUERY = "CREATE TABLE IF NOT EXISTS ". $TBL . " (" . $QUERY . ");";
$x = @mysql_query($QUERY, $link) or die('!->No se pudo crear la tabla "'. $TBL .'".<br /><pre>' . mysql_error() . '</pre>');
if ($x) {echo "- Creada: '$TBL'<br />";}
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="es" lang="es">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<meta http-equiv="Content-Style-type" content="text/css" />
	<meta http-equiv="Content-Script-type" content="text/javascript" />
	<meta http-equiv="Content-Language" content="es" />
	<link rel="StyleSheet" href="estilo.css" type="text/css" />
	<link rel="shortcut icon" href="/favicon.ico" type="image/x-icon" />
	<link rel="start" href="/" />
	<title>Instalador de MUPI</title>
</head>
<body>
<div id="centerwrapper">
<div id="content">

<?
$DB_motor = isset($DB_motor) ? $DB_motor : "localhost";
$DB_usuario = isset($DB_usuario) ? $DB_usuario : "";
$DB_base = isset($DB_base) ? $DB_base : "";
$MapKey = isset($MapKey) ? $MapKey : "";
if (!isset($_POST['instalar'])) {
echo '<h1>'._NOMBRE_.' - Instalador</h1><br />
<form action="'. $_SERVER['PHP_SELF'] .'" method="post">
<table border="0">
<tr><td colspan="2"><h2>Configuración MySQL</h2></td></tr>
<tr>
<td>Dirección del servidor MySQL:</td>
<td><input type="text" name="motor"  maxlength="100" size="20" value="localhost" /></td>
</tr>
<tr>
<td>Base de datos a utilizar:</td>
<td><input type="text" name="base"  maxlength="100" size="20" value="'.$DB_base.'" /></td>
</tr>
<tr>
<td>Usuario:</td>
<td><input type="text" name="usuario"  maxlength="100" size="20" value="'.$DB_usuario.'" /></td>
</tr>
<tr>
<td>Clave:</td>
<td><input type="password" name="clave"  maxlength="30" size="20" value="" /></td>
</tr>
<tr><td colspan="2"><h2>Google Maps</h2></td></tr>
<tr>
<tr><td colspan="2"><blockquote>Puede utilizar la siguiente Google Map API Key si es una instalación en Localhost:<br />ABQIAAAASN5hkWhvednkFD23rB1SbBT2yXp_ZAY8_ufC3CFXhHIE1NvwkxSp-JAzoCx9P7_e-8fs8e7L37rPSw</blockquote></td></tr>
<td>Google Map API Key:</td>
<td><input type="text" name="MapKey"  maxlength="100" size="20" value="'.$MapKey.'" /></td>
</tr>
<tr><td colspan="2"><h2>Administración</h2></td></tr>
<tr>
<td>Nombre Administrador:</td>
<td><input type="text" name="admin"  maxlength="100" size="20" value="" /></td>
</tr>
<tr>
<td>Correo electrónico:</td>
<td><input type="text" name="email"  maxlength="100" size="20" value="" /></td>
</tr>
<tr>
<td>Clave:</td>
<td><input type="password" name="admin_clave"  maxlength="20" size="20" value="" /></td>
</tr>
<tr>
<td>Clave (repetir):</td>
<td><input type="password" name="admin_clave2"  maxlength="20" size="20" value="" /></td>
</tr>
</table>
<br />
<input type="checkbox" name="reiniciar"  value="" />Destruir tablas si existen
<br />
<input type="submit" name="instalar" value="Instalar" />
</form>
';
} else {
echo '<b>'._NOMBRE_.' - Instalador : Instalando</b><br />';
if ($_POST['admin_clave'] != $_POST['admin_clave2']) {
echo '<h3>+Las contraseñas no coinciden.</h3><br />
<a href="javascript:history.back();">Regresar al instalador</a>';
}
echo '<h3>+Creando conexión a la base de datos...</h3><br />';
$link = @mysql_connect($_POST['motor'], $_POST['usuario'], $_POST['clave']) or die('Por favor revise sus datos, puesto que se produjo el siguiente error:<br /><pre>' . mysql_error() . '</pre>');
mysql_select_db($_POST['base'], $link) or die('!->La base de datos seleccionada "'.$_POST['base'].'" no existe');
echo '- Base de datos conectada...<br />';
echo '<h3>+Creando Archivo con datos de conexión...</h3><br />';
$fh = @fopen("include/data.php", 'w') or die("No se pudo escribir 'data.php'.<br />");
if ($fh) {
$Datos = "<?php ";
fwrite($fh, $Datos);
$Datos = '$DB_motor = "'. $_POST['motor'] .'";' . '$DB_usuario = "'. $_POST['usuario'] . '";'. '$DB_clave = "'. $_POST['clave'] .'";' . '$DB_base = "'. $_POST['base'] .'";' . '$MapKey = "'. $_POST['MapKey'] .'";';
fwrite($fh, $Datos);
$Datos = " ?>\n";
fwrite($fh, $Datos);
fclose($fh);
}
echo '- Creado<br />';
echo '<h3>+Creando Tablas...</h3><br />';
if ( isset($_POST['reiniciar']) ) { echo '->Se solicitó destruir y crear tablas<br />'; }
/*
Tabla que contiene los datos de los clientes.
Esta tabla solo contendrá los datos relaventes a la identificación y contacto de cliente
*/
$q="codigo VARCHAR(100) primary key, clave VARCHAR(32) not null, nombre VARCHAR(32) not null, razon VARCHAR(100) not null, email VARCHAR(50), telefono1 VARCHAR(20) not null, telefono2 VARCHAR(20), telefono3 VARCHAR(20), logotipo VARCHAR(255), notas VARCHAR(500), userlevel tinyint(1) unsigned not null, userid VARCHAR(32), timestamp int(11) unsigned not null";
CREAR_TBL(TBL_USERS, $q);

/*
Tabla que contiene a los USUARIOS activos
NOTA: En un futuro se va a unir con TBL_ACTIVE_GUESTS
*/
$q="codigo VARCHAR(30) primary key, timestamp int(11) unsigned not null";
CREAR_TBL(TBL_ACTIVE_USERS, $q);

/*
Tabla que contiene a los VISITANTES activos
NOTA: En un futuro se va a unir con TBL_ACTIVE_USERS
*/
$q="ip VARCHAR(15) primary key, timestamp int(11) unsigned not null";
CREAR_TBL(TBL_ACTIVE_GUESTS, $q);

/*
Tabla que contiene la descripción de cada mupi habido y por haber
No contiene información sobre quién lo arrenda ni desde cuando, solo lo ubica en el mapa.
Ademas contiene la direccion (path) a la imagen/foto generica de este MUPI en particular.
NOTA: El campo evento tendrá el código de algún evento ocurrido (si hubiera) a este MUPI.
Si el evento le ocurré al MUPI en general (ej. le choca un automovil) entonces deberá
asignarle el evento al MUPI.
Si el evento llegará a afectar a una sola de las pantallas, entonces deberá relacionar dicho
evento con la pantalla afectada y no con el MUPI. Ver detalles en tabla TBL_MUPI_FACES.
*/
$q="id_mupi INT NOT NULL AUTO_INCREMENT PRIMARY KEY, codigo_mupi VARCHAR(100) NOT NULL, direccion VARCHAR(255), foto_generica VARCHAR(255), lon DOUBLE DEFAULT NULL, lat DOUBLE DEFAULT NULL, codigo_evento VARCHAR(50), codigo_calle VARCHAR(255)";
CREAR_TBL(TBL_MUPI, $q);

/*
Tabla que contiene la descripción de cada pedido (compra) realizada.
La finalidad es no repetir los mismos datos para cada pantalla, si no que se enlazaría cada pantalla con un código de pedido que le indicaría el cliente y la foto que debe llevar.
*/
$q="codigo_pedido INT NOT NULL AUTO_INCREMENT PRIMARY KEY, codigo VARCHAR(100), catorcena_inicio int(11), catorcena_fin int(11), foto_pantalla VARCHAR(255), costo  INT, descripcion VARCHAR(255)";
CREAR_TBL(TBL_MUPI_ORDERS, $q);

/*
Tabla que contiene la descripción de cada calle.
La finalidad es poder enlazar con la tabla de mupis para que puedan determinar que MUPIS estan sobre la misma calle.
*/
$q="codigo_calle INT NOT NULL AUTO_INCREMENT PRIMARY KEY, ubicacion VARCHAR(255), grupo_calle VARCHAR(255), impactos INT";
CREAR_TBL(TBL_STREETS, $q);

/*
Esta tabla es una tabla relacionada con TBL_MUPI en el sentido de que a travez de ella se determinan las pantallas alquiladas
Ademas de su fecha de alquiler, quien es su "alquilador", que tipo de pantalla es (peatonal/vehicular) y la foto actual del MUPI.
NOTA: Dado que el daño a un MUPI (llamado "evento") puede producirse en una sola pantalla (ej. el pintando callejero de una pantalla)
entonces también los eventos pueden ser asociados a estas (pantallas).
*/
$q="id_pantalla INT NOT NULL AUTO_INCREMENT PRIMARY KEY, tipo_pantalla tinyint(1), codigo_mupi VARCHAR(100), codigo_pedido INT, codigo_evento VARCHAR(50), foto_real VARCHAR(255), catorcena int(11)";
CREAR_TBL(TBL_MUPI_FACES, $q);

/*
La tabla de eventos contiene la fecha cuando sucedió el evento y la descripción del evento.
No tiene campo para decir que MUPI afectó porque al contrario es en las tablas TBL_MUPI y
TBL_MUPI_FACES en las que se relaciona con una entrada de esta tabla.
*/

$q="id_evento INT NOT NULL AUTO_INCREMENT PRIMARY KEY, timestamp INT(11), categoria VARCHAR(100), afectado VARCHAR(100), descripcion_evento VARCHAR(500), foto_evento INT(11)";
CREAR_TBL(TBL_EVENTS, $q);

/*
Esta tabla es un cache de GEOCODES. Requerido por PHPGoogleMapApi para optimizar la búsqueda de los mismos GEOCODES.
Ademas puede ser util en un futuro para referenciar los MUPIS por su dirección en lugar de sus coordenadas.
*/
$q="address VARCHAR(255) NOT NULL PRIMARY KEY, lon float default NULL, lat float default NULL";
CREAR_TBL(TBL_GEOCODE_CACHE, $q);

/*
Esta tabla funciona como una versión básica de un registro de pares.
Servirá para llevar las estadísticas de todo el sitio:
-Numero de visitas
*/
$q="id_registro INT NOT NULL AUTO_INCREMENT PRIMARY KEY, clave VARCHAR(255), valor TEXT, detalle TEXT, autor VARCHAR(255), timestamp int(11) unsigned not null";
CREAR_TBL(TBL_REGISTRY, $q);

/*
Esta tabla albergara todas las imagenes usadas.
Se diferenciaran por su categoria y podrán verla a travez de su ID.
*/
$q="id_imagen INT NOT NULL AUTO_INCREMENT PRIMARY KEY, categoria VARCHAR(100), mime VARCHAR(100)";
CREAR_TBL(TBL_IMG, $q);

/*
Esta tabla se encarga de llevar los comentarios de los usuarios.
*/
$q="id_comentario INT NOT NULL AUTO_INCREMENT PRIMARY KEY, codigo TEXT, comentario VARCHAR(500), timestamp int(11), tipo tinyint(1)";
CREAR_TBL(TBL_COMMENTS, $q);

/*
Esta tabla se encarga de llevar las referencias en calle de los Eco Mupis
*/
$q="id_referencia INT NOT NULL AUTO_INCREMENT PRIMARY KEY, lon DOUBLE NULL, lat DOUBLE default NULL, codigo_calle INT(11), imagen_referencia INT(11)";
CREAR_TBL(TBL_REFS, $q);

echo '<h3>+Creando usuario '.$_POST['admin'].'...</h3><br />';
$q = "INSERT INTO ".TBL_USERS." VALUES ('".$_POST['admin'] . "', '" . md5($_POST['admin_clave']) . "', 'Administrador Principal', 'Administrador', '" . $_POST['email'] . "', '0','','','','Creado durante de la instalación', 9, 0," . time() . ") ON DUPLICATE KEY UPDATE codigo=VALUES(codigo), clave=VALUES(clave), email=VALUES(email)";
@mysql_query($q, $link)  or die('!->No se pudo insertar el usuario<br /><pre>' . mysql_error() . '</pre>');;
echo '- Creado<br />';
mysql_close($link);
echo '<br /><b>Instalación completa</b><br />';
}
?>
</div>
</div>
</body>
</html>
