<?php

declare(strict_types=1);

use App\Models\User;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('會員中心 - 編輯個人資料')]
class extends Component
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
            'name'         => [
                'required', 'string', 'regex:/^[A-Za-z0-9\-\_]+$/u', 'between:3,25',
                'unique:users,name,'.$this->user->id
            ],
            'introduction' => ['max:120'],
        ];
    }

    protected function messages(): array
    {
        return [
            'name.required'    => '請填寫會員名稱',
            'name.string'      => '會員名稱必須為字串',
            'name.regex'       => '會員名稱只支持英文、數字、橫槓和底線',
            'name.between'     => '會員名稱必須介於 3 - 25 個字元之間。',
            'name.unique'      => '會員名稱已被使用，請重新填寫',
            'introduction.max' => '個人簡介至多 120 個字元',
        ];
    }

    public function update(User $user): void
    {
        $this->authorize('update', $user);

        $this->validate();

        // 更新會員資料
        $user->update([
            'name'         => $this->name,
            'introduction' => $this->introduction,
        ]);

        $this->dispatch('toast', status: 'success', message: '個人資料更新成功');
    }
};
?>

<x-layouts.main>
    <div class="container mx-auto grow">
        <div class="flex flex-col gap-6 justify-center items-start px-4 md:flex-row">
            <x-users.member-center-side-menu />

            <x-card class="flex flex-col gap-6 justify-center w-full md:max-w-2xl">
                <div class="space-y-4">
                    <h1 class="w-full text-2xl text-center dark:text-zinc-50">編輯個人資料</h1>
                    <hr class="h-0.5 border-0 bg-zinc-300 dark:bg-zinc-700">
                </div>

                <div class="flex flex-col gap-4 justify-center items-center">
                    {{-- 大頭貼照片 --}}
                    <img
                        class="rounded-full size-48"
                        src="{{ $user->gravatar_url }}"
                        alt="{{ $name }}"
                    >

                    <div class="flex dark:text-zinc-50">
                        <span class="mr-2">個人圖像由</span>
                        <a
                            class="text-zinc-400 dark:hover:text-zinc-50 hover:text-zinc-700"
                            href="https://zh-tw.gravatar.com/"
                            target="_blank"
                            rel="nofollow noopener noreferrer"
                        >Gravatar</a>
                        <span class="ml-2">提供</span>
                    </div>
                </div>

                {{-- 驗證錯誤訊息 --}}
                <x-auth-validation-errors :errors="$errors" />

                <form
                    class="space-y-6 w-full"
                    wire:submit="update({{ $user->id }})"
                >
                    @php
                        $emailLength = strlen($user->email);
                        $startToMask = round($emailLength / 4);
                        $maskLength = ceil($emailLength / 2);
                    @endphp

                    <x-floating-label-input
                        id="email"
                        type="text"
                        value="{{ str()->mask($user->email, '*', $startToMask, $maskLength) }}"
                        placeholder="信箱"
                        disabled
                    />

                    <x-floating-label-input
                        id="name"
                        type="text"
                        value="{{ old('name', $name) }}"
                        wire:model.blur="name"
                        placeholder="你的名字 (只能使用英文、數字、_ 或是 -)"
                        required
                        autofocus
                    />

                    <x-floating-label-textarea
                        id="introduction"
                        name="introduction"
                        wire:model.blur="introduction"
                        placeholder="介紹一下你自己吧！ (最多 80 個字)"
                        rows="5"
                    >{{ old('introduction', $introduction) }}</x-floating-label-textarea>

                    <div class="flex justify-end items-center">
                        {{-- 儲存按鈕 --}}
                        <x-button>
                            <x-icons.save class="w-5" />
                            <span class="ml-2">儲存</span>
                        </x-button>
                    </div>
                </form>
            </x-card>
        </div>
    </div>
</x-layouts.main>
