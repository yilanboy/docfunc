<?php

declare(strict_types=1);

namespace App\Traits;

use Illuminate\Support\Facades\Log;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Event\DocumentParsedEvent;
use League\CommonMark\Exception\CommonMarkException;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\CommonMark\Node\Block\Heading;
use League\CommonMark\Extension\ExternalLink\ExternalLinkExtension;
use League\CommonMark\MarkdownConverter as CommonMarkdownConverter;
use League\CommonMark\Node\Block\Paragraph;

trait MarkdownConverter
{
    protected static ?CommonMarkdownConverter $staticConverter = null;

    public function convertToHtml(string $body): string
    {
        if (static::$staticConverter === null) {
            $config = [
                'html_input'         => 'strip',
                'allow_unsafe_links' => false,
                'max_nesting_level'  => 10,
                'external_link'      => [
                    'internal_hosts'     => parse_url(config('app.url'), PHP_URL_HOST),
                    'open_in_new_window' => true,
                    'nofollow'           => 'external',
                    'noopener'           => 'external',
                    'noreferrer'         => 'external',
                ],
            ];

            $environment = new Environment($config);
            $environment->addExtension(new CommonMarkCoreExtension);
            $environment->addExtension(new ExternalLinkExtension);
            // replace heading to paragraph
            $environment->addEventListener(DocumentParsedEvent::class, function (DocumentParsedEvent $event) {
                $walker = $event->getDocument()->walker();

                while ($walkerEvent = $walker->next()) {
                    $node = $walkerEvent->getNode();

                    if ($node instanceof Heading) {
                        $paragraph = new Paragraph;

                        foreach ($node->children() as $child) {
                            $paragraph->appendChild($child);
                        }

                        $node->replaceWith($paragraph);
                    }
                }
            });

            static::$staticConverter = new CommonMarkdownConverter($environment);
        }

        try {
            return static::$staticConverter->convert($body)->getContent();
        } catch (CommonMarkException $e) {
            Log::error($e->getMessage());

            return '<p class="text-red-400">Oops！Markdown 轉換錯誤</p>';
        }
    }
}
