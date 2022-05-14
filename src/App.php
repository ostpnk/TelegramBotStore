<?php

  namespace Store;

  class App {

    protected $bot;
    protected $db_host;
    protected $db_user;
    protected $db_password;
    protected $db_name;

    function __construct($config) {

      $this->db_host = $config['db_host'];
      $this->db_user = $config['db_user'];
      $this->db_password = $config['db_password'];
      $this->db_name = $config['db_name'];

      $this->bot = new \TelegramBot\Api\Client($config['token']);

      $bot = $this->bot;
      $this->bot->on(function (\TelegramBot\Api\Types\Update $update) use ($bot) {

        $message = $update->getMessage();
        $chat_id = $message->getChat()->getId();
        $this->bot->sendMessage($chat_id, $chat_id);

           // global $screens;
           //
           //
           // $callbackQuery = $update->getCallbackQuery();
           // $message = $update->getMessage();
           //
           // if($callbackQuery){
           //
           //   $chat_id = $callbackQuery->getMessage()->getChat()->getId();
           //   $state = load_state($chat_id);
           //
           //   if ( $state['last_bot_message_id'] && $state['last_bot_message_id'] != $callbackQuery->getMessage()->getMessageId() ) return;
           //
           //   $state['user_input'] = ['text'=>$callbackQuery->getData()];
           //   $state['username'] = $callbackQuery->getMessage()->getChat()->getUsername();
           //
           //
           // } elseif ($message){
           //
           //   $chat_id = $message->getChat()->getId();
           //   $state = load_state($chat_id);
           //
           //
           //
           //   //if ( $state['last_bot_message_id'] && $state['last_bot_message_id'] != $message->getMessageId() ) return;
           //
           //   $state['user_input'] = ['text'=>$message->getText()];
           //   $state['username'] = $message->getChat()->getUsername();
           //
           //
           //   if ( $message->getPhoto() !== null ){
           //     $state['user_input'] = ['photo_id' => $message->getPhoto()[0]->getFileId(), 'text' => $message->getCaption()];
           //   }
           //
           // }
           //
           //
           // $screen = $screens[$state['screen']];
           // $text = $state['user_input']['text'];
           //
           //
           // // setting callback
           // if ($text) {
           //   if ( isset( $screen['callback'][$text] ) ){
           //     $callback = $screen['callback'][$text];
           //   } else {
           //     $callback = $screen['callback']['__text'];
           //   }
           // }
           // if ( $state['user_input']['photo_id'] ){
           //   if ( isset( $screen['callback']['__photo'] ) ){
           //     $callback = $screen['callback']['__photo'];
           //   }
           // }
           //
           //
           // // firing callback
           //
           // if ($callback){
           //   if ( is_callable( $callback['action'] ) ) $state = $callback['action']($state);
           //   if ( is_callable( $callback['next_screen'] ) ){
           //     $next_screen = $callback['next_screen']($state);
           //   } else {
           //     $next_screen = $callback['next_screen'];
           //   }
           // }
           //
           // // checking admin for an admin screen
           // if ( ( substr( $next_screen, 0, 5 ) === "admin" ) && !is_admin($state) ) $next_screen = 'start';
           //
           // $state['screen'] = ($next_screen && isset($screens[$next_screen])) ? $next_screen : 'start';
           //
           // save_state( $chat_id, drawScreen($chat_id, $state) );


       }, function () {
           return true;
       });

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
