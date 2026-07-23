@extends('layouts.app')

@section('content')
    <div class="container" x-data="{deleteUrl: ''}">

        @auth
        <div class="d-flex justify-content-end mb-3">
            <a href="{{ route('posts.create') }}" class="btn btn-primary">
            投稿作成
            </a>
        </div>
        @endauth

        <div class="row row-cols-1 g-3">
            @foreach ($posts as $post)
                <div class="col">
                    <div class="card shadow-sm">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div>
                                <a href="/posts/{{$post->id}}" class="fs-5 text-decoration-none">
                                    {{$post->title}}
                                </a>
                                <p class="text-muted small mb-0">作成日:{{$post->created_at}}/作成者: {{$post->user?->name}}</p>
                            </div>
                            @can('delete', $post)
                            <div>
                                <button type="button" class="btn btn-outline-danger btn-sm"
                                    data-bs-toggle="modal"
                                    data-bs-target="#deleteModal"
                                    @click="deleteUrl = '{{ route('posts.destroy', $post->id) }}'">
                                    投稿を削除する
                                </button>
                            </div>
                            @endcan
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
    </div>
@endsection