<?
if(isset($_SESSION['forgotpass'])){
   if($_SESSION['forgotpass']){
      echo "<h3>Nueva Clave Generada</h3>";
      echo "<p>Tu nueva clave ha sido generada y enviada a el correo asociado con tu cuenta. "
          ."<a href=\"./\">Continuar</a>.</p>";
   }
   else{
      echo "<h3>Fallo al Recuperar Contraseña</h3>";
      echo "<p>Hubo un error mientras se te enviaba el corrreo con la nueva clave, asi que tu clave no a sido cambiada."
		."<a href=\"./\">Continuar</a>.</p>";
   }
       
   unset($_SESSION['forgotpass']);
}
function CONTENIDO_recuperar_clave(){
global $form;
echo '
<h3>Recuperación de Constraseña</h3>
Una nueva clave será envíada a tu cuenta de correo y será asociada a tu usuario.<br><br>'  . $form->error("user") . 
'<form action="x.php" method="POST">
<table border="0">
<tr>
<td><b>Usuario o código cliente:</b></td><td><input type="text" name="user" maxlength="30" value="' . $form->value("user") . '"></td>
</tr>
<tr>
<td></td><td><input type="submit" value="Obtener nueva clave"></td>
</tr>
</table>
<input type="hidden" name="subforgot" value="1">
</form>
';
}
?>
