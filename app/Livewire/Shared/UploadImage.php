<?php

namespace App\Livewire\Shared;

use App\Services\FileService;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Modelable;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;
use Random\RandomException;

class UploadImage extends Component
{
    use WithFileUploads;

    #[Validate(['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:1024'], message: [
        'image' => '必須是圖片',
        'mimes' => '圖片格式必須是 jpeg, png, jpg',
        'max' => '圖片大小不能超過 1024 KB'
    ])]
    public $image = null;

    #[Modelable]
    public ?string $imageUrl = null;

    /**
     * @throws RandomException
     */
    public function store(): void
    {
        $this->validate();

        if (is_null($this->image)) {
            return;
        }

        $imageName = app(FileService::class)
            ->generateFileName($this->image->getClientOriginalExtension());

        $path = $this->image->storeAs('images', $imageName, config('filesystems.default'));

        $this->imageUrl = Storage::disk()->url($path);
    }

    /**
     * When the image is updated, store the image and set the image url
     *
     * @throws RandomException
     */
    public function updatedImage(): void
    {
        $this->store();
    }

    public function render()
    {
        return view('livewire.shared.upload-image');
    }
}
