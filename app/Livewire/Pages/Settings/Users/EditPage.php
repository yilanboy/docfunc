<?php

namespace App\Livewire\Pages\Settings\Users;

use App\Models\User;
use Illuminate\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;

class EditPage extends Component
{
    public string $name;

    public ?string $introduction;

    public User $user;

    public function mount(int $id): void
    {
        $this->user = User::findOrFail($id);

        // 會員只能進入自己的頁面，規則寫在 UserPolicy
        $this->authorize('update', $this->user);

        $this->name = $this->user->name;
        $this->introduction = $this->user->introduction;
    }

    protected function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'regex:/^[A-Za-z0-9\-\_]+$/u',
                'between:3,25',
                'unique:users,name,'.$this->user->id,
            ],
            'introduction' => ['max:120'],
        ];
    }

    protected function messages(): array
    {
        return [
            'name.required' => '請填寫會員名稱',
            'name.string' => '會員名稱必須為字串',
            'name.regex' => '會員名稱只支持英文、數字、橫槓和底線',
            'name.between' => '會員名稱必須介於 3 - 25 個字元之間。',
            'name.unique' => '會員名稱已被使用，請重新填寫',
            'introduction.max' => '個人簡介至多 120 個字元',
        ];
    }

    public function update(User $user): void
    {
        $this->authorize('update', $user);

        $this->validate();

        // 更新會員資料
        $user->update([
            'name' => $this->name,
            'introduction' => $this->introduction,
        ]);

        $this->dispatch('toast', status: 'success', message: '個人資料更新成功');
    }

    #[Title('會員中心 - 編輯個人資料')]
    public function render(): View
    {
        return view('livewire.pages.settings.users.edit-page');
    }
}
