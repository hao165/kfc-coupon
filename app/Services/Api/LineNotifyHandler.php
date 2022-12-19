<?php

namespace App\Services\Api;

class LineNotifyHandler
{
    public function __construct()
    {
    }

    /**
     * 傳送通知
     *
     * @return Boolean 是否成功
     */
    public function send($token, $text)
    {
        if (!$token) {
            return false;
        }

        $headers = array("Content-Type: multipart/form-data",
                        "Authorization: Bearer {$token}");
        $message = array("message" => "{$text}");

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://notify-api.line.me/api/notify");
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $message);
        $result = curl_exec($ch);
        curl_close($ch);

        $result = json_decode($result);

        if ($result->status == '200') {
            return true;
        }
        return false;
    }
}
