# Docker + Laravel + MySQLで開発を進めるための基礎知識

## 目次

- [1. 全体の流れ（アプリが動く仕組み）](#1-全体の流れアプリが動く仕組み)
- [2. Docker Compose の基礎とコマンド](#2-docker-compose-の基礎とコマンド)
- [3. フォルダ・ファイル構成の意味](#3-フォルダファイル構成の意味)
- [4. 【時系列】開発の進め方](#4-時系列開発の進め方)
- [5. PHPの基礎文法](#5-phpの基礎文法)
- [6. Git の基礎](#6-git-の基礎)
- [7. エラー調査の手順](#7-エラー調査の手順)
- [8. その他知識](#8-その他)

---

## <p id=anchor1> 1. 全体の流れ（アプリが動く仕組み）</p>

ブラウザで`http://127.0.0.1/posts`を開いたとき、裏側では以下の順番で処理が動いている。

```
① ブラウザ
    ↓ リクエスト（「/postsをGETで見せて」）
② nginx（コンテナ）… 窓口。リクエストを受け取り、PHPの処理が必要な部分はphp-fpmに渡す
    ↓
③ php-fpm（コンテナ）… 実際にLaravelのコード（PHP）を実行する
    ↓
④ routes/web.php … 「/postsというURLには、どの処理を割り当てるか」を確認
    ↓
⑤ Controller（コントローラ） … 実際の処理を行う（DBからデータを取ってくる等）
    ↓
⑥ Model（モデル） … DB（mysqlコンテナ）とやり取りする
    ↓
⑦ View（ビュー） … 取得したデータをHTMLに変換する
    ↓
⑧ nginx経由でブラウザにHTMLが返る
```

この「①〜⑧」の順番を頭に入れておくと、エラーが起きたときに「今どの段階で止まっているのか」を切り分けやすくなる。

## <p id=anchor2> 2. Docker Compose の基礎とコマンド </p>

### そもそも何をしているか

1台のPCの中に、複数の「コンテナ」（＝独立した小さなLinux環境）を立てて、それぞれに役割を持たせている。

| サービス名   | 中身        | 役割                                                         |
| ------------ | ----------- | ------------------------------------------------------------ |
| `nginx`      | Webサーバー | ブラウザからのリクエストを受け取る窓口。ポート80を公開       |
| `php`        | PHP-FPM     | PHPのコード（Laravel）を実際に実行する。ポート9000は内部専用 |
| `db`         | MySQL       | データを保存する場所。ポート3306                             |
| `phpmyadmin` | DB管理画面  | ブラウザでDBの中身を見るためのツール。ポート8080             |
| `node`       | Node.js     | フロントエンドのビルド（CSS/JSのコンパイルなど）用           |

コンテナ同士は「サービス名」で通信できる（例：`php`コンテナから見て`db`は`db`という名前でアクセスできる。これが`.env`の`DB_HOST=db`の理由）。

### `docker compose`と`docker-compose`の違い

昔は`docker-compose`（ハイフン、別の独立したコマンド）が使われていたが、今は`docker`コマンドに統合された`docker compose`（スペース区切り）が主流。今回はこちらを使う。挙動はほぼ同じ。

### コマンドとオプションの意味

```powershell
docker compose up -d
```

- `up` … `docker-compose.yml`に書かれた全サービスをまとめて起動する
- `-d` … "detached"（デタッチ、切り離す）の略。バックグラウンドで起動し、ターミナルを占有しない。付けないと、そのターミナルはログ表示専用になり他の操作ができなくなる

```powershell
docker compose down
```

- `down` … 起動中の全コンテナを停止し、さらに**コンテナ自体を削除**する
- ただし`volumes:`に書かれた名前付きボリューム（例：`db_data_test`）は消えない。なので、次回`up`すればDBの中身はそのまま復元される

```powershell
docker compose stop
```

- コンテナを止めるだけで、削除はしない（`down`より少し軽い）。基本的には`down`で問題ない

```powershell
docker compose ps
```

- 今このプロジェクトで起動しているコンテナの一覧と状態を表示（`docker ps`は「このPC全体」のコンテナを表示、`docker compose ps`は「このdocker-compose.ymlのプロジェクト」だけに絞って表示、という違いがある）

```powershell
docker compose logs php
```

- `php`サービスのログ（エラー出力含む）を表示する。ブラウザには出ないエラーの調査に便利
- `-f`オプションを付けると（`docker compose logs -f php`）、リアルタイムで流れ続けるログを見られる（`Ctrl+C`で終了）

```powershell
docker compose build
```

- `Dockerfile`（`./docker/php`など）を書き換えたときに、イメージを作り直すコマンド。中身を変えただけでは反映されないので注意

```powershell
docker exec -it php-fpm bash
```

- `exec` … 起動中のコンテナの中で、追加のコマンドを実行する
- `-it` … `-i`（インタラクティブ、キー入力を受け付ける）＋`-t`（ターミナルらしい表示にする）の組み合わせ。この2つはセットでほぼ必ず一緒に使う
- `php-fpm` … 対象のコンテナ名（`docker-compose.yml`の`container_name`で指定したもの）
- `bash` … コンテナの中で実行するコマンド。「bashという対話シェルを起動して」という意味

### なぜわざわざコンテナの中に入って作業するのか

`php artisan migrate`や`composer install`などのコマンドは、**PHPやLaravelそのものが入っている環境でしか実行できない**。

Windows側のPCには基本的にPHPが直接インストールされていない（このプロジェクトの前提だと）。一方、`php`コンテナの中には、`./docker/php`でビルドされたPHP実行環境が用意されている。なので、

- Windows側 … ファイルを編集する場所（`./src`フォルダ。エディタで開くのはここ）
- コンテナの中 … そのファイルを**実行する**場所
  という役割分担になっている。`volumes: - ./src:/var/www`という設定によって、Windows側の`./src`フォルダとコンテナ内の`/var/www`が「同期」されているので、Windows側でファイルを保存すれば、コンテナ内にもすぐ反映される（逆も同じ）。

## <p id=anchor3> 3. フォルダ・ファイル構成の意味 </p>

Laravelプロジェクト（`./src`以下）の主要フォルダ。

```
src/
├── app/
│   ├── Http/
│   │   └── Controllers/     ← コントローラを置く場所（例：PostController.php）
│   └── Models/               ← モデルを置く場所（例：Post.php）
├── bootstrap/                ← アプリ起動時の初期設定（通常はほぼ触らない）
├── config/                   ← 各種設定ファイル（DB接続方法、タイムゾーン等）
├── database/
│   ├── migrations/           ← テーブル定義（マイグレーション）ファイル
│   └── seeders/              ← テスト用の仮データを投入するためのファイル
├── public/                   ← 外部に公開される唯一のフォルダ。index.phpがここにある
├── resources/
│   └── views/                ← 画面（Bladeテンプレート）を置く場所
├── routes/
│   └── web.php                ← URLと処理の対応（ルーティング）を書く場所
├── storage/                  ← ログファイルやキャッシュなどが自動生成される場所
├── vendor/                   ← Composerでインストールした外部ライブラリ（自動生成、直接編集しない）
├── .env                      ← 環境ごとの設定（DB接続情報など）。Gitには含めない
├── artisan                   ← `php artisan ○○`コマンドの実体（これがある場所がLaravelプロジェクトのルート）
└── composer.json              ← PHPの依存ライブラリ一覧（npmでいうpackage.json）
```

覚えておくべきポイント：

- **`app/Http/Controllers`** … 「リクエストが来たときに何をするか」のロジックを書く
- **`app/Models`** … DBのテーブル1つにつき1つのモデルを作るのが基本
- **`database/migrations`** … Git越しにチーム全員のDB構造を揃えるための「設計図」
- **`resources/views`** … ここに書いたBladeファイルが、そのままブラウザに表示されるHTMLになる
- **`routes/web.php`** … 全ての処理の入り口。ここに書いていないURLは404になる
- **`public/`** … Webサーバー（nginx）が実際に見ているのはこのフォルダだけ。他のフォルダは直接外部からアクセスできない（セキュリティのため）

## <p id=anchor4> 4. 【時系列】開発の進め方 </p>

実際に手を動かす順番に沿って、コードの意味をコメントで説明する。今回の「投稿一覧ページ」実装を例にする。

### Step 0：コンテナを起動する

```powershell
docker compose up -d
```

### Step 1：マイグレーションでテーブルを作る

まずDBの設計を確定させる。コンテナの中に入って作業する。

```powershell
docker exec -it php-fpm bash
```

```bash
# --create=posts を付けると「posts」という名前のテーブルを作る前提のひな形が生成される
php artisan make:migration create_posts_table --create=posts
```

生成されたファイル（`database/migrations/xxxx_create_posts_table.php`）を編集：

```php
<?php

use Illuminate\Database\Migrations\Migration; // マイグレーションの元になるクラスを読み込む
use Illuminate\Database\Schema\Blueprint;      // テーブルの「設計図」を組み立てるためのクラス
use Illuminate\Support\Facades\Schema;          // 実際にテーブルを作成・削除する命令を出すクラス

return new class extends Migration
// extends Migration … 「Migrationクラスの機能を引き継いだ、名前の無いクラスを作る」という意味。
// マイグレーションファイルは1ファイル1クラスなので、あえて名前を付けずこの書き方が標準
{
    /**
     * マイグレーションを実行するときの処理（テーブルを作る側）
     */
    public function up(): void
    // up() … 「これから進める（テーブルを作る）」ときに呼ばれるメソッド。
    // 名前は決まっており、Laravelが `php artisan migrate` 実行時に自動でこの名前を呼び出す
    {
        Schema::create('posts', function (Blueprint $table) {
            // Schema::create('テーブル名', 設計内容) … 実際にCREATE TABLE文を発行する
            // 引数の function ($table) はクロージャ（無名関数）。$table を使って中でカラムを定義していく

            $table->id();
            // id() … 主キーとなる自動採番の整数カラム「id」を追加する（INTEGER, AUTO_INCREMENT, PRIMARY KEY）

            $table->string('title', 100);
            // string('カラム名', 文字数) … VARCHAR型のカラムを追加。第2引数は最大文字数（省略時は255）

            $table->text('content');
            // text('カラム名') … 長文向けのTEXT型カラムを追加（文字数制限なし）

            $table->timestamps();
            // timestamps() … created_at と updated_at という2つの日時カラムをまとめて追加する便利メソッド
            // レコードの作成時・更新時に自動で値がセットされる
        });
    }

    /**
     * マイグレーションを取り消すときの処理（テーブルを消す側）
     */
    public function down(): void
    // down() … `php artisan migrate:rollback` を実行したときに呼ばれる「元に戻す」処理
    {
        Schema::dropIfExists('posts');
        // dropIfExists('テーブル名') … そのテーブルが存在すれば削除する（存在しなくてもエラーにならない）
    }
};
```

実行：

```bash
php artisan migrate
```

### Step 2：モデルを作る

```bash
php artisan make:model Post
```

```php
<?php

namespace App\Models;
// namespace … このファイルが「どのフォルダ配下に属するか」をPHPに伝える宣言。
// フォルダ構成とnamespaceは一致させるのがLaravelのルール（app/Models/Post.php → App\Models）

use Illuminate\Database\Eloquent\Model;
// use … 他の場所（別のnamespace）にあるクラスを、このファイル内で「省略した名前」で使えるようにする宣言。
// これが無いと `Illuminate\Database\Eloquent\Model` と毎回フルパスで書く必要がある

class Post extends Model
// extends Model … LaravelのEloquent ORM（DB操作を簡単にする仕組み）の基本機能を、
// このPostクラスに全部引き継ぐ、という意味。これにより Post::all() などのメソッドが自動で使えるようになる
{
    // 何も書かなくても、Laravelは「Postモデル」→「postsテーブル」と自動で対応づける
    // （クラス名を複数形の小文字にしたものがテーブル名、という命名規則があるため）
}
```

### Step 3：ルーティングを書く

`routes/web.php`

```php
<?php

use Illuminate\Support\Facades\Route;
// use Illuminate\Support\Facades\Route;
// 「Route」という名前のクラス（正確には Facade と呼ばれる仕組み）をこのファイルで使えるようにする宣言。
// これを書かずに Route::get() と書くと「Routeなんてクラス知りません」というエラーになる

use App\Http\Controllers\PostController;
// 同様に、自作した PostController クラスをこのファイルで使えるようにする宣言

Route::get('/posts', [PostController::class, 'index']);
// Route::get(URL, 処理内容)
//   ・第1引数 '/posts' … このURLにGETでアクセスされたときに反応する
//   ・第2引数 [PostController::class, 'index']
//       → 「PostControllerクラスのindexというメソッドを呼び出してください」という意味の書き方
//       → PostController::class は「そのクラスの正式な名前（namespace込みのフルパス）」を
//         文字列として取得する書き方。'App\Http\Controllers\PostController' と書くのと同じだが、
//         タイプミスをコンパイル時に検出できるので ::class を使うのが推奨されている
```

### Step 4：コントローラを書く

```bash
php artisan make:controller PostController
```

```php
<?php

namespace App\Http\Controllers;

use App\Models\Post;
// 先ほど作った Post モデルを使えるようにする

class PostController extends Controller
// extends Controller … Laravelの基本コントローラ機能（バリデーションのヘルパー等）を引き継ぐ
{
    public function index()
    // public … 「このメソッドはクラスの外からでも呼び出せる」という公開範囲の指定。
    //           ルーティングから呼び出す必要があるメソッドは基本的にpublicにする
    {
        $posts = Post::all();
        // Post::all() … postsテーブルの全レコードを取得する。
        // 結果は「Collection」という、配列を拡張した特殊な型で返ってくる（foreachでそのまま回せる）

        return view('posts.index', ['posts' => $posts]);
        // view('ビュー名', 渡すデータの配列)
        //   ・'posts.index' … resources/views/posts/index.blade.php を指す
        //     （ドット区切り＝フォルダの階層区切り）
        //   ・['posts' => $posts] … 連想配列。キー 'posts' が、ビュー側で使える変数名 $posts になる
        //     つまりビュー側では「$posts」という名前でこのデータにアクセスできる
    }
}
```

### Step 5：ビューを書く

`resources/views/posts/index.blade.php`

```blade
<h1>投稿一覧</h1>

<a href="/posts/create">投稿作成</a>

<ul>
    @foreach ($posts as $post)
        {{-- @foreach … PHPのforeach文をBlade記法で書いたもの。$postsの中身を1件ずつ$postに入れて繰り返す --}}
        <li>
            {{ $post->title }}（{{ $post->created_at }}）
            {{-- {{ }} … 変数の中身をHTMLとして安全に出力する記法（自動でエスケープされ、XSS対策になる） --}}
            {{-- $post->title … オブジェクトのプロパティにアクセスする書き方。矢印(->)を使う --}}
        </li>
    @endforeach
    {{-- @endforeach … @foreachの終わりを示す。閉じ忘れるとエラーになる --}}
</ul>
```

### Step 6：動作確認

```
http://127.0.0.1/posts
```

エラーが出たら「7. エラー調査の手順」を参照。

### Step 7：Gitに記録する

```powershell
git add .
git status
git commit -m "投稿一覧ページを実装"
git push
```

## <p id=anchor5> 5. PHPの基礎文法 </p>

```php
<?php
// 変数：先頭に $ を付ける。型宣言は不要（動的型付け）
$name = "田中";

// 配列
$fruits = ['りんご', 'バナナ'];

// 連想配列（キーと値のペア）
$user = ['name' => '田中', 'age' => 20];

// 関数
function greet($name) {
    return "こんにちは、{$name}さん"; // 文字列内で {$変数名} と書くと変数を埋め込める
}

// クラス
class Post {
    public $title; // プロパティ（そのクラスが持つデータ）

    public function __construct($title) {
        // __construct … クラスからインスタンス（実体）を作るときに自動で呼ばれる特殊なメソッド
        $this->title = $title;
        // $this … 「今操作しているこのインスタンス自身」を指すキーワード
    }
}

$post = new Post("初めての投稿");
// new … クラスからインスタンスを1つ作る、という意味

// 条件分岐
if ($age >= 18) {
    echo "成人";
} elseif ($age >= 13) {
    echo "未成年（中高生）";
} else {
    echo "子供";
}

// 繰り返し
foreach ($fruits as $fruit) {
    echo $fruit;
}

// null合体演算子（値が無ければデフォルト値を使う、よく使う書き方）
$title = $post->title ?? '無題';
```

記号の意味まとめ：

| 記号        | 意味                                                               | 例                         |
| ----------- | ------------------------------------------------------------------ | -------------------------- |
| `->`        | インスタンス（オブジェクト）のプロパティ・メソッドにアクセス       | `$post->title`             |
| `::`        | クラス自体（インスタンス化せずに）に直接アクセス（静的メソッド等） | `Post::all()`              |
| `$this`     | 今のインスタンス自身を指す                                         | `$this->title`             |
| `use`       | 別の場所にあるクラスや機能を、このファイルで使えるようにする宣言   | `use App\Models\Post;`     |
| `namespace` | このファイルの「住所」を宣言する（フォルダ構成と対応）             | `namespace App\Models;`    |
| `extends`   | 親クラスの機能を全部引き継いで、新しいクラスを作る（継承）         | `class Post extends Model` |

## <p id=anchor6> 6. Git の基礎 </p>

| コマンド                      | 意味                                             |
| ----------------------------- | ------------------------------------------------ |
| `git status`                  | 変更されたファイルの一覧を確認                   |
| `git add .`                   | 変更を全てステージ（コミット候補）に上げる       |
| `git add ファイル名`          | 特定のファイルだけステージに上げる               |
| `git commit -m "メッセージ"`  | ステージした変更を1つの記録として保存            |
| `git push`                    | ローカルの記録をGitHub等のリモートに反映         |
| `git pull`                    | リモートの変更を取り込む                         |
| `git log --oneline`           | コミット履歴を簡潔に一覧表示                     |
| `git checkout -b feature/xxx` | 新しいブランチを作って、そのブランチに切り替える |
| `git diff`                    | まだステージしていない変更内容を確認             |

**注意点（経験済みのハマりどころ）**

- `.gitignore`は「リポジトリのルートから見た相対パス」で書く（`../`のような親ディレクトリ指定は効かない）
- すでに`git add`済みのファイルを`.gitignore`に追加しても効かない。その場合は`git rm -r --cached ファイルパス`で追跡を解除してからコミットする
- `phpmyadmin/sessions/`のような自動生成ファイルはコミット対象から外す

## <p id=anchor7> 7. エラー調査の手順 </p>

1. **エラーメッセージを最後まで読む**：一番上に「何が」「なぜ」起きたかが書いてある（例：`Table 'posts' doesn't exist`）
2. **画面の案内（緑や青のボックス）を確認**：解決方法のヒントが書かれていることが多い
3. **エラーが起きたファイル・行数を確認**：赤くハイライトされた`app/Http/Controllers/PostController.php:12`のような表示で特定する
4. **`docker compose logs php`でログを確認**：ブラウザに出ないエラーもここに出ることがある
5. **`.env`の設定ミスを疑う**：DB接続エラーの多くは`.env`の`DB_HOST`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`のズレが原因

## <p id=anchor8> 8. その他</p>

現時点の「投稿一覧のみ」の実装では出てこないが、機能を増やしていく中で必ず出会うもの。

### フォームとPOSTリクエスト

Bladeでフォームを作るとき、Laravelでは「CSRFトークン」という不正リクエスト防止の仕組みを必ず入れる必要がある。

```blade
<form method="POST" action="/posts/create">
    @csrf
    {{-- @csrf … 不正な第三者からのフォーム送信（CSRF攻撃）を防ぐための、見えないトークンを埋め込む --}}
    <input type="text" name="title">
    <button type="submit">保存</button>
</form>
```

### バリデーション（入力チェック）

```php
public function store(Request $request)
{
    $validated = $request->validate([
        'title' => 'required|max:100', // required=必須, max:100=100文字以内
        'content' => 'required',
    ]);
    // バリデーションに失敗すると自動的に元のページに戻り、エラーメッセージがセットされる

    Post::create($validated);

    return redirect('/posts');
    // redirect() … 別のURLに転送する。作成・更新・削除の後は一覧ページに戻すのが定石
}
```

### Eloquentのリレーション（テーブル同士の関連付け）

例えば将来「ユーザー」と「投稿」を関連付ける場合。

```php
class Post extends Model
{
    public function user()
    {
        return $this->belongsTo(User::class);
        // belongsTo … 「このPostは、1人のUserに属している」という関係を定義（外部キーは posts.user_id）
    }
}

class User extends Model
{
    public function posts()
    {
        return $this->hasMany(Post::class);
        // hasMany … 「このUserは、複数のPostを持っている」という関係を定義
    }
}

// 使うとき
$post->user;      // その投稿を書いたユーザー情報を取得
$user->posts;      // そのユーザーが書いた投稿一覧を取得
```

### 認証機能（ログイン）

Laravelには`Laravel Breeze`や`Laravel Fortify`といった追加パッケージで、ログイン機能を素早く導入できる仕組みがある。コメントアウトされている「ユーザーアカウント機能」を実装する際に必要になる。

### ミドルウェア

「コントローラに処理が届く前に割り込んで、共通のチェックを行う仕組み」。例えば「ログインしていないユーザーは弾く」という処理。

```php
Route::get('/posts/create', [PostController::class, 'create'])->middleware('auth');
// middleware('auth') … 未ログインユーザーがこのURLにアクセスしたら、ログインページに転送される
```

### Route::resource（ルート定義の省略記法）

今は1本ずつ`Route::get`/`Route::post`を書いているが、CRUDの基本7ルート（index, create, store, show, edit, update, destroy）を使う場合はこう1行で書ける。

```php
Route::resource('posts', PostController::class);
// これだけで、index/create/store/show/edit/update/destroy 用のルートが自動的にまとめて登録される
```

### シーダー（テスト用データの自動投入）

```bash
php artisan make:seeder PostSeeder
```

開発中に毎回手入力でデータを作らなくても、コマンド1つでダミーデータを投入できるようにする仕組み。

### API化（Reactやスマホアプリと連携する場合）

画面をBladeで作るのではなく、JSON形式でデータをやり取りする「API」を作りたくなった場合は`routes/api.php`を使い、コントローラは`return response()->json($posts);`のような形でデータを返す。前に構想していたWeBWorK用GUIアプリ（React連携）を作る際はこの形になる。

### テスト（自動テスト）

```bash
php artisan make:test PostTest
```

「コードを変更したときに、既存の機能が壊れていないか」を自動でチェックする仕組み。開発規模が大きくなってきたら導入を検討する。
