<?
require_once("database.php");
require_once("mailer.php");
require_once("form.php");
require_once("depurar.php");
class Session
{
   var $codigo;       //codigo given on sign-up
   var $userid;       //Random value generated on current login
   var $userlevel;    //The level to which the user pertains
   var $time;         //Time user was last active (page loaded)
   var $logged_in;    //True if user is logged in, false otherwise
   var $userinfo = array();  //The array holding all user info
   var $url;          //The page url current being viewed
   var $referrer;     //Last recorded site page viewed

   /* Class constructor */
   function Session(){
      $this->time = time();
      $this->startSession();
   }

   /**
    * startSession - Performs all the actions necessary to 
    * initialize this session object. Tries to determine if the
    * the user has logged in already, and sets the variables 
    * accordingly. Also takes advantage of this page load to
    * update the active visitors tables.
    */
   function startSession(){
      global $database;  //The database connection
      session_start();   //Tell PHP to start the session
      /* Determine if user is logged in */
      $this->logged_in = $this->checkLogin();

      /**
       * Set guest value to users not logged in, and update
       * active guests table accordingly.
       */
      if(!$this->logged_in){
         $this->codigo = $_SESSION['codigo'] = GUEST_NAME;
         $this->userlevel = GUEST_LEVEL;
         $database->addActiveGuest($_SERVER['REMOTE_ADDR'], $this->time);
      }
      /* Update users last active timestamp */
      else{
         $database->addActiveUser($this->codigo, $this->time);
      }
      
      /* Remove inactive visitors from database */
      $database->removeInactiveUsers();
      $database->removeInactiveGuests();
   }

   /**
    * checkLogin - Checks if the user has already previously
    * logged in, and a session with the user has already been
    * established. Also checks to see if user has been remembered.
    * If so, the database is queried to make sure of the user's 
    * authenticity. Returns true if the user has logged in.
    */
   function checkLogin(){
      global $database;  //The database connection
      /* Check if user has been remembered */
      if(isset($_COOKIE['cookname']) && isset($_COOKIE['cookid'])){
         $this->codigo = $_SESSION['codigo'] = $_COOKIE['cookname'];
         $this->userid   = $_SESSION['userid']   = $_COOKIE['cookid'];
	 DEPURAR ("Check Login: " . $this->codigo);
      }

	DEPURAR ("Check Login GUEST_NAME: " . GUEST_NAME);
      /* codigo and userid have been set and not guest */
      if(isset($_SESSION['codigo']) && isset($_SESSION['userid']) &&
	 $_SESSION['codigo'] != GUEST_NAME){
	 DEPURAR ("Check Login: codigo and userid have been set and not guest");
         /* Confirm that codigo and userid are valid */
         if($database->confirmUserID($_SESSION['codigo'], $_SESSION['userid']) != 0){
	 DEPURAR("Check Login: Variables are incorrect, user not logged in");
            /* Variables are incorrect, user not logged in */
            unset($_SESSION['codigo']);
            unset($_SESSION['userid']);
            return false;
         }
	DEPURAR ("Check Login SESSION[codigo]: " . $_SESSION['codigo']);
         /* User is logged in, set class variables */
         $this->userinfo = $database->getUserInfo($_SESSION['codigo']);
         $this->codigo = $this->userinfo['codigo'];
         $this->userid = $this->userinfo['userid'];
         $this->userlevel = $this->userinfo['userlevel'];
	 DEPURAR ("Check Login IN!");
         return true;
      }
      /* User not logged in */
      else{
      DEPURAR("Check Login NOT IN!");
         return false;
      }
   }

   /**
    * login - The user has submitted his codigo and clave
    * through the login form, this function checks the authenticity
    * of that information in the database and creates the session.
    * Effectively logging in the user if all goes well.
    */
   function login($subuser, $subpass, $subremember){
      global $database, $form;  //The database and form object
      DEPURAR ("Login:".$subuser);
      /* codigo error checking */
      $field = "codigo";  //Use field name for codigo
      if(!$subuser || strlen($subuser = trim($subuser)) == 0){
         $form->setError($field, "* No se ingreso Código o Carné del Instructor");
      }
      else{
         /* Check if codigo is not alphanumeric */
         if(!eregi("^([0-9a-z])*$", $subuser)){
            $form->setError($field, "* codigo not alphanumeric");
         }
      }

      /* clave error checking */
      $field = "clave";  //Use field name for clave
      if(!$subpass){
         $form->setError($field, "* Olvidó ingresar la clave");
      }
      
      /* Return if form errors exist */
      if($form->num_errors > 0){
         return false;
      }
	DEPURAR ("Login: Checks 1 passed");
      /* Checks that codigo is in database and clave is correct */
      $subuser = stripslashes($subuser);
      $result = $database->confirmUserPass($subuser, md5($subpass));

      /* Check error codes */
      if($result == 1){
         $field = "codigo";
         $form->setError($field, "* Código o Carné de Instructor no encontrado");
	 DEPURAR ("Login: Not user");
      }
      else if($result == 2){
         $field = "clave";
         $form->setError($field, "* Clave inválida");
	 DEPURAR ("Login: Not clave");
      }
      
      /* Return if form errors exist */
      if($form->num_errors > 0){
         return false;
      }
	DEPURAR ("Login: Checks  2 passed");
      /* codigo and clave correct, register session variables */
      $this->userinfo  = $database->getUserInfo($subuser);
      $this->codigo  = $_SESSION['codigo'] = $this->userinfo['codigo'];
      $this->userid    = $_SESSION['userid']   = $this->generateRandID();
      $this->userlevel = $this->userinfo['userlevel'];
      
      /* Insert userid into database and update active users table */
      $database->updateUserField($this->codigo, "userid", $this->userid);
      $database->addActiveUser($this->codigo, $this->time);
      $database->removeActiveGuest($_SERVER['REMOTE_ADDR']);

      /**
       * This is the cool part: the user has requested that we remember that
       * he's logged in, so we set two cookies. One to hold his codigo,
       * and one to hold his random value userid. It expires by the time
       * specified in constants.php. Now, next time he comes to our site, we will
       * log him in automatically, but only if he didn't log out before he left.
       */
      if($subremember){
         setcookie("cookname", $this->codigo, time()+COOKIE_EXPIRE, COOKIE_PATH);
         setcookie("cookid",   $this->userid,   time()+COOKIE_EXPIRE, COOKIE_PATH);
      }
	DEPURAR ("Login: IN");
      /* Login completed successfully */
      return true;
   }

   /**
    * logout - Gets called when the user wants to be logged out of the
    * website. It deletes any cookies that were stored on the users
    * computer as a result of him wanting to be remembered, and also
    * unsets session variables and demotes his user level to guest.
    */
   function logout(){
      global $database;  //The database connection
      /**
       * Delete cookies - the time must be in the past,
       * so just negate what you added when creating the
       * cookie.
       */
      if(isset($_COOKIE['cookname']) && isset($_COOKIE['cookid'])){
         setcookie("cookname", "", time()-COOKIE_EXPIRE, COOKIE_PATH);
         setcookie("cookid",   "", time()-COOKIE_EXPIRE, COOKIE_PATH);
      }

      /* Unset PHP session variables */
      unset($_SESSION['codigo']);
      unset($_SESSION['userid']);

      /* Reflect fact that user has logged out */
      $this->logged_in = false;
      
      /**
       * Remove from active users table and add to
       * active guests tables.
       */
      $database->removeActiveUser($this->codigo);
      $database->addActiveGuest($_SERVER['REMOTE_ADDR'], $this->time);
      
      /* Set user level to guest */
      $this->codigo  = GUEST_NAME;
      $this->userlevel = GUEST_LEVEL;
   }

   /**
    * register - Gets called when the user has just submitted the
    * registration form. Determines if there were any errors with
    * the entry fields, if so, it records the errors and returns
    * 1. If no errors were found, it registers the new user and
    * returns 0. Returns 2 if registration failed.
    */
   function register($codigo, $clave, $nombre, $razon, $email, $telefono1, $telefono2, $telefono3, $logotipo, $notas){
      global $database, $form, $mailer;  //The database, form and mailer object
      $codigo = trim($codigo);
      $form->setValue("codigo", $codigo);
      $form->setValue("clave", $clave);
      $form->setValue("nombre", $nombre);
      $form->setValue("razon", $razon);
      $form->setValue("email", $email);
      $form->setValue("telefono1", $telefono1);
      $form->setValue("telefono2", $telefono2);
      $form->setValue("telefono3", $telefono3);
      $form->setValue("logotipo", $logotipo);
      $form->setValue("notas", $notas);

      //print_r (array($codigo, $clave, $nombre, $razon, $email, $telefono1, $telefono2, $telefono3, $logotipo, $notas));
      /* codigo error checking */
      $field = "codigo";
      if(!$codigo){
         $form->setError($field, "* Nombre de usuario no ingresado");
      }
      else{
         /* Spruce up codigo, check length */
         $codigo = stripslashes($codigo);
         if(strlen($codigo) < 5){
            $form->setError($field, "* Código fiscal o nombre de usuario debe ser mayor a 5 caracteres");
         }
         else if(strlen($codigo) > 100){
            $form->setError($field, "* Código fiscal o nombre de usuario debe ser menor de 100 caracteres");
         }
         /* Check if codigo is not alphanumeric */
         else if(!eregi("^([0-9a-z])+$", $codigo)){
            $form->setError($field, "* Código fiscal o nombre de usuario debe ser Alfanumerico");
         }
         /* Check if codigo is reserved */
         else if(strcasecmp($codigo, GUEST_NAME) == 0){
            $form->setError($field, "* Código fiscal o nombre de usuario introducido es una palabra reservada");
         }
         /* Check if codigo is already in use */
         else if($database->codigoTaken($codigo)){
            $form->setError($field, "* Código fiscal o nombre de usuario ya esta en uso");
         }
      }
     
      $field = "clave";
      if(!$clave){
         $form->setError($field, "* Clave no ingresada");
      }
      else{
         // Spruce up clave and check length
         $clave = stripslashes($clave);
         if(strlen($clave) < 4){
            $form->setError($field, "* Clave debe ser mayor a 4 caracteres");
         }
         // Check if clave is not alphanumeric
         else if(!eregi("^([0-9a-z])+$", ($clave = trim($clave)))){
            $form->setError($field, "* Clave no es Alfanumerica");
         }
      }
      
    
      /* Email error checking */
      $field = "email";  //Use field name for email
      
      if(!$email){
         $form->setError($field, "* Email no ingresado");
           
      }
      else{
      
         /* Check if valid email address */
         $regex = "^[_+a-z0-9-]+(\.[_+a-z0-9-]+)*"
                 ."@[a-z0-9-]+(\.[a-z0-9-]{1,})*"
                 ."\.([a-z]{2,}){1}$";
         if(!eregi($regex,$email)){
            $form->setError($field, "* Email inválido");
         }
         $email = stripslashes($email);
      }

      /* Errors exist, have user correct them */
      if($form->num_errors > 0){
         return 1;  //Errors with form
      }
      /* No errors, add the new account to the */
      else{
         if($database->addNewUser($codigo, md5($clave), $nombre, $razon, $email, $telefono1, $telefono2, $telefono3, $logotipo, $notas)){
            if(EMAIL_WELCOME){
               $mailer->sendWelcome($codigo,$email,$clave);
            }
            return 0;  //New user added succesfully
         }else{
            return 2;  //Registration attempt failed
         }
      }
   }
   
   /**
    * editAccount - Attempts to edit the user's account information
    * including the clave, which it first makes sure is correct
    * if entered, if so and the new clave is in the right
    * format, the change is made. All other fields are changed
    * automatically.
    */
   function editAccount($subnewpass, $codigo, $nombre, $razon, $email, $telefono1, $telefono2, $telefono3, $logotipo, $notas){
      global $database, $form;  //The database and form object
      $form->setValue("clave", $clave);
      $form->setValue("nombre", $nombre);
      $form->setValue("razon", $razon);
      $form->setValue("email", $email);
      $form->setValue("telefono1", $telefono1);
      $form->setValue("telefono2", $telefono2);
      $form->setValue("telefono3", $telefono3);
      $form->setValue("logotipo", $logotipo);
      $form->setValue("notas", $notas);
      /* Email error checking */
      $field = "email";  //Use field name for email
      if($email && strlen($email = trim($email)) > 0){
         /* Check if valid email address */
         $regex = "^[_+a-z0-9-]+(\.[_+a-z0-9-]+)*"
                 ."@[a-z0-9-]+(\.[a-z0-9-]{1,})*"
                 ."\.([a-z]{2,}){1}$";
         if(!eregi($regex,$email)){
            $form->setError($field, "* Email inválido");
         }
         $email = stripslashes($email);
      }
      
      /* Errors exist, have user correct them */
      if($form->num_errors > 0){
         return false;  //Errors with form
      }
      
	$database->updateUserField($codigo,"clave",md5($subnewpass));
	$database->updateUserField($codigo,"nombre",$nombre);
	$database->updateUserField($codigo,"razon",$razon);
	$database->updateUserField($codigo,"email",$email);
	$database->updateUserField($codigo,"telefono1",$telefono1);
	$database->updateUserField($codigo,"telefono2",$telefono2);
	$database->updateUserField($codigo,"telefono3",$telefono3);
	$database->updateUserField($codigo,"logotipo",$logotipo);
	$database->updateUserField($codigo,"notas",$notas);

      
      /* Success! */
      return true;
   }
   
   /**
    * isAdmin - Returns true if currently logged in user is
    * an administrator, false otherwise.
    */
   function isAdmin(){
      return ($this->userlevel == ADMIN_LEVEL ||
              $this->codigo  == ADMIN_NAME);
   }
   
   /**
    * generateRandID - Generates a string made up of randomized
    * letters (lower and upper case) and digits and returns
    * the md5 hash of it to be used as a userid.
    */
   function generateRandID(){
      return md5($this->generateRandStr(16));
   }
   
   /**
    * generateRandStr - Generates a string made up of randomized
    * letters (lower and upper case) and digits, the length
    * is a specified parameter.
    */
   function generateRandStr($length){
      $randstr = "";
      for($i=0; $i<$length; $i++){
         $randnum = mt_rand(0,61);
         if($randnum < 10){
            $randstr .= chr($randnum+48);
         }else if($randnum < 36){
            $randstr .= chr($randnum+55);
         }else{
            $randstr .= chr($randnum+61);
         }
      }
      return $randstr;
   }
};


/**
 * Initialize session object - This must be initialized before
 * the form object because the form uses session variables,
 * which cannot be accessed unless the session has started.
 */
$session = new Session;
/* Initialize form object */
$form = new Form;

?>
