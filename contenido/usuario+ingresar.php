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
			case DEMO_LEVEL:
			echo '
			<p style="width:40em">
			<b>Bienvenido al sistema <em>Mupiair</em> de Ecomupis</b>.<br />
			El usuario y la clave de acceso que Ud. recibió le ha permitido ingresar al sistema en modo de demostración y en base a ello se le ha otorgado acceso a una parte del sistema.<br /><br />
			Este sistema <em>-el cúal es una herramienta única en el país-</em> permite que los clientes puedan monitorear las 24 horas y desde cualquier lugar del mundo, la ubicación exacta de su publicidad, la cúal previamente como cliente ha contratado en nuestro medio publicitario.<br /><br />
			Ud. puede visualizar las ubicaciones en las diferentes calles en las cuales ha tenido presencia su marca tanto en catorcenas anteriores como en la presente.<br /><br />
			Otra característica del sistema es la capacidad de informarle inmediatamente de cualquier evento que haya ocurrido en un <b>Ecomupis</b> que contenga su publicidad, tales eventos podrían ser: destrucción total o parcial, daños por vandalismo, otros daños, etc. posteriormente y gracias a nuestro equipo de reparación 24/7, también podrá ver el momento en que se llevo a cabo su respectiva reinstalación y reparación, la cúal se realiza en periodos de <b>24 horas</b> como máximo.<br /><br />
			También puede Ud. ver estadísticas de impactos publicitarios diarios, costo por impacto, etc. así como generar desde la web reportes PDF de todas sus ubicaciones, pasadas o presentes y mucho más.<br /><br />
			Por todo lo anterior, le garantizamos que esta herramienta lo mantendrá al tanto de todo lo referente a sus espacios publicitarios, porque en <b>Ecomupis nos preocupamos por dar a nuestros clientes las herramientas mas sofisticadas y de fácil uso para que su experiencia con nosotros sea <em>la mejor posible</em></b>.<br/><br/>
			Lo invitamos a navegar en la opción <b><a href="./?accion=ver+ubicaciones">Ubicaciones</a></b>, donde podrá ver nuestras ubicaciones con fotografía actual.<br/><br />
			<span style="font-size:.9em;text-decoration: overline;"><em>Sistema mupiair de Ecomupis</em></span>
			</p>
			';
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
<table class="limpia">
<tr>
<td width="50%" class="texto_der">Código o nombre de usuario</td>
<td><input type="text" name="codigo" style="width: 11em;" value="" /></td>
</tr>
<tr>
<td class="texto_der">Clave (contraseña)</td>
<td><input type="password" name="clave" style="width: 11em;" value="" /></td>
</tr>
<tr>
<td class="texto_der">¿Recordar mi acceso en este equipo?</td>
<td><input type="checkbox" name="remember" <? if($form->value("remember")){ echo "checked"; } ?> onMouseOver="toolTip('Recordar sus datos de acceso para esta maquina.<br />Se recomienda <b>no</b> utilizar en equipos compartidos.')" onMouseOut="toolTip()" /></td>
</tr>
</table>
<center><input type="submit" name="ingresar" value="Clic aquí para ingresar al sistema Eco Mupis" /></center>
<input type="hidden" name="sublogin" value="1">
</form>
<hr /><? echo "Si ha olvidado su clave por favor haga clic en el enlace: " . CREAR_LINK_GET("rpr+clave", "Recuperar clave", "Clic en este enlace para intentar recuperar su clave"); ?></a>
</ul>
<?
}
?>
