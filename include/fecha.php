<?php
function Combobox_catorcenas_todas() {
$inicio=mktime(0,0,0,1,1,date('Y'));
$s='<select name= "nombreDelCombo">';
for ($i=0; $i<26; $i++){
  $catorcena = strtotime("+13 day",$inicio);
  //echo "Del " . date('d-m-Y',$inicio) . ' al ' . date('d-m-Y',$catorcena) . '<br />';
  $s.='<option value= "'.$inicio.'">'."Del " . date('d-m-Y',$inicio) . ' al ' . date('d-m-Y',$catorcena) .'</option>';
  $inicio =  strtotime("+1 day",$catorcena);
}
$s.= '</select>';
return $s;
}

function Combobox_catorcenas_futuras($cuantas = 3) {
$inicio=mktime(0,0,0,date('n'),1,date('Y'));
$s= '<select name= "nombreDelCombo">';
for ($i=0; $i<=$cuantas; $i++){
  $catorcena = strtotime("+13 day",$inicio);
  //echo "Del " . date('d-m-Y',$inicio) . ' al ' . date('d-m-Y',$catorcena) . '<br />';
  $s.= '<option value= "'.$inicio.'">'."Del " . date('d-m-Y',$inicio) . ' al ' . date('d-m-Y',$catorcena) .'</option>';
  $inicio =  strtotime("+1 day",$catorcena);
}
$s.='</select>';
return $s;
}
?>