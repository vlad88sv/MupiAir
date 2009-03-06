<?
function CONTENIDO_cargar_pantallas(){
	global $database;
	// Si ya envió el archivo entonces nos detenemos a procesarlo.
	// El procesado del archivo seguirá el siguiente orden:
	// 0. Se comprobará que el archivo subido sea .zip.
	// 1. Se descomprimirá el archivo subido.
	// 2. Se tomará la 1era carpeta en el zip (solo debería de existir una).
	// 3. Se tomara la fecha de esa carpeta como fecha de catorcena.
	// 4. Si todo lo anterior es exitoso, entonces se actualizarán los campos de imagen de todas las pantallas correspondientes.
	
	// -----------------------------------------------------------------------------------------------------------------------
	// Verificamos si ya puso a cargar el archivo
	$NombreCampo = 'ArchivoZip';
	$Catorcena = '';
	if ( isset ($_FILES[$NombreCampo]) ) {
		// Si lo puso entonces verificamos que no haya habido ningún error
		if ( !$_FILES[$NombreCampo]['error'] ) {
			// Será ZIP ?
			if ( strtolower(@end(explode(".",$_FILES[$NombreCampo]['name']))) == "zip" ) {
				// Si, es ZIP, entonces procedamos a descomprimirlo...
				echo MENSAJE ("Archivo es ZIP, procediendo a descomprimir...", _M_INFO);
				ob_flush(); flush();
				$zip = zip_open($_FILES[$NombreCampo]['tmp_name']);
				if ($zip) {
					// Creemos el directorio padre del zip.
					$DirectorioZip = 'zip/'.$_FILES[$NombreCampo]['name'].'/';
					//mkdir($DirectorioZip,0777,true);
					while ($zip_entry = zip_read($zip)) {
						if ( substr(zip_entry_name($zip_entry),-1) == "/" ) {
							// Es directorio, crear si no existe.
							// if ( !is_dir($DirectorioZip.zip_entry_name($zip_entry)) ) {
								//mkdir($DirectorioZip.zip_entry_name($zip_entry),0777,true);
								$Catorcena = strtotime(basename($DirectorioZip.zip_entry_name($zip_entry)));
								if ( !is_numeric($Catorcena) ) {
									echo MENSAJE ("Una carpeta del ZIP '" . zip_entry_name($zip_entry) . "' no tiene formato válido de catorcena", _M_ERROR);
								} // Si era valida la fecha de la catorcena
								echo MENSAJE ("Se procesará la catorcena: " .basename($DirectorioZip.zip_entry_name($zip_entry)) . " [". $Catorcena ."]", _M_INFO);
								ob_flush(); flush();
							// } // Si no existia el directorio
						} else {
							
							// Si ya tenemos la catorcena a usar entonces procesar
							// si no es que estamos ante un archivo fuera de carpeta.
							if ( $Catorcena ) {
							//$fp = fopen($DirectorioZip.zip_entry_name($zip_entry), "w");  // No queremos escribirlo al disco :)
							if (zip_entry_open($zip, $zip_entry, "r")) {
								// Será JPG ?
								if ( strtolower(@end(explode(".",zip_entry_name($zip_entry)))) == "jpg" ) {
								//**********************************************************
								// Metemos la imagen a la base de datos en 3 pasos
								// 1ro. Encontramos la cara a la que corresponde la imagen y si existe la descomprimimos y...
								// 2do. agregamos la imagen en la tabla de imagenes.
								// 3ro. Actualizamos el campo de foto_real de la cara con la nueva imagen y punto :)
								//__________________________________________________________
								//1.
								$id_pantalla = pathinfo(zip_entry_name($zip_entry), PATHINFO_FILENAME);
								$q = "SELECT id_pantalla FROM ".TBL_MUPI_FACES." WHERE id_pantalla='$id_pantalla'";
								$result = $database->query($q);
								$num_rows = mysql_numrows($result);
								if($num_rows == 1){
									$buf = zip_entry_read($zip_entry, zip_entry_filesize($zip_entry));
									zip_entry_close($zip_entry);
									//2
									$ParsedIMG = mysql_real_escape_string($buf);
									//echo $ParsedIMG;
									$q = "INSERT INTO ".TBL_IMG." (data, categoria, mime) VALUES('".$ParsedIMG."', 'PANTALLAS', 'image/jpeg');";
									$database->query($q);
									$foto_real = mysql_insert_id($database->connection);
									//fwrite($fp,"$buf"); // No queremos escribirlo al disco :)
									//fclose($fp);  // No queremos escribirlo al disco :)
									//3
									$q = "UPDATE ".TBL_MUPI_FACES." SET foto_real='$foto_real' WHERE id_pantalla='$id_pantalla'";
									$database->query($q);
									echo MENSAJE ("Cara $id_pantalla actualizada!", _M_INFO);
								} else {
									echo "'" . zip_entry_name($zip_entry) . "' descartado por no existir en la base de datos.";
								} // Si habia 1 resultado
								
								//**********************************************************
							} else {
								echo MENSAJE ("Un archivo del ZIP '" . zip_entry_name($zip_entry) . "' no tiene formato válido JPG", _M_ERROR);
							} // Si era JPG
						    } // Si se pudo abrir
							} // Si era catorcena
						} // Si no era directorio
					} // Mientras habia archivos en el zip
					zip_close($zip);
					echo MENSAJE ("Concluido.", _M_INFO);
					ob_flush(); flush();					
				} else {
					echo MENSAJE ("Archivo no pudo ser descompreso, deteniendo operación", _M_ERROR);
				} // Si era ZIP valido
			} else {
				// No, no es ZIP, no proceder.
				echo MENSAJE ("Lo siento, no puedo procesar el archivo cargado, la extensión no es 'zip'", _M_ERROR);
			} // Si era extension zip
		}
	}
	// Instrucciones
	echo '
	<h1>Utilidad de cargado de fotos de pantallas</h1>
	<hr />
	<h2>Instrucciones de uso</h2>
	Para un correcto cargado de pantallas, se recomienda seguir los siguientes pasos:
	<ol>
	<li>Asegurese de que todos los mupis esten creados para la catorcena a cargar, y que tengan una pantalla asignada.<br />Utilice la herramienta "<strong>Clonar anterior</strong>" si es necesario.</li>
	<li>Obtenga (e imprima si es posible) la lista númerica de pantallas para la catorcena deseada.<br /><a href="./?'._ACC_.'=listas&tipo=id_pantallas" target="_blank">Puede obtenerla haciendo clic aquí.</a></li>
	<li>Cree una carpeta con nombre igual a la fecha de inicio de la catorcena a la cual corresponden las fotos, formato dd-mm-yy (dia-mes-año).<br />Ej. "<strong>05-01-09</strong>" - evite añadir espacios u otros caracteres.</li>
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
	echo '<tr><td class="limpio" width="20%">Archivo a cargar</td><td class="limpio"><input type="file" name="ArchivoZip" size="100%"></td></tr>';
	echo '<tr><td class="limpio" width="20%"></td><td class="limpio"><input type="submit" value="Cargar archivo"></td></tr>';
	echo '</table>';
	echo '</form>';
	echo Mensaje('recuerde que esta es una operación intensiva para el servidor, evite realizar la carga de imagenes en horas pico', _M_NOTA);
}
?>
