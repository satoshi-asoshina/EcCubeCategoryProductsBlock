# カテゴリー別商品一覧ブロック

## 概要
EC-CUBE 4向けのブロックプラグインです。カテゴリー別に商品を表示するブロックを提供します。
ユーザーはカテゴリータグをクリックするだけで、異なるカテゴリーの商品に切り替えて表示できます。

## 機能
- カテゴリータグによる商品表示の動的切り替え（Ajax使用）
- グリッド表示/リスト表示の選択
- 行数・列数のカスタマイズ
- デフォルトカテゴリーの設定
- 商品一覧ページへの「もっと見る」リンク
- 商品詳細ページでの関連商品表示
- カテゴリー一覧ページでの関連カテゴリータグ表示
- トップページへの自動表示（オプション）
- レスポンシブデザイン対応
- カテゴリータグが多い場合のスクロール機能

## インストール方法

### 管理画面からのインストール
1. プラグインのZIPファイルをダウンロード
2. EC-CUBE管理画面の「プラグイン > プラグイン一覧」へアクセス
3. 「プラグインのアップロード」ボタンをクリック
4. ZIPファイルを選択してアップロード
5. プラグインを有効化

### コマンドラインからのインストール
```bash
bin/console eccube:plugin:install --code=CategoryProductsBlock
bin/console eccube:plugin:enable --code=CategoryProductsBlock
bin/console cache:clear



CategoryProductsBlock/
├── Controller/                        # コントローラー
│   ├── Admin/
│   │   └── ConfigController.php      # 管理画面設定コントローラー
│   └── BlockController.php           # フロント表示用ブロックコントローラー
├── Entity/
│   └── Config.php                    # 設定エンティティ
├── EventSubscriber/
│   └── CategoryProductsBlockSubscriber.php  # イベントサブスクライバー
├── Form/
│   └── Type/
│       └── Admin/
│           └── ConfigType.php        # 管理画面設定フォーム
├── Repository/
│   └── ConfigRepository.php          # 設定リポジトリ
├── Resource/
│   ├── assets/                       # アセットファイル
│   │   ├── style.scss                # スタイルシート
│   │   └── category_products_block.js # JavaScript
│   ├── config/
│   │   └── services.yaml             # サービス設定
│   └── template/                     # テンプレート
│       ├── admin/
│       │   ├── config.twig           # 管理画面設定テンプレート
│       │   └── nav.twig              # 管理画面ナビゲーションテンプレート
│       ├── Block/
│       │   ├── category_products.twig      # ブロックメインテンプレート
│       │   └── category_products_items.twig # 商品一覧部分テンプレート
│       ├── category_tags.twig        # カテゴリータグ表示テンプレート
│       └── related_products.twig     # 関連商品表示テンプレート
├── Twig/
│   └── CategoryProductsExtension.php # Twig拡張機能
├── composer.json                     # Composerファイル
├── config.yml                        # プラグイン設定ファイル
├── PluginManager.php                 # プラグイン管理クラス
├── LICENSE                           # ライセンスファイル
└── README.md                         # READMEファイル

BlockController.php を開いてください。
カテゴリID（int型）をもとに Category エンティティを取得してから、
$Category->getSelfAndDescendants() を安全に呼び出し、商品リストを取得するコードを追加したいです。