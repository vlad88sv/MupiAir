<?php
function DEPURAR($sTexto){
	if (0){echo '<br />'.$sTexto.'<br />';}
}

function print_ar($array, $count=0) {
    $i=0;
    $tab ='';
		$k=0;
    while($i != $count) {
        $i++;
        $tab .= "&nbsp;&nbsp;|&nbsp;&nbsp;";
    }
    foreach($array as $key=>$value){
        if(is_array($value)){
            echo $tab."[$key]<br />";
            $count++;
            print_ar($value, $count);
            $count--;
        }
        else{
            $tab2 = substr($tab, 0, -12);
            echo "$tab2~ $key: $value<br />";
        }
        $k++;
    }
    $count--;
}
?>
