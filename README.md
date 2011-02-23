FeliCa Auth
=============

FeliCa Auth は WordPress の認証機構を FeliCa を利用して行うプラグインです。

このプログラムのほとんどは別の WordPress のプラグインである [OpenID][openid] を参考にして作られています。

主に実験用のプログラムなのでご利用される場合は各自ご注意ください。

著作権など問題がありましたらご指摘くださいませ。

必要なもの
-------

FeliCa Auth は [PaSoRi][pasori]  を利用して FeliCa の読み取りを行っています。ご利用の PaSoRi をご用意ください。

また、FeliCa を接続するパソコンでは下記ソフトウェアをインストールしてください。

* [PC - FeliCaポートソフトウェア][felicasoftware1]
* [mac - FeliCa Proxy][felicasoftware2]

使い方（FeliCa の登録）
-------

1. 通常のパスワードでログインする。
2. サイドメニューのユーザーから Your FeliCa をクリックする。
3. FeliCa StandBy.  が表示されたら FeliCa をかざす。
4. FeliCa Detected.  が表示されたら送信ボタンをクリックする。

---

* 一つのアカウントで複数の FeliCa を登録する事ができます。
* FeliCa 一枚に対して登録できるアカウントは一つまでです。

使い方（ログイン）
-------

1. PaSoRi をパソコンに接続する。
2. ログイン画面を開く。
3. FeliCa StandBy.  が表示されたら FeliCa をかざす。
4. FeliCa Detected.  が表示されたらログインボタンをクリックする。

連絡先
-------

Source Code Repository: [FeliCa Auth - GitHub][felicaauth]

Twitter: [Atrac613][twitter]

[twitter]: http://twitter.com/Atrac613
[openid]: http://wordpress.org/extend/plugins/openid
[felicaauth]: https://github.com/Atrac613/felica-auth
[felicasoftware1]: http://www.sony.co.jp/Products/felica/consumer/download/felicaportsoftware.html
[felicasoftware2]: http://blog.felicalauncher.com/sdk_for_air/?p=2617
[pasori]: http://amzn.to/fSvLeu

