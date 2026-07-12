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