<?php
require_once("../include/sesion.php");
require_once("../include/database.php");

      /* Make sure administrator is accessing page */
      if($session->isAdmin()){
      
      /* Admin submitted update user level form */
      if(isset($_POST['subupdlevel'])){
         procUpdateLevel();
      }
      /* Admin submitted delete user form */
      else if(isset($_POST['subdeluser'])){
         procDeleteUser();
      }
      /* Admin submitted delete inactive users form */
      else if(isset($_POST['subdelinact'])){
         procDeleteInactive();
      }
   } else {
   
   return;
   
   }

   /**
    * procUpdateLevel - If the submitted username is correct,
    * their user level is updated according to the admin's
    * request.
    */
   function procUpdateLevel(){
      global $session, $database, $form;
      /* Username error checking */
      $subuser = checkUsername("upduser");
	  $database->REGISTRAR("USUARIO_NIVEL", "Se cambió el nivel de acceso de un usuario.", "Usuario afectado: $subuser");
      
      /* Errors exist, have user correct them */
      if($form->num_errors > 0){
         $_SESSION['value_array'] = $_POST;
         $_SESSION['error_array'] = $form->getErrorArray();
         header("Location: ../?accion=gestionar+clientes");
      }
      /* Update user level */
      else{
         $database->updateUserField($subuser, "userlevel", (int)$_POST['updlevel']);
         header("Location: ../?accion=gestionar+clientes");
      }
   }
   
   /**
    * procDeleteUser - If the submitted username is correct,
    * the user is deleted from the database.
    */
   function procDeleteUser(){
      global $session, $database, $form;
      /* Username error checking */
      $subuser = checkUsername("deluser");
      $database->REGISTRAR("USUARIO_ELIMINAR", "Se eliminó un usuario.", "Usuario afectado: $subuser");
      /* Errors exist, have user correct them */
      if($form->num_errors > 0){
         $_SESSION['value_array'] = $_POST;
         $_SESSION['error_array'] = $form->getErrorArray();
         header("Location: ../?accion=gestionar+clientes");
      }
      /* Delete user from database */
      else{
         $q = "DELETE FROM ".TBL_USERS." WHERE codigo = '$subuser'";
         $database->query($q);
         header("Location: ../?accion=gestionar+clientes");
      }
   }
      
   /**
    * checkUsername - Helper function for the above processing,
    * it makes sure the submitted username is valid, if not,
    * it adds the appropritate error to the form.
    */
   function checkUsername($uname, $ban=false){
      global $database, $form;
      /* Username error checking */
      $subuser = $_POST[$uname];
      $field = $uname;  //Use field name for username
      if(!$subuser || strlen($subuser = trim($subuser)) == 0){
         $form->setError($field, "* Usuario no ingresado<br>");
      }
      else{
         /* Make sure username is in database */
         $subuser = stripslashes($subuser);
         if(strlen($subuser) < 5 || strlen($subuser) > 30 ||
            !eregi("^([0-9a-z])+$", $subuser) ||
            (!$ban && !$database->codigoTaken($subuser))){
            $form->setError($field, "* Usuario no existe<br>");
         }
      }
      return $subuser;
}

?>
