<?php

  namespace Store;

  class App {

    protected $bot;
    protected $db_host;
    protected $db_user;
    protected $db_password;
    protected $db_name;

    function __construct($config) {
      $this->bot = new \TelegramBot\Api\Client($config['token']);
      $this->db_host = $config['db_host'];
      $this->db_user = $config['db_user'];
      $this->db_password = $config['db_password'];
      $this->db_name = $config['db_name'];
    }

    protected function connect_db(){
      return new mysqli($this->db_host, $this->db_user, $this->db_password, $this->db_name);
      if ($mysqli->connect_error) {
        $mysqli->close();
        //return ['errno'=>$mysqli->connect_errno, 'error'=>$mysqli->connect_error];
        return null;
      } else {
        return $mysqli;
      }
    }

    protected function load_state($chat_id) {
      if ( $mysqli = $this->connect_db() ) {
        $result = $mysqli->query("SELECT * FROM state_cache WHERE chat_id =".$chat_id." LIMIT 1;");
        $mysqli->close();
        if ( $result->num_rows > 0 ) {
          $assoc = $result->fetch_assoc();
          $state = json_decode($assoc['state'], true);
          $state['chat_id'] = $chat_id;
          return $state;
        }
        return [];
      }
    }

    function save_state($chat_id, $state){
      if ( $mysqli = $this->connect_db() ) {
        try {
          $stmt = $mysqli->prepare("REPLACE INTO state_cache (chat_id, state, time) VALUES (?, ?, current_timestamp)");
          $stmt->bind_param("is", ...[$chat_id,json_encode($state)] );
          $result = $stmt->execute();
          $error = $stmt->error;
          $stmt->close();
          $mysqli->close();
          return $error;
        } catch (Exception $e){
          return $e;
        }
      }
    }

  }
  
?>
