@component('mail::message')
  # 成功建立新的密碼金鑰

  你的帳號已成功建立一個新的密碼金鑰「{{ $passkeyName }}」。

  謝謝,<br>
  {{ config('app.name') }}
@endcomponent
