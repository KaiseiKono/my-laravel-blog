@extends('layouts.app')

@section('content')
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
                        <label for="title" class="d-block form-label">タイトル</label>
                        <input type="text" name="title" id="title" value="{{ old('title', $post->title) }}" class="d-block form-control @error('title') is-invalid @enderror">
                    </div>
                    @error('title')
                        <div class="alert alert-danger mt-2">{{ $message }}</div>
                    @enderror
                    <div x-data x-init="
                        ClassicEditor.create($refs.editor, {
                            licenseKey: 'GPL',
                            plugins: [
                                CKEditorPlugins.Essentials,
                                CKEditorPlugins.Paragraph,
                                CKEditorPlugins.Bold,
                                CKEditorPlugins.Italic,
                                CKEditorPlugins.Link,
                                CKEditorPlugins.Heading,
                                CKEditorPlugins.SourceEditing,
                            ],
                            toolbar: ['heading', '|', 'bold', 'italic', 'link', '|', 'sourceEditing'],
                        }).catch(error => { console.error(error); });
                        " class="mb-3 @error('content') has-error @enderror">
                        <label for="editor" class="d-block form-label">内容</label>
                        <textarea style="display: none" name="content" class="form-control @error('content') is-invalid @enderror" id="editor" x-ref="editor">{{ old('content', $post->content) }}</textarea>
                    </div>
                    @error('content')
                        <div class="alert alert-danger mt-2">{{ $message }}</div>
                    @enderror
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
        </div>
    </div>
@endsection
