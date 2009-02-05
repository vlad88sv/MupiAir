<?
function CONTENIDO_usuario_editar($usuario) {
global $database, $session;
if(!$session->isAdmin()){
   echo "<h1>Acceso denegado</h1>";
   return;
} else {
if ( !$usuario ) { $usuario = $session->codigo; }
//Verificamos que el usuario que quiere editar exista...
if(!$database->codigoTaken($usuario)) {
   echo "<h1>El usuario '$usuario' no existe</h1>";
   return;
}
}
/* Si esta en proceso de edición */
if(isset($_SESSION['useredit'])){
   unset($_SESSION['useredit']);
   
   echo "<h2>¡Cuenta de cliente editada exitosamente!</h2><hr />";
   echo "<h3>La cuenta de ".$_SESSION['user_edic'] ." ha sido exitosamente actualizada.</h3>";
   return;
}
$_SESSION['user_edic'] = $usuario;
$req_user_info = $database->getUserInfo($_SESSION['user_edic']);
?>
<h1>Editar cuenta del Cliente: <? echo $_SESSION['user_edic']; ?></h1>
<form action="include/x.php" enctype="multipart/form-data" method="POST">
<table  border="0" cellspacing="0">
<tr>
<td>Nueva clave:</td>
<td><input type="password" name="newpass" maxlength="30" style="width: 98%;" value=""></td>
</tr>

<tr>
<td>Código fiscal:</td>
<td><input type="text" name="codigo" maxlength="100" style="width: 98%;" value="<? echo $req_user_info['codigo']; ?>"></td>
</tr>

<tr>
<td>Nombre de cliente:</td>
<td><input type="text" name="nombre" maxlength="100" style="width: 98%;" value="<? echo $req_user_info['nombre']; ?>"></td>
</tr>

<tr>
<td>Email:</td>
<td><input type="text" name="email" maxlength="50" style="width: 98%;" value="<? echo $req_user_info['email']; ?>"></td>
</tr>

<tr>
<td>Razón social:</td>
<td><input type="text" name="razon" maxlength="100" style="width: 98%;" value="<? echo $req_user_info['razon']; ?>"></td>
</tr>

<tr>
<td>Teléfono #1:</td>
<td><input type="text" name="telefono1" maxlength="100" style="width: 98%;" value="<? echo $req_user_info['telefono1']; ?>"></td>
</tr>

<tr>
<td>Teléfono #2:</td>
<td><input type="text" name="telefono2" maxlength="100" style="width: 98%;" value="<? echo $req_user_info['telefono2']; ?>"></td>
</tr>

<tr>
<td>Teléfono #3:</td>
<td><input type="text" name="telefono3" maxlength="100" style="width: 98%;" value="<? echo $req_user_info['telefono3']; ?>"></td>
</tr>

<?php
$OnChangePantalla = '';
if ( $req_user_info['telefono3'] ) {
echo '
<tr>
<td>Conservar logotipo con Id. '.$req_user_info['logotipo'].':</td>
<td><span id="CampoConservarLogotipo"><input type="checkbox" name="ConservarLogotipo" value="'.$req_user_info['logotipo'].'" checked="checked"></span></td>
</tr>
';
echo '<input type="hidden" name="ConservarLogotipo2" value="'.$req_user_info['logotipo'].'">';
$OnChangePantalla = 'onchange="document.getElementById(\'CampoConservarLogotipo\').innerHTML=\'Se reemplazará la imagen actual con la seleccionada\'"';
}
?>

<tr>
<td>Logotipo:</td>
<td><input type="file" name="logotipo" <?php echo $OnChangePantalla ?>/></td>
</tr>

<tr>
<td>Notas u otras observaciones:</td>
<td><textarea name="notas" rows="5" cols="10" style="width: 98%;" ><? echo $req_user_info['notas']; ?></textarea></td>
</tr>

<tr>
<td></td>
<td><input type="submit" value="Editar cuenta"></td>
</tr>
</table>
<input type="hidden" name="subedit" value="1">
<input type="hidden" name="username" value="<?echo $_SESSION['user_edic']; ?>">
</form>

<?
}
?>
