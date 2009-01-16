<?php
DEFINE ("CORREO_ADMIN", "vladimiroski@gmail.com");
function CONTENIDO_ayuda_contacto() {
if ( isset($_POST['enviar']) ) {
      $from = "From: ".$session->codigo." <".$session->userinfo['email'].">";
      $subject = "¡Comentario del Sistema de Horarios!";
      $body = $_POST['mensaje'].",\n\n".
		"atte. $session->codigo";
	if (mail(CORREO_ADMIN,$subject,$body,$from)) {
		echo  "<h3>Su mensaje fue enviado exitosamente</h3>";
		return;
	}else{
		echo  "<h3>Su mensaje no pudo ser enviado, intente mas tarde</h3>";
		return ;
	}
}

	echo '
	<h1>Sistema de Horarios - Contactar el administrador</h1><br />
	<form action="./?'._ACC_."=ayuda+contacto".'" method="post">
	<table border=0>
	<tr>
	<td>Mensaje:</td>
	<td><textarea name="mensaje" style="width: 100%;"  rows="10" cols="40"></textarea></td>
	</tr>
	<tr>
	<td></td><td><input type="submit" name="enviar" value="Enviar" /></td>
	</tr>
	</table>
	</form>
	</ul>
	';
}
?>
