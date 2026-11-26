<?php

declare(strict_types=1);

namespace App\Providers;

use App\Listeners\DisconnectExpiredDsqlConnection;
use App\Services\Serializer;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Events\ConnectionEstablished;
use Illuminate\Foundation\DevCommands;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Laravel\Head\Enums\OgType;
use Laravel\Head\Enums\TwitterCard;
use Laravel\Head\Facades\Head;
use Laravel\Head\HeadBuilder;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(Serializer::class, function () {
            return Serializer::make();
        });

        DevCommands::except('server');
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::shouldBeStrict();
        Date::use(CarbonImmutable::class);

        Event::listen(ConnectionEstablished::class, [DisconnectExpiredDsqlConnection::class, 'recordConnection']);

        Head::defaults(function (HeadBuilder $head) {
            $head
                ->title(config('app.name'))
                ->description(config('app.name'))
                ->og(type: OgType::Website, image: 'https://blobs.docfunc.com/share.webp', siteName: config('app.name'))
                ->twitter(card: TwitterCard::SummaryWithLargeImage);
        });
    }
}
