<?php

declare(strict_types=1);

namespace App\Http\Controllers\Post;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;
use Yilanboy\Preview\Canvas\Enums\Format;
use Yilanboy\Preview\Canvas\Enums\GradientDirection;
use Yilanboy\Preview\Canvas\Enums\Size;
use Yilanboy\Preview\Canvas\Gradient;
use Yilanboy\Preview\Generator;
use Yilanboy\Preview\Text\Enums\Alignment;
use Yilanboy\Preview\Text\Enums\Font;
use Yilanboy\Preview\Text\Enums\FontSize;
use Yilanboy\Preview\Text\Enums\Position;
use Yilanboy\Preview\Text\TextBlock;

class ShowDefaultPreviewController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, Post $post)
    {
        $format = Format::WEBP;

        $image = new Generator()
            ->size(Size::OpenGraph)
            ->format($format)
            ->background(new Gradient(from: '#00d5be', to: '#00d3f2', direction: GradientDirection::Diagonal))
            ->title(new TextBlock(
                text: $post->title,
                color: 'white',
                fontSize: FontSize::Medium,
                font: Font::NotoSansTCMedium,
                alignment: Alignment::Left,
                position: Position::Center,
            ))
            ->description(new TextBlock(
                text: config('app.name'),
                color: 'white',
                fontSize: FontSize::Small,
                font: Font::JetBrainsMonoMedium,
                alignment: Alignment::Left,
                position: Position::Bottom,
            ))
            ->bytes();

        return response($image, 200, [
            'Content-Type' => $format->mimeType(),
        ]);
    }
}
