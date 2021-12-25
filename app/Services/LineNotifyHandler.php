<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use QL\QueryList;

class LineNotifyHandler
{
    /**
     * 執行通知
     *
     * @return Boolean 是否成功
     */
    function notifyHandle($token, $text)
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
