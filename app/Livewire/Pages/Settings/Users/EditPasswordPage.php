<?php

namespace App\Livewire\Pages\Settings\Users;

use App\Models\User;
use App\Rules\MatchOldPassword;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;

class EditPasswordPage extends Component
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
            'current_password' => ['required', new MatchOldPassword],
            'new_password' => ['required', 'confirmed', $passwordRule],
        ];
    }

    protected function messages(): array
    {
        return [
            'current_password.required' => '請輸入現在的密碼',
            'new_password.required' => '請輸入新密碼',
            'new_password.confirmed' => '新密碼與確認新密碼不符合',
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

    #[Title('會員中心 - 更改密碼')]
    public function render(): View
    {
        return view('livewire.pages.settings.users.edit-password-page');
    }
}
