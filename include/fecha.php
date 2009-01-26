<?php
/* Configuración */
$MesInicio = 1;
$DiaInicio = 13;
$AnioInicio = 2009;

/*****************************/
/* Constantes exportadas */
define ("_F_AMBAS", 0);
define ("_F_INICIOS", 1);
define ("_F_FINES", 2);

/*****************************/
function Combobox_catorcenas($nombre="catorcena", $default=NULL, $cuantas = 26, $tipo=_F_AMBAS) {
global $MesInicio, $DiaInicio, $AnioInicio;
if ( !$default ) { $default=time(); }
$inicio=Obtener_catorcena_cercana(mktime(0,0,0,1,1,date('Y')));
$s='<select name="'.$nombre.'">';
for ($i=0; $i<$cuantas; $i++){
  $catorcena = strtotime("+13 day",$inicio);
  if ( $inicio == $default || $catorcena == $default ) { $selected = ' selected="selected"'; } else { $selected = ""; }
  
  switch ( $tipo ) {
  case _F_AMBAS:
	$s.='<option value="'.$inicio.'"'.$selected.'>'."Del " . date('d-m-Y',$inicio) . ' al ' . date('d-m-Y',$catorcena) .'</option>';
	break;
  case _F_INICIOS:
	$s.='<option value="'.$inicio.'"'.$selected.'>'."Del " . date('d-m-Y',$inicio)  .'</option>';
	break;
  case _F_FINES:
  	$s.='<option value="'.$catorcena.'"'.$selected.'>'.'al ' . date('d-m-Y',$catorcena) .'</option>';
	break;
  }
	$inicio =  strtotime("+1 day",$catorcena);
}
$s.= '</select>';
return $s;
}

function Obtener_catorcena_cercana ($referencia = NULL ) {
global $MesInicio, $DiaInicio, $AnioInicio;
if ( !$referencia ) { $referencia = mktime(0,0,0,date('n'),date('d'),date('y')); }
$inicio=mktime(0,0,0,$MesInicio,$DiaInicio,$AnioInicio);
if ($referencia < $inicio ) { $referencia = $inicio;}
do {
   $catorcena = strtotime("+13 day",$inicio);
   if (($referencia >= $inicio) && ($referencia <= $catorcena)) {return $inicio; }
   $inicio =  strtotime("+1 day",$catorcena);
} while ( 1 );
}

function Contar_catorcenas ($inicio=0, $fin=0 ) {
return ((($fin-$inicio)/60/60/24) + 1) / 14;
}

function Fin_de_catorcena ($referencia = NULL) {
if (!$referencia) {return NULL;}
return strtotime("+13 day",$referencia);
}
?>
