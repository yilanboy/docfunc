{{-- 文章內容 --}}
@extends('layouts.app')

@section('title', $post->title)

@section('description', $post->excerpt)

@section('css')
    <link rel="stylesheet" href="{{ asset('css/content-styles.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('css/prism.css') }}" type="text/css">
@endsection

@section('content')
    {{-- 返回頂部按鈕的 position 改為 absolute 時，上一層樣式需要設定 relative  --}}
    <div class="position-relative">
        {{-- 返回頂部按鈕 --}}
        <button  class="btn btn-danger shadow d-none position-fixed"
        style="bottom: 30px;right: 30px;z-index: 99;"
        onclick="scrollToTop()" id="scroll-to-top-btn" title="Go to top">
            <i class="fas fa-arrow-up"></i> 返回頂部
        </button>

        <div class="container mb-5">
            <div class="row justify-content-md-center">
                <div class="col-12 col-xl-8">

                    {{-- 編輯區塊 --}}
                    @can('update', $post)
                        <div class="d-flex justify-content-end mb-2">
                            <a role="button"  class="btn btn-success shadow me-2"
                            href="{{ route('posts.edit', ['post' => $post->id]) }}">
                                <i class="far fa-edit mr-2"></i> 編輯
                            </a>

                            <form action="{{ route('posts.destroy', ['post' => $post->id]) }}" method="POST"
                            style="display: inline-block;"
                            onsubmit="return confirm('您確定要刪除此文章嗎？')">
                                @csrf
                                @method('DELETE')

                                <button type="submit" class="btn btn-danger shadow">
                                    <i class="far fa-trash-alt mr-2"></i> 刪除
                                </button>
                            </form>
                        </div>
                    @endcan

                    {{-- 文章 --}}
                    <div class="card shadow mb-4">
                        <div class="card-body p-4">

                            <h1 class="text-center mb-2">{{ $post->title }}</h1>

                            <div class="text-center mb-2">
                                <span class="text-secondary">
                                    <i class="far fa-user"></i>
                                    <a class="link-secondary text-decoration-none"
                                    href="{{ route('users.show', ['user' => $post->user->id]) }}">{{ $post->user->name }}</a>
                                    •
                                    <i class="far fa-folder"></i>
                                    <a class="link-secondary text-decoration-none"
                                    href="{{ $post->category->link_with_name }}">{{ $post->category->name }}</a>
                                    •
                                    <i class="far fa-clock"></i>
                                    {{ $post->created_at->diffForHumans() }}
                                    •
                                    <i class="far fa-comment"></i>
                                    {{ $post->reply_count }}
                                </span>
                            </div>

                            <div class="text-center mb-4">
                                {{-- 文章標籤--}}
                                @if ($post->tags()->exists())
                                    <span class="text-primary"><i class="fas fa-tags"></i></span>
                                    @foreach ($post->tags as $tag)
                                        <a role="button" class="btn btn-primary btn-sm rounded-pill py-0 shadow mb-1"
                                        href="{{ route('tags.show', ['tag' => $tag->id])}}">
                                            {{ $tag->name }}
                                        </a>
                                    @endforeach
                                @endif
                            </div>

                            {{-- 文章內容 --}}
                            <div class="ck-content mb-4">
                                {!! $post->body !!}
                            </div>

                            <hr>
                            {{-- 作者 --}}
                            <div class="d-flex justify-content-start align-items-center p-4">
                                <div class="align-self-center me-4">
                                    <img class="rounded-circle" src="{{ $post->user->gravatar() }}" width="60px" height="60px">
                                </div>
                                <div class="d-flex flex-column">
                                    <label class="fs-4">
                                        <a class="link-dark text-decoration-none" href="{{ route('users.show', ['user' => $post->user->id]) }}">
                                            {{ $post->user->name }}
                                        </a>
                                    </label>
                                    <span class="text-dark">{!! nl2br(e($post->user->introduction)) !!}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- 回覆區塊 --}}
                    @livewire('replies', ['post' => $post])
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    {{-- 至頂按鈕 --}}
    <script src="{{ asset('js/scroll-to-top-btn.js') }}"></script>
    {{-- 文章中的嵌入影片顯示 --}}
    <script async charset="utf-8" src="{{ asset('js/platform.js') }}"></script>
    <script src="{{ asset('js/embedly.js') }}"></script>

    {{-- 程式碼區塊高亮 --}}
    <script src="{{ asset('js/prism.js') }}"></script>
@endsection
