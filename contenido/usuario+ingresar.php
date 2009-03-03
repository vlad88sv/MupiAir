<?php
function CONTENIDO_usuario_ingresar() {

	global $session, $form;
	
	/* Ya se encuentra registrado */
	if($session->logged_in){
		/* Limpiamos todo lo que podamos */
		unset($_SESSION['reguname']);
		unset($_SESSION['regsuccess']);
		
		/* Lo mandamos a su respectiva página de inicio	*/
		
		switch ( $session->userlevel ) {
			case ADMIN_LEVEL:
			case CLIENT_LEVEL:
			CONTENIDO_global_estadisticas("");
			break;
			case SALESMAN_LEVEL:
			case USER_LEVEL:
			CONTENIDO_mupis_ubicaciones();
			break;
		}
		return;
	}
	echo '<h1>Iniciar sesión en el sistema de '._NOMBRE_.'</h1><hr>';
	/* Fallo en el registro */
	if(isset($_SESSION['regsuccess']) && $_SESSION['regsuccess'] == false){
		echo Mensaje("Datos de acceso incorrectos, por favor intente de nuevo.",_M_ERROR);
	}
	/* Empezar en limpio */
	unset($_SESSION['regsuccess']);
	echo SCRIPT('
	$("input[name=\'codigo\']").toggleVal({
    populateFrom: "custom",
    text: "",
	focusClass: "hasFocus",
    changedClass: "isChanged"
	});
	$("input[name=\'clave\']").toggleVal({
    populateFrom: "custom",
    text: "",
	focusClass: "hasFocus",
    changedClass: "isChanged"
	});
');
?>
<form action="include/x.php" method="post">
<table>
<tr>
<td class="limpio" width="10%" >Código:</td>
<td class="limpio"><input type="text" name="codigo" maxlength="100" size="30" value="" /></td>
</tr>
<tr>
<td class="limpio">Clave:</td>
<td class="limpio"><input type="password" id="clave" name="clave" maxlength="30" size="30" value="" /></td>
</tr>
<tr>
<td class="limpio">¿Recordarme?:</td>
<td class="limpio"><input type="checkbox" name="remember" <? if($form->value("remember")){ echo "checked"; } ?> onMouseOver="toolTip('Recordar sus datos de acceso para esta maquina.<br />Se recomienda <b>no</b> utilizar en equipos compartidos.')" onMouseOut="toolTip()" /></td>
</tr>
<tr><td class="limpio"></td><td class="limpio"><br /><input type="submit" name="ingresar" value="Clic aquí para ingresar al sistema Eco Mupis" /></td></tr>
</table>
<input type="hidden" name="sublogin" value="1">
</form>
<hr /><? echo "Si ha olvidado su clave por favor haga clic en el enlace: " . CREAR_LINK_GET("rpr+clave", "Recuperar clave", "Clic en este enlace para intentar recuperar su clave"); ?></a>
</ul>
<?
}
?>
