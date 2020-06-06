<?php

//https://api.telegram.org/bot510753855:AAHGIbd7FSPmkR-s8ySqOkumL6zqeXa8kwI/getUpdates

class TelegramBot
{
	private $botToken = "553416028:AAEUkX9ipzPALtAUQGz6SPXQRZZ5Tj2kEDI";
	private $webSite  = "http://api.e--sslx.telegram.freegoogle.ir/bot";
	private $chatId   = "-316377063";

    public $res;

	function __construct($chatId = '')
	{
        if($chatId != '')
            $this->chatId = $chatId;
	}


    public function sendMessage($message)
    {
        $message_end = '';

        if(is_array($message))
        {
            foreach($message as $key => $val)
                $message_end .= "<b>$key: </b>\n $val\n\n";
        }
        else
            $message_end .= $message;

        return $this->_send($message_end);
    }


    public function sendRate($message,$stars)
    {
        $message_end = str_repeat('⭐️',intval($stars));
        $message_end .= "\n\n";

        if(is_array($message))
        {
            foreach($message as $key => $val)
                $message_end .= "<b>$key: </b>\n $val\n";
        }
        else
            $message_end .= $message;

        return $this->_send($message_end);
    }


    private function _send($message)
    {
        $url = $this->webSite . $this->botToken . "/sendMessage?chat_id=" . $this->chatId . "&text=" . urlencode($message) . "&parse_mode=HTML&reply_markup=";
        //echo $url;
        //$this->res = file_get_contents($url);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url); //set url
        curl_setopt($ch, CURLOPT_HEADER, false); //get header
        curl_setopt($ch, CURLOPT_NOBODY, false); //do not include response body
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); //do not show in browser the response
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); //follow any redirects
        $this->res = curl_exec($ch);

        curl_close($ch);

        return $this->res;
    }

}
