FeliCa Auth
=============

FeliCa Auth は WordPress の認証機構を FeliCa で行うプラグインです。

このプログラムのほとんどは別の WordPress のプラグインである [OpenID][openid] を参考にして作られています。

主に実験用のプログラムなのでご利用される場合は各自ご注意ください。

著作権など問題がありましたらご指摘くださいませ。

必要なもの
-------

FeliCa Auth は [PaSoRi][pasori]  を利用して FeliCa の読み取りを行っています。

プラグインご利用の方は PaSoRi をご用意ください。

また、FeliCa を接続するパソコンでは下記ソフトウェアをインストールしてください。

* [PC - FeliCa ポートソフトウェア][felicasoftware1]
* [mac - FeliCa Proxy][felicasoftware2]

使い方（FeliCa の登録）
-------

1. PaSoRi をパソコンに接続する。
2. 通常のパスワードでログインする。
3. サイドメニューのユーザーから Your FeliCa をクリックする。
4. FeliCa StandBy.  が表示されたら FeliCa をかざす。
5. FeliCa Detected.  が表示されたら送信ボタンをクリックする。

---

* 一つのアカウントで複数の FeliCa を登録する事ができます。
* FeliCa 一枚に対して登録できるアカウントは一つまでです。

使い方（ログイン）
-------

1. PaSoRi をパソコンに接続する。
2. ログイン画面を開く。
3. FeliCa StandBy.  が表示されたら FeliCa をかざす。
4. FeliCa Detected.  が表示されたらログインボタンをクリックする。

技術的な話
-------

Q: FeliCa 識別情報はそのままサーバーに送信されるの？

A: いいえ。プラグインで利用する FeliCa 識別情報 imm、pmm はハッシュ化されサーバーに送信されます。またハッシュを生成する際には各ブログ毎に違うハッシュが生成されますので複数のブログ間で同じハッシュ情報ではログイン（二次利用は）できません。

また、ハッシュの生成はローカル（SWF）で行われます。

連絡先 & リポジトリ
-------

Source Code Repository: [FeliCa Auth - GitHub][felicaauth]

Source Code Repository: [FeliCa Auth SWF - GitHub][felicaauthswf]

Twitter: [Atrac613][twitter]

[twitter]: http://twitter.com/Atrac613
[openid]: http://wordpress.org/extend/plugins/openid
[felicaauth]: https://github.com/Atrac613/felica-auth
[felicaauthswf]: https://github.com/Atrac613/felica-auth-swf
[felicasoftware1]: http://www.sony.co.jp/Products/felica/consumer/download/felicaportsoftware.html
[felicasoftware2]: http://blog.felicalauncher.com/sdk_for_air/?p=2617
[pasori]: http://amzn.to/fSvLeu

