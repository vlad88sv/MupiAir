<?
function CONTENIDO_cargar_pantallas(){
	// Instrucciones
	echo '
	<h1>Utilidad de cargado de fotos de pantallas</h1>
	<hr />
	<h2>Instrucciones de uso</h2>
	Para un correcto cargado de pantallas, se recomienda seguir los siguientes pasos:
	<ol>
	<li>Asegurese de que todos los mupis esten creados para la catorcena a cargar, y que tengan una pantalla asignada.<br />Utilice la herramienta "<strong>Clonar anterior</strong>" si es necesario.</li>
	<li>Obtenga (e imprima si es posible) la lista númerica de pantallas para la catorcena deseada.<br /><a onclick="alert(\'Aún no disponible\')">Puede obtenerla haciendo clic aquí.</a></li>
	<li>Cree una carpeta con nombre igual a la fecha de inicio de la catorcena a la cual corresponden las fotos.<br />Ej. "<strong>13-01-09</strong>" - evite añadir espacios u otros caracteres</li>
	<li>Agregue (en el folder recien creado) las fotos deseadas de las pantallas, enumerandolas de acuerdo a la lista del paso #1.<br />Solo serán válidos archivos con extensión "<strong>.jpg</strong>" (notese las minúsculas).</li>
	<li><span style="color:#FF0000">Por favor no continúe sin corroborar los nombres de los archivos, <strong>esta operación es irreversible una vez cargados los datos.</strong></span></li>
	<li>Comprima el folder en formato <strong>.zip</strong><br />El nombre del archivo compreso es irrelevante, aunque se sugiere utilizar un nombre que haga referencia a la catorcena de fotos que contiene el archivo.</li>
	<li>Presione el botón <strong>Examinar</strong> (browser ó búscar dependiendo de su navegador) y seleccione el archivo compreso recién creado.</li>
	<li>Presione el botón <strong>Cargar archivo</strong> y espere hasta que la carga este completa.</li>
	</ol>
	';
}
?>
