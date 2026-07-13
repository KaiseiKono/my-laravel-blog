<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>投稿作成</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])

</head>
<body>
    <form action="{{ route('posts.store') }}" method="POST">
        @csrf
        <div>
            <label for="title">タイトル</label>
            <input type="text" name="title" id="title" required>
        </div>
        <div>
            <label for="content">内容</label>
            <textarea name="content" id="content" required></textarea>
        </div>
        <button type="submit">投稿を作成</button>
    </form>
    <a href="{{route('posts.index')}}" onclick="return confirm('編集中の内容は保存されません。投稿一覧に戻りますか？')">投稿一覧に戻る</a>
</body>
</html>
