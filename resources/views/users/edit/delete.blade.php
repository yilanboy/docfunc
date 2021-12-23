@extends('users.edit.index')

@section('title', '會員中心-刪除帳號')

@section('users.content')
  {{-- 說明 --}}
  <div class="flex flex-col items-start justify-center mb-4">
    <span class="dark:text-gray-50">很遺憾您要離開...</span>
    <span class="dark:text-gray-50">如果您確定要刪除帳號，請點選下方的按鈕並收取信件</span>
    <span class="mt-4 text-red-400">請注意！您撰寫的文章與留言都會一起刪除，而且無法恢復</span>
  </div>

  {{-- Session 狀態訊息 --}}
  <x-auth-session-status class="mb-4" :status="session('status')" />

  <form
    method="POST"
    action="{{ route('users.sendDestroyEmail', ['user' => $user->id]) }}"
    onSubmit="return confirm('您確定要寄出刪除帳號信件嗎？');"
    class="w-full"
  >
    @csrf

    <div class="flex items-center justify-end mt-4">
      {{-- 寄出刪除帳號信件 --}}
      <button
        type="submit"
        class="inline-flex items-center justify-center px-4 py-2 font-semibold tracking-widest uppercase transition duration-150 ease-in-out bg-red-600 border border-transparent rounded-md text-gray-50 hover:bg-red-700 active:bg-red-900 focus:outline-none focus:border-red-900 focus:ring ring-red-300 disabled:opacity-25"
      >
        <i class="bi bi-exclamation-triangle-fill"></i><span class="ml-2">寄出刪除帳號信件</span>
      </button>
    </div>
  </form>
@endsection
