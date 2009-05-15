<?php
function ADMIN_reportes() {
	global $database, $session;
	echo "<h1>Reportes</h1>";

	//
	echo Mensaje("Esta sección se encuentra en desarrollo intensivo actualmente, gracias por la espera.",_M_INFO);
	//
	echo "<h2>Reportes rápidos</h2>";
	echo "<ul>";
	if ($session->isAdmin()) echo "<li>Generar reporte de <a href='contenido/admin+reportes+dinamico.php?sub=generar&reporte=rapido_todos_los_mupis'>todos los mupis</a></li>";
	echo "<li>Generar reporte de los mupis activos en <a href='contenido/admin+reportes+dinamico.php?sub=generar&reporte=rapido_mupis_catorcena_anterior'>catorcena anterior</a>, <a href='contenido/admin+reportes+dinamico.php?sub=generar&reporte=rapido_mupis_catorcena_actual'>catorcena actual</a></li>";
	if ($session->isAdmin()) echo "<li>Generar reporte de los clientes <a href='contenido/admin+reportes+dinamico.php?sub=generar&reporte=rapido_usuarios_catorcena_anterior'>activos en la catorcena anterior</a>, <a href='contenido/admin+reportes+dinamico.php?sub=generar&reporte=rapido_usuarios_catorcena_actual'>catorcena actual</a></li>";
	echo "</ul>";
	//
}
?>
