<?php
  $inicioCatorcena = Obtener_catorcena_cercana();
  function CONTENIDO_global_estadisticas($usuario)
  {
      global $session, $database, $inicioCatorcena;
      echo '
  <script type="text/javascript">
  $(document).ready(function() {
  $("#toggler_pantallas_activas").click(function() {
  $("#tabla_pantallas_activas").toggle();
  });
  $("#toggler_registros").click(function() {
  $("#tabla_registros").toggle();
  });
  });
  </script>
  ';
      $NivelesPermitidos = array(ADMIN_LEVEL);
      if (in_array($session->userlevel, $NivelesPermitidos) && !$usuario) {
          echo "<h1>Estadísticas y notas administrativas</h1>";
          
          echo MOSTRAR_comentarios();
		  
		  //=====================================================================================//
		  //	Pantallas activas en esta catorcena
		  //_____________________________________________________________________________________
          echo "<hr /><h2>Pantallas activas esta catorcena</h2>";
          $q = "SELECT id_pantalla, tipo_pantalla, codigo_mupi, (SELECT CONCAT(b.codigo_mupi, '. ' , (SELECT ubicacion FROM " . TBL_STREETS . " AS c WHERE c.codigo_calle = b.codigo_calle), ', ' , b.direccion) FROM " . TBL_MUPI . " as b WHERE b.id_mupi=a.codigo_mupi) as codigo_mupi_traducido, codigo_pedido, (SELECT CONCAT(codigo_pedido, '. ' , o.descripcion) FROM " . TBL_MUPI_ORDERS . " as o WHERE o.codigo_pedido = a.codigo_pedido) as codigo_pedido_traducido, catorcena, foto_real, codigo_evento FROM " . TBL_MUPI_FACES . " as a WHERE catorcena = '$inicioCatorcena' ORDER BY codigo_mupi, tipo_pantalla;";
          $result = $database->query($q);
          $num_rows = mysql_numrows($result);
          if ($num_rows == 0) {
              echo Mensaje("¡No hay pantallas ingresadas!", _M_NOTA);
          } else {
			  echo "<a id='toggler_pantallas_activas'>Mostrar/Ocultar lista de pantallas activas</a>";
              echo "<table id='tabla_pantallas_activas' style='display:none'>";
              echo "<tr><th>Código Eco Mupis</th><th>Cara</th><th>Código pedido</th></tr>";
              for ($i = 0; $i < $num_rows; $i++) {
                  $tipo_pantalla = mysql_result($result, $i, "tipo_pantalla") == 0 ? 'Vehicular' : 'Peatonal';
                  $codigo_mupi = mysql_result($result, $i, "codigo_mupi_traducido");
                  $codigo_pedido = mysql_result($result, $i, "codigo_pedido_traducido");
                  echo "<tr><td>$codigo_mupi</td><td>$tipo_pantalla</td><td>$codigo_pedido</td></tr>";
              }
			  echo "<tfoot>";
			  echo "<td colspan='2'>Total</td><td>$num_rows</td>";
			  echo "</tfoot>";
              echo "</table><br>";
          }
		  //=====================================================================================//

		  //=====================================================================================//
		  //	Clientes con notas administrativas
		  //_____________________________________________________________________________________
          echo "<hr /><h2>Clientes con notas administrativas</h2>";
          $q = "SELECT codigo, notas FROM emupi_usuarios WHERE notas!='' and userlevel!=9;";
          $result = $database->query($q);
          $num_rows = mysql_numrows($result);
          if ($num_rows == 0) {
              echo Mensaje("¡No hay clientes con notas administativas!", _M_NOTA);
          } else {
              echo '<table>';
              echo "<tr><th>Cliente</th><th>Nota</th></tr>";
              for ($i = 0; $i < $num_rows; $i++) {
                  $codigo = mysql_result($result, $i, "codigo");
                  $notas = mysql_result($result, $i, "notas");
                  echo "<tr><td>$codigo</td><td>$notas</td></tr>";
              }
			  echo "<tfoot>";
			  echo "<td>Total</td><td>$num_rows</td>";
			  echo "</tfoot>";
              echo "</table><br>";
          }
		  //=====================================================================================//
		  //	Eventos
		  //_____________________________________________________________________________________
          MOSTRAR_eventos();
		  //=====================================================================================//

		  //=====================================================================================//
		  //	Registro
		  //_____________________________________________________________________________________
		  echo "<hr /><h2>Registro</h2>";
          $q = "SELECT clave, valor, detalle, autor, timestamp FROM " . TBL_REGISTRY . " ORDER BY timestamp DESC LIMIT 10";
          $result = $database->query($q);
          $num_rows = mysql_numrows($result);
          if ($num_rows == 0) {
              echo Mensaje("¡No hay registros!", _M_NOTA);
          } else {
              echo "<a id='toggler_registros'>Mostrar/Ocultar lista de registros (10 últimos, más reciente primero).</a>";
              echo "<table id=\"tabla_registros\" style=\"display:none\">";
              echo "<tr><th>Fecha y Hora</th><th>Clave</th><th>Valor</th><th>Autor</th></tr>";
              for ($i = 0; $i < $num_rows; $i++) {
                  $timestamp = date("h:i:s @ d/m/Y", mysql_result($result, $i, "timestamp"));
                  $clave = mysql_result($result, $i, "clave");
                  $valor = mysql_result($result, $i, "valor");
                  $detalle = mysql_result($result, $i, "detalle");
                  $autor = mysql_result($result, $i, "autor");
                  echo "<tr><td>$timestamp</td><td>$clave</td><td>$valor <acronym title=\"$detalle\">¿?</acronym></td><td>$autor</td></tr>";
              }
			  echo "<tfoot>";
			  echo "<td colspan='3'>Total</td><td>$num_rows</td>";
			  echo "</tfoot>";
              echo "</table><br>";
          }
          return;
      }
      
      $NivelesPermitidos = array(ADMIN_LEVEL, SALESMAN_LEVEL);
      if (!in_array($session->userlevel, $NivelesPermitidos)) {
          $usuario = $session->codigo;
          $estadisticasPara = "";
      } else {
          $estadisticasPara = " para $usuario";
      }
      echo "<h1>Estadísticas$estadisticasPara</h1>";
      //Dinamismo en selección de catorcenas.firef
      echo SCRIPT('
  function ObtenerEstad(){
    $("#datos_catorcena").load("contenido/global+estadisticas+dinamico.php?usuario=' . $usuario . '&catorcena="+$(\'#catorcenas_presencia\').val());
  }
  $("#catorcenas_presencia").change(function (){ObtenerEstad();});
  ObtenerEstad();
');
      /*********************************************************************************************/
      // Inicio de parte dinámica.
      /*********************************************************************************************/
      echo "<br />Seleccione la catorcena de la cúal desee ver las estadísticas " . $database->Combobox_CatorcenasConPresencia("catorcenas_presencia", $usuario);
      echo '<hr><span id="datos_catorcena"><b>Seleccione una catorcena por favor</b></span>';
      echo '<hr />';
      
      if (!$usuario) {
          echo MOSTRAR_comentarios();
          echo MOSTRAR_eventos();
      }
      return;
  }
  
  function MOSTRAR_comentarios()
  {
      global $session, $database, $inicioCatorcena;
      echo "<hr /><h2>Comentarios publicados esta catorcena</h2>";
      $finCatorcena = Obtener_Fecha_Tope(Fin_de_catorcena($inicioCatorcena));
      $usuario = $tipo = null;
      if (!$session->isAdmin()) {
          $tipo = 'AND tipo=1';
          $usuario = $session->codigo;
      }
      $q = "SELECT (SELECT nombre FROM emupi_usuarios AS b WHERE b.codigo=a.codigo) AS codigo, comentario, timestamp, tipo FROM emupi_comentarios AS a WHERE timestamp>=$inicioCatorcena AND timestamp<=$finCatorcena $tipo ORDER BY tipo;";
      DEPURAR($q, 0);
      $result = $database->query($q);
      $num_rows = mysql_numrows($result);
      if ($num_rows == 0) {
          echo Mensaje("¡No hay comentarios ingresados!", _M_NOTA);
      } else {
          echo '<table>';
          if (!$usuario) {
              echo "<tr><th>Cliente</th><th>Comentario</th><th>Fecha</th><th>Tipo</th></tr>";
          } else {
              echo "<tr><th>Cliente</th><th>Comentario</th><th>Fecha</th></tr>";
          }
          for ($i = 0; $i < $num_rows; $i++) {
              $codigo = mysql_result($result, $i, "codigo");
              $comentario = mysql_result($result, $i, "comentario");
              $timestamp = date("h:i:s @ d/m/Y", mysql_result($result, $i, "timestamp"));
              if (!$usuario)
                  $tipo = mysql_result($result, $i, "tipo") == '1' ? 'Público' : 'Privado';
              if (!$usuario) {
                  echo "<tr><td>$codigo</td><td>$comentario</td><td>$timestamp</td><td>$tipo</td></tr>";
              } else {
                  echo "<tr><td>$codigo</td><td>$comentario</td><td>$timestamp</td></tr>";
              }
          }
		  echo "<tfoot>";
		  if (!$usuario) {
			echo "<td colspan='3'>Total</td><td>$num_rows</td>";
		  } else  {
			echo "<td colspan='2'>Total</td><td>$num_rows</td>";
		  }
		  echo "</tfoot>";
          echo "</table><br>";
      }
  }
  
  function MOSTRAR_eventos()
  {
      global $session, $database, $inicioCatorcena;
      $finCatorcena = Obtener_Fecha_Tope(Fin_de_catorcena($inicioCatorcena));
      echo "<hr /><h2>Eventos en esta catorcena</h2>";
      $usuario = $tipo = null;
      if (!$session->isAdmin()) {
          $usuario = $session->codigo;
          $tipo = "AND codigo_pedido IN (SELECT codigo_pedido FROM emupi_mupis_pedidos WHERE codigo='$usuario')";
      }
      $q = "select id_evento, timestamp, categoria, afectado, (SELECT CONCAT((SELECT ubicacion FROM emupi_calles AS b WHERE c.codigo_calle=@codigo_calle:=b.codigo_calle), ', ', direccion ) FROM emupi_mupis as c WHERE c.id_mupi=(SELECT codigo_mupi FROM emupi_mupis_caras WHERE id_pantalla=afectado)) AS afectado_traducido, descripcion_evento, foto_evento from emupi_mupis_eventos WHERE categoria='PANTALLA' AND afectado IN (SELECT id_pantalla FROM emupi_mupis_caras WHERE catorcena>=$inicioCatorcena AND catorcena<=$finCatorcena $tipo);";
      $result = $database->query($q);
      $num_rows = mysql_numrows($result);
      if ($num_rows == 0) {
          echo Mensaje("¡No hay eventos ingresados!", _M_NOTA);
      } else {
          echo '<table>';
          if ($usuario) {
              echo "<tr><th>Fecha y Hora</th><th>Objeto Afectado</th><th>Descripción</th><th>Foto</th></tr>";
          } else {
              echo "<tr><th>Código Evento " . _NOMBRE_ . "</th><th>Fecha y Hora</th><th>Categoría</th><th>Objeto Afectado</th><th>Descripción</th><th>Foto</th></tr>";
          }
          for ($i = 0; $i < $num_rows; $i++) {
              if (!$usuario)
                  $id_evento = mysql_result($result, $i, "id_evento");
              $timestamp = date('h:i:s @ d/m/Y', mysql_result($result, $i, "timestamp"));
              if (!$usuario)
                  $categoria = mysql_result($result, $i, "categoria");
              $afectado = mysql_result($result, $i, "afectado_traducido");
              $descripcion_evento = mysql_result($result, $i, "descripcion_evento");
              $foto_evento = mysql_result($result, $i, "foto_evento");
              if ($foto_evento) {
                  $foto_evento = CREAR_LINK_GET("ver:" . mysql_result($result, $i, "foto_evento"), "Ver foto", "Muestra la foto del evento");
              }
              if (!$usuario)
                  $id_evento = CREAR_LINK_GET("gestionar+eventos&amp;evento=" . $id_evento, $id_evento, "Editar los datos de este evento");
              if ($usuario) {
                  echo "<tr><td>$timestamp</td><td>$afectado</td><td>$descripcion_evento</td><td>$foto_evento</td></tr>";
              } else {
                  echo "<tr><td>$id_evento</td><td>$timestamp</td><td>$categoria</td><td>$afectado</td><td>$descripcion_evento</td><td>$foto_evento</td></tr>";
              }
          }
		  echo "<tfoot>";
		  if ($usuario) {
			echo "<td colspan='3'>Total</td><td>$num_rows</td>";
		  } else  {
			echo "<td colspan='5'>Total</td><td>$num_rows</td>";
		  }
		  echo "</tfoot>";
          echo "</table><br>";
      }
  }
?>
