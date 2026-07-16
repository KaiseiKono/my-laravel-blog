<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>投稿一覧</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-light"  x-data="{deleteUrl: ''}">

    <!-- ヘッダー -->
    <nav class="navbar navbar-dark bg-dark mb-4">
        <div class="container">
            <span class="navbar-brand mb-0 h1">投稿一覧</span>
        </div>
    </nav>

    <div class="container">
        <div class="d-flex justify-content-end mb-3">
            <a href="/posts/create" class="btn btn-primary">
                投稿作成
            </a>
        </div>

        <div class="row row-cols-1 g-3">
            @foreach ($posts as $post)
                <div class="col">
                    <div class="card shadow-sm">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div>
                                <a href="/posts/{{$post->id}}" class="fs-5 text-decoration-none">
                                    {{$post->title}}
                                </a>
                                <p class="text-muted small mb-0">作成日:{{$post->created_at}}</p>
                            </div>
                            <div>
                                <button type="button" class="btn btn-outline-danger btn-sm"
                                    data-bs-toggle="modal"
                                    data-bs-target="#deleteModal"
                                    @click="deleteUrl = '{{ route('posts.destroy', $post->id) }}'">
                                    投稿を削除する
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        @if ($posts->isEmpty())
            <div class="text-center text-muted py-5">
                まだ投稿がありません
            </div>
        @endif

    </div>

    <!-- 削除確認モーダル -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">削除の確認</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    本当に削除しますか?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">キャンセル</button>
                    <form id="delete-form" method="POST" :action="deleteUrl">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">削除する</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>

</html>