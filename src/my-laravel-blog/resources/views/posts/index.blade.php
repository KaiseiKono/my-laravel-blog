<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>投稿一覧</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])

</head>
<body>
    <h1>投稿一覧</h1>

    <a href="/posts/create">投稿作成</a>
    <ul>
        @foreach ($posts as $post)
            <li>
                <div class="post">
                    <a href="/posts/{{$post->id}}">{{$post->title}}</a>
                    <p>{{$post->created_at}}</p>
                </div>
                <form action="{{ route('posts.destroy', $post->id) }}" method='POST' onsubmit="return confirm('本当に削除しますか？')">
                    @csrf
                    @method('DELETE')
                    <button type="submit">投稿を削除する</button>
                </form>
            </li>
        @endforeach
    </ul>
</body>
</html>
