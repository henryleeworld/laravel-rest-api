# Laravel 11 具象狀態傳輸應用程式介面

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
- 產生 Laravel 要使用的一組 32 字元長度的隨機字串 APP_KEY 並存在 .env 內。
```sh
$ php artisan key:generate
```
- 執行 __Artisan__ 指令的 __migrate__ 來執行所有未完成的遷移。
```sh
$ php artisan migrate
```
- 在瀏覽器中輸入已定義的路由 URL 來訪問，例如：http://127.0.0.1:8000。
- 你可以經由 `/api/v1/register` 來進行註冊使用者。
- 你可以經由 `/api/v1/login` 來進行使用者登入。
- 或可以經由 `/api/v1/user` 來進行個人資料取得。
- 或可以經由 `/api/v1/logout` 來進行使用者登出。

----

## 畫面截圖
![](https://i.imgur.com/ipOt9xf.png)
> 傳送 HTML 表單資料註冊建立使用者

![](https://i.imgur.com/HztPBLD.png)
> 傳送 HTML 表單資料使用建立使用者來做登入

![](https://i.imgur.com/TMbwRpQ.png)
> 在 Authorization 請求標頭中指定存取權杖作為憑證令牌來做個人資料取得

![](https://i.imgur.com/TwX3bUx.png)
> 在 Authorization 請求標頭中指定存取權杖作為憑證令牌來做登出
