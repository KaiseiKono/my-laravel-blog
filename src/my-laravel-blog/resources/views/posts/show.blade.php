
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{$post->title}}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <h1>{{ $post->title }}</h1>
    <p>{{ $post->content }}</p>
    <p>投稿日: {{ $post->created_at }}</p>

    @if (!$post->created_at->equalTo($post->updated_at))
    <p>最終更新：{{$post->updated_at}}</p>
    @endif

    <a href="/posts/{{ $post->id }}/edit">投稿を編集する</a>

    <form action="{{ route('posts.destroy', $post->id) }}" method='POST' onsubmit="return confirm('本当に削除しますか？')">
        @csrf
        @method('DELETE')
        <button type="submit">投稿を削除する</button>
    </form>

    <a href="/posts">投稿一覧に戻る</a>
</body>
</html>
