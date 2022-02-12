<p align="center">
<a href="https://kfc.izo.tw" target="_blank"><img src="https://kfc.izo.tw/icon.png" width="150px"><br>( 網址： https://kfc.izo.tw )</a>
</p>

## KFC優惠選
- 後端框架：Laravel 8 + Jetstream
- 前端框架：Bootstrap 5.1 (with icon) + jQuery 3.6
- REDIS (PECL-phpRedis) 用於流量統計.快取.SESSION
- Git： 使用 Fork GUI 進行版本控管
- VPS： Oracle Cloud + hestiaCP

## 網站說明
- 平均日流250人次，單日高峰3600人次
- 蒐集肯德基優惠券，並提供留言討論
- 透過爬蟲抓取PTT討論，採半自動化轉入討論區
- 前端功能：提供篩選、排序、搜尋
- 會員功能：新增優惠券、留言討論、收藏管理
- 後台功能：管理爬蟲資訊、Line Notify全站動態通知管理、審核優惠券

## 預覽圖
![01](resources/pics/01.jpg "01")
![02](resources/pics/02.jpg "02")
![03](resources/pics/03.jpg "03")

## 為什麼會有流量？
因為肯德基優惠券於過期後，還是有可能可以繼續使用的，(長可達半年之久)
故此討論區，可以查找想要的優惠券之外，
還是可以從過期的優惠券討論中，使用更好的優惠！

## 優化
- 商業邏輯：將熱門討論的優惠券，預設排序於前面
- GitHook：自動化更新支線
- 運用轉場動畫，提高流暢感
- 全站使用RWD
- 第三方社群快速登入 (FaceBook、Line)

## 前端套件
- SweetAlert2 (用途：Model彈窗)
- jquery.mobile (用途：判斷真人瀏覽，觸擊流量統計)
- AOS (用途：滾動動畫)
- LazyLoad (用途：圖片延遲載入)

## Composer
- jaeger/querylist：爬蟲框架
- intervention/image：圖片管理 - 浮水印、縮圖調整
- laravel/jetstream：權限管理
- laravel/socialite：第三方登入

## API運用
- Line Notify：全站動態、爬蟲資訊，傳訊至管理員Line
- imgur：圖片上傳平台
