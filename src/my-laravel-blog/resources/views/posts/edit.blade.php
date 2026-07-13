<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{$post->title}}を編集</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])

</head>
<body>
    <form action="{{ route('posts.update', $post->id) }}"  method="POST">
        @csrf
        @method('PUT')
        <div>
            <label for="title">タイトル</label>
            <input type="text" name="title" id="title" value="{{ $post->title }}" required>
        </div>
        <div>
            <label for="content">内容</label>
            <textarea name="content" id="content" required>{{ $post->content }}</textarea>
        </div>
        <button type="submit">投稿を更新</button>
    </form>
</body>
</html>
