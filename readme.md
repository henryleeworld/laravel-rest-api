# Laravel 7 具象狀態傳輸應用程式介面

一組實現效率、可讀性、還有可擴展分散式系統的軟體架構設計規範，符合具象狀態傳輸原則的系統有五個主要特性/限制：伺服器/客戶端分離、無狀態、可快取、分層、統一操作介面。

## 使用方式
- 把整個專案複製一份到你的電腦裡，這裡指的「內容」不是只有檔案，而是指所有整個專案的歷史紀錄、分支、標籤等內容都會複製一份下來。
```sh
$ git clone
```
- 將 __.env.example__ 檔案重新命名成 __.env__，如果應用程式金鑰沒有被設定的話，你的使用者 sessions 和其他加密的資料都是不安全的！
- 當你的專案中已經有 composer.lock，可以直接執行指令以讓 Composer 安裝 composer.lock 中指定的套件及版本。
```sh
$ composer install
```
- 產⽣ Laravel 要使用的一組 32 字元長度的隨機字串 APP_KEY 並存在 .env 內。
```sh
$ php artisan key:generate
```
- 執行 __Artisan__ 指令的 __migrate__ 來執行所有未完成的遷移。Passport 服務提供者在框架中已註冊好本身的資料庫遷移目錄，所以你應該在遷移資料庫之後註冊這個提供者。Passport 的遷移檔會建立應用程式需要儲存客戶端與 Access Token 的資料表。
```sh
$ php artisan migrate
```
- 執行 __Artisan__ 指令的 __passport:install__ 會建立用來產生安全 Access Token 的加密金鑰。此外，該指令會建立用於產生 Access Token 的「個人存取」與「密碼授權」的客戶端。
```sh
$ php artisan passport:install
```
- 利用工具輸入已定義的路由 URL 模擬 HTTP 請求，例如：http://127.0.0.1:8000/api/register。

----

## 畫面截圖
![](https://i.imgur.com/y1DjDzl.png)
> 傳送 HTML 表單資料註冊建立使用者

![](https://i.imgur.com/bwyVWsH.png)
> 傳送 HTML 表單資料使用建立使用者來做登入