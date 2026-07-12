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