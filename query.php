<?php
  // Emoncms definitions
  require_once('defs_emoncms.php');

  // Create an user, asign feeds and inputs
  //
  // Parameters:
  //   $username: username
  //   $email: email of the user
  //   $password: password of the user
  //   $panelType: type of the panel ("PV" or "Consumption")
  //
  // Returns
  //   true: user, feeds and inputs successfully created
  //   *anything else*: error string
  function create_linked_user($username, $email, $password, $panelType) {
    // Connect to the DB
    $ret = create_connection($connection);
    if($ret !== true) {
      return $ret;
    }

    // Validate input
    $ret = validate_input($username, $email, $password, $panelType);
    if($ret !== true) {
      $connection->close();
      return $ret;
    }

    // Create user
    if(create_user($username, $email, $password, $userid, $connection) !== true) {
      $connection->close();
      return 'El nombre de usuario ya existe';
    }

    // Set the type of user data
    $prefix = 'data/';
    // PV
    if($panelType == 'PV') {
      $prefix .= 'pv';
    }
    // Consumption
    else {
      $prefix .= 'consumption';
    }

    // Create feeds
    if(create_feeds($prefix . '_feeds.json', $userid, $feeds, $connection) !== true) {
      $connection->close();
      return 'Fallo al crear los feeds';
    }

    // Create inputs
    if(create_inputs($prefix . '_inputs.json', $userid, $inputs, $connection) !== true) {
      $connection->close();
      return 'Fallo al crear los inputs';
    }

    // Create processes
    if(create_processes($prefix . '_processes.json', $userid, $feeds, $inputs, $connection) !== true) {
      $conncetion->close();
      return 'Fallo al crear los procesos';
    }

    return true;
  }

  // Create a connection with the database
  //
  // Parameters:
  //   $connection: output parameter, connection
  //
  // Returns
  //   true: connection successfully created
  //   *anything else*: error string
  function create_connection(&$connection) {
    // Get DB settings
    require_once('settings.php');

    $connection = new mysqli($db_server, $db_username, $db_password, $db_name);
    if($connection->connect_error) {
      return true;
    }
    return true;
  }

  // Validate the input
  //
  // Parameters:
  //   $username: username
  //   $email: email of the user
  //   $password: password of the user
  //   $panelType: type of the panel ("PV" or "Consumption")
  //
  // Returns
  //   true: parameters valid
  //   *anything else*: error string
  function validate_input($username, $email, $password, $panelType) {
    // Username
    if((!isset($username)) || (strlen($username) == 0)) {
      echo $username;
      return 'Introduzca un nombre de usuario';
    }
    if(!ctype_alnum($username)) {
      return 'El nombre de usuario solo puede contener letras y números';
    }
    if(strlen($username) < 4) {
      return 'El nombre de usuario debe tener al menos 4 caracteres';
    }
    if(strlen($username) > 30) {
      return 'El nombre de usuario no puede tener más de 30 caracteres';
    }

    // Email
    if((!isset($email)) || (strlen($email) == 0)) {
      return 'Introduzca un email';
    }
    if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      return 'Email no válido';
    }

    // Password
    if((!isset($password)) || (strlen($password) == 0)) {
      return 'Introduzca una contraseña';
    }
    if(strlen($password) < 4) {
      return 'La contraseña debe tener al menos 4 caracteres';
    }
    if(strlen($password) > 30) {
      return 'La contraseña no puede tener más de 30 caracteres';
    }

    // Panel Type
    if((!isset($panelType)) || (strlen($panelType) == 0)) {
      return 'Seleccione un tipo de instalación';
    }
    if(!($panelType === 'PV' || $panelType === 'Consumption')) {
      return 'Seleccione un tipo válido de instalación';
    }
    return true;
  }

  // Create the user
  //
  // Parameters:
  //   $username: username, must be unique
  //   $email: email of the user
  //   $password: password of the user
  //   $userid: output parameter, id of the user created
  //   $connection: connection with the database
  //
  // Returns
  //   true: user successfully created
  //   false: error creating the user (already exists)
  function create_user($username, $email, $password, &$userid, $connection) {
    // Rest of the parameters
    $hash = hash('sha256', $password);
    $string = md5(uniqid(mt_rand(), true));
    $salt = substr($string, 0, 3);
    $hash = hash('sha256', $salt . $hash);
    $apikey_write = md5(uniqid(mt_rand(), true));
    $apikey_read = md5(uniqid(mt_rand(), true));
    $timezone = 'Europe/Madrid';
    $language = 'es_ES';

    // Query
    $sqlQuery = "INSERT INTO users (username, password, email, salt ,apikey_read, apikey_write, admin, timezone, language)
                VALUES ('$username', '$hash', '$email', '$salt', '$apikey_read', '$apikey_write', 0, '$timezone', '$language');";
    if ($connection->query($sqlQuery) !== TRUE) {
      return false;
    }

    // Asign userid
    $userid = $connection->insert_id;
    return true;
  }

  // Create the feeds
  //
  // Parameters:
  //   $datafile: path to the feeds data
  //   $userid: id of the user
  //   $feeds: output parameter for the feeds, in the format 'feedName'=>'feedId'
  //   $connection: connection with the database
  //
  // Returns
  //   true: feeds successfully created
  //   false: error creating the feeds
  function create_feeds($datafile, $userid, &$feeds, $connection) {
    // Read the feeds from file
    $feedArray = json_decode(file_get_contents($datafile), true);

    // Create each feed
    foreach($feedArray as $feed) {
      // TODO
      // Make query
      // Assign the created feed id to the feeds array
    }

    return true;
  }

  // Create the inputs
  //
  // Parameters:
  //   $datafile: path to the inputs data
  //   $userid: id of the user
  //   $inputs: output parameter for the inputs, in the format 'inputName'=>'inputId'
  //   $connection: connection with the database
  //
  // Returns
  //   true: inputs successfully created
  //   false: error creating the inputs
  function create_inputs($datafile, $userid, &$inputs, $connection) {
    // Create the inputs from file
    $inputArray = json_decode(file_get_contents($datafile), true);

    // Create each input
    foreach($inputArray as $input) {
      // TODO
      // Make query
      // Assign the created input id to the feeds array
    }

    return true;
  }

  // Create the processes
  //
  // Parameters:
  //   $datafile: path to the processes data
  //   $userid: id of the user
  //   $feeds: array of feed ids, in the format 'feedName'=>'feedId'
  //   $inputs: array of input ids, in the format 'inputName'=>'inputId'
  //   $connection: connection with the database
  //
  // Returns
  //   true: processes successfully created
  //   false: error creating the processes
  function create_processes($datafile, $userid, $feeds, $inputs, $connection) {
    // Read the processes from file
    $processArray = json_decode(file_get_contents($datafile), true);

    // Create each process
    foreach($processArray as $process) {
      // TODO
      // Translate process
      // Make query
    }

    return true;
  }
?>
