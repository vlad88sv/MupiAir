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
$s='<select name="'.$nombre.'" id="'.$nombre.'">';
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
if ( !$referencia ) { 
	$referencia = mktime(0,0,0,date('n'),date('d'),date('y')); 
} else {
	//echo "REFERENCIA: ".$referencia. date(" - h:i:s d/m/Y", $referencia)."<br />";
	$referencia = mktime(0,0,0,date('m',$referencia), date('d',$referencia), date('Y',$referencia));
	//echo "POST-REFERENCIA: ".$referencia. date(" - h:i:s d/m/Y", $referencia)."<br />";
}
$inicio=mktime(0,0,0,$MesInicio,$DiaInicio,$AnioInicio);
//echo $inicio;
if ($referencia < $inicio ) { $referencia = $inicio;}
do {
   $catorcena = strtotime("+13 day",$inicio);
   //echo "INICIO: ".$inicio. "; FIN: ". $catorcena."<br />";
   if (($referencia >= $inicio) && ($referencia <= $catorcena)) {return $inicio; }
   $inicio =  strtotime("+1 day",$catorcena);
} while ( 1 );
}

function Obtener_catorcena_siguiente() {
	return strtotime("+14 day",Obtener_catorcena_cercana());
}

function Obtener_catorcena_anterior($referencia=NULL) {
	if ( !$referencia ) { $referencia = Obtener_catorcena_cercana(); }
	return strtotime("-14 day", $referencia);
}

function Contar_catorcenas ($inicio=0, $fin=0 ) {
if ($fin)
return ceil((($fin-$inicio)/1209600));
return ceil((($inicio)/1209600));
}

function Fin_de_catorcena ($referencia = NULL) {
if (!$referencia) {return NULL;}
return strtotime("+13 day",$referencia);
}

function Obtener_Fecha_Base ( $referencia = NULL) {
	if (!$referencia) {return NULL;}
	$return = mktime(0,0,0,date('m',$referencia), date('d',$referencia), date('Y',$referencia));
}

function Obtener_Fecha_Tope ( $referencia = NULL) {
	if (!$referencia) {return NULL;}
	return mktime(23,59,59,date('m',$referencia), date('d',$referencia), date('Y',$referencia));
}
?>
