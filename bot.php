<?php
class ReplyKeyboardMarkup
{
    public $keyboard;
    public $resize_keyboard;
    public $one_time_keyboard;
    public $selective;

    function __construct($resize_keyboard = FALSE, $one_time_keyboard = FALSE, $selective = FALSE)
    {
        $this->keyboard = array();
        $this->keyboard[0] = array();
        $this->resize_keyboard = $resize_keyboard;
        $this->one_time_keyboard = $one_time_keyboard;
        $this->selective = $selective;
    }

    public function add_option($option)
    {
        $this->keyboard = $option;
    }
}

class ReplyKeyboardRemove
{
    public $remove_keyboard;
    public $selective;

    function __construct($remove_keyboard = TRUE, $selective = FALSE)
    {
        $this->remove_keyboard = $remove_keyboard;
        $this->selective = $selective;
    }
}

class ForceReply
{
    public $force_reply;
    public $selective;

    function __construct($force_reply = TRUE, $selective = FALSE)
    {
        $this->force_reply = $force_reply;
        $this->selective = $selective;
    }
}

class InlineKeyboardMarkup
{
    public $inline_keyboard;

    function __construct()
    {
        $this->inline_keyboard = array();
        $this->inline_keyboard[0] = array();
    }

    public function add_option($option)
    {
        $this->inline_keyboard = $option;
    }
}

class telegram_bot
{
    private $token;

    private function open_url($url, $method = "GET", $data = null)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        if ($method === "POST") {
            if (isset($data)) curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        return curl_exec($ch);
    }

    private function file_request($file_path)
    {
        $token = $this->token;
        return $this->open_url("https://api.telegram.org/file/bot$token/$file_path");
    }

    private function control_api($action, $data = NULL)
    {
        $token = $this->token;
        $response = json_decode($this->open_url("https://api.telegram.org/bot$token$action", "POST", $data));
        return $response;
    }

    function __construct($token)
    {
        $this->token = $token;
    }

    public function status()
    {
        $response = $this->control_api("/getme");
        return ($response);
    }

    public function get_updates($offset = null)
    {
        $data = array();
        $data["offset"] = $offset;
        $response = $this->control_api("/getUpdates", $data);
        return ($response);
    }

    public function reset_messages_queue()
    {
        $this->set_webhook();
        return $this->get_updates(-1);
    }

    public function send_action($to, $action)
    {
        $data = array();
        $data["chat_id"] = $to;
        $data["action"] = $action;
        $response = $this->control_api("/sendChatAction", $data);
        return $response;
    }

    public function send_message($to, $msg, $id_msg = null, $reply = null, $type = "HTML", $disable_preview = true)
    {
        $data = array();
        $data["chat_id"] = $to;
        $data["text"] = $msg;
        $data["disable_web_page_preview"] = (string)$disable_preview;
        if (isset($id_msg)) $data["reply_to_message_id"] = $id_msg;
        if (isset($reply)) $data["reply_markup"] = $reply;
        if (isset($type)) $data["parse_mode"] = $type; // "Markdown" or "HTML"; see https://core.telegram.org/bots/api#formatting-options
        $response = $this->control_api("/sendMessage", $data);
        return $response;
    }

    public function get_chat_member_count($to)
    {
        $data = array();
        $data["chat_id"] = $to;
        $response = $this->control_api("/getChatMembersCount", $data);
        return $response;
    }

    public function getChat($to)
    {
        $data = array();
        $data["chat_id"] = $to;
        $response = $this->control_api("/getChat", $data);
        return $response;
    }

    public function send_document($to, $document, $caption = null, $parse_mode = null, $id_msg = null, $reply = null)
    {
        $this->send_action($to, "upload_document");
        $data = array();
        $data["chat_id"] = $to;
        if (substr($document, 0, 1) == "@") $document = substr($document, 1); // support for "@$filename"
        if (file_exists($document)) {
            if (class_exists('CurlFile', false)) $document = new CURLFile(realpath($document));
            else $document = "@" . $document;
        }
        $data["document"] = $document;
        if (isset($caption)) $data["caption"] = $caption;
        if (isset($parse_mode)) $data["parse_mode"] = $parse_mode;
        if (isset($id_msg)) $data["reply_to_message_id"] = $id_msg;
        if (isset($reply)) $data["reply_markup"] = $reply;
        $response = $this->control_api("/sendDocument", $data);
        return $response;
    }


    public function send_inline($inline_query_id, $results, $cache_time = null, $is_personal = null)
    {
        $data = array();
        $data["inline_query_id"] = $inline_query_id;
        $data["results"] = $results;
        if (isset($cache_time)) $data["cache_time"] = $cache_time;
        if (isset($is_personal)) $data["is_personal"] = $is_personal;
        $response = $this->control_api("/answerInlineQuery", $data);
        return $response;
    }

    public function edit_message($chatid = null, $message_id = null, $text, $inline_message_id = null, $parse_mode = null, $disable_web_page_preview = null, $reply_markup = null)
    {
        $data = array();
        $data["text"] = $text;
        if (isset($chatid)) $data["chat_id"] = $chatid;
        if (isset($message_id)) $data["message_id"] = $message_id;
        if (isset($inline_message_id)) $data["inline_message_id"] = $inline_message_id;
        if (isset($parse_mode)) $data["parse_mode"] = $parse_mode;
        if (isset($disable_web_page_preview)) $data["disable_web_page_preview"] = $disable_web_page_preview;
        if (isset($reply_markup)) $data["reply_markup"] = $reply_markup;
        $response = $this->control_api("/editMessageText", $data);
        return $response;
    }

    public function edit_caption($chatid = null, $message_id = null, $inline_message_id = null, $caption = null, $reply_markup = null)
    {
        $data = array();
        if (isset($chatid)) $data["chat_id"] = $chatid;
        if (isset($message_id)) $data["message_id"] = $message_id;
        if (isset($inline_message_id)) $data["inline_message_id"] = $inline_message_id;
        if (isset($caption)) $data["caption"] = $caption;
        if (isset($reply_markup)) $data["reply_markup"] = $reply_markup;
        $response = $this->control_api("/editMessageCaption", $data);
        return $response;
    }

    public function edit_replymarkup($chatid = null, $message_id = null, $inline_message_id = null, $reply_markup = null)
    {
        $data = array();
        if (isset($chatid)) $data["chat_id"] = $chatid;
        if (isset($message_id)) $data["message_id"] = $message_id;
        if (isset($inline_message_id)) $data["inline_message_id"] = $inline_message_id;
        if (isset($reply_markup)) $data["reply_markup"] = $reply_markup;
        $response = $this->control_api("/editMessageReplyMarkup", $data);
        return $response;
    }

    public function forward_message($to, $from, $msg_id)
    {
        $data = array();
        $data["chat_id"] = $to;
        $data["from_chat_id"] = $from;
        $data["message_id"] = $msg_id;
        $response = $this->control_api("/forwardMessage", $data);
        return $response;
    }

    public function set_webhook($url = null, $certificatefile = null, $allowed_updates = null)
    {
        $data = array();
        $data["url"] = $url;
        if ($certificatefile != null) {
            $data["certificate"] = $certificatefile;
            $data["allowed_updates"] = $allowed_updates;
        }
        $response = $this->control_api("/setWebhook", $data);
        return $response;
    }

    public function get_user_profile_photos($id_user, $offset = null, $limit = null)
    {
        $data = array();
        $data["user_id"] = $id_user;
        if (isset($offset)) $data["offset"] = $offset;
        if (isset($limit)) $data["limit"] = $limit;
        $response = $this->control_api("/getUserProfilePhotos", $data);
        return $response;
    }

    public function get_chat_member($chat_id, $user_id)
    {
        $data = array();
        $data["chat_id"] = $chat_id;
        $data["user_id"] = $user_id;
        $response = $this->control_api("/getChatMember", $data);
        return $response;
    }

    public function get_file($file_id, $output_file)
    {
        try {
            // getting file path
            $data = array();
            $data["file_id"] = $file_id;
            $response = $this->control_api("/getFile", $data);
            if ($response->ok != 1) return null; // no downloadable file like contact or location
            $file_path = $response->result->file_path;
            $ext = strrchr($file_path, '.');
            // getting file content
            $file_content = $this->file_request($file_path);
            // storing to file
            $output_file = "$output_file$ext";
            $fp = fopen($output_file, 'w');
            fwrite($fp, $file_content);
            fclose($fp);
            return $output_file;
        } catch (Exception $e) {
        }
        return null;
    }

    public function read_post_message()
    {
        return json_decode(file_get_contents('php://input'));
    }
}
