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