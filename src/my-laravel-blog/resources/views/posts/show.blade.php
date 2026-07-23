@extends('layouts.app')
@section('content')
    <div class="container py-4" x-data="{deleteUrl: ''}">

        <h1 class="display-5 fw-bold pt-3"> {{ $post->title }}</h1>
        <div class="d-flex justify-content-between align-items-center mb-1">
            <div class="text-muted fs-6 d-flex flex-column">
                @if (!$post->created_at->equalTo($post->updated_at))
                <p class="mb-1">投稿日: {{ $post->created_at }}</p>
                <p class="mb-0">最終更新：{{$post->updated_at}}</p>
                @else
                <p class="mb-0">投稿日: {{ $post->created_at }}</p>
                @endif
            </div>
            <p class="mb-0">作成者: {{$post->user?->name}}</p>
            <div class="fs-6">

            </div>
            <div class="d-flex justify-content-end align-items-center">
                <a href="/posts" class="btn btn-secondary btn-sm me-1">投稿一覧に戻る</a>

                @can('update', $post)
                <a href="/posts/{{ $post->id }}/edit" class="btn btn-outline-info btn-sm me-1">投稿を編集する</a>
                @endcan
                @can('delete', $post)
                <button type="button" class="btn btn-outline-danger btn-sm"
                    data-bs-toggle="modal"
                    data-bs-target="#deleteModal"
                    @click="deleteUrl = '{{ route('posts.destroy', $post->id) }}'">
                    投稿を削除する
                </button>
                @endcan
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-body my-3 post-content">
                {!! $post->content !!}
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
                    <form id="delete-form" method="POST" :action="deleteUrl">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">削除する</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection