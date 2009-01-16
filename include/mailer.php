<? 

class Mailer
{
   function sendWelcome($user, $email, $pass){
      $from = "From: ".EMAIL_FROM_NAME." <".EMAIL_FROM_ADDR.">";
      $subject = "¡Bienvenido al Sistema de Horarios!";
      $body = $user.",\n\n"
             ."¡Bienvenido! acabas de se registrado como instructor en el Sistema de Horarios"
             ." con la siguiente información:\n\n"
             ."Usuario: ".$user."\n"
             ."Clave: ".$pass."\n\n"
	     ."Si tu clave esta en blanco significa que no necesitas de ella para ingresar al sistema\n"
             ."De lo contrario será necesario que la ingreses para poder acceder a tu cuenta";

      return @mail($email,$subject,$body,$from);
   }
   
   function sendNewPass($user, $email, $pass){
      $from = "From: ".EMAIL_FROM_NAME." <".EMAIL_FROM_ADDR.">";
      $subject = "Sistema de Horarios - Nueva contraseña";
      $body = $user.",\n\n"
             ."Hemos generado una nueva contraseña a tu petición "
             ."para que puedas reingresar al sistema con ella. "
             ."username to log in to Jpmaster77's Site.\n\n"
             ."Usuario: ".$user."\n"
             ."Clave: ".$pass."\n\n"
	     ."Si tu clave esta en blanco significa que no necesitas de ella para ingresar al sistema\n"
             ."De lo contrario será necesario que la ingreses para poder acceder a tu cuenta\n"
             ."Se recomienda solicitar un cambio de contraseña una vez ingrese al sistema";
             
      return mail($email,$subject,$body,$from);
   }
};

$mailer = new Mailer;
 
?>
