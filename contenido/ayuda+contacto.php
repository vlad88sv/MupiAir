<?php
DEFINE ("CORREO_ADMIN", "crystalworld70@gmail.com");
function CONTENIDO_ayuda_contacto() {
	global $database, $session;
	echo '<h1>Sistema de Horarios - Contactar el administrador</h1><hr />';
	if ( isset($_POST['enviar']) ) {
		$tipo = isset($_POST['alcance']) ? 1 : 0;
		$q = "INSERT INTO ".TBL_COMMENTS." (codigo, comentario, timestamp, tipo) VALUES ('".$session->codigo."', '".$_POST['mensaje']."', ".time().",".$tipo.");";
		$result = $database->query($q);
		
		$from = "From: ".$session->codigo." <".$session->userinfo['email'].">";
		$subject = "¡Comentario del Sistema de Horarios!";
		$body = $_POST['mensaje'].",\n\n".
		"atte. $session->codigo";
		mail(CORREO_ADMIN,$subject,$body,$from);
		if ( $result ) {
			echo  "<h2>Su mensaje fue enviado exitosamente</h2>";
			return;
		}else{
			echo  "<h2>Su mensaje no pudo ser enviado, intente mas tarde</h2>";
			return ;
		}
	}

	echo '
	<form action="./?'._ACC_."=ayuda+contacto".'" method="post">
	<table border=0>
	<tr>
	<td><textarea name="mensaje" style="width: 100%;"  rows="10" cols="40"></textarea></td>
	</tr>
	<tr>
	<td>
	<input type="checkbox" name="alcance" value="publico" />
	Deseo hacer este comentario público.
	</td>
	</tr>
	<tr>
	<td><input type="submit" name="enviar" value="Enviar" /></td>
	</tr>
	</table>
	</form>
	</ul>
	';
}
?>
