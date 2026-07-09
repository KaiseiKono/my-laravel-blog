<h1>{{ $post->title }}</h1>
<p>{{ $post->content }}</p>
<p>投稿日: {{ $post->created_at }}</p>

<a href="/posts/{{ $post->id }}/edit">投稿を編集する</a>
<a href="/posts/{{ $post->id }}" onclick="return confirm('本当に削除しますか？')" method="DELETE">投稿を削除する</a>
<a href="/posts">投稿一覧に戻る</a>