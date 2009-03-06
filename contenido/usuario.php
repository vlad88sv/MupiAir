<?
function displayUsers(){
   global $database,$session;
   if ( !$session->isAdmin() ) {
	   $where_userlevel = " WHERE userlevel <= 3 ";
   } else {
	   $where_userlevel = "";
   }
   $q = "SELECT * FROM ".TBL_USERS."$where_userlevel ORDER BY userlevel DESC;";
   $result = $database->query($q);
   /* Error occurred, return given name by default */
   $num_rows = mysql_numrows($result);
   if(!$result || ($num_rows < 0)){
      echo "Error mostrando la información";
      return;
   }
   if($num_rows == 0){
      /*Esto nunca deberia de pasar realmente...*/
      echo "¡No hay clientes/usuarios ingresados!";
      return;
   }
echo '
Nivel
<select name="FiltroNivel">
<option value="10">Todos
<option value="1">Usuario
<option value="2">Demo
<option value="3">Cliente
<option value="5">Vendedor
<option value="9">Administrador
</select>
<input type="button" value="Filtrar!" />
';

//echo '<br />';

echo '
Catorcena
'.Combobox_catorcenas("FiltroCatorcenas", Obtener_catorcena_cercana()).'
<input type="button" value="Filtrar!" />
';

echo '<hr />';
   echo '<table border="0">';
	if ( $session->isAdmin() ) {
	   echo "<tr><th>Código</th><th>Nombre</th><th>Nivel</th><th>Email</th><th>Última actividad</th><th>Acciones</th></tr>";
	} else {
		echo "<tr><th>Código</th><th>Nombre</th><th>Acciones</th></tr>";
	}
   for($i=0; $i<$num_rows; $i++){
	  $acciones='';
      $uname  = mysql_result($result,$i,"codigo");
      $nombre = mysql_result($result,$i,"nombre");
      if ($session->isAdmin()) $ulevel = mysql_result($result,$i,"userlevel");
      if ($session->isAdmin()) $email  = mysql_result($result,$i,"email");
      if ($session->isAdmin()) $time   = date("d-m-y\nh:ia", mysql_result($result,$i,"timestamp"));
      if ($session->isAdmin()) $acciones .= CREAR_LINK_GET("gestionar+pedidos:$uname", "Pedidos", "Le mostrara los pedidos realizados por este cliente y le dará la opción de agregar más.")."<br />";
      if ($session->isAdmin()) $acciones .= CREAR_LINK_GET("gestionar+pantallas:$uname", "Pantallas", "Le mostrara las pantallas en las cuales se encuentran colocados los pedidos.")."<br />";
      $acciones .= CREAR_LINK_GET("ver+ubicaciones:$uname", "Ubicaciones", "Le mostrara las ubicaciones de los MUPIS de este cliente.")."<br />";
      $acciones .= CREAR_LINK_GET("ver+estadisticas:$uname", "Estadísticas", "Le mostrara las estadísticas de este cliente.")."<br />";
	  if ($session->isAdmin()) $acciones .= "<hr />".CREAR_LINK_GET("ver+reportes:$uname", "Reporte", "Le generará un reporte sobre este cliente.");
      if ($session->isAdmin()) $uname = CREAR_LINK_GET("ver+cliente:".$uname, $uname, "Ver datos de este cliente");
      if ( $session->isAdmin() ) {
		echo "<tr><td>$uname</td><td>$nombre</td><td>$ulevel</td><td>$email</td><td>$time</td><td>$acciones</td></tr>";
	  } else {
		echo "<tr><td>$uname</td><td>$nombre</td><td>$acciones</td></tr>";
	  }
   }
   echo "</table><br />";
}

function CONTENIDO_admin() {
global $session, $database, $form;
echo '<h1>Centro de gestión de clientes</h1><hr />';
if($form->num_errors > 0){
   echo "<font size=\"4\" color=\"#ff0000\">"
       ."!*** Error con petición, por favor corregir</font><br><br>";
}
echo '<h2>Cientes registrados en el sistema</h2>';
displayUsers();

if ( $session->isAdmin() ) {
echo '<hr /><h2>Establecer permisos a cliente/usuario</h2>';
echo $form->error("upduser"); 
echo '
<form action="contenido/adminprocess.php" method="POST">
<table>
<tr>
<td>Código:
<input type="text" name="upduser" maxlength="30" value="'.$form->value("upduser").'"></td>
<td>
Nivel:<br />
<select name="updlevel">
<option value="1">Usuario
<option value="2">Demo
<option value="3">Cliente
<option value="5">Vendedor
<option value="9">Administrador
</select>
</td>
</tr>
</table>
<input type="hidden" name="subupdlevel" value="1">
<input type="submit" value="Actualizar Cliente/Usuario">

</form>';

echo '<hr /><h2>Quitar cliente/usuario</h2>';
echo $form->error("deluser");
echo '
<form action="contenido/adminprocess.php" method="POST">
<table>
<td>Código:
<input type="text" name="deluser"  maxlength="30" value="'.$form->value("deluser").'"></td>
<input type="hidden" name="subdeluser" value="1">
</table>
<input type="submit" value="Quitar Cliente/Usuario">
</form>';
}
}
?>
