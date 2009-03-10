<?
function CONTENIDO_admin() {
	global $session, $database, $form;
	echo '<h1>Centro de gestión de clientes</h1><hr />';
	if($form->num_errors > 0){
    echo "<font size=\"4\" color=\"#ff0000\">!*** Error con petición, por favor corregir</font><br><br>";
	}
	
	echo '<h2>Resumen clientes</h2>';
	echo 'Catorcena ' . $database->Combobox_CatorcenasConPresencia("FiltroCatorcenasResumen") . '<input type="button" OnClick="$(\'#clientes_resumen\').load(\'contenido/usuario+dinamico.php?resumen=1&amp;catorcena=\'+$(\'#FiltroCatorcenasResumen\').val())" value="Filtrar!" />';
	echo '<div id="clientes_resumen"></div>';
	
	echo '<hr />';
	echo '<h2>Cientes</h2>';
	echo 'Nivel
	<select id="FiltroNivel">
	<option value="">Todos
	<option value="1">Usuario
	<option value="2">Demo
	<option value="3">Cliente
	<option value="5">Vendedor
	<option value="9">Administrador
	</select><input type="button" OnClick="$(\'#clientes_completo\').load(\'contenido/usuario+dinamico.php?completo=1&nivel=\'+$(\'#FiltroNivel\').val())" value="Filtrar!" /> ';

	echo 'Catorcena ' . $database->Combobox_CatorcenasConPresencia("FiltroCatorcenas") . '<input type="button" OnClick="$(\'#clientes_completo\').load(\'contenido/usuario+dinamico.php?completo=1&nivel=\'+$(\'#FiltroNivel\').val()+\'&amp;catorcena=\'+$(\'#FiltroCatorcenas\').val())" value="Filtrar!" />';
	echo '<br /><br />';
	echo '<div id="clientes_completo"></div>';
	if ( $session->isAdmin() ) {
	echo '<hr /><h2>Establecer permisos a cliente/usuario</h2>';
	echo $form->error("upduser"); 
	echo '
	<form action="contenido/adminprocess.php" method="POST">
	<table>
	<tr>
	<td>Código:
	<input type="text" name="upduser" maxlength="30" value="'.$form->value("upduser").'"></td>
	<td>
	Nivel:<br />
	<select name="updlevel">
	<option value="1">Usuario
	<option value="2">Demo
	<option value="3">Cliente
	<option value="5">Vendedor
	<option value="9">Administrador
	</select>
	</td>
	</tr>
	</table>
	<input type="hidden" name="subupdlevel" value="1">
	<input type="submit" value="Actualizar Cliente/Usuario">

	</form>';

	echo '<hr /><h2>Quitar cliente/usuario</h2>';
	echo $form->error("deluser");
	echo '
	<form action="contenido/adminprocess.php" method="POST">
	<table>
	<td>Código:
	<input type="text" name="deluser"  maxlength="30" value="'.$form->value("deluser").'"></td>
	<input type="hidden" name="subdeluser" value="1">
	</table>
	<input type="submit" value="Quitar Cliente/Usuario">
	</form>';
	}
}
?>
