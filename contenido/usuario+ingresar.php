<?php
function CONTENIDO_usuario_ingresar() {

	global $session, $form;
	
	/* Ya se encuentra registrado */
	if($session->logged_in){
		echo '<h1>Sesión iniciada en el sistema de '._NOMBRE_.'</h1><hr>';
		echo "¡Bienvenido! <b>$session->codigo</b>, Ud. ha ingresado al sistema.<br />";
		if($session->isAdmin()){
		echo '<br />Se ha habilitado el menú "Herramientas".<br />';
		}
		echo '<hr />';
		echo CREAR_LINK_GET("ver+cliente", "Ver los datos de mi cuenta" , "Avanzar hacia su perfil");
		/* Limpiamos todo lo que podamos */
		unset($_SESSION['reguname']);
		unset($_SESSION['regsuccess']);
		return;
	
	}
	echo '<h1>Iniciar sesión en el sistema de '._NOMBRE_.'</h1><hr>';
	/* Fallo en el registro */
	if(isset($_SESSION['regsuccess']) && $_SESSION['regsuccess'] == false){
		echo "<h3>Error - Por favor intentelo de nuevo.</h3><hr>";
	}
	/* Empezar en limpio */
	unset($_SESSION['regsuccess']);
?>
<form action="include/x.php" method="post">
<table border=0>
<tr>
<td>Código <acronym title="Su código fiscal">(?)</acronym>:</td>
<td><input type="text" name="codigo" maxlength="100" size="30" value="" /></td>
</tr>
<tr>
<td>Clave <acronym title="Su clave (contraseña) secreta asociada con su código fiscal">(?)</acronym>:</td>
<td><input type="password" name="clave" maxlength="30" size="30" value="" /></td>
</tr>
<tr>
<td>¿Recordarme?:</td>
<td><input type="checkbox" name="remember" <? if($form->value("remember")){ echo "checked"; } ?> /></td>
</tr>
</table>
<input type="hidden" name="sublogin" value="1">
<input type="submit" name="ingresar" value="Ingresar" />
</form>
<br /><? echo "Si ha olvidado su clave por favor haga clic en el enlace: " . CREAR_LINK_GET("rpr+clave", "Recuperar clave", "Clic en este enlace para intentar recuperar su clave"); ?></a>
</ul>
<?
}
?>
