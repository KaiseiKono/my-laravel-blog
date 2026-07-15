<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{$post->title}}を編集</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    @vite(['resources/css/app.css', 'resources/js/app.js'])

</head>
<body class="bg-light">
    <!-- ヘッダー -->
    <nav class="navbar navbar-dark bg-dark mb-4">
        <div class="container">
            <span class="navbar-brand mb-0 h1">投稿編集</span>
        </div>
    </nav>

    <div class="container">
        <button type="button" class="btn btn-outline-secondary mb-3" data-bs-toggle="modal" data-bs-target="#backModal">
                投稿一覧に戻る
            </button>

        <form action="{{ route('posts.update', $post->id) }}"  method="POST">
            @csrf
            @method('PUT')
            <div class="card shadow-sm">
                <div class="card-body">
                    <div>
                        <label for="title" required class="d-block form-label">タイトル</label>
                        <input type="text" name="title" id="title" value="{{ $post->title }}" required class="d-block form-control">
                    </div>
                    <div>
                        <label for="content" class="d-block form-label" required>内容</label>
                        <textarea name="content" id="content" required class="d-block form-control">{{ $post->content }}</textarea>
                    </div>
                </div>
            </div>
            <div class="d-flex justify-content-end">
                <button type="submit" class="mt-2 btn btn-primary">投稿を更新</button>
            </div>
        </form>
    </div>

    <!-- 戻る確認モーダル -->
    <div class="modal fade" id="backModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">確認</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                編集中の内容は保存されません。<br>
                投稿一覧に戻りますか？
            </div>

            <div class="modal-footer">
                <button
                    type="button"
                    class="btn btn-secondary"
                    data-bs-dismiss="modal">
                    キャンセル
                </button>

                <a href="{{ route('posts.index') }}" class="btn btn-primary">
                    戻る
                </a>
            </div>

        </div>
</body>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>

</html>
