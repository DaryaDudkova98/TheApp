<?php

namespace App\Service;

use Google\Client;
use Google\Service\Gmail;
use Google\Service\Gmail\Message;

class GmailMailer
{
    public function send(string $to, string $subject, string $html): void
    {

        $client = new Client();
        $client->setApplicationName('The App');
        $client->setScopes([Gmail::GMAIL_SEND]);
        $client->setAuthConfig(__DIR__ . '/../../credentials.json');
        $client->setAccessType('offline');

        $tokenBase64 = dd(getenv('GMAIL_TOKEN_BASE64') ?: 'getenv not found');
        if (!$tokenBase64) {
            throw new \RuntimeException("GMAIL_TOKEN_BASE64 is missing");
        }

        $decoded = base64_decode($tokenBase64, true);

        if (!$decoded) {
            throw new \RuntimeException("Base64 decode failed");
        }

        $token = json_decode($decoded, true);

        if (!$token || !is_array($token)) {
            throw new \RuntimeException("Decoded token is not valid JSON");
        }

        if (!isset($token['access_token'])) {
            throw new \RuntimeException("Token JSON does not contain access_token");
        }

        $client->setAccessToken($token);

        if ($client->isAccessTokenExpired()) {
            if ($client->getRefreshToken()) {
                $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
            } else {
                throw new \RuntimeException("Access token expired and no refresh_token available");
            }
        }

        $gmail = new Gmail($client);

        $raw = "To: $to\r\n";
        $raw .= "Subject: $subject\r\n";
        $raw .= "MIME-Version: 1.0\r\n";
        $raw .= "Content-Type: text/html; charset=utf-8\r\n\r\n";
        $raw .= $html;

        $message = new Message();
        $message->setRaw(rtrim(strtr(base64_encode($raw), '+/', '-_'), '='));

        $gmail->users_messages->send('me', $message);
    }
}

