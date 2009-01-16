<?php
function MOSTRAR_SELECCION_TALLER() {
	echo
	'
	<form action="./?accion=horarios" method="post">
	<table border=0>
	<tr><td>Taller a asignar: </td><td>
	<select name="taller">
	';
	if ($_SESSION['dpto'] == 0) {
	echo
	'
	<option value="LSA">'."LSA".'
	<option value="LID">'."LID".'
	<option value="LAI">'."LAI".'
	<option value="LIV">'."LIV".'
	';
	} else {
	echo
	'
	<option value="TPP">'."TPP".'
	<option value="TIS">'."TIS".'
	<option value="TAS">'."TAS".'
	<option value="TEC">'."TEC".'
	';
	}
	echo
	'
	</select>
	</td></tr>
	</table>
	<input type="hidden" name="HORARIO_registrar" value="insertar">
	<input type="submit" name="continuar" value="Continuar" />
	</form>
	';
}

/*****************************************************************************************************************************************************/
//Se nos pide registrar un instructor?
function HORARIO_registrar_instructor() {
	global $session, $link, $base, $motor, $usuario, $clave;
	/*
	echo 'Procediendo a registro de instructor, parametros:';
	echo 'Usuario: '.$_SESSION['user'].'<hr />';
	echo 'Tipo: '.$_SESSION['tipo'].'<hr />';
	echo 'Dpto: '.$_SESSION['dpto'].'<hr />';
	echo 'dia: '.$_SESSION['dia'].'<hr />';
	echo 'posicion: '.$_SESSION['posicion'].'<hr />';
	echo '<hr />';
	*/
	if (!$session->isAdmin()) {die("Lo siento, Ud. esta intentando registrar un Instructor sin ser administrador");}

	echo "<h2>Visor de Horarios - Procesando la asignación de horarios para ".$_SESSION['user']."<br /><hr />";
	
	$link = @mysql_connect($motor, $usuario, $clave) or die('Por favor revise sus datos, puesto que se produjo el siguiente error:<br /><pre>' . mysql_error() . '</pre>');
	mysql_select_db($base, $link) or die('!->La base de datos seleccionada "'.$base.'" no existe');

	//Si es encargado de taller no importa si tiene otro laboratorio
	$num_rows = 0;
	if ( $_SESSION['tipo'] != 2 ) {
		//Verificamos que no tenga otro laboratorio al mismo tiempo.
		$q = "SELECT * FROM horarios WHERE username='". $_SESSION['user'] ."' AND dia='".$_SESSION['dia']."' AND posicion='".$_SESSION['posicion']."';";
		//echo $q."<br />";
		$resultados = @mysql_query($q, $link) or die('!->Ocurrió un error mientras se revisaba la disponibilidad del instructor.');
		$num_rows = mysql_numrows($resultados);
		//$num_rows  > 0 significa que el muchacho ya tiene asignado otro laboratorio a la misma hora :)
		if ($num_rows > 0) {
		echo
		'
		<h3>Registro de Intructor abortado</h3><hr />
		Lo sentimos, pero este Instructor ya tiene otros laboratorios asignados el mismo día y hora.
		';
		echo CONTINUAR;
		return;
		}
	}

	//Ok, no tiene otro laboratorio simultaneo, entonces verificamos que no haya nadie mas ya asignado a ese taller
	//Si no es asistente, entonces contar todos los que hay que NO sean asistentes.
	//Tampoco hay que contar los Encargados de Taller
	switch ( $_SESSION['tipo'] ) {
		 //Asistente de Catedratico, contar solo asistentes en ese taller.
		case 0:
			$q = "SELECT * FROM horarios, users WHERE horarios.username = users.username AND tipo = 0 AND taller='". $_SESSION['taller'] ."' AND dia='".$_SESSION['dia']."' AND posicion='".$_SESSION['posicion']."';";
			break;
		//Asistente de Taller + Instructor, contar todo menos asistentes y encargados.
		case 1:
		case 3:
			$q = "SELECT * FROM horarios, users WHERE horarios.username = users.username AND tipo != 0 AND tipo != 2 AND taller LIKE'%". $_SESSION['taller'] ."%' AND dia='".$_SESSION['dia']."' AND posicion='".$_SESSION['posicion']."';";
			break;
		//Encargado de Taller, verificar si EL esta.
		case 2:
			//Si ya hay uno: $q = "SELECT * FROM horarios, users WHERE horarios.username = users.username AND tipo = 2 AND taller='". $_SESSION['taller'] ."' AND dia='".$_SESSION['dia']."' AND posicion='".$_SESSION['posicion']."';";
			$q = "SELECT * FROM horarios, users WHERE horarios.username = '".$_SESSION['user']. "' AND users.username = '" .$_SESSION['user']. "' AND tipo = 2 AND taller='". $_SESSION['taller'] ."' AND dia='".$_SESSION['dia']."' AND posicion='".$_SESSION['posicion']."';";
			break;
	}
	//echo $q.'<br />';
	$resultados = @mysql_query($q, $link) or die('!->Ocurrió un error mientras se revisaba la disponibilidad del instructor. (2)');
	$num_rows = mysql_numrows($resultados);
	
	//Umm si >0 es que hay alguien ahi, y podemos proceder solo si ese numero es menor a 8 y  somos asistentes...
	if ( $num_rows > 0 && ($_SESSION['tipo'] != 0 && $num_rows < 8) ) {
	echo	'<h3>Registro de Intructor abortado</h3><hr />';
	
	switch ( $_SESSION['tipo'] ) {
	case 0:
		echo 'Lo sentimos, pero ya hay 8 ' . TI_0 . ' en este taller a esta hora<br />';
		break;
	case 1:
	case 3:
		echo 'Lo sentimos, pero este taller ya tiene otro(s) Instructor(es) asignado(s) el mismo día y hora.<br />';
		break;
	case 2:
		echo 'Lo sentimos, pero este Encargado de taller ya esta asignado a este horario.<br />';
		break;
	}
	echo CONTINUAR;
	return;
	}
	//Finalmente, si no estabamos ocupados y no lo estaban ocupando, nos insertamos.
	//Insertamos al instructor en el horario.

	if ( $_SESSION['tipo'] == 2 ) {
	 $tempTaller = "";
	 } else {
	 $tempTaller = $_SESSION['taller'];
	 }
	$q = "INSERT INTO horarios VALUES ('".$_SESSION['user'] ."', ' " . $tempTaller . "','".$_SESSION['dia']."','".$_SESSION['posicion']."')";
	//echo $q;
	@mysql_query($q, $link) or die('!->Ocurrió un error en la ultima etapa de la adición de usuarios.');
	echo
	'
	<h3>Registro exitoso</h3><br />
	';
	echo CONTINUAR;
	return;
}
function HORARIO__DESCRIBIR_ELEMENTO_EN_POS_UNICO($dia, $hora) {
	global $session, $link;
	// 1. Tengo ocupada esa hora?
	switch ( $_SESSION['tipo'] ) {
	case 0:
	case 1:
	case 3:
	case 2:
	$q = "SELECT users.username, nombre, horarios.taller FROM users,horarios WHERE horarios.username =  '".$_SESSION['user'] . "' AND users.username =  '".$_SESSION['user']."' AND tipo = '".$_SESSION['tipo']."' AND dia='$dia' AND posicion='$hora'";
	break;
	}
	//echo $q;
	$resultados = @mysql_query($q, $link);
	$num_rows = mysql_numrows($resultados);

	 if($num_rows > 0){
		//Los encargados estan ocupados y punto.
		if ( $_SESSION['tipo'] == 2 ) {
			$msj = $msj.'<FONT COLOR="#800000">'."Ocupado"."</font><br />";
		} else {
			//Si, la tengo ocupada, ¿en que taller estoy?
			//Hacemos for para mostrar si hay mas de un taller (error)!
			for($i=0; $i<$num_rows; $i++){
				$msj = $msj.'<FONT COLOR="#800000">'.mysql_result($resultados,$i,"taller")."</font><br />";
			}
		}
	}else {
		//No, no la tengo ocupada!, entonces dejemos ponerle trabajo, claro, si somos admins...
		if ($session->isAdmin()){
				$msj=$msj .'<a href="./?accion=horarios&amp;o=a&amp;d='.$dia.'&amp;p='.$hora.'">Asignar</a>';
		} else {
			$msj="";
			
		}
	}
return "<td>".$msj."</td>";
}

function HORARIO__DESCRIBIR_ELEMENTO_EN_POS($taller,$dia, $hora) {
	global $session, $link;
	//Si existe un nombre en $_GET entonces mostrar solo ese nombre o asignar.
	if ( isset($_GET['user']) )
		{$userExtra = "horarios.username =  '".$_GET['user'] . "' AND users.username =  '".$_GET['user']."'";}
	else
		{$userExtra = "horarios.username = users.username";}

	// ¿Hay alguien(es) en esa posición?
	$q = "SELECT users.username, nombre FROM users,horarios WHERE $userExtra AND taller LIKE '%$taller%' AND tipo = '".$_SESSION['tipo']."' AND dia='$dia' AND posicion='$hora'";
	//echo $q;
	$resultados = @mysql_query($q, $link);
	$num_rows = mysql_numrows($resultados);

	 if($num_rows > 0){
		$msj="";
		for($i=0; $i<$num_rows; $i++){
		      $uname = mysql_result($resultados,$i,"nombre");
		      $uinfo = mysql_result($resultados,$i,"username");
		      /* Por petición solo el primer nombre es mostrado */
		      unset ($nombre);
		      ereg("([^ ]*)", $uname, $nombre);
		      $msj = $msj.'<a href="./?'._ACC_.'=usuario+info&amp;usr='.$uinfo.'">'.$nombre[1].'</a>[<a href="./?accion=horarios&amp;o=e&amp;t='.$taller.'&amp;d='.$dia.'&amp;p='.$hora.'&amp;user='.$uinfo.'">X</a>]<br />';
		}
	}
	
	if($num_rows == 0 || ($_SESSION['tipo'] == 0  && $num_rows < 9)  || $_SESSION['tipo'] == 2){
		if ( isset($_SESSION['user']) ) { $u = "&amp;nombre=".$_SESSION['user']; } else { $u = ""; }
		$msj=$msj .'<a href="./?accion=horarios&amp;t='.$taller.'&amp;d='.$dia.'&amp;p='.$hora.'&amp;o=a&amp;tipo='.$_SESSION['tipo'].$u.'">Asignar</a>';
	} 
	
return "<td>".$msj."</td>";
}

function MOSTRAR_HORARIOS_UNICO() {
	/* Muestra  la tabla de horario en base a las opciones de filtro establecidas */
	global $link, $base, $motor, $usuario, $clave;
	$link = @mysql_connect($motor, $usuario, $clave) or die('Por favor revise sus datos, puesto que se produjo el siguiente error:<br /><pre>' . mysql_error() . '</pre>');
	@mysql_select_db($base, $link) or die('!->La base de datos seleccionada "'.$base.'" no existe');
	echo "<h3>";
	switch ($_SESSION['dpto'])
	{
	case 0: echo DE_0; break;
	case 1: echo DE_1; break;
	}
	echo 
	' </h3>
	<hr />
	<table border="1" cellspacing="0" cellpadding="3">
	<tr><th>Horario</th><th>Lunes</th><th>Martes</th><th>Miercoles</th><th>Jueves</th><th>Viernes</th><th>Sabado</th></tr>
	';
	for ($i=450; $i<=1170; $i+=60){
	echo "<tr><td><b>". date("h:ia", mktime(0,$i)) . "</b></td>". HORARIO__DESCRIBIR_ELEMENTO_EN_POS_UNICO(1, $i).HORARIO__DESCRIBIR_ELEMENTO_EN_POS_UNICO(2, $i).HORARIO__DESCRIBIR_ELEMENTO_EN_POS_UNICO(3, $i).HORARIO__DESCRIBIR_ELEMENTO_EN_POS_UNICO(4, $i).HORARIO__DESCRIBIR_ELEMENTO_EN_POS_UNICO(5, $i).HORARIO__DESCRIBIR_ELEMENTO_EN_POS_UNICO(6, $i)."</tr>";
	}
	echo "</table>";
	mysql_close($link);
}

function MOSTRAR_HORARIOS() {
	/* Muestra  la tabla de horario en base a las opciones de filtro establecidas */
	global $link, $base, $motor, $usuario, $clave;
	$link = @mysql_connect($motor, $usuario, $clave) or die('Por favor revise sus datos, puesto que se produjo el siguiente error:<br /><pre>' . mysql_error() . '</pre>');
	@mysql_select_db($base, $link) or die('!->La base de datos seleccionada "'.$base.'" no existe');
	echo "<h3>";
	switch ($_SESSION['dpto'])
	{
	case 0: echo DE_0; break;
	case 1: echo DE_1; break;
	}
	echo ' - Taller '.$_SESSION['taller'].' - ';
	switch ($_SESSION['tipo']) 
	{
	case 0: echo TI_0; break;
	case 1: echo TI_1; break;
	case 2: echo TI_2; break;
	case 3: echo TI_3; break;
	}

	echo 
	' </h3>
	<hr />
	<table border="1" cellspacing="0" cellpadding="3">
	<tr><th>Horario</th><th>Lunes</th><th>Martes</th><th>Miercoles</th><th>Jueves</th><th>Viernes</th><th>Sabado</th></tr>
	';
	for ($i=450; $i<=1170; $i+=60){
	echo "<tr><td><b>". date("h:ia", mktime(0,$i)) . "</b></td>". HORARIO__DESCRIBIR_ELEMENTO_EN_POS($_SESSION['taller'], 1, $i).HORARIO__DESCRIBIR_ELEMENTO_EN_POS($_SESSION['taller'], 2, $i).HORARIO__DESCRIBIR_ELEMENTO_EN_POS($_SESSION['taller'], 3, $i).HORARIO__DESCRIBIR_ELEMENTO_EN_POS($_SESSION['taller'], 4, $i).HORARIO__DESCRIBIR_ELEMENTO_EN_POS($_SESSION['taller'], 5, $i).HORARIO__DESCRIBIR_ELEMENTO_EN_POS($_SESSION['taller'], 6, $i)."</tr>";
	}
	echo "</table>";
	mysql_close($link);
}

function CONTENIDO_horarios() {
/* Esta es la función principal que muta dependiendo de la acción requerida */
global $session, $database, $link, $base, $motor, $usuario, $clave;

/*****************************************************************************************************************************************************/

//Tenemos un nombre y somos admins, o somos instructores? - Y no estamos procesando un (o)bjectivo.
if ( !$session->isAdmin() || ($session->isAdmin() && isset($_GET['user'])) && !isset($_GET['o']) && !isset($_POST['HORARIO_registrar']) ) {
	if ( $session->isAdmin() ) {
		$_SESSION['user']=$_GET['user']; 
	} else { 
		$_SESSION['user'] = $session->username; 
	}
	//echo "We've got a name: "  . $_SESSION['user'];
	$req_user_info = $database->getUserInfo($_SESSION['user']);

	$_SESSION['tipo'] = $req_user_info['tipo'];
	$_SESSION['dpto'] = $req_user_info['departamento'];
	unset($_SESSION['taller']);
	
	MOSTRAR_HORARIOS_UNICO();
	return;
} 
/*****************************************************************************************************************************************************/
/*	Teoricamente solo un Admin puede pasar de esta linea.																       */
if (!$session->isAdmin()) {die("Lo siento, Ud. esta intentando ingresar a un área especial sin ser administrador");}
/*****************************************************************************************************************************************************/

/* Necesitamos saber el día y la posición en la que lo quieren registrar */
if (!isset($_POST['HORARIO_registrar']) ) {
	$_SESSION['dia'] = $_GET['d'];
	$_SESSION['posicion'] = $_GET['p'];
}
/* Se nos pide registrar a un instructor */  /*Entramos si somos Encargados de Taller */
if ( isset($_POST['HORARIO_registrar']) || ($_GET['o'] == 'a' && $_SESSION['tipo'] == 2 && $_SESSION['user'] != "")) {
	if ( !isset($_SESSION['taller']) ) { $_SESSION['taller'] = $_POST['taller']; }
	if ( isset($_POST['user']) ) { $_SESSION['user'] = $_POST['user']; }
	if ($_GET['o'] == 'a' && $_SESSION['tipo'] == 2)  { $_SESSION['taller'] = ""; }
	HORARIO_registrar_instructor();
	return;
}

/* Se nos pide ir a la pantalla de ingresar o registrar usuario */
if 	( isset($_GET['d']) && isset($_GET['p'])&& isset($_GET['o'])) {
	
	$link = @mysql_connect($motor, $usuario, $clave) or die('Por favor revise sus datos, puesto que se produjo el siguiente error:<br /><pre>' . mysql_error() . '</pre>');
	mysql_select_db($base, $link) or die('!->La base de datos seleccionada "'.$base.'" no existe');
	echo '<h2>Visor de Horarios - Filtro de instructores</h2><hr>';
	
	//	¿Quieren agregar?
	if ($_GET['o'] == 'a') {
		if ( !isset($_SESSION['taller']) ) {
			// Si entro por nombre, entonces no tiene taller preseleccionado: mandar a seleccion de Taller
			MOSTRAR_SELECCION_TALLER();
			return;
		}
		
		//Sino busquemos al elegido: ¿Hay alguien(es) en ese departamente y con ese cargo?
		$q = "SELECT username, nombre FROM users WHERE departamento='".$_SESSION['dpto'] ."' AND tipo='" . $_SESSION['tipo'] . "'";
		$resultados = @mysql_query($q, $link);
		$num_rows = mysql_numrows($resultados);
		
		//Si hay personal entonces mostrar.
		if($num_rows > 0){
		echo
		'
		<form action="./?'._ACC_.'=horarios" method="post">
		<table border=0>
		<tr><td>Instructores disponibles para el filtro actual: </td><td>
		<select name="user">
		';


		for($i=0; $i<$num_rows; $i++){
			$uname = mysql_result($resultados,$i,"username");
			$unombre = mysql_result($resultados,$i,"nombre");
			echo '<option value="' . $uname . '">'.$unombre;
		}
		echo
		'
		</select>
		</td></tr>
		</table>
		<input type="hidden" name="HORARIO_registrar" value="insertar">
		<input type="submit" name="continuar" value="Continuar" />
		</form>
		';
		} else {
			echo '<h3>Error<h3><hr />';
			echo 'No se encontró ningún instructor que cumpliera con su criterio<br />';
		}
	
	//Quieren eliminar
	} else {
		$req_user_info = $database->getUserInfo($_GET['user']);
		//eliminar
		if ( $req_user_info['tipo'] == 2 ) { 
			$q = "DELETE FROM horarios WHERE username LIKE '%".$_GET['user']."%' AND dia = ".$_GET['d']." AND posicion = ".$_GET['p'].";";
		} else {
			$q = "DELETE FROM horarios WHERE username LIKE '%".$_GET['user']."%' AND taller LIKE '%".$_GET['t']."%' AND dia = ".$_GET['d']." AND posicion = ".$_GET['p'].";";
		}
		//echo $q;
		$resultado = mysql_query($q, $link) or die('!->La operación de eliminación no pudo ser completada');
		echo "<h3>Operación de eliminación completada</h3><hr />".$_GET['user']." ha sido removido del horario.<hr />";
		echo CONTINUAR;
	}
	return;
}

/*****************************************************************************************************************************************************/
/* Acción por defecto de Administrador: Se nos pide filtrar los horarios.*/
$paso = $_POST['paso'];
/*
0. Selección de departamento.
$_POST['dpto']
1. Selección de ocupacion del instructor
$_POST['tipo']
1.1 Salta paso 2 si $_POST['tipo'] = 2 -> Encargado de Taller
2. Selección de taller.
$_POST['taller']
*/
//¿alguien tiene cuello y nos han pedido que lo asignemos de una vez sin pasar por licitación de Instructores XD?:
if (isset($_GET['user'])) { $_SESSION['user'] = $_GET['user']; } else { unset ($_SESSION['user']); }
echo '<h2>Visor de Horarios</h2></b><hr />';
switch ($paso) {
case 0:
	if (!isset($_GET['dpto'])) {
		echo
		'
		<form action="./?accion=horarios" method="post">
		<table border=0>
		<tr><td>Carrera a administrar: </td><td>
		<select name="dpto">
		<option value="0">'.DE_0.'
		<option value="1">'.DE_1.'
		</select>
		</td></tr>
		</table>
		<input type="hidden" name="paso" value="1">
		<input type="submit" name="continuar" value="Continuar" />
		</form>
		';
		break;
	} else {
		$_SESSION['dpto'] = $_GET['dpto'];
	}
case 1:
	if (!isset($_GET['tipo'])) {
		$_SESSION['dpto'] = $_POST['dpto'];

		echo
		'
		<form action="./?accion=horarios" method="post">
		<table border=0>
		<tr><td>Tipo de instructor a administrar: </td><td>
		<select name="tipo">
		<option value="0">'.TI_0.'
		<option value="1">'.TI_1.'
		<option value="2">'.TI_2.'
		<option value="3">'.TI_3.'
		</select>
		</td></tr>
		</table>
		<input type="hidden" name="paso" value="2">
		<input type="submit" name="continuar" value="Continuar" />
		</form>
		';
		echo '<hr />';
		switch ($_SESSION['dpto'])
		{
		case 0: echo DE_0; break;
		case 1: echo DE_1; break;
		}
		break;
	} else {
		$_POST['tipo'] = $_GET['tipo'];
	}
case 2:
	$_SESSION['tipo'] = $_POST['tipo'];
	//Si es encargado de Taller nos saltamos la seleccion.
	if ($_SESSION['tipo'] != 2) {
	if ( isset($_GET['user'] ) ) { $ChinearGet = "&amp;user=".$_GET['user']; } else { $ChinearGet = ""; }
	echo
	'
	<form action="./?accion=horarios'.$ChinearGet.'" method="post">
	<table border=0>
	<tr><td>Taller a administrar: </td><td>
	<select name="taller">
	';
	if ($_SESSION['dpto'] == 0) {
	echo
	'
	<option value="LSA">'."LSA".'
	<option value="LID">'."LID".'
	<option value="LAI">'."LAI".'
	<option value="LIV">'."LIV".'
	';
	} else {
	echo
	'
	<option value="TPP">'."TPP".'
	<option value="TIS">'."TIS".'
	<option value="TAS">'."TAS".'
	<option value="TEC">'."TEC".'
	';
	}
	echo
	'
	</select>
	</td></tr>
	</table>
	<input type="hidden" name="paso" value="3">
	<input type="submit" name="continuar" value="Continuar" />
	</form>
	';
	echo '<hr />';
	switch ($_SESSION['dpto'])
	{
	case 0: echo DE_0; break;
	case 1: echo DE_1; break;
	}
	echo " -> ";
	switch ($_SESSION['tipo'])
	{
	case 0: echo TI_0; break;
	case 1: echo TI_1; break;
	case 2: echo TI_2; break;
	case 3: echo TI_3; break;
	}
	break;
	} else {
		$_POST['taller'] = "";
	}
case 3:
	$_SESSION['taller'] = $_POST['taller'];
	MOSTRAR_HORARIOS();
break;
default:
	return;
}
}
?>