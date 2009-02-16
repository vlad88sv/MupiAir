<?  
require_once("const.php");
class MySQLDB
{
   var $connection;         //The MySQL database connection
   var $num_active_users;   //Number of active users viewing site
   var $num_active_guests;  //Number of active guests viewing site
   var $num_members;        //Number of signed-up users
   /* Note: call getNumMembers() to access $num_members! */

   /* Class constructor */
   function MySQLDB(){
      /* Make connection to database */
      $this->connection = @mysql_connect(DB_SERVER, DB_USER, DB_PASS) or die("Fue imposible conectarse a la base de datos, posiblemente no ha ejecutado el instalador (instalar.php) de " . _NOMBRE_ . " correctamente.<br /><hr />Detalles del error:<pre>". mysql_error() ."</pre>");
      mysql_select_db(DB_NAME, $this->connection) or die(mysql_error());
      
      /**
       * Only query database to find out number of members
       * when getNumMembers() is called for the first time,
       * until then, default value set.
       */
      $this->num_members = -1;
      
      if(TRACK_VISITORS){
         /* Calculate number of users at site */
         $this->calcNumActiveUsers();
      
         /* Calculate number of guests at site */
         $this->calcNumActiveGuests();
      }
   }

   /**
    * confirmUserPass - Checks whether or not the given
    * codigo is in the database, if so it checks if the
    * given clave is the same clave in the database
    * for that user. If the user doesn't exist or if the
    * claves don't match up, it returns an error code
    * (1 or 2). On success it returns 0.
    */
   function confirmUserPass($codigo, $clave){
      /* Add slashes if necessary (for query) */
      if(!get_magic_quotes_gpc()) {
	      $codigo = addslashes($codigo);
      }

      /* Verify that user is in database */
      $q = "SELECT clave FROM ".TBL_USERS." WHERE codigo = '$codigo'";
      $result = mysql_query($q, $this->connection);
      if(!$result || (mysql_numrows($result) < 1)){
         return 1; //Indicates codigo failure
      }

      /* Retrieve clave from result, strip slashes */
      $dbarray = mysql_fetch_array($result);
      $dbarray['clave'] = stripslashes($dbarray['clave']);
      $clave = stripslashes($clave);

      /* Validate that clave is correct */
      if($clave == $dbarray['clave']){
         return 0; //Success! codigo and clave confirmed
      }
      else{
         return 2; //Indicates clave failure
      }
   }
   
   /**
    * confirmUserID - Checks whether or not the given
    * codigo is in the database, if so it checks if the
    * given userid is the same userid in the database
    * for that user. If the user doesn't exist or if the
    * userids don't match up, it returns an error code
    * (1 or 2). On success it returns 0.
    */
   function confirmUserID($codigo, $userid){
      /* Add slashes if necessary (for query) */
      if(!get_magic_quotes_gpc()) {
	      $codigo = addslashes($codigo);
      }

      /* Verify that user is in database */
      $q = "SELECT userid FROM ".TBL_USERS." WHERE codigo = '$codigo'";
      $result = mysql_query($q, $this->connection);
      if(!$result || (mysql_numrows($result) < 1)){
         return 1; //Indicates codigo failure
      }

      /* Retrieve userid from result, strip slashes */
      $dbarray = mysql_fetch_array($result);
      $dbarray['userid'] = stripslashes($dbarray['userid']);
      $userid = stripslashes($userid);

      /* Validate that userid is correct */
      if($userid == $dbarray['userid']){
         return 0; //Success! codigo and userid confirmed
      }
      else{
         return 2; //Indicates userid invalid
      }
   }
   
   /**
    * codigoTaken - Returns true if the codigo has
    * been taken by another user, false otherwise.
    */
   function codigoTaken($codigo){
      if(!get_magic_quotes_gpc()){
         $codigo = addslashes($codigo);
      }
      $q = "SELECT codigo FROM ".TBL_USERS." WHERE codigo = '$codigo'";
      $result = mysql_query($q, $this->connection);
      return (mysql_numrows($result) > 0);
   }
   
   /**
    * codigoBanned - Returns true if the codigo has
    * been banned by the administrator.
    */
   function codigoBanned($codigo){
      if(!get_magic_quotes_gpc()){
         $codigo = addslashes($codigo);
      }
      $q = "SELECT codigo FROM ".TBL_BANNED_USERS." WHERE codigo = '$codigo'";
      $result = mysql_query($q, $this->connection);
      return (mysql_numrows($result) > 0);
   }
   
   /**
    * addNewUser - Inserts the given (codigo, clave, email)
    * info into the database. Appropriate user level is set.
    * Returns true on success, false otherwise.
    */
   function addNewUser( $codigo, $clave, $nombre, $razon, $email, $telefono1, $telefono2, $telefono3, $logotipo, $notas ){
      $time = time();
      DEPURAR ("Nuevo usuario:");
      /* If admin sign up, give admin user level */
      if(strcasecmp($codigo, ADMIN_NAME) == 0){
         $ulevel = ADMIN_LEVEL;
      }else{
         $ulevel = CLIENT_LEVEL;
      }
      $q = "INSERT INTO ".TBL_USERS." VALUES ('$codigo', '$clave', '$nombre', '$razon', '$email', '$telefono1', '$telefono2', '$telefono3', '$logotipo', '$notas', $ulevel, 0, ".time().")";
      DEPURAR($q);
      return mysql_query($q, $this->connection);
   }
   
   /**
    * updateUserField - Updates a field, specified by the field
    * parameter, in the user's row of the database.
    */
   function updateUserField($codigo, $field, $value){
      $q = "UPDATE ".TBL_USERS." SET ".$field." = '$value' WHERE codigo = '$codigo'";
      return mysql_query($q, $this->connection);
   }
   
   /**
    * getUserInfo - Returns the result array from a mysql
    * query asking for all information stored regarding
    * the given codigo. If query fails, NULL is returned.
    */
   function getUserInfo($codigo){
      $q = "SELECT * FROM ".TBL_USERS." WHERE codigo = '$codigo'";
      $result = mysql_query($q, $this->connection);
      /* Error occurred, return given name by default */
      if(!$result || (mysql_numrows($result) < 1)){
         return NULL;
      }
      /* Return result array */
      $dbarray = mysql_fetch_array($result);
      return $dbarray;
   }
   
   /**
    * getNumMembers - Returns the number of signed-up users
    * of the website, banned members not included. The first
    * time the function is called on page load, the database
    * is queried, on subsequent calls, the stored result
    * is returned. This is to improve efficiency, effectively
    * not querying the database when no call is made.
    */
   function getNumMembers(){
      if($this->num_members < 0){
         $q = "SELECT * FROM ".TBL_USERS;
         $result = mysql_query($q, $this->connection);
         $this->num_members = mysql_numrows($result);
      }
      return $this->num_members;
   }
   
   /**
    * calcNumActiveUsers - Finds out how many active users
    * are viewing site and sets class variable accordingly.
    */
   function calcNumActiveUsers(){
      /* Calculate number of users at site */
      $q = "SELECT * FROM ".TBL_ACTIVE_USERS;
      $result = mysql_query($q, $this->connection);
      $this->num_active_users = mysql_numrows($result);
   }
   
   /**
    * calcNumActiveGuests - Finds out how many active guests
    * are viewing site and sets class variable accordingly.
    */
   function calcNumActiveGuests(){
      /* Calculate number of guests at site */
      $q = "SELECT * FROM ".TBL_ACTIVE_GUESTS;
      $result = mysql_query($q, $this->connection);
      $this->num_active_guests = mysql_numrows($result);
   }
   
   /**
    * addActiveUser - Updates codigo's last active timestamp
    * in the database, and also adds him to the table of
    * active users, or updates timestamp if already there.
    */
   function addActiveUser($codigo, $time){
      $q = "UPDATE ".TBL_USERS." SET timestamp = '$time' WHERE codigo = '$codigo'";
      mysql_query($q, $this->connection);
      
      if(!TRACK_VISITORS) return;
      $q = "REPLACE INTO ".TBL_ACTIVE_USERS." VALUES ('$codigo', '$time')";
      mysql_query($q, $this->connection);
      $this->calcNumActiveUsers();
   }
   
   /* addActiveGuest - Adds guest to active guests table */
   function addActiveGuest($ip, $time){
      if(!TRACK_VISITORS) return;
      $q = "REPLACE INTO ".TBL_ACTIVE_GUESTS." VALUES ('$ip', '$time')";
      mysql_query($q, $this->connection);
      $this->calcNumActiveGuests();
   }
   
   /* These functions are self explanatory, no need for comments */
   
   /* removeActiveUser */
   function removeActiveUser($codigo){
      if(!TRACK_VISITORS) return;
      $q = "DELETE FROM ".TBL_ACTIVE_USERS." WHERE codigo = '$codigo'";
      //echo $q."<br>";
      mysql_query($q, $this->connection);
      $this->calcNumActiveUsers();
   }
   
   /* removeActiveGuest */
   function removeActiveGuest($ip){
      if(!TRACK_VISITORS) return;
      $q = "DELETE FROM ".TBL_ACTIVE_GUESTS." WHERE ip = '$ip'";
      mysql_query($q, $this->connection);
      $this->calcNumActiveGuests();
   }
   
   /* removeInactiveUsers */
   function removeInactiveUsers(){
      if(!TRACK_VISITORS) return;
      $timeout = time()-USER_TIMEOUT*60;
      $q = "DELETE FROM ".TBL_ACTIVE_USERS." WHERE timestamp < $timeout";
      mysql_query($q, $this->connection);
      $this->calcNumActiveUsers();
   }

   /* removeInactiveGuests */
   function removeInactiveGuests(){
      if(!TRACK_VISITORS) return;
      $timeout = time()-GUEST_TIMEOUT*60;
      $q = "DELETE FROM ".TBL_ACTIVE_GUESTS." WHERE timestamp < $timeout";
      mysql_query($q, $this->connection);
      $this->calcNumActiveGuests();
   }
   
   function Combobox_usuarios ($nombre="codigo", $default=NULL) {
      $q = "SELECT codigo, nombre FROM ".TBL_USERS." WHERE userlevel <> 9;";
   $result = mysql_query($q, $this->connection);
   /* Error occurred, return given name by default */
   $num_rows = mysql_numrows($result);
   $s='';
   if(!$result || ($num_rows < 0)){
      $s.= "Error mostrando la información";
      return $s;
   }
   if($num_rows == 0){
      /*Esto nunca deberia de pasar realmente...*/
      $s.= "¡No hay clientes/usuarios ingresados!";
      return $s;
   }
  $s='<select name="'.$nombre.'">';
  for($i=0; $i<$num_rows; $i++){
      $uname  = mysql_result($result,$i,"codigo");
      $nombre = mysql_result($result,$i,"nombre");
      if ( $uname == $default ) { $selected = ' selected="selected"'; } else { $selected = ""; }
      $s.='<option value="'.$uname.'"'.$selected.'>'. $nombre .'</option>';
   }
   $s.= '</select>';
   return $s;
   }
   
    function Combobox_pedido ($nombre="codigo_pedido", $default=NULL, $desde = NULL, $hasta = NULL) {
    $intervalo = '';
    if ($desde && $hasta) { $intervalo = " WHERE catorcena_inicio<=$desde AND catorcena_fin>=$hasta"; }
    //if ($desde && $hasta) { $intervalo .= " AND catorcena_fin<='$hasta'"; }
   $q = "SELECT codigo_pedido, CONCAT(codigo_pedido,'. ', (SELECT nombre FROM ".TBL_USERS." AS b WHERE b.codigo = a.codigo), ', ' , descripcion) as nombre FROM ".TBL_MUPI_ORDERS . " as a$intervalo;";
   $result = mysql_query($q, $this->connection);
   /* Error occurred, return given name by default */
   $num_rows = mysql_numrows($result);
   $s='';
   if(!$result || ($num_rows < 0)){
      $s.= "Error mostrando la información";
      return $s;
   }
   if($num_rows == 0){
      /*Esto nunca deberia de pasar realmente...*/
      $s.= "¡No hay pedidos ingresados!";
      return $s;
   }
  $s='<select name="'.$nombre.'">';
  for($i=0; $i<$num_rows; $i++){
      $codigo_pedido  = mysql_result($result,$i,"codigo_pedido");
      $nombre = mysql_result($result,$i,"nombre");
      if ( $codigo_pedido == $default ) { $selected = ' selected="selected"'; } else { $selected = ""; }
      $s.='<option value="'.$codigo_pedido.'"'.$selected.'>'. $nombre .'</option>';
   }
   $s.= '</select>';
   return $s;
   }
   
    function Combobox_mupi ($nombre="codigo_mupi", $default=NULL) {
		//id_mupi, codigo_calle.codigo_mupi , calle, ubicacion.
		$q = "SELECT id_mupi, CONCAT(codigo_calle, '.' , codigo_mupi, ' | ', (SELECT ubicacion FROM emupi_calles AS b WHERE a.codigo_calle=b.codigo_calle), ', ', direccion ) as nombre FROM emupi_mupis AS a;";
		//echo $q;
   $result = mysql_query($q, $this->connection);
   /* Error occurred, return given name by default */
   $num_rows = mysql_numrows($result);
   $s='';
   if(!$result || ($num_rows < 0)){
      $s.= "Error mostrando la información";
      return $s;
   }
   if($num_rows == 0){
      /*Esto nunca deberia de pasar realmente...*/
      $s.= "¡No hay "._NOMBRE_." ingresados!";
      return $s;
   }
  $s='<select name="'.$nombre.'">';
  for($i=0; $i<$num_rows; $i++){
      $id_mupi  = mysql_result($result,$i,"id_mupi");
      $nombre = mysql_result($result,$i,"nombre");
      if ( $id_mupi == $default ) { $selected = ' selected="selected"'; } else { $selected = ""; }
      $s.='<option value="'.$id_mupi.'"'.$selected.'>'. $nombre .'</option>';
   }
   $s.= '</select>';
   return $s;
   }
   
    function Combobox_calle ($nombre="codigo_calle", $default=NULL, $calle=NULL) {
		if ( $calle ) { $wCalle = " AND codigo_calle='$calle'";  } else { $wCalle = ""; }
      $q = "SELECT codigo_calle, CONCAT(codigo_calle,'. ',ubicacion) as nombre FROM ".TBL_STREETS.$wCalle;
   $result = mysql_query($q, $this->connection);
   /* Error occurred, return given name by default */
   $num_rows = mysql_numrows($result);
   $s='';
   if(!$result || ($num_rows < 0)){
      $s.= "Error mostrando la información";
      return $s;
   }
   if($num_rows == 0){
      /*Esto nunca deberia de pasar realmente...*/
      $s.= "¡No hay calles "._NOMBRE_." ingresadas!";
      return $s;
   }
  $s='<select name="'.$nombre.'">';
  for($i=0; $i<$num_rows; $i++){
      $codigo_calle  = mysql_result($result,$i,"codigo_calle");
      $nombre = mysql_result($result,$i,"nombre");
      if ( $codigo_calle == $default ) { $selected = ' selected="selected"'; } else { $selected = ""; }
      $s.='<option value="'.$codigo_calle.'"'.$selected.'>'. $nombre .'</option>';
   }
   $s.= '</select>';
   return $s;
   }
   
  function Combobox_CatorcenasConPresencia ($nombre="catorcena_presencia", $codigo=NULL, $OnChange=NULL) {
	  global $session;
	  $WHERE_USER = '';
	  $NivelesPermitidos = array(ADMIN_LEVEL, SALESMAN_LEVEL);
	  if ( !in_array($session->userlevel,$NivelesPermitidos) || $codigo ) {$WHERE_USER = "WHERE codigo='".$codigo."'";}
   $q = "SELECT DISTINCT catorcena FROM ".TBL_MUPI_FACES." WHERE catorcena <=".Obtener_catorcena_siguiente()." AND codigo_pedido IN (SELECT codigo_pedido FROM ".TBL_MUPI_ORDERS." $WHERE_USER)  ORDER BY catorcena;";
   $result = mysql_query($q, $this->connection);
   //echo $q.'<br />';
   /* Error occurred, return given name by default */
   $num_rows = mysql_numrows($result);
   $s='';
   if(!$result || ($num_rows < 0)){
      $s.= "Error mostrando la información";
      return $s;
   }
   if($num_rows == 0){
      $s.= "¡No tiene ninguna pantalla alquilada en ninguna catorcena!";
      return $s;
   }
   $catorcena_actual = Obtener_catorcena_cercana();
  $s='<select id="'.$nombre.'" name="'.$nombre.'" onkeyup="'.$OnChange.'" onclick="'.$OnChange.'">';
  for($i=0; $i<$num_rows; $i++){
      $catorcena_inicio  = mysql_result($result,$i,"catorcena");
      $catorcena_fin = Fin_de_catorcena($catorcena_inicio);
      if ( $catorcena_inicio == $catorcena_actual ) { $selected = ' selected="selected"'; } else { $selected = ""; }
      $s.='<option value="'.$catorcena_inicio.'"'.$selected.'>'."Del " . date('d-m-Y',$catorcena_inicio) . ' al ' . date('d-m-Y',$catorcena_fin) .'</option>';
   }
   $s.= '</select>';
   return $s;
   }
   
   function Combobox_CallesConPresencia($nombre, $codigo, $catorcena){
	   // Calles donde el usuario $codigo tiene caras alquiladas en la catorcena $catorcena.
	   global $session;
	   $WHERE_USER = '';
	   $NivelesPermitidos = array(ADMIN_LEVEL, SALESMAN_LEVEL);
	   if ( !in_array($session->userlevel,$NivelesPermitidos) || $codigo ) {$WHERE_USER = " AND codigo_pedido IN (SELECT codigo_pedido FROM emupi_mupis_pedidos WHERE codigo='".$codigo."')";}
	   // Filtro de cadena:
	   // 1. Filtrar todas caras que esten en la catorcena requerida
	   // 2. De esas filtrar las que tengan pedidos de $codigo
	   // 3. De ahi tenemos codigo_mupi, del cual sacamos codigo_calle
	   // 4. Posteriormente la ubicacion de esa calle.
	   // 5. Mostramos nada mas las distintas calles.
	   // - Combobox espera calle y ubicación.
	    $q = "SELECT DISTINCT @calle := (SELECT codigo_calle FROM emupi_mupis AS b WHERE a.codigo_mupi=b.id_mupi) AS 'calle', (SELECT ubicacion FROM emupi_calles WHERE codigo_calle=@calle) AS ubicacion FROM emupi_mupis_caras AS a WHERE catorcena=".$catorcena. $WHERE_USER .";";
		//echo $q.'<br />';
   $result = mysql_query($q, $this->connection);
   $num_rows = mysql_numrows($result);
   $s='';
   if(!$result || ($num_rows < 0)){
      $s.= "Error mostrando la información";
      return $s;
   }
   if($num_rows == 0){
      $s.= "¡No tiene presencia en ninguna calle para esta catorcena!";
      return $s;
   }
   $s='<select id="'.$nombre.'" name="'.$nombre.'">';
  for($i=0; $i<$num_rows; $i++){
      $s.='<option value="'.mysql_result($result,$i,"calle").'">'. mysql_result($result,$i,"ubicacion") .'</option>';
   }
   $s.= '</select>';
   return $s;
   }
   
   /**
    * query - Performs the given query on the database and
    * returns the result, which may be false, true or a
    * resource identifier.
    */
   function query($query){
     $resultado = @mysql_query($query, $this->connection);
     if ( mysql_error($this->connection) ) {
	echo '<pre>MySQL:'. mysql_error().'</pre>';
      }
	return $resultado;
   }
}

/* Create database connection */
$database = new MySQLDB;

?>
