<?php
function byteConvert($bytes)
{
	$s = array('B', 'Kb', 'MB', 'GB', 'TB', 'PB');
	$e = floor(log($bytes)/log(1024));

	return sprintf('%.2f '.$s[$e], ($bytes/pow(1024, floor($e))));
}
$HTML_HEAD = '
	<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
	<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="es" lang="es">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<title>Reporte de Eco Mupis - CEPASA de C.V.</title>
	</head>
	<body>
	';
$HTML_FOOT = '</body></html>';

function ADMIN_reportes() {
	global $HTML_HEAD, $HTML_FOOT, $database, $session;
	echo "<h1>Reportes</h1>";

	//
	echo Mensaje("Esta sección se encuentra en desarrollo intensivo actualmente, gracias por la espera.",_M_INFO);
	//

	/***************************************************************************************************************************/
	/*							USER      													        	*/
	/***************************************************************************************************************************/
	//Nos pasaron un nombre?, entonces quieren un reporte SOLO de ese usuario:
	if ( isset($_GET['user']) ) {
		$_SESSION['user'] = $_GET['user'];
		$req_user_info = $database->getUserInfo($_SESSION['user']);
		$_SESSION['tipo'] = $req_user_info['tipo'];
		$_SESSION['nombre'] = $req_user_info['nombre'];
		$_SESSION['dpto'] = $req_user_info['departamento'];
		unset($_SESSION['taller']);
		/***************************************************************************************************************************/
		/*							USER - HTML													        */
		/***************************************************************************************************************************/
		$s = $HTML_HEAD. MOSTRAR_HORARIOS_UNICO_ECHO() . $HTML_FOOT;
		$archivo_HTML_INSTRUCTORES_IND = "reportes/+I/+HTML/instructores+".$_SESSION['user']."+".$tiempo_ord.".html";
		$fh = @fopen($archivo_HTML_INSTRUCTORES_IND, 'w') or die("'/reportes/+HTML/' bloqueado");
		fwrite($fh, $s);
		fclose($fh);
		/***************************************************************************************************************************/
		/*							USER - PDF													        */
		/***************************************************************************************************************************/
		@set_time_limit(300);
		$dompdf = new DOMPDF();
		$dompdf->load_html($s);
		$dompdf->render();
		$PDF_INSTRUCTORES_IND = $dompdf->output();
		$archivo_PDF_INSTRUCTORES_IND = "reportes/+I/+PDF/horarios+".$_SESSION['user']."+".$tiempo_ord.".pdf";
		file_put_contents($archivo_PDF_INSTRUCTORES_IND, $PDF_INSTRUCTORES_IND);
		unset($dompdf);
		unset($PDF_INSTRUCTORES);
		@set_time_limit(30);

		/* LINKS */
		echo "<pre>";
		echo '<a href="'.$archivo_HTML_INSTRUCTORES_IND.'" target="_blank">Descargar reportes de horario para '.$_SESSION['user'].'[HTML]</a><br />';
		echo '<a href="'.$archivo_PDF_INSTRUCTORES_IND.'" target="_blank">Descargar reportes de horario para '.$_SESSION['user'].'[PDF]</a><br />';
		echo "</pre>";
		echo '<blockquote>Por favor realice clic derecho sobre el enlace de descarga y posteriormente utilice la opción "Guardar como" de su navegador</blockquote>';
		/* FIN USER */
		return;
	}
	if ( isset($_POST['generar']) ) {
		echo '<h3>Reporte(s) generado(s) en base a las opciones de configuración:<br />';
		$tiempo_ord = date('y\-m\-d\+h.ia', time());
		if ($_POST['generar_horarios'] == 1 ) {
		/***************************************************************************************************************************/
		/*							HORARIOS - HTML												        */
		/***************************************************************************************************************************/
		$s = $HTML_HEAD;
		if (isset($_POST['dpto0'])) {
		if ($_POST['dpto0'] == 1) {
			$_SESSION['dpto'] = 0;
			$s = $s . "<h2>".DE_0. "</h2>";
			for ($i=0; $i < 4; $i++) {
			if (isset($_POST["tipo_instructor$i"])) {
				if ( $_POST["tipo_instructor$i"] == 1 ) {
					$_SESSION['tipo'] = $i;
					if ($i != 2 ) {
					if (isset($_POST['taller0'])) { if ( $_POST['taller0'] ) {$_SESSION['taller'] = 'LSA'; $s = $s . MOSTRAR_HORARIOS_ECHO(); }}
					if (isset($_POST['taller1'])) { if ( $_POST['taller1'] ) {$_SESSION['taller'] = 'LID'; $s = $s . MOSTRAR_HORARIOS_ECHO(); }}
					if (isset($_POST['taller2'])) { if ( $_POST['taller2'] ) {$_SESSION['taller'] = 'LAI'; $s = $s . MOSTRAR_HORARIOS_ECHO(); }}
					if (isset($_POST['taller3'])) { if ( $_POST['taller3'] ) {$_SESSION['taller'] = 'LIV'; $s = $s . MOSTRAR_HORARIOS_ECHO(); }}
					} else {
					//Encargado de Taller, solo mostrar 1 horario.
					$_SESSION['taller'] = 'GENERAL'; $s = $s . MOSTRAR_HORARIOS_ECHO();
					}
				}
			}
			}
		}
		}
		if (isset($_POST['dpto1'])) {
		if ($_POST['dpto1'] == 1) {
			$_SESSION['dpto'] = 1;
			$s = $s . "<h2>".DE_1. "</h2>";
			for ($i=0; $i < 4; $i++) {
			if (isset($_POST["tipo_instructor$i"])) {
				if ( $_POST["tipo_instructor$i"] == 1 ) {
					$_SESSION['tipo'] = $i;
					if ($i != 2 ) {
					if (isset($_POST['taller4'])) { if ( $_POST['taller4'] ) {$_SESSION['taller'] = 'TPP'; $s = $s . MOSTRAR_HORARIOS_ECHO(); }}
					if (isset($_POST['taller5'])) { if ( $_POST['taller5'] ) {$_SESSION['taller'] = 'TIS'; $s = $s . MOSTRAR_HORARIOS_ECHO(); }}
					if (isset($_POST['taller6'])) { if ( $_POST['taller6'] ) {$_SESSION['taller'] = 'TAS'; $s = $s . MOSTRAR_HORARIOS_ECHO(); }}
					if (isset($_POST['taller7'])) { if ( $_POST['taller7'] ) {$_SESSION['taller'] = 'TEC'; $s = $s . MOSTRAR_HORARIOS_ECHO(); }}
					} else {
					//Encargado de Taller, solo mostrar 1 horario.
					$_SESSION['taller'] = 'GENERAL'; $s = $s . MOSTRAR_HORARIOS_ECHO();
					}
				}
			}
			}
		}
		}
		$s = $s . $HTML_FOOT;

		//Haremos HORARIOS_HTML?
		if ( $_POST['tipo_reporte1'] == 1 ) {
		$archivo_HTML_HORARIOS = "reportes/+H/+HTML/horarios+".$tiempo_ord.".html";
		$fh = @fopen($archivo_HTML_HORARIOS, 'w') or die("'/reportes/+HTML/' bloqueado");
		fwrite($fh, $s);
		fclose($fh);
		}

		/***************************************************************************************************************************/
		/*							HORARIOS - PDF												        */
		/***************************************************************************************************************************/

		if ( $_POST['tipo_reporte0'] == 1 ) {
		@set_time_limit(300);
		$dompdf = new DOMPDF();
		$dompdf->load_html($s);
		$dompdf->render();
		$PDF_HORARIOS = $dompdf->output();
		$archivo_PDF_HORARIOS = "reportes/+H/+PDF/horarios+".$tiempo_ord.".pdf";
		file_put_contents($archivo_PDF_HORARIOS, $PDF_HORARIOS);
		unset($dompdf);
		unset($PDF_HORARIOS);
		@set_time_limit(30);
		}
		}

		/***************************************************************************************************************************/
		/*							INSTRUCTORES - HTML											        */
		/***************************************************************************************************************************/
		if ( $_POST['generar_instructores'] == 1 ) {
		$s = $HTML_HEAD. displayUsers_ECHO() . $HTML_FOOT;

		//Haremos Instructores_HTML?
		if ( $_POST['tipo_reporte1'] == 1 ) {
		$archivo_HTML_INSTRUCTORES = "reportes/+I/+HTML/instructores+".$tiempo_ord.".html";
		$fh = @fopen($archivo_HTML_INSTRUCTORES, 'w') or die("'/reportes/+HTML/' bloqueado");
		fwrite($fh, $s);
		fclose($fh);
		}

		/***************************************************************************************************************************/
		/*							INSTRUCTORES - PDF												*/
		/***************************************************************************************************************************/
		if ( $_POST['tipo_reporte0'] == 1 ) {
		@set_time_limit(300);
		$dompdf = new DOMPDF();
		$dompdf->load_html($s);
		$dompdf->render();
		$PDF_INSTRUCTORES = $dompdf->output();
		$archivo_PDF_INSTRUCTORES = "reportes/+I/+PDF/horarios+".$tiempo_ord.".pdf";
		file_put_contents($archivo_PDF_INSTRUCTORES, $PDF_INSTRUCTORES);
		unset($dompdf);
		unset($PDF_INSTRUCTORES);
		@set_time_limit(30);
		}
		}

		/***************************************************************************************************************************/
		/* 			GENERACIÓN DE REPORTES TERMINANDA, MOSTRAR LINKS */
		/***************************************************************************************************************************/
		echo "<pre>";
		if ( $_POST['tipo_reporte1'] == 1 && $_POST['generar_horarios'] == 1 ){ echo '<a href="'.$archivo_HTML_HORARIOS.'" target="_blank">Descargar reportes de Horarios[HTML]</a><br />'; }
		if ( $_POST['tipo_reporte0'] == 1 && $_POST['generar_horarios'] == 1 ) {echo '<a href="'.$archivo_PDF_HORARIOS.'" target="_blank">Descargar reportes de Horarios[PDF]</a><br />'; }
		if ( $_POST['tipo_reporte1'] == 1 && $_POST['generar_instructores'] == 1 ){ echo '<a href="'.$archivo_HTML_INSTRUCTORES.'" target="_blank">Descargar reportes de Instructores[HTML]</a><br />'; }
		if ( $_POST['tipo_reporte0'] == 1 && $_POST['generar_instructores'] == 1 ){ echo '<a href="'.$archivo_PDF_INSTRUCTORES.'" target="_blank">Descargar reportes de Instructores[PDF]</a><br />'; }
		echo "</pre>";
		echo '<blockquote>Por favor realice clic derecho sobre el enlace de descarga y posteriormente utilice la opción "Guardar como" de su navegador</blockquote>';
	}
	if ( $session->isAdmin() ) {
		echo '<form action="./?accion=reportes" method="post">';
		echo "<hr /><h2>Por favor seleccione el/los tipo(s) de reporte(s) a generar</h2>";
		echo "<h3>Reporte de clientes</h3>";
		echo '<td><input type="checkbox" name="reporte_clientes_0" value="1" checked="cheked">Listado breve de clientes</td><br />';
		echo "<h3>Reporte de Eco Mupis</h3>";
		echo '
		<td><input type="checkbox" name="reporte_eco_0" value="1" checked="cheked">Listado de Eco Mupis</td><br />
		<td><input type="checkbox" name="reporte_eco_0" value="1" checked="cheked">Listado de Eco Mupis Activos</td><br />
		<td><input type="checkbox" name="reporte_eco_1" value="1" checked="cheked">Listado de Pedidos</td><br />
		<td><input type="checkbox" name="reporte_eco_2" value="1" checked="cheked">Listado de Eventos</td><br />
		<td><input type="checkbox" name="reporte_eco_2" value="1" checked="cheked">Listado de Calles</td><br />
		<td><input type="checkbox" name="reporte_eco_2" value="1" checked="cheked">Listado de Referencias</td><br />
		<td><input type="checkbox" name="reporte_eco_2" value="1" checked="cheked">Listado de Comentarios</td><br />
		';
		echo "<hr /><h3>Por favor seleccione el/los tipo(s) de formato de salida a generar</h3>";
		echo '
		<table>
		<tr>
		<td><input type="checkbox" name="tipo_reporte0" value="1" checked="cheked">PDF</td>
		<td><input type="checkbox" name="tipo_reporte1" value="1" checked="cheked">HTML</td>
		</tr>
		</table>
		<input type="hidden" name="generar" value="1">
		<input type="submit" name="bgenerar" value="Generar" />
		</form>
		';
		return;
	} else {

	}
}
?>
