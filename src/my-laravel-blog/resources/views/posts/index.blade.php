<h1>投稿一覧</h1>

<a href="/posts/create">投稿作成</a>
<ul>
    @foreach ($posts as $post)
        <li>
            <div class="post">
                <a href="/posts/{{$post->id}}">{{$post->title}}</a>
                <p>{{$post->created_at}}</p>
            </div>
        </li>
    @endforeach
</ul>