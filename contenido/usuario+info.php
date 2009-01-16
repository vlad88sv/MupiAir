<?
function CONTENIDO_usuario_info($req_user){
global $session, $database;
if ( $session->logged_in && !$req_user )
$req_user = $session->codigo;

/* Logged in user viewing own account */
if(strcmp($session->codigo,$req_user) == 0){
   echo "<h1>Mi cuenta</h1><hr />";
}
/* Visitor not viewing own account */
else{
   echo "<h1>Información del cliente</h1><hr />";
}

/* Display requested user information */
$req_user_info = $database->getUserInfo($req_user);
echo '<table>';
echo '<tr><td><b>Código fiscal:</b></td><td  style="width: 70%;" >'.$req_user_info['codigo']."</td></tr>";
echo "<tr><td><b>Nombre de cliente:</b></td><td>".$req_user_info['nombre']."</td></tr>";
echo "<tr><td><b>Correo Electronico (e-mail):</b></td><td>".$req_user_info['email']."</td></tr>";
echo "<tr><td><b>Razón social:</b></td><td>".$req_user_info['razon']."</td></tr>";
echo "<tr><td><b>Teléfono #1:</b></td><td>".$req_user_info['telefono1']."</td></tr>";
echo "<tr><td><b>Teléfono #1:</b></td><td>".$req_user_info['telefono2']."</td></tr>";
echo "<tr><td><b>Teléfono #3:</b></td><td>".$req_user_info['telefono3']."</td></tr>";
echo "<tr><td><b>Notas u otras observaciones:</b></td><td>".$req_user_info['notas']."</td></tr>";
echo "</table>";

if($session->isAdmin()){
   echo "<hr />".CREAR_LINK_GET("editar+usuario:$req_user", "Editar información de la cuenta", "Modifica los detalles de esta cuenta");
}
}
?>
