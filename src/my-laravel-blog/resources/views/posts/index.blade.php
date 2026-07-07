<h1>投稿一覧</h1>

<ul>
    @foreach ($posts as $post)
        <li>
            {{$post->title}} ({{$post->created_at}})
        </li>
    @endforeach
</ul>