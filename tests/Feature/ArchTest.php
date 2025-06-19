<?php

arch()->preset()->laravel();

arch()->preset()->security();

arch()
    ->expect('App\Enums')
    ->toBeEnums();

arch('livewire full-page component must have a  \'Page\' suffix')
    ->expect('App\Livewire\Pages')
    ->toHaveSuffix('Page');

arch('livewire shared component must have a  \'Part\' suffix')
    ->expect('App\Livewire\Shared')
    ->toHaveSuffix('Part');

arch('Application uses strict typing')
    ->expect('App')
    ->toUseStrictTypes();
