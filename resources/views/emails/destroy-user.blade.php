<x-mail::message>
    # 帳號刪除確認

    如果您確定要刪除帳號，請點選下方的按鈕連結 (連結將在 5 分鐘後失效)。

    <x-mail::button
        :url="$destroyLink"
        color="error"
    >
        確認刪除帳號
    </x-mail::button>

    謝謝,<br>
    {{ config('app.name') }}
</x-mail::message>
