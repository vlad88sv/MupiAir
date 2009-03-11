<?php
  function CONTENIDO_mupis_ubicaciones($usuario = '')
  {
      global $session, $database, $map;
	  //Cosas que solo Admin y Vendedor pueden tener acceso
      $NivelesPermitidos = array(ADMIN_LEVEL, SALESMAN_LEVEL, DEMO_LEVEL);
      if (!in_array($session->userlevel, $NivelesPermitidos)) {
		  $usuario = $session->codigo;
      }
	  //Rebajamos el nivel de acceso a solo admin, para evitar que vean el botón verpormupis.
	  $NivelesPermitidos = array(ADMIN_LEVEL);
	  if (!in_array($session->userlevel, $NivelesPermitidos)) {
		unset($_GET['verpormupis']);
	  }
      //Importante!!! Esto tiene que suceder antes de cualquier cuestión AJAX porque Google esta usando document.write en algún momento!.
      echo sprintf('<script src="http://maps.google.com/maps?file=api&amp;v=2&amp;key=%s" type="text/javascript" charset="utf-8"></script>', GOOGLE_MAP_KEY);
      // AJAX ;)
      if (!isset($_GET['verpormupis'])) {
          echo "\n".
				'<script>
				function funcion_combo_catorcenas(){
				$("#botones_arte").empty();
				$("#datos_calles").load("contenido/mupis+ubicaciones+dinamico.php?accion=calles&usuario=' . $usuario . '&catorcena="+$(\'#combo_catorcenas\').val());
				$("li#MM_paso_1").css("text-decoration", "line-through");
				$.jGrowl.defaults.position = "bottom-left";
				$(\'div.close\').trigger("click.jGrowl");
				$.jGrowl.defaults.closer = false;
				//$.jGrowl.defaults.closerTemplate = "<div>Cerrar todas</div>";
				$.jGrowl("Si selecciona la calle \"Todas\", podrá desplazarse sobre todo el mapa para encontrar sus ubicaciones, de lo contrario se le mostrará unicamente los Ecomupis que esten en la calle seleccionada", { sticky: true , theme:  \'aviso\' });
				}
				function funcion_combo_calles() {
				$("#botones_arte").empty();
				$("#Mensajes").empty();
				window.location="#ubicaciones";
				$("#grafico_mapa").load("contenido/mupis+ubicaciones+dinamico.php?accion=mapas&usuario=' . $usuario . '&catorcena="+$(\'#combo_catorcenas\').val()+"&calle="+$(\'#combo_calles\').val());
				}
				</script>
			    ';
	  }
				
      $BotonVerPorMupis = NULL;
	  echo '<div id="div_peatonal" style="display:none"></div><div id="div_vehicular" style="display:none"></div>';
      echo '<h1 id="ubicaciones">Ubicaciones de MUPIS contratados</h1><hr />';
      
      echo '<table>';
      echo '<tr>';
      echo '<td valign="top" width="15%">';
      
      if (!isset($_GET['verpormupis'])) {
          $Boton_combo_catorcenas = '<input type="button" OnClick="funcion_combo_catorcenas()" value="Mostrar calles">';
          echo '<b>Ver Catorcena:</b><br />' . $database->Combobox_CatorcenasConPresencia("combo_catorcenas", $usuario) . $Boton_combo_catorcenas . '<br /><br />';
          echo '<span id="datos_calles"><b>Seleccione una catorcena</b><br /><br /></span>';
          //echo '<span id="lista_mupis"><b>Seleccione una calle</b><br /><br /></span>';
          //Deshabilitado - 17/02/09 - petición de Alejandro.
          echo '<span id="lista_mupis"></span>';
          
          if (in_array($session->userlevel, $NivelesPermitidos)) {
              $BotonVerPorMupis = "<br /><hr />" . '<input type="button" OnClick="window.location=\'./?' . _ACC_ . '=ver+ubicaciones&verpormupis=1\'" value="Ver por Mupis">';
          }
      } else {
          $Boton_combo_calles = '<input type="button" OnClick="funcion_combo_ver_mupi_calles()" value="Ver">';
		  echo '<b>Trabajar Catorcena:</b><br />' . Combobox_catorcenas("combo_catorcenas", Obtener_catorcena_cercana()) . '<br />';
          echo '<b>Ver Calle:</b><br />' . $database->Combobox_calle("combo_calles") . $Boton_combo_calles . '<br /><br />';
          
          if (in_array($session->userlevel, $NivelesPermitidos)) {
              $BotonVerPorMupis = "<br /><hr />" . '<input type="button" OnClick="window.location=\'./?' . _ACC_ . '=ver+ubicaciones\'" value="Ver por Pantallas">';
          }
		  echo "\n".
		  '<script>
		  function funcion_combo_ver_mupi_calles() {
		  $("#grafico_mapa").load("contenido/mupis+ubicaciones+dinamico.php?accion=mupis&sin_presencia=si&catorcena="+$(\'#combo_catorcenas\').val()+"&calle="+$(\'#combo_calles\').val());
		  }
		  </script>
		  ';
      }
      echo $BotonVerPorMupis;
	  echo "<br /><hr /><div id='botones_arte'></div>";
	  
      echo '</td>';
      
      echo '<td>';
	  
      echo '
	  <div id="Mensajes" style="font-weight:bold;padding: 10px 10px">
	  <h2>Instrucciones de uso.</h2>
	  Para utilizar su sistema de ubicación Eco Mupis debe seguir los siguientes pasos:<br />
	  <ol>
	  <li id="MM_paso_1">Escoja la catorcena de la cual desea ver sus Eco Mupis y presione el botón "Mostrar calles".</li>
	  <li>Aparecerá una selección de calles en las cuales Ud. tiene Eco Mupis con su publicidad, escoja la calle de la cual desee ver el mapa y presione "Mostrar Mapa".</li>
	  <li>Deberá aparecer un Mapa con los Eco Mupis (representados como pequeños cuadros rojos) que contienen las fotos de su publicidad.<br />Al realizar "clic" sobre dichos cuadros rojos podrá observar las fografías reales de sus caras contratadas.</li>
	  <li>Repita los pasos 1 a 3 tanto como Ud. guste.</li>
	  </ol>
	  </div>';
	  echo '<div id="map" style="width: 100%; height: 500px"></div>';
	  echo '<div id="sidebar_map"></div>';
	  echo '<div id="grafico_mapa"></div>';
	  
      echo '</td>';
      
      echo '</tr>';
      echo '</table>';
      echo '<span id="datos_mupis">Seleccione un ' . _NOMBRE_ . '</span>';
  }
?>
