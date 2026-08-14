# Laravel 13 具象狀態傳輸應用程式介面

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
- 運行單元測試和功能測試。大多數的單元測試可能只專注於單一個方法，功能測試則可以測試大部分的程式碼，包含一些物件如何進行互動，甚至是完整的 HTTP 請求到一個 JSON 端點。
```sh
$ php artisan test
```
- 在瀏覽器中輸入已定義的路由 URL 來訪問，例如：http://127.0.0.1:8000。
- 你可以經由 `/api/v1/register` 來進行註冊使用者。
- 你可以經由 `/api/v1/login` 來進行使用者登入。
- 或可以經由 `/api/v1/user` 來進行個人資料取得。
- 或可以經由 `/api/v1/logout` 來進行使用者登出。
- 你可以經由 `/docs/api` 來進行應用程式介面文件閱讀。

----

## 畫面截圖
![](https://i.imgur.com/DYRyldq.png)
> 檢查程式碼是否如預期般執行

![](https://i.imgur.com/ZGfZ8ZK.png)
> 傳送 HTML 表單資料註冊建立使用者

![](https://i.imgur.com/9D4xPei.png)
> 傳送 HTML 表單資料使用建立使用者來做登入

![](https://i.imgur.com/iU0bfXq.png)
> 在 Authorization 請求標頭中指定存取權杖作為憑證令牌來做個人資料取得

![](https://i.imgur.com/tYqwIP4.png)
> 在 Authorization 請求標頭中指定存取權杖作為憑證令牌來做登出

![](https://i.imgur.com/SdUomcQ.png)
> 建議找不同領域或程度的開發者來閱讀文件，確認文件的內容適合任何程度的人閱讀
