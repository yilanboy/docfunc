<?php

use App\Traits\MarkdownConverter;
use Tests\TestCase;

uses(TestCase::class);

it('can block the header tags', function () {
    $trait = new class
    {
        use MarkdownConverter;
    };

    $body = <<<'MARKDOWN'
    # Header 1
    ## Header 2
    ### Header 3
    #### Header 4
    ##### Header 5
    ###### Header 6
    MARKDOWN;

    $convertedBody = $trait->convertToHtml($body);

    expect($convertedBody)
        ->toContain('<p>Header 1</p>')
        ->not->toContain('<h1>Header 1</h1>')
        ->toContain('<p>Header 2</p>')
        ->not->toContain('<h2>Header 2</h2>')
        ->toContain('<p>Header 3</p>')
        ->not->toContain('<h3>Header 3</h3>')
        ->toContain('<p>Header 4</p>')
        ->not->toContain('<h4>Header 4</h4>')
        ->toContain('<p>Header 5</p>')
        ->not->toContain('<h5>Header 5</h5>')
        ->toContain('<p>Header 6</p>')
        ->not->toContain('<h6>Header 6</h6>');
});

it('can convert the markdown content to html', function () {
    $trait = new class
    {
        use MarkdownConverter;
    };

    $body = <<<'MARKDOWN'
    # Title

    This is a bold **text**

    This a italic *text*

    Show a list

    - item 1
    - item 2
    - item 3
    MARKDOWN;

    $convertedBody = $trait->convertToHtml($body);

    expect($convertedBody)
        ->toContain('<p>Title</p>')
        ->toContain('<p>This is a bold <strong>text</strong></p>')
        ->toContain('<p>This a italic <em>text</em></p>')
        ->toContain('<p>Show a list</p>')
        ->toContain('<ul>')
        ->toContain('<li>item 1</li>')
        ->toContain('<li>item 2</li>')
        ->toContain('<li>item 3</li>')
        ->toContain('</ul>');
});
