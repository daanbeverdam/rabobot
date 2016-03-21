<?php

/**
* A simple Telegram bot that blames the Rabobank for everything.
*/

class RaboBot
{
    function __construct()
    {
        $this->config = parse_ini_file('config.ini');
        $this->base_url = 'https://api.telegram.org/bot' . $this->config['token'] . '/';
        $this->photo = new CURLFile(realpath("de_schuldige.jpg"));
    }

    function get_updates()
    {

        if (file_exists('offset')) {
            $offset = file_get_contents('offset');
        } else {
            $offset = 0;
        }

        $update_url = $this->base_url . 'getUpdates?offset=' . $offset . '&timeout=' . $this->config['timeout'];
        $update = file_get_contents($update_url);
        $update = json_decode($update);
        $this->log($update);

        if ($update->ok) {

            foreach ($update->result as $result) {

                if (isset($result->message)) {
                    $offset = $result->update_id + 1;
                    file_put_contents('offset', $offset);
                    $chat_id = $result->message->chat->id;
                    $this->answer($chat_id);

                } elseif (isset($result->inline_query)) {
                    $query_id = $result->inline_query->id;
                    $this->answer_inline($query_id);

                }

            }

        };

    }

    function answer_inline($query_id)
    {
        $url = $this->base_url . 'answerInlineQuery';
        $this->log($query_id);
    }

    function answer($chat_id)
    {
        $url = $this->base_url . 'sendPhoto';
        $post_fields = array('chat_id' => $chat_id,
        'photo' => $this->photo,
        'caption' => 'Het is allemaal de schuld van de Rabobank!'
        );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Content-Type:multipart/form-data"
        ));
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
        $response = curl_exec($ch);
        $this->log($response);
    }

    function log($entry)
    {
        print_r($entry); // just prints log entry for now
    }

    function run()
    {
        while (true) {
            $this->get_updates();
        }
    }

}

$bot = new RaboBot;
$bot->run();