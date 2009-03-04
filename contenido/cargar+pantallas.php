<?
function CONTENIDO_cargar_pantallas(){
	
	// Si ya envió el archivo entonces nos detenemos a procesarlo.
	// El procesado del archivo seguirá el siguiente orden:
	// 0. Se comprobará que el archivo subido sea .zip.
	// 1. Se descomprimirá el archivo subido.
	// 2. Se comprobará que solo exista 1 carpeta.
	// 3. Se tomara la fecha de esa carpeta como fecha de catorcena.
	// 4. Se verificará que la catorcena a ser cargada sea de igual número de pantallas que número de archivos.
	// 5. Si todo lo anterior es exitoso, entonces se actualizarán los campos de imagen de todas las pantallas correspondientes.
	// 6. Fin.
	
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
	<li><span style="color:#FF0000">Por favor <strong>no</strong> continúe sin corroborar los nombres de los archivos, <strong>esta operación es irreversible una vez cargados los datos.</strong></span></li>
	<li>Comprima el folder en formato <strong>.zip</strong><br />El nombre del archivo compreso es irrelevante, aunque se sugiere utilizar un nombre que haga referencia a la catorcena de fotos que contiene el archivo.</li>
	<li>Presione el botón <strong>Examinar</strong> (Choose, Browse o Búscar dependiendo de su navegador) y seleccione el archivo compreso recién creado.</li>
	<li>Presione el botón <strong>Cargar archivo</strong> y espere hasta que la carga este completa.</li>
	</ol>
	';
	echo '<hr />';
	echo '<form action="./?'._ACC_.'=cargar+pantallas" enctype="multipart/form-data" method="POST">';
	echo '<table>';
	echo '<tr><td class="limpio" width="20%">Archivo a cargar</td><td class="limpio"><input type="file" size="100%"></td></tr>';
	echo '<tr><td class="limpio" width="20%"></td><td class="limpio"><input type="submit" value="Cargar archivo"></td></tr>';
	echo '</table>';
	echo '</form>';
}
?>
