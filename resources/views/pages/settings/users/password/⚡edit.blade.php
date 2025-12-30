<?php

declare(strict_types=1);

use App\Models\User;
use App\Rules\MatchOldPassword;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('會員中心 - 更改密碼')]
class extends Component
{
    public User $user;

    public string $current_password = '';

    public string $new_password = '';

    public string $new_password_confirmation = '';

    public function mount(int $id): void
    {
        $this->user = User::findOrFail($id);

        $this->authorize('update', $this->user);
    }

    protected function rules(): array
    {
        $passwordRule = Password::min(8)->letters()->mixedCase()->numbers();

        return [
            'current_password' => ['required', new MatchOldPassword()],
            'new_password'     => ['required', 'confirmed', $passwordRule],
        ];
    }

    protected function messages(): array
    {
        return [
            'current_password.required' => '請輸入現在的密碼',
            'new_password.required'     => '請輸入新密碼',
            'new_password.confirmed'    => '新密碼與確認新密碼不符合',
        ];
    }

    public function update(User $user): void
    {
        $this->authorize('update', $user);

        $this->validate();

        $user->update(['password' => $this->new_password]);

        $this->dispatch('toast', status: 'success', message: '密碼更新成功！');

        $this->reset(['current_password', 'new_password', 'new_password_confirmation']);
    }
};
?>

<x-layouts.main>
    <div class="container mx-auto grow">
        <div class="flex flex-col gap-6 justify-center items-start px-4 md:flex-row">
            <x-users.member-center-side-menu />

            <x-card class="flex flex-col gap-6 justify-center w-full md:max-w-2xl">
                <div class="space-y-4">
                    <h1 class="w-full text-2xl text-center dark:text-zinc-50">修改密碼</h1>
                    <hr class="h-0.5 border-0 bg-zinc-300 dark:bg-zinc-700">
                </div>

                {{-- 驗證錯誤訊息 --}}
                <x-auth-validation-errors :errors="$errors" />

                <form
                    class="space-y-6 w-full"
                    wire:submit="update({{ $user->id }})"
                >
                    {{-- 舊密碼 --}}
                    <x-floating-label-input
                        id="current_password"
                        type="password"
                        placeholder="舊密碼"
                        wire:model="current_password"
                        required
                    />

                    {{-- 新密碼 --}}
                    <x-floating-label-input
                        id="new_password"
                        type="password"
                        placeholder="新密碼"
                        wire:model="new_password"
                        required
                    />

                    {{-- 確認新密碼 --}}
                    <x-floating-label-input
                        id="new_password_confirmation"
                        type="password"
                        placeholder="確認新密碼"
                        wire:model="new_password_confirmation"
                        required
                    />

                    <div class="flex justify-end items-center">
                        {{-- 儲存按鈕 --}}
                        <x-button>
                            <x-icons.save class="w-5" />
                            <span class="ml-2">修改密碼</span>
                        </x-button>
                    </div>
                </form>
            </x-card>
        </div>
    </div>
</x-layouts.main>
