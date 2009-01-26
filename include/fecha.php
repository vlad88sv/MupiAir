<?php
/* Configuración */
$MesInicio = 1;
$DiaInicio = 13;
$AnioInicio = 2009;

/*****************************/
function Combobox_catorcenas($nombre="catorcena", $default=NULL, $cuantas = 26) {
global $MesInicio, $DiaInicio, $AnioInicio;
if ( !$default ) { $default=time(); }
$inicio=Obtener_catorcena_cercana($default);
$s='<select name="'.$nombre.'">';
for ($i=0; $i<$cuantas; $i++){
  $catorcena = strtotime("+13 day",$inicio);
  if ( $default && $inicio == $default ) { $selected = ' selected="selected"'; } else { $selected = ""; }
  $s.='<option value="'.$inicio.'"'.$selected.'>'."Del " . date('d-m-Y',$inicio) . ' al ' . date('d-m-Y',$catorcena) .'</option>';
  $inicio =  strtotime("+1 day",$catorcena);
}
$s.= '</select>';
return $s;
}

function Obtener_catorcena_cercana ($referencia = NULL ) {
global $MesInicio, $DiaInicio, $AnioInicio;
if ( !$referencia ) { $referencia = time(); }
$inicio=mktime(0,0,0,$MesInicio,$DiaInicio,$AnioInicio);
if ($referencia < $inicio ) { return NULL;}
do {
   $catorcena = strtotime("+13 day",$inicio);
   if (($referencia >= $inicio) && ($referencia <= $catorcena)) { return $inicio; }
   $inicio =  strtotime("+1 day",$catorcena);
} while ( 1 );
}
?>
