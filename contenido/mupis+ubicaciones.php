<?php
  function CONTENIDO_mupis_ubicaciones($usuario = '')
  {
      global $session, $database, $map;
      $NivelesPermitidos = array(ADMIN_LEVEL, SALESMAN_LEVEL);
      if (!in_array($session->userlevel, $NivelesPermitidos)) {
          $usuario = $session->codigo;
		  unset($_GET['verpormupis']);
      }
      //Importante!!! Esto tiene que suceder antes de cualquier cuestión AJAX porque Google esta usando document.write en algún momento!.
      echo sprintf('<script src="http://maps.google.com/maps?file=api&amp;v=2&amp;key=%s" type="text/javascript" charset="utf-8"></script>', GOOGLE_MAP_KEY);
      echo '<script src="include/jquery.form.js" charset="utf-8"></script>';
      // AJAX ;)
      if (!isset($_GET['verpormupis'])) {
          echo "\n".
				'<script>
				function funcion_combo_catorcenas(){
				$("#grafico_mapa").html("<center><b>Por favor escoja Calle de la lista a su Izq. y presione Ver</b></center>");
				$("#datos_calles").load("contenido/mupis+ubicaciones+dinamico.php?accion=calles&usuario=' . $usuario . '&catorcena="+$(\'#combo_catorcenas\').val());
				}
				function funcion_combo_calles() {
				$("#datos_mupis").empty();
				$("#grafico_mapa").html("<center><b>Generando mapa, por favor espere...</b></center>");
				$("#grafico_mapa").load("contenido/mupis+ubicaciones+dinamico.php?accion=mapas&usuario=' . $usuario . '&catorcena="+$(\'#combo_catorcenas\').val()+"&calle="+$(\'#combo_calles\').val());
				}
				</script>
			    ';
	  }
      $BotonVerPorMupis = NULL;
      echo "<h1>Ubicaciones de MUPIS contratados</h1><hr />";
      
      echo '<table>';
      echo '<tr>';
      echo '<td valign="top">';
      
      if (!isset($_GET['verpormupis'])) {
          $Boton_combo_catorcenas = '<input type="button" OnClick="funcion_combo_catorcenas()" value="Ver">';
          echo '<b>Ver Catorcena:</b><br />' . $database->Combobox_CatorcenasConPresencia("combo_catorcenas", $usuario) . $Boton_combo_catorcenas . '<br /><br />';
          echo '<span id="datos_calles"><b>Seleccione una catorcena</b><br /><br /></span>';
          //echo '<span id="lista_mupis"><b>Seleccione una calle</b><br /><br /></span>';
          //Deshabilitado - 17/02/09 - petición de Alejandro.
          echo '<span id="lista_mupis"></span>';
          
          if (in_array($session->userlevel, $NivelesPermitidos)) {
              $BotonVerPorMupis = '<input type="button" OnClick="window.location=\'./?' . _ACC_ . '=ver+ubicaciones&verpormupis=1\'" value="Ver por Mupis">';
          }
      } else {
          $Boton_combo_calles = '<input type="button" OnClick="funcion_combo_ver_mupi_calles()" value="Ver">';
		  echo '<b>Trabajar Catorcena:</b><br />' . Combobox_catorcenas("combo_catorcenas", Obtener_catorcena_cercana()) . '<br />';
          echo '<b>Ver Calle:</b><br />' . $database->Combobox_calle("combo_calles") . $Boton_combo_calles . '<br /><br />';
          
          if (in_array($session->userlevel, $NivelesPermitidos)) {
              $BotonVerPorMupis = '<input type="button" OnClick="window.location=\'./?' . _ACC_ . '=ver+ubicaciones\'" value="Ver por Pantallas">';
          }
		  echo "\n".
		  '<script>
		  function funcion_combo_ver_mupi_calles() {
		  $("#grafico_mapa").load("contenido/mupis+ubicaciones+dinamico.php?accion=mupis&sin_presencia=si&catorcena="+$(\'#combo_catorcenas\').val()+"&calle="+$(\'#combo_calles\').val());
		  }
		  </script>
		  ';
      }
      echo "<br /><hr />" . $BotonVerPorMupis;
      echo '</td>';
      
      echo '<td id="grafico_mapa" width="80%">';
      echo '<center><b>Escoja Catorcena de la lista a su Izq. y presione Ver.</b></center>';
      echo '</td>';
      
      echo '</tr>';
      echo '</table>';
      echo '<span id="datos_mupis"><center><b>Seleccione un ' . _NOMBRE_ . '</b></center></span>';
  }
?>
