<?php

namespace App\Service;

use Google\Client;
use Google\Service\Gmail;
use Google\Service\Gmail\Message;

class GmailMailer
{
    private Gmail $gmail;

    public function __construct()
    {
        $client = new Client();
        $client->setApplicationName('The App');
        $client->setScopes([Gmail::GMAIL_SEND]);
        $client->setAuthConfig(__DIR__ . '/../../credentials.json');
        $client->setAccessType('offline');

        if (file_exists('token.json')) {
            $client->setAccessToken(json_decode(file_get_contents('token.json'), true));
        }

        if ($client->isAccessTokenExpired() && $client->getRefreshToken()) {
            $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
            file_put_contents('token.json', json_encode($client->getAccessToken()));
        }

        $this->gmail = new Gmail($client);
    }

    public function send(string $to, string $subject, string $html): void
    {
        $raw = "To: $to\r\n";
        $raw .= "Subject: $subject\r\n";
        $raw .= "MIME-Version: 1.0\r\n";
        $raw .= "Content-Type: text/html; charset=utf-8\r\n\r\n";
        $raw .= $html;

        $message = new Message();
        $message->setRaw(rtrim(strtr(base64_encode($raw), '+/', '-_'), '='));

        $this->gmail->users_messages->send('me', $message);
    }
}
