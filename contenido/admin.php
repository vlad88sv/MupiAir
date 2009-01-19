<?
function displayUsers(){
   global $database;
   $q = "SELECT * FROM ".TBL_USERS." ORDER BY userlevel DESC;";
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
   echo '<table border="0">';
   echo "<tr><th>Código</th><th>Nombre</th><th>Nivel</th><th>Email</th><th>Última actividad</th><th>Acciones</th></tr>";
   for($i=0; $i<$num_rows; $i++){
      $uname  = mysql_result($result,$i,"codigo");
      $nombre = mysql_result($result,$i,"nombre");
      $ulevel = mysql_result($result,$i,"userlevel");
      $email  = mysql_result($result,$i,"email");
      $time   = date("d-m-y\nh:ia", mysql_result($result,$i,"timestamp"));
      $verMUPIS = CREAR_LINK_GET("gestionar+pantallas:$uname", "Pantallas", "Le mostrara las Pantallas contratadas por este cliente.");
      $reporte = CREAR_LINK_GET("reportes:$uname", "Reporte", "Le generará un reporte sobre este cliente.");
      $uname = CREAR_LINK_GET("ver+cliente:".$uname, $uname, "Ver datos de este cliente");
      echo "<tr><td>$uname</td><td>$nombre</td><td>$ulevel</td><td>$email</td><td>$time</td><td>$verMUPIS<hr />$reporte</td></tr>";
   }
   echo "</table><br>\n";
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

echo '<hr /><h2>Establecer permisos a cliente/usuario</h2>';
echo $form->error("upduser"); 
echo '
<form action="include/adminprocess.php" method="POST">
<table>
<tr>
<td>Código:
<input type="text" name="upduser" maxlength="30" value="'.$form->value("upduser").'"></td>
<td>
Nivel:<br />
<select name="updlevel">
<option value="1">Cliente/Usuario
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
?>
