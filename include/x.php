<?
require_once("sesion.php");
require_once("../contenido/sub.php");

class Process
{
   /* Class constructor */
   function Process(){
      global $session;
      /* User submitted login form */
      if(isset($_POST['sublogin'])){
	 $this->procLogin();
      }
      /* User submitted registration form */
      else if(isset($_POST['subjoin'])){
         $this->procRegister();
      }
      /* User submitted forgot password form */
      else if(isset($_POST['subforgot'])){
         $this->procForgotPass();
      }
      /* User submitted edit account form */
      else if(isset($_POST['subedit'])){
         $this->procEditAccount();
      }
      /**
       * The only other reason user should be directed here
       * is if he wants to logout, which means user is
       * logged in currently.
       */
      else if($session->logged_in){
	DEPURAR ('ok Logout');
         $this->procLogout();
      }
      /**
       * Should not get here, which means user is viewing this page
       * by mistake and therefore is redirected.
       */
       else{
         header("Location: ../");
       }
   }

   /**
    * procLogin - Processes the user submitted login form, if errors
    * are found, the user is redirected to correct the information,
    * if not, the user is effectively logged in to the system.
    */
   function procLogin(){
      global $session, $form;
      /* Login attempt */
      DEPURAR ("Intengo de LOGIN");
      $retval = $session->login($_POST['codigo'], $_POST['clave'], isset($_POST['remember']));
      
      /* Login successful */
      if($retval){
	$_SESSION['regsuccess'] = true;
	
      }
      /* Login failed */
      else{
	 $_SESSION['regsuccess'] = false;
         $_SESSION['value_array'] = $_POST;
         $_SESSION['error_array'] = $form->getErrorArray();
      }
	 header("Location:../");      
   }
   
   /**
    * procLogout - Simply attempts to log the user out of the system
    * given that there is no logout form to process.
    */
   function procLogout(){
      global $session;
      $retval = $session->logout();
	header("Location: ../");
   }
   
   /**
    * procRegister - Processes the user submitted registration form,
    * if errors are found, the user is redirected to correct the
    * information, if not, the user is effectively registered with
    * the system and an email is (optionally) sent to the newly
    * created user.
    */
   function procRegister(){
      global $session, $form;
      /* Convert username to all lowercase (by option) */
      if(ALL_LOWERCASE){
         $_POST['codigo'] = strtolower($_POST['codigo']);
      }
      /* Registration attempt */
      $retval = $session->register($_POST['codigo'], $_POST['clave'], $_POST['nombre'],$_POST['razon'], $_POST['email'], $_POST['telefono1'], $_POST['telefono2'], $_POST['telefono3'], '', $_POST['notas']);
      //echo $retval;
      /* Registration Successful */
      $_SESSION['reguname'] = $_POST['codigo'];
      if($retval == 0){
         $_SESSION['regsuccess'] = true;
      }
      /* Error found with form */
      else if($retval == 1){
         $_SESSION['value_array'] = $_POST;
         $_SESSION['error_array'] = $form->getErrorArray();
         $_SESSION['regsuccess'] = false;
      }
      /* Registration attempt failed */
      else if($retval == 2){
         $_SESSION['regsuccess'] = false;

      }
	header("Location: ../?"._ACC_."=registro");
}
   
   /**
    * procForgotPass - Validates the given username then if
    * everything is fine, a new password is generated and
    * emailed to the address the user gave on sign up.
    */
   function procForgotPass(){
      global $database, $session, $mailer, $form;
      /* Username error checking */
      $subuser = $_POST['codigo'];
      $field = "user";  //Use field name for username
      if(!$subuser || strlen($subuser = trim($subuser)) == 0){
         $form->setError($field, "* Username not entered<br>");
      }
      else{
         /* Make sure username is in database */
         $subuser = stripslashes($subuser);
         if(strlen($subuser) < 5 || strlen($subuser) > 30 ||
            !eregi("^([0-9a-z])+$", $subuser) ||
            (!$database->usernameTaken($subuser))){
            $form->setError($field, "* Username does not exist<br>");
         }
      }
      
      /* Errors exist, have user correct them */
      if($form->num_errors > 0){
         $_SESSION['value_array'] = $_POST;
         $_SESSION['error_array'] = $form->getErrorArray();
      }
      /* Generate new password and email it to user */
      else{
         /* Generate new password */
         $newpass = $session->generateRandStr(8);
         
         /* Get email of user */
         $usrinf = $database->getUserInfo($subuser);
         $email  = $usrinf['email'];
         
         /* Attempt to send the email with new password */
         if($mailer->sendNewPass($subuser,$email,$newpass)){
            /* Email sent, update database */
            $database->updateUserField($subuser, "password", md5($newpass));
            $_SESSION['forgotpass'] = true;
         }
         /* Email failure, do not change password */
         else{
            $_SESSION['forgotpass'] = false;
         }
      }
      
      header("Location: ../?accion=rpr+clave");
   }
   
   /**
    * procEditAccount - Attempts to edit the user's account
    * information, including the password, which must be verified
    * before a change is made.
    */
   function procEditAccount(){
      global $session, $form;
      /* Account edit attempt */
      $retval = $session->editAccount($_POST['newpass'], $_POST['codigo'], $_POST['nombre'],$_POST['razon'], $_POST['email'], $_POST['telefono1'], $_POST['telefono2'], $_POST['telefono3'], $_POST['logotipo'], $_POST['notas']);

      /* Account edit successful */
      if($retval){
         $_SESSION['useredit'] = true;
      }
      /* Error found with form */
      else{
         $_SESSION['value_array'] = $_POST;
         $_SESSION['error_array'] = $form->getErrorArray();
      }
	header("Location: ../?"._ACC_."=editar+usuario"); 
   }
};

/* Initialize process */
$process = new Process;

?>
