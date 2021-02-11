<?php
// PHP скрипт бота Telegram https://t.me/kema_robot
// Разработчик: Роботехмастер https://vk.com/myrobotics

define('BOT_TOKEN', '**************************************');
define('API_URL', 'https://api.telegram.org/bot'.BOT_TOKEN.'/');
define('WEBHOOK_URL', 'https://***********************/');

function apiRequestWebhook($method, $parameters) {
  if (!is_string($method)) {
    error_log("Method name must be a string\n");
    return false;
  }

  if (!$parameters) {
    $parameters = array();
  } else if (!is_array($parameters)) {
    error_log("Parameters must be an array\n");
    return false;
  }

  $parameters["method"] = $method;

  header("Content-Type: application/json");
  echo json_encode($parameters);
  return true;
}

function exec_curl_request($handle) {
  $response = curl_exec($handle);

  if ($response === false) {
    $errno = curl_errno($handle);
    $error = curl_error($handle);
    error_log("Curl returned error $errno: $error\n");
    curl_close($handle);
    return false;
  }

  $http_code = intval(curl_getinfo($handle, CURLINFO_HTTP_CODE));
  curl_close($handle);

  if ($http_code >= 500) {
    // do not wat to DDOS server if something goes wrong
    sleep(10);
    return false;
  } else if ($http_code != 200) {
    $response = json_decode($response, true);
    error_log("Request has failed with error {$response['error_code']}: {$response['description']}\n");
    if ($http_code == 401) {
      throw new Exception('Invalid access token provided');
    }
    return false;
  } else {
    $response = json_decode($response, true);
    if (isset($response['description'])) {
      error_log("Request was successful: {$response['description']}\n");
    }
    $response = $response['result'];
  }

  return $response;
}

function apiRequest($method, $parameters) {
  if (!is_string($method)) {
    error_log("Method name must be a string\n");
    return false;
  }

  if (!$parameters) {
    $parameters = array();
  } else if (!is_array($parameters)) {
    error_log("Parameters must be an array\n");
    return false;
  }

  foreach ($parameters as $key => &$val) {
    // encoding to JSON array parameters, for example reply_markup
    if (!is_numeric($val) && !is_string($val)) {
      $val = json_encode($val);
    }
  }
  $url = API_URL.$method.'?'.http_build_query($parameters);

  $handle = curl_init($url);
  curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, 5);
  curl_setopt($handle, CURLOPT_TIMEOUT, 60);

  return exec_curl_request($handle);
}

function apiRequestJson($method, $parameters) {
  if (!is_string($method)) {
    error_log("Method name must be a string\n");
    return false;
  }

  if (!$parameters) {
    $parameters = array();
  } else if (!is_array($parameters)) {
    error_log("Parameters must be an array\n");
    return false;
  }

  $parameters["method"] = $method;

  $handle = curl_init(API_URL);
  curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, 5);
  curl_setopt($handle, CURLOPT_TIMEOUT, 60);
  curl_setopt($handle, CURLOPT_POST, true);
  curl_setopt($handle, CURLOPT_POSTFIELDS, json_encode($parameters));
  curl_setopt($handle, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));

  return exec_curl_request($handle);
}

function processMessage($message) {
	
  // process incoming message
  $message_id = $message['message_id'];
  $chat_id = $message['chat']['id'];
  $unknown_command = true;
  if (isset($message['text'])) {
    // incoming text message
    $text = $message['text'];

    if (strpos($text, "/foto") === 0) 
	{
      apiRequestJson("sendMessage", array('chat_id' => $chat_id, "text" => 'Выбери фото:', 'reply_markup' => array(
        'keyboard' => array(array('Основной вид', 'Вид спереди'),array('Вид сбоку', 'Вид сзади')),
        'one_time_keyboard' => true,
        'resize_keyboard' => true)));
		$unknown_command = false;
		return true;
    }
	
    if (mb_ereg_match("Вид спереди", $text)) 
	{
	  $unknown_command = false;
      apiRequestWebhook("sendPhoto", array('chat_id' => $chat_id, "photo" => 'http://**********************/foto/front.jpg'));
  	  return true;
    }

    if (mb_ereg_match("Вид сзади", $text)) 
	{
	  $unknown_command = false;
      apiRequestWebhook("sendPhoto", array('chat_id' => $chat_id, "photo" => 'http://**********************/foto/back.jpg'));
      return true; 
	}

    if (mb_ereg_match("Вид сбоку", $text)) 
	{
	  $unknown_command = false;
      apiRequestWebhook("sendPhoto", array('chat_id' => $chat_id, "photo" => 'http://**********************/foto/side.jpg'));
      return true;
	}

    if (mb_ereg_match("Основной вид", $text)) 
	{
	  $unknown_command = false;
      apiRequestWebhook("sendPhoto", array('chat_id' => $chat_id, "photo" => 'http://**********************/foto/base.jpg'));
      return true;
	}
	
    if (strpos($text, "/design") === 0) 
	{
      apiRequestJson("sendMessage", array('chat_id' => $chat_id, "text" => 'Выбери схему/чертеж:', 'reply_markup' => array(
        'keyboard' => array(array('Схема соединений робота', 'Схема соединений пульта'),array('Чертеж платформы', 'Схема платформы')),
        'one_time_keyboard' => true,
        'resize_keyboard' => true)));
		$unknown_command = false;
       return true;
	}
	
    if (mb_ereg_match("Схема соединений робота", $text)) 
	{
	  $unknown_command = false;
      apiRequestWebhook("sendDocument", array('chat_id' => $chat_id, "document" => 'http://**********************/design/shema.pdf'));
      return true;
	}

    if (mb_ereg_match("Схема соединений пульта", $text)) 
	{
	  $unknown_command = false;
      apiRequestWebhook("sendDocument", array('chat_id' => $chat_id, "document" => 'http://**********************/design/shema_pult.pdf'));
      return true;
	}

    if (mb_ereg_match("Чертеж платформы", $text)) 
	{
	  $unknown_command = false;
      apiRequestWebhook("sendDocument", array('chat_id' => $chat_id, "document" => 'http://**********************/design/platforma.zip'));
	  return true;
    }

    if (mb_ereg_match("Схема платформы", $text)) 
	{
	  $unknown_command = false;
      apiRequestWebhook("sendPhoto", array('chat_id' => $chat_id, "photo" => 'http://**********************/design/platforma.jpg'));
      return true;
	}

    if (strpos($text, "/code") === 0) 
	{
      $code_txt = file_get_contents("code.txt");
      apiRequestJson("sendMessage", array('chat_id' => $chat_id, "text" => $code_txt, 'reply_markup' => array(
        'keyboard' => array(array('Скетч для робота', 'Скетч для пульта'),array('Библиотеки для Ардуино')),
        'one_time_keyboard' => true,
        'resize_keyboard' => true)));
		$unknown_command = false;
      return true;
	}

    if (mb_ereg_match("Скетч для робота", $text)) 
	{
      apiRequestWebhook("sendDocument", array('chat_id' => $chat_id, "document" => 'http://**********************/code/KemaII.zip'));
	  $unknown_command = false;
      return true;
	}

    if (mb_ereg_match("Скетч для пульта", $text)) 
	{
      apiRequestWebhook("sendDocument", array('chat_id' => $chat_id, "document" => 'http://**********************/code/KemaIIRC.zip'));
	  $unknown_command = false;
      return true;
	}

    if (mb_ereg_match("Библиотеки для Ардуино", $text)) 
	{
      apiRequestWebhook("sendDocument", array('chat_id' => $chat_id, "document" => 'http://**********************/code/arduino-libraries.zip'));
	  $unknown_command = false;
      return true;
	}
	
    if (strpos($text, "/start") === 0) 
	{
	  $start_txt = file_get_contents("start.txt");
      apiRequestWebhook("sendMessage", array('chat_id' => $chat_id, "text" => $start_txt));
	  processMsgArr($chat_id, $text);
	  $unknown_command = false;
      return true;
	}
	
	if (strpos($text, "/help") === 0) 
	{
	  $help_txt = file_get_contents("help.txt");
      apiRequestWebhook("sendMessage", array('chat_id' => $chat_id, "text" => $help_txt));
	  $unknown_command = false;
      return true;
	}

    if (strpos($text, "/about") === 0) 
	{
	  $about_txt = file_get_contents("about.txt");
      apiRequestWebhook("sendMessage", array('chat_id' => $chat_id, "text" => $about_txt));
	  $unknown_command = false;
      return true;
	}

	if (strpos($text, "/parts") === 0) 
	{
	  $parts_txt = file_get_contents("parts.txt");
      apiRequestWebhook("sendMessage", array('chat_id' => $chat_id, "text" => $parts_txt));
	  $unknown_command = false;
      return true;
	}

	if (strpos($text, "/home") === 0) 
	{
	  $home_txt = file_get_contents("home.txt");
      apiRequestWebhook("sendMessage", array('chat_id' => $chat_id, "text" => $home_txt));
	  $unknown_command = false;
      return true;
	}

	if (strpos($text, "/ttx") === 0) 
	{
	  $ttx_txt = file_get_contents("ttx.txt");
      apiRequestWebhook("sendMessage", array('chat_id' => $chat_id, "text" => $ttx_txt));
	  $unknown_command = false;
      return true;
	}
     
	if ($unknown_command) 
	{
		apiRequestWebhook("sendMessage", array('chat_id' => $chat_id, "text" => 'Неизвестная команда. См. /help'));
		processMsgArr($chat_id, $text);
	}
	 
  } else {
    apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => 'Я понимаю только текстовые сообщения'));
  }
}

function processMsgArr($chat, $txt) 
{
	$txt = mb_convert_encoding($txt, "Windows-1251");
	$msgs = array();
    if(file_exists("msgs.bas"))
	   $msgs = unserialize(file_get_contents("msgs.bas"));
    $msgs2 = array();
	if(array_key_exists($chat, $msgs))
		$msgs2 = $msgs[$chat];
	array_push($msgs2, $txt);
	$msgs[$chat] = $msgs2;
	@file_put_contents("msgs.bas",serialize($msgs));
}

$content = file_get_contents("php://input");
$update = json_decode($content, true);

if (!$update) {
  // receive wrong update, must not happen
  exit;
}

if (isset($update["message"])) {
	processMessage($update["message"]);
}

?>