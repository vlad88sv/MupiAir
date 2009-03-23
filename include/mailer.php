<? 

class Mailer
{
   function sendWelcome($user, $email, $pass){
      $from = "From: ".EMAIL_FROM_NAME." <".EMAIL_FROM_ADDR.">";
      $subject = "¡Bienvenido al Sistema Mupiair de Ecomupis!";
      $body = $user.",\n\n"
             ."¡Bienvenido! acaba de ser registrado en el sistema Ecomupis,"
             ." su registro se hizo con la siguiente información:\n\n"
             ."Usuario: ".$user."\n"
             ."Clave: ".$pass."\n\n"
             ."_________________________"."\n"
             ."Ecomupis";

      return @mail($email,$subject,$body,$from);
   }
   
   function sendNewPass($user, $email, $pass){
      $from = "From: ".EMAIL_FROM_NAME." <".EMAIL_FROM_ADDR.">";
      $subject = "Sistema Mupiair - Nueva contraseña";
      $body = $user.",\n\n"
             ."Hemos generado una nueva contraseña a tu petición "
             ."para que puedas reingresar al sistema con ella. "
             ."Usuario: ".$user."\n"
             ."Clave: ".$pass."\n\n"
             ."_________________________"."\n"
             ."Ecomupis";
             
      return mail($email,$subject,$body,$from);
   }
};

$mailer = new Mailer;
 
?>
