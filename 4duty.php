<?php 
if (!isset($_REQUEST)) { 
return; 
}

function array_dot($array, $prepend = ''){
    $results = [];
    foreach ($array as $key => $value) {
        if (is_array($value) && ! empty($value)) {
            $results = array_merge($results, array_dot($value, $prepend.$key.'.'));
        } else {
            $results[$prepend.$key] = $value;
        }
    }
    
    return $results;
}
function startsWith($haystack, $needle)
{
     $length = strlen($needle);
     return (substr($haystack, 0, $length) === $needle);
}
function get_num_pages_docx($filename)
{
       $zip = new ZipArchive();
       if($zip->open($filename) === true)
       {  
           if(($index = $zip->locateName('docProps/app.xml')) !== false)
           {
               $data = $zip->getFromIndex($index);
               $zip->close();

               $xml = new SimpleXMLElement($data);
               return $xml->Pages;
           }

           $zip->close();
       }

       return false;
}


$anisimov_token = 'VK API userToken';
$token = 'VK API BOT TOKEN';
$confirmation_token = 'VK API CONFIRMATION TOKEN'; 
$data = json_decode(file_get_contents('php://input'));
if ($data->secret=='VK API GROUP SERCRET CODE') {
    

switch ($data->type) {
//Если это уведомление для подтверждения адреса... 
case 'confirmation': 
//...отправляем строку для подтверждения 
echo $confirmation_token; 
break; 
case 'message_deny':  
//нас забанил пользователь, фича для отслеживания
$request_params = array( 
'message' => "user https://vk.com/id".$data->object->user_id." block us :( \nLet's remove him! ^_^", 
'random_id' => random_int(10000000, 99999999),
'user_id' => 38936276, 
'access_token' => $token, 
'v' => '5.124'
); 
$request = http_build_query($request_params);
file_get_contents('https://api.vk.com/method/messages.send?'.$request);
echo("ok");
break;
//Если это уведомление о новом сообщении... 
case 'message_new':

$msg_object = $data->object;
if ( $data->object->message->peer_id != 2000000004) { //айди чата, где необходимо было отслеживать 
	if (preg_replace("/[0-9]/","",$msg_object->message->text)=="" AND $msg_object->message->text!="") {
    $block_number = $msg_object->message->text;
    include 'conf.php';
    $id = $data->object->message->peer_id; #8 цифр user id
    $link = new mysqli($host, $login, $pass, $db);
    $link->set_charset("utf8");
    $sql_Req = "INSERT INTO dormitory_users(user_block,user_id) VALUES ('".$block_number."','".$id."')";
    mysqli_query($link, $sql_Req);
    $raw_json = file_get_contents("4domitory.json");
    $json = json_decode($raw_json,true);
    if ((!$json[$block_number])AND(strpos($raw_json, "https:\/\/vk.com\/id{$id}")==0)){
        $json[$block_number] = array();
            array_push($json[$block_number], "https://vk.com/id{$id}");
            $otvet = $otvet."Вы первый из вашей комнаты, уведомления пока приходят только вам!";
        }
    else{
        if ((!in_array($id, $json[$block_number]))AND(strpos($raw_json, "https:\/\/vk.com\/id{$id}")==0)){
            $otvet = "Мы добавили Вас в список жильцов блока {$block_number},теперь уведомления приходят и вам";
            array_push($json[$block_number], "https://vk.com/id{$id}");
        }

        elseif(count($json[$domitory][$room])==1){
            $otvet = $otvet."Вы уже зарегестрировались! уведомления пока приходят только вам";
        }

        else{
            $otvet = "Вы уже регестрировались, если вы сделали это по ошибке или переехали, удалите себя из базы командой \nУдалить";

        }
        
 }
$result = json_encode($json);
file_put_contents("4domitory.json", $result);
}



elseif ($msg_object->message->text=="Начать") {
    $otvet = "Начнём?&#128521;";
    $keyboard = file_get_contents("keyboard_duty_main.json");
}


elseif ($msg_object->message->text=="Удалить") {
    $id = $data->object->message->peer_id; #8 цифр айдишника
    include 'conf.php';
    $link = new mysqli($host, $login, $pass, $db);
    $link->set_charset("utf8");
    $sql_Req = "DELETE FROM dormitory_users WHERE user_id=".$id;
    mysqli_query($link, $sql_Req);

    $raw_json = file_get_contents("4domitory.json");
    $json = json_decode($raw_json,true);
    $ego_id = "https://vk.com/id".$id;
    $counter_blocks = 0;
    $block = "";
    $blocks_num = array_keys($json);
    $counter_blocks = 0;
    foreach ($json as $value) {
        $amount = array_search($ego_id, $value);

        // var_dump($value);
        if ($amount!==false) {
            $block = $blocks_num[$counter_blocks];
            $otvet = "Вы успешно удалены из {$block} блока";
            break;
        }

        $counter_blocks = $counter_blocks+1;
    }
if (!$otvet) {
    $otvet = "Мы не нашли Вас в нашей базе, вы ещё не привязаны к блоку";
}
unset($json[$block][$amount]);
$result = json_encode($json);
file_put_contents("4domitory.json", $result);
$keyboard = file_get_contents("keyboard_duty_main.json");
}
// elseif($msg_object->message->text=="Кофе"){
//  $otvet = "Мы рады, что вас заинтересовало наше предложение!\nИ мы проводим акцию - каждый 6⃣ кофе в подарок!&#9749;\nВаш кофе будет доставлен до блока\nА теперь наше меню:\n\nЭспрессо 50мл 50р\n----\nАмерикано 100мл 60р\n----\nКапучино 150мл 99р\nЭспрессо/Молочная пена/Шоколадная крошка\n----\nЛатте 400мл 109р\nЭспрессо/Молоко/Молочная пена/Сироп карамельный";
//  $keyboard = file_get_contents('keyboard_duty_coffie.json');
// }
elseif($msg_object->message->text=="Кофе"){
$otvet = "Мы Очень рады, что вы заинтересовались данной функцией, она будет доступна позже";
$keyboard = file_get_contents("keyboard_duty_main.json");
    }











elseif($msg_object->message->text=="take order"){

    $otvet = "Красавчик, свяжись с заказчиком";
    // if ($msg_object->user_id=="") {
    //     $getter = "";}
    // if ($msg_object->user_id=="") {
    //     $getter = "";}
    // $keyboard = file_get_contents('keyboard_duty_main.json');
    //     $request_params = array( 
    //         'message' => "Заказ выполняют, бро, расслабься",
    //         'keyboard' => $keyboard,
    //         'user_id' => $getter, 
    //         'access_token' => $token, 
    //         'v' => '5.124'
    //     );  
    // $get_params = http_build_query($request_params); 
    // file_get_contents('https://api.vk.com/method/messages.send?'.$get_params);

}




elseif($msg_object->message->text=="Печать"){
    $otvet = "Олично! Заказ оставлен!
    Когда его примут, вам придёт оповещение с контактной информацией &#128521;";
    include "conf.php";
    $link = mysqli_connect($host,$login,$pass,$db);
        mysqli_query($link, "INSERT INTO print_orders SET client_id=".$data->object->message->peer_id);
        #get the last order id
        $last_order_id = mysqli_insert_id($link);
        #print to client id of his order
        // $otvet.="order ".$last_id;
        #get the list of printer owners
        $query = mysqli_query($link, "SELECT * FROM `printer_owners`");
        $request_a = "SELECT * FROM `printer_owners`";
        $owners_data = mysqli_fetch_assoc($query);
        //foreach all data from table 'printer owners'
    if(mysqli_num_rows($query) == 0)
    {
        $err[] = "something gone wrong";
    }
        if(count($err) == 0)
    {
        //if no errors send notification for printer owners
        //GET INFO ABOUT USER
        if ($result = mysqli_query($link, $request_a)) {
            $request_params = array( 
            'fields' => 'photo_id,first_name,last_name',
            'user_ids' => $data->object->message->peer_id, 
            'access_token' => $token, 
            'v' => '5.124'
        ); 
        $get_params = http_build_query($request_params); 
        $out = file_get_contents('https://api.vk.com/method/users.get?'.$get_params);
        $user_data = json_decode($out, true);
        extract($user_data['response'][0]);
    /* выборка данных и помещение их в массив */
    $messages_list = '';
    //send notify to printer owners, that client make on order

    //get the list of orders to build a keyboard
    // $SQL_orders = "SELECT order_id FROM print_orders WHERE done is NULL";
    $SQL_orders = "SELECT order_id FROM print_orders WHERE done is NULL ORDER BY order_id DESC LIMIT 4";
    if ($SQL_orders_response = mysqli_query($link, $SQL_orders)) {
        $order_ids = array();
        while ($orders_row = mysqli_fetch_row($SQL_orders_response)) {
            array_push($order_ids, $orders_row[0]);
        }
    }
    //now we have an order_ids array
    //let's build a keyboard using foreach
    $keyboard = '{ 
    "one_time": true, 
    "buttons": [';
    foreach ($order_ids as $key => $value) {
        $keyboard .= '[{ 
        "action": { 
          "type": "text", 
          "payload": "{\"button\": \"1\"}", 
          "label": "Печатаю #'.$value.'" 
        }, 
        "color": "positive" 
      }],';
    }
    $keyboard = substr_replace($keyboard ,'', -1).']}';
    //new keyboard done, sending...
    while ($row = mysqli_fetch_row($result)) {  
        $message = "&#128224; ПЕЧАТЬ\nБыстрее жми кнопку, если готов распечатать прямо сейчаc!\nЗаказ ".$last_order_id." оставил: [id".$data->object->message->peer_id."|".$first_name." ".$last_name."] ";
        $request_params = array( 
            'message' => $message,
            'random_id' => random_int(10000000, 99999999),
            "attachment" => "photo".$photo_id,
            "keyboard" => $keyboard,
            'user_id' => $row[1], 
            'access_token' => $token, 
            'v' => '5.124'
        ); 
        $get_params = http_build_query($request_params); 
        $out = file_get_contents('https://api.vk.com/method/messages.send?'.$get_params);
        $msg_id_arr = json_decode($out,true);
        $msg_id = $msg_id_arr['response'];
        $messages_list .= $msg_id.',';
}
$messages_list = substr_replace($messages_list ,'', -1);
unset($keyboard);
mysqli_query($link, "UPDATE print_orders SET messages_id='".$messages_list."' WHERE order_id=".$last_order_id);
    /* очищаем результирующий набор */
    mysqli_free_result($result);
}
    }
    else
    {
        file_put_contents('print_err.log', $err[0]);
        //save error log to the file 
        $otvet .= json_encode($err);
    }

}

elseif(preg_replace("/Печатаю #[0-9]{1,5}/","",$msg_object->message->text)=="") {
    preg_match_all('!\d+!', $msg_object->message->text, $matches);
    $order_number = implode(' ', $matches[0]);
    include "conf.php";
    $link = mysqli_connect($host,$login,$pass,$db); //connect to DB
    #take an order and make it 'done' in DB
    mysqli_query($link, "UPDATE `print_orders` SET `worker_id`=".$data->object->message->peer_id.",`done`=TRUE WHERE `order_id`=".$order_number);

    $keyboard = file_get_contents("keyboard_duty_main.json");
    //removing messages sended to other printer owners
    //get message_ids:
    $SQL_request = "SELECT messages_id,client_id FROM print_orders WHERE order_id=".$order_number;

    $message_ids_sql = mysqli_query($link, $SQL_request);
    $row = mysqli_fetch_assoc($message_ids_sql);
    $msg_ids = $row['messages_id'];
    $client_id = $row['client_id'];
    $otvet = "Заказ принят, ваш номер телефона и блок переданы [id".$client_id."|заказчику]!\nКогда за заказом подойдут, вы получите уведомление";
    //building a GET request for the VK api
    $request_params = array( 
        'message_ids' => $msg_ids,
        'delete_for_all' => 1, 
        'access_token' => $token, 
        'v' => '5.124'
    ); 
        $get_params = http_build_query($request_params); 
        //send request to DELETE messages for other printer owners, couse one of them take the order already
        //Удалим сообщения, т.к. заказ уже был принят
        $out = file_get_contents('https://api.vk.com/method/messages.delete?'.$get_params);

    //send printer owners contacts to the client
    //get printer owners information from his page
    $request_params = array( 
            'fields' => 'photo_id,first_name,last_name',
            'user_ids' => $data->object->message->peer_id, 
            'access_token' => $token, 
            'v' => '5.124'
        ); 
        $get_params = http_build_query($request_params); 
        $out = file_get_contents('https://api.vk.com/method/users.get?'.$get_params);
        $user_data = json_decode($out, true);
        extract($user_data['response'][0]);
    //get printer owners information from DB
    //получим список id исполнителей
    $SQL_request = "SELECT * FROM printer_owners WHERE owner_id=".$data->object->message->peer_id;
    $message_ids_sql = mysqli_query($link, $SQL_request);
    $row = mysqli_fetch_assoc($message_ids_sql);
    $owner_block = $row['owner_block'];
    $cost = $row['cost'];
    $phone = $row['phone'];

    //send notify to client
    // if ($data->object->message->peer_id == "") { 
    //     $message = "Ваш заказ принят! \nПрямо сейчас вам готов напечатать [id".$data->object->message->peer_id."|".$first_name." ".$last_name."] \nОбратитесь в лс для уточнения пункта выдачи";
    // }
    // else{ //если у человека указан блок, пишем в каком блоке стоит принтер
    //     $message = "Ваш заказ принят! \nПрямо сейчас вам готов напечатать [id".$data->object->message->peer_id."|".$first_name." ".$last_name."] из ".$owner_block." блока";
    // }
    $intblock = intval($owner_block);
    $floor_withoutcell = $intblock/8;
    $floor = ceil($floor_withoutcell)+1;
    $message = "Ваш заказ принят! \nПрямо сейчас вам готов напечатать [id".$data->object->message->peer_id."|".$first_name." ".$last_name."]\nЭтаж: {$floor} \nБлок: {$owner_block}\nТелефон для оплаты: {$phone} \nОтправляй файл в лс и жми кнопку как подойдёшь";
    $keyboard_inline = '

{   
    "inline": true, 
    "buttons": [
    [{ 
        "action": { 
          "type": "text", 
          "payload": "'.$data->object->message->peer_id.'", 
          "label": "Подошёл" 
        }, 
        "color": "positive" 
      }] 
    ] 
  }
  ';
    $request_params = array( 
    'message' => $message,
    'random_id' => random_int(100000, 999999),
    "attachment" => "photo".$photo_id, 
    'user_id' => $client_id, 
    'keyboard' => $keyboard_inline,
    'access_token' => $token, 
    'v' => '5.124'
    );
    $get_params = http_build_query($request_params); 
    $out = file_get_contents('https://api.vk.com/method/messages.send?'.$get_params);


    //send default keyboard to other printer owners
    //отправим обычную клавиатуру, если больше нет заказов или обновлённую, если остались невыполненные

    //get the list of orders to build a keyboard
    //получаем список оставшихся заказов
    $SQL_orders = "SELECT order_id FROM print_orders WHERE done is NULL ORDER BY order_id DESC LIMIT 4";
    if ($SQL_orders_response = mysqli_query($link, $SQL_orders)) {
        $order_ids = array();
        while ($orders_row = mysqli_fetch_row($SQL_orders_response)) {
            array_push($order_ids, $orders_row[0]);
        }
    }
    //now we have an order_ids array
    if (count($order_ids)==0) { //если заказов не осталось, шлём стандартную клавиатуру
        $keyboard = file_get_contents('keyboard_duty_main.json');
        $message = "Пропущен заказ";
    }
    else{ //если есть ещё заказы, то:
            //let's build a keyboard using foreach
            $keyboard = '{ 
            "one_time": true, 
            "buttons": [';
            foreach ($order_ids as $key => $value) {
                $keyboard .= '[{ 
                "action": { 
                  "type": "text", 
                  "payload": "{\"button\": \"1\"}", 
                  "label": "Печатаю #'.$value.'" 
                }, 
                "color": "positive" 
              }],';
            }
            $keyboard = substr_replace($keyboard ,'', -1).']}';
            $message = "Пропущен заказ, но остались другие! Успей принять!";
            //new keyboard done, sending...
    }
    $request_a = "SELECT * FROM `printer_owners`";
        if ($result = mysqli_query($link, $request_a)) {
            while ($row = mysqli_fetch_row($result)) {
                if ($row[1]!=$data->object->message->peer_id) { //не отправляем это сообщение тому, кто принял заказ
                    $request_params = array( 
                        'message' => $message,
                        'random_id' => random_int(10000000, 99999999),
                        "keyboard" => $keyboard ,
                        'user_id' => $row[1], 
                        'access_token' => $token, 
                        'v' => '5.124'
                    ); 
                $get_params = http_build_query($request_params); 
                $out = file_get_contents('https://api.vk.com/method/messages.send?'.$get_params);
            }
            }
        }
}



elseif($msg_object->message->text=="Подошёл"){
    $payload = $msg_object->message->payload;
    $request_params = array( 
                        'message' => "За заказом пришли",
                        'random_id' => random_int(10000000, 99999999),
                        'user_id' => $payload, 
                        'access_token' => $token, 
                        'v' => '5.124'
                    ); 
                $get_params = http_build_query($request_params); 
                $out = file_get_contents('https://api.vk.com/method/messages.send?'.$get_params);
    $keyboard = file_get_contents('keyboard_duty_main.json');
    $otvet = "Сотрудник уведомлён!";

}
elseif($msg_object->message->text=="Главное меню"){
    $otvet = "Вы в главном меню!";
    $keyboard = file_get_contents("keyboard_duty_main.json");
}
elseif($msg_object->message->text=="Онлайн" or $msg_object->message->text=="Оффлайн"){
    include "conf.php";
    $link = mysqli_connect($host,$login,$pass,$db); //connect to DB
    if ($msg_object->message->text=="Онлайн") {
        $from_table = "printer_owners";
        $to_table = "printer_owners_offline";
        $otvet .= "Готово, теперь ты один принимаешь заказы)";
    }
    if ($msg_object->message->text=="Оффлайн") {
        $from_table = "printer_owners_offline";
        $to_table = "printer_owners";
        $otvet .= "Готово, теперь все могут принимать заказы";
    }
    $sql_select_exceptme = "SELECT * FROM `{$from_table}` WHERE `owner_id` != ''";
    if ($SQL_orders_response = mysqli_query($link, $sql_select_exceptme)) {
        while ($row = mysqli_fetch_row($SQL_orders_response)) {
           mysqli_query($link, "INSERT INTO `{$to_table}` (`prime`, `owner_id`, `owner_block`, `cost`, `phone`, `name`) VALUES ('{$row[0]}','{$row[1]}','{$row[2]}','{$row[3]}','{$row[4]}','{$row[5]}')");

            
        }
        mysqli_query($link, "DELETE FROM `{$from_table}` WHERE `owner_id`!='38936276'");
        
    }
    else{
        $otvet = ":( у нас что-то не получилось";
    }
    
} //end of online block

elseif (($msg_object->message->text=="Зарегистрироваться")OR($msg_object->message->text=="Регистрация")) {
    $otvet="Просто напиши номер своего блока без букв и пользуйся всеми возможностями бота!&#128521;";
}
elseif ($msg_object->message->text=="Настройки") {
    $otvet="Вы находитесь в меню настроек.\nВы можете зарегестрироваться или удалить себя из бота\nА так же включить или выключить уведомления о дежурствах!";
    $keyboard = file_get_contents('keyboard_duty_settings.json');
}
elseif ($msg_object->message->text=="Энергетики") {
    $otvet = "Выбирайте понравившийся напиток и пишите продавцу! \nПокупка в 2 клика!\nБольше товаров доступно в [public180202655|разделе товаров в сообществе] !";
    // echo "anisimov_token";
    $out = file_get_contents('https://api.vk.com/method/market.get?owner_id=-180202655&album_id=1&access_token='.$anisimov_token."&v=5.124");
    $from_vk = json_decode($out);
    $items = $from_vk->response->items;
    $energy_album = '';
    foreach ($items as $key => $value) {
        $energy_album .= 'market'.$value->owner_id.'_'.$value->id.",";
        // file_put_contents('market.txt', 'market'.$value->owner_id.'_'.$value->id."\n", FILE_APPEND);
    }
    $energy_album = substr_replace($energy_album ,'', -1);

    // // $otvet="Если вас заинтересовало предложение, просто нажмите кнопку Заказать!";
    // $energy_album = "market_album".json_encode($from_vk->response);
}

elseif ($msg_object->message->text=="Вкл уведомления") {
    $otvet="Режим получения уведомлений о дежурствах включен!";
    include "conf.php";
    $link = mysqli_connect($host,$login,$pass,$db);
    mysqli_query($link, "UPDATE dormitory_users SET notify=1 WHERE user_id=".$msg_object->user_id);
}
elseif ($msg_object->message->text=="Удаление") {
    $raw_json = file_get_contents("4domitory.json");
    $json = json_decode($raw_json,true);
    $ego_id = "https://vk.com/id".$id;
    $counter_blocks = 0;
    $block = "";
    $blocks_num = array_keys($json);
    $counter_blocks = 0;
    foreach ($json as $value) {
        $amount = array_search($ego_id, $value);

        // var_dump($value);
        if ($amount!==false) {
            $block = $blocks_num[$counter_blocks];
            $otvet = "Вы успешно удалены из {$block} блока";
            $otvet="Вы удалены из бота\nТеперь вам недоступны такие сервисы, как:\nПечать\nЗаявки на ремонт\nДоставка энергетиков(скоро)\nЗря вы так :(";
    include "conf.php";
    $link = mysqli_connect($host,$login,$pass,$db);
    mysqli_query($link, "DELETE FROM dormitory_users WHERE user_id=".$msg_object->user_id);
            file_put_contents('removed_users.log', "> ".$msg_object->user_id." removed couse of REMOVE_REQUEST \n", FILE_APPEND);
            break;
        }

        $counter_blocks = $counter_blocks+1;
    }
if (!$otvet) {
    $otvet = "Мы не нашли Вас в нашей базе, вы ещё не привязаны к блоку";
}
unset($json[$block][$amount]);
$result = json_encode($json);
file_put_contents("4domitory.json", $result);
$keyboard = file_get_contents("keyboard_duty_main.json");

    
}
elseif ($msg_object->message->text=="Выкл уведомления") {
    $otvet="Режим получения уведомлений о дежурствах выключен! :(";
    include "conf.php";
    $link = mysqli_connect($host,$login,$pass,$db);
    mysqli_query($link, "UPDATE dormitory_users SET notify=0 WHERE user_id=".$msg_object->user_id);
}

elseif(in_array($msg_object->message->text, array("Эспрессо","Американо","Капучино","Латте","Латте-карамель"))){
    $coffie_db = file_get_contents("coffie_orders.json");
    $coffie_orders = json_decode($coffie_db, true);
    $coffie_orders[$id] = $coffie_orders[$id]+1;
    $coffie_db = json_encode($coffie_orders);
    file_put_contents("coffie_orders.json", $coffie_db);
    if (($coffie_orders[$id]%6)==0) {
        $otvet = "Ваш заказ на {$msg_object->message->text} принят и его скоро начнут готовить!\nЭто ваша шестая покупка, поэтому ваш кофе БЕСПЛАТНЫЙ!";
    }
    else{
    $otvet = "Ваш заказ на {$msg_object->message->text} принят и его скоро начнут готовить!\nВы можете оплатить заказ сейчас, сделав перевод на карту сбер: 2202200314484387 или оплатить при получении";}
    $json = file_get_contents("4domitory.json");
    $json1 = json_decode($json);
    $test = array_dot($json1);
    $id = $msg_object->user_id;
    $domroom = array_search("https://vk.com/id{$id}", $test);
    $block = explode(".",$domroom)[0];
    $request_params1 = array( 
        'message' => "Заказ в {$block} блок\n{$msg_object->message->text}\nhttps://vk.com/id{$id}\n", 
        'user_ids' => "",  
        'random_id' => random_int(10000000, 99999999),
        'access_token' => $token, 
        'v' => '5.124'); 
    $get_params1 = http_build_query($request_params1); 
    file_get_contents('https://api.vk.com/method/messages.send?'.$get_params1);

}
// ---------TUT TOZHE VSE OHEN PLOHO -------------
elseif($msg_object->message->text=="Ремонт"){
        $raw_json = file_get_contents("4domitory.json");
    $id = $msg_object->user_id;
    if (strpos($raw_json, "https:\/\/vk.com\/id{$id}")==0) {
        $otvet="Чтобы воспользоваться данной функцией вам нужно сперва указать номер вашего блока!";
    }
    else{
    $otvet = "Выберите тип требуемых ремонтных работ";
    $keyboard = file_get_contents('keyboard_duty_fix.json');

}}
// ------------------HELP-------------TRASH CONTENT -------------
// elseif(in_array($msg_object->message->text, array("Сантехника","Плотник","Электрика","Другое")))
// {
//     $raw_json = file_get_contents("4domitory.json");
//     $id = $msg_object->user_id;
//     if (strpos($raw_json, "https:\/\/vk.com\/id{$id}")==0) {
//         $otvet="Чтобы воспользоваться данной функцией вам нужно сперва указать номер вашего блока!";
//     }
//     else{
//     $fix_raw = file_get_contents("fix.json");
//     $fix_arr = json_decode($fix_raw);


//     $json = file_get_contents("4domitory.json");
//     $json1 = json_decode($json);
//     $test = array_dot($json1);
//     $id = $msg_object->user_id;
//     $domroom = array_search("https://vk.com/id{$id}", $test);
//     $block = explode(".",$domroom)[0];
//     $out = file_get_contents('https://api.vk.com/method/users.get?user_ids='.$id."&fields=first_name&access_token=".$token."&v=5.124");
//     $from_vk = json_decode($out);
//     $resp = $from_vk->response;
//     $name = json_encode($resp[0]);
//     $wok = $resp[0];
//     $first_name = $wok->first_name;
//     $last_name = $wok->last_name;
//     $today = getdate();
//     $day = $today[mday].".".$today[mon].".".$today[year]." ".$today[hours].":".$today[minutes].":".$today[seconds];
//     $vkurl = "https://vk.com/id{$id}";
//     $state = "state";
//     $name = $first_name." ".$last_name;
//     $output = array(
//         day=>$day,
//         block=>$block,
//         name=>$name,
//         vkurl=>$vkurl,
//         type=>$msg_object->message->text,
//         );
//     $request = http_build_query($output);
//    $otvet = "Чтобы оставить заявку, перейдите по ссылке: https://miningmeeting.ru/claim_dom.php?{$request}";

// }
// }


// ---------NE TROGAY ONA TEBYA SOZHRET ----------------------
else{
    if ($msg_object->message->peer_id<2000000000) {
        $raw_json = file_get_contents("4domitory.json");
    if (strpos($raw_json, "https:\/\/vk.com\/id{$id}")!=0) {
        $otvet = "Вы в главном меню!";
        $keyboard = file_get_contents("keyboard_duty_main.json");
    }
    else{
    $otvet = "Чтобы начать работу с ботом, вам необходимо указать номер своего блока. Напишите номер блока в этот чат!";}
    }
    
}

}

$request_params = array( 
'random_id' => random_int(10000000, 99999999),
'peer_id' => $data->object->message->peer_id, 
'access_token' => $token, 
'v' => '5.124'
); 
// $keyboard = file_get_contents("keyboard_duty_main.json");
if ($keyboard) {
    $request_params['keyboard']=$keyboard;
}
if ($energy_album) {
    $request_params['attachment']=$energy_album;
}
if ($otvet) {
    $request_params['message']=$otvet;
}

$get_params = http_build_query($request_params); 
$out = file_get_contents('https://api.vk.com/method/messages.send?'.$get_params);
file_put_contents("AAAAAAAAAAAAOUT.txt", $out." > https://api.vk.com/method/messages.send?".$get_params);
echo('ok'); 
// file_put_contents("ERRORKEYBOARD.TXT", $out);
break;

}}
?> 
