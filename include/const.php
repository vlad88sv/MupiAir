<?
/*-----------------------DEFINICIONES-------------------*/
define("_NOMBRE_", "Eco Mupis");
define("_ACC_", "accion");
// Cosas de Sesion
require_once("data.php");
define("DB_SERVER", $DB_motor);
define("DB_USER", $DB_usuario);
define("DB_PASS", $DB_clave);
define("DB_NAME", $DB_base);
define("GOOGLE_MAP_KEY", $MapKey);
define("TBL_REGISTRY", "emupi_registro");
define("TBL_USERS", "emupi_usuarios");
define("TBL_ACTIVE_USERS",  "emupi_usuarios_activos");
define("TBL_ACTIVE_GUESTS", "emupi_visitantes_activos");
define("TBL_COMMENTS", "emupi_comentarios");
define("TBL_MUPI", "emupi_mupis");
define("TBL_MUPI_ORDERS", "emupi_mupis_pedidos");
define("TBL_MUPI_FACES", "emupi_mupis_caras");
define("TBL_EVENTS", "emupi_mupis_eventos");
define("TBL_GEOCODE_CACHE", "emupi_geocode_cache");
define("TBL_STREETS", "emupi_calles");
define("TBL_IMG","emupi_imagenes");
define("ADMIN_NAME", "admin");
define("GUEST_NAME", "Visitante");
define("ADMIN_LEVEL", 9);
define("USER_LEVEL",  1);
define("GUEST_LEVEL", 0);
define("TRACK_VISITORS", true);
define("USER_TIMEOUT", 10);
define("GUEST_TIMEOUT", 5);
define("COOKIE_EXPIRE", 60*60*24*100);
define("COOKIE_PATH", "/");
define("EMAIL_FROM_NAME", _NOMBRE_);
define("EMAIL_FROM_ADDR", "administrador@mupi.com.sv");
define("EMAIL_WELCOME", true);
define("ALL_LOWERCASE", false);
?>
