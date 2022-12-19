<p align="center">
<a href="https://kfc.izo.tw" target="_blank"><img src="https://kfc.izo.tw/icon.png" width="150px"><br>( 網址： https://kfc.izo.tw )</a>
</p>

## KFC優惠選
- 後端框架：Laravel 8 + Jetstream
- 前端框架：Bootstrap 5.1 (with icon) + jQuery 3.6
- REDIS (PECL-phpRedis) 用於流量統計.快取.SESSION
- Git： GitHub，透過Hook進行自動部屬，並使用 Fork GUI 進行控管
- VPS： Oracle Cloud，網站管理面板使用hestiaCP

## 網站說明
- 平均日流250人次，單日高峰13000人次
- 蒐集肯德基優惠券，並提供留言討論
- 透過爬蟲抓取PTT討論，採半自動化轉入討論區
- 前端功能：提供篩選、排序、搜尋
- 會員功能：新增優惠券、留言討論、收藏管理、第三方登入(FaceBook、Line)
- 後台功能：管理爬蟲資訊、Line Notify全站動態通知管理、審核優惠券

## 預覽圖
![01](https://i.imgur.com/9sY8pWU.jpg "01")
![02](https://i.imgur.com/15369qN.jpg "02")
![03](https://i.imgur.com/io0wAcF.jpg "03")

## 為什麼會有流量？
因為肯德基優惠券於過期後，還是有可能可以繼續使用的，(長可達半年之久)
故此討論區，可以查找想要的優惠券之外，
還是可以從過期的優惠券討論中，使用更好的優惠！

## 優化
- 商業邏輯：將熱門討論的優惠券，預設排序於前面
- 運用轉場動畫，提高流暢感
- 全站使用RWD

## 前端套件
- SweetAlert2 (用於Model彈窗)
- jquery.mobile (用於判斷真人瀏覽，觸擊流量統計)
- AOS (用於滾動動畫)
- LazyLoad (用於圖片延遲載入)

## Composer
- jaeger/querylist：爬蟲框架
- intervention/image：圖片調整 - 浮水印、縮圖調整
- laravel/jetstream：權限管理
- laravel/socialite：第三方登入

## 其他API運用
- imgur：圖片上傳平台
