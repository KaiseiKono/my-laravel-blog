
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{$post->title}}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-light" x-data="{deleteUrl: ''}">
    <div class="container py-4">
        <h1 class="display-5 fw-bold pt-3">{{ $post->title }}</h1>

        <div class="d-flex justify-content-between align-items-center mb-1">
            <div class="text-muted fs-6">
                @if (!$post->created_at->equalTo($post->updated_at))
                <p class="mb-1">投稿日: {{ $post->created_at }}</p>
                <p class="mb-0">最終更新：{{$post->updated_at}}</p>
                @else
                <p class="mb-0">投稿日: {{ $post->created_at }}</p>
                @endif
            </div>
            <div class="d-flex justify-content-end align-items-center">
                <a href="/posts" class="btn btn-secondary btn-sm me-1">投稿一覧に戻る</a>
                <a href="/posts/{{ $post->id }}/edit" class="btn btn-outline-info btn-sm me-1">投稿を編集する</a>
                <button type="button" class="btn btn-outline-danger btn-sm"
                                data-bs-toggle="modal"
                                data-bs-target="#deleteModal"
                                @click="deleteUrl = '{{ route('posts.destroy', $post->id) }}'">
                                投稿を削除する
                            </button>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-body">
                <p class="fs-3 my-3">{{ $post->content }}</p>
            </div>
        </div>

    </div>
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
                    <form id="delete-form" method="POST" action="">
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
