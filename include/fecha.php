<?php
function Combobox_catorcenas($nombre="catorcena", $default=NULL, $cuantas = NULL) {
if ( !$default ) { $default = time(); }
if ( $cuantas ) {
	$inicio=mktime(0,0,0,date('n',$default),1,date('Y', $default));
} else {
	$inicio=mktime(0,0,0,1,1,date('Y', $default));
	$cuantas = 26;
}
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
?>