<?php

test('background component renders without framework and strict types tags', function () {
    $this->blade('<x-layouts.background />')
        ->assertSee('hero')
        ->assertDontSee('Laravel')
        ->assertDontSee('Livewire')
        ->assertDontSee('declare(strict_types=1)');
});

test('home page does not render background framework tags', function () {
    $this->get('/')
        ->assertOk()
        ->assertDontSee('Laravel 13')
        ->assertDontSee('Livewire 4')
        ->assertDontSee('declare(strict_types=1)');
});
