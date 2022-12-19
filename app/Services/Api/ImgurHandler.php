<?php

namespace App\Services\Api;

use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class ImgurHandler
{
    public function __construct()
    {
        $this->clientId    = config('services.imgur.client_id');
        // $this->clientSecret = config('services.imgur.client_secret');

        // 暫存檔案名稱
        $this->imgTempFile = sprintf('%s.jpg', rand());
        // 暫存檔案路徑
        $storagePath = storage_path('app/temp');
        $this->imgTempPath = sprintf('%s/%s', $storagePath, $this->imgTempFile);

        if (!file_exists($storagePath)) {
            mkdir($storagePath, 666, true);
        }
    }

    /**
     * 上傳團片
     */
    public function upload($request)
    {
        $requestImg = $request->file('img_file');

        $this->addImgMark($requestImg);

        $data = fread(fopen($this->imgTempPath, "r"), filesize($this->imgTempPath));
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, 'https://api.imgur.com/3/image.json');
        curl_setopt($curl, CURLOPT_TIMEOUT, 30);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Authorization: Client-ID ' . $this->clientId));
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, array('image' => base64_encode($data)));
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        $out = curl_exec($curl);
        curl_close($curl);

        // 刪除暫存的圖片
        Storage::delete('temp/' . $this->imgTempFile);

        return json_decode($out, true);
    }


    /**
     * 新增浮水印
     */
    public function addImgMark($requestImg)
    {
        // 新增浮水印
        $img = Image::make($requestImg->getRealPath());
        $imgMark = Image::make('watermark.png');
        $imgWidth = $img->width();
        $imgMarkWidth = round($imgWidth * 0.25);
        $imgMark->resize($imgMarkWidth, null, function ($constraint) {
            $constraint->aspectRatio();
        });
        $img->insert($imgMark, 'bottom-left', 30, 20)->save($this->imgTempPath);
    }
}
