<?php
function CONTENIDO_usuario_registrar(){
global $form;
if ( isset( $_SESSION['regsuccess'] ) && isset( $_SESSION['reguname'] ) ) {
	if( $_SESSION['regsuccess'] == true ) {
		if( $_SESSION['regsuccess'] ){
			echo "Cliente registrado. '<b>".$_SESSION['reguname']."</b>' ha sido agregado a la base de datos.</p>";
		}else{
			echo "Registro fallido de cliente";
			echo "<p>Lo sentimos pero el registro para el cliente '<b>".$_SESSION['reguname']."</b>' a fallado.</p>";
		}
		echo '<hr />';
	} else {
	if( $form->num_errors > 0 ){
		 echo $form->num_errors ." error(es) encontrado(s)<br />";
		 echo print_ar($form->getErrorArray(), true);
	}
	}
}
unset( $_SESSION['reguname'] );
unset( $_SESSION['regsuccess'] );
?>
<h2>Registro de Clientes</h2><hr />
<form action="include/x.php" enctype="multipart/form-data" method="POST">
<table>
<tr><td>Código fiscal:</td><td><input type="text" name="codigo" maxlength="50" style="width: 100%;" value="<? echo $form->value("codigo"); ?>"></td></tr>
<tr><td>Clave (contraseña):</td><td><input type="password" name="clave" style="width: 100%;" maxlength="30" value="<? echo $form->value("clave"); ?>"></tr>
<tr><td>Nombre del cliente:</td><td><input type="text" name="nombre" style="width: 100%;" maxlength="100" value="<? echo $form->value("nombre"); ?>"></td></tr>
<tr><td>Razón social:</td><td><input type="text" name="razon" style="width: 100%;" maxlength="50" value="<? echo $form->value("razon"); ?>"></td></tr>
<tr><td>Correo Electrónico (e-mail):</td><td><input type="text" name="email" style="width: 100%;" maxlength="50" value="<? echo $form->value("email"); ?>"></td></tr>
<tr><td>Teléfono #1:</td><td><input type="text" name="telefono1" style="width: 100%;" maxlength="50" value="<? echo $form->value("telefono1"); ?>"></td></tr>
<tr><td>Teléfono #2:</td><td><input type="text" name="telefono2" style="width: 100%;" maxlength="50" value="<? echo $form->value("telefono2"); ?>"></td></tr>
<tr><td>Teléfono #3:</td><td><input type="text" name="telefono3" style="width: 100%;" maxlength="50" value="<? echo $form->value("telefono3"); ?>"></td></tr>
<tr><td>Logotipo:</td><td><input type="file" name="logotipo" ></td></tr>
<tr><td>Notas u otras observaciones:</td><td><TEXTAREA style="width: 100%;" name="notas" rows="5" cols="80"><? echo $form->value("notas"); ?></TEXTAREA></td></tr>
</table>
<input type="submit" value="Registrar">
<input type="hidden" name="subjoin" value="1">
</form>
<?
}
?>
