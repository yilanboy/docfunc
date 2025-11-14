<?php

declare(strict_types=1);

namespace App\Services;

use Dom\HTMLDocument;
use Symfony\Component\HtmlSanitizer\HtmlSanitizer;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;

class ContentService
{
    /**
     * 生成用來優化 SEO 的 slug
     *
     * @param  string  $title  標題
     */
    public static function getSlug(string $title): string
    {
        // 去掉特殊字元，只留中文與英文
        $title = preg_replace('/[^A-Za-z0-9 \p{Han}]+/u', '', $title);
        // 將空白替換成 '-'
        $title = preg_replace('/\s+/u', '-', $title);
        // 英文全部改為小寫
        $title = strtolower($title);

        // 後面加個 '-post' 是為了避免 slug 只有 'edit' 時，會與編輯頁面的路由發生衝突
        return $title.'-post';
    }

    /**
     * 過濾 html 格式的文章內容，避免 XSS 攻擊
     */
    public static function getPurifiedBody(string $html): string
    {
        $htmlSanitizer = new HtmlSanitizer(
            new HtmlSanitizerConfig()
                ->allowSafeElements()
                ->allowAttribute('data-language', 'pre')
                ->allowAttribute('class', ['span', 'code', 'figure'])
                ->allowAttribute('style', ['p', 'figure'])
                ->forceAttribute('a', 'rel', 'noopener noreferrer')
                ->forceAttribute('a', 'target', '_blank')
                ->allowElement('oembed', ['url', 'class'])
                ->withMaxInputLength(-1)
        );

        return $htmlSanitizer->sanitize($html);
    }

    /**
     * 生成文章內容的摘錄
     */
    public static function getExcerpt(string $body, int $length = 200): string
    {
        return (string) str(strip_tags($body))->limit($length);
    }

    /**
     * 取得文章中的圖片連結
     */
    public static function getImagesInContent(string $body): array
    {
        $dom = HTMLDocument::createFromString($body, LIBXML_NOERROR);

        $imageList = [];

        foreach ($dom->getElementsByTagName('img') as $img) {
            $pattern = '/\d{4}_\d{2}_\d{2}_\d{2}_\d{2}_\d{2}_[a-zA-Z0-9]+\.(jpeg|png|jpg|gif|svg)/u';

            $imageName = basename($img->getAttribute('src'));

            if (preg_match($pattern, $imageName)) {
                $imageList[] = $imageName;
            }
        }

        // format:
        // [
        //     '2023_01_01_10_18_21_63b0ed6d06d52.jpg',
        //     '2022_12_30_22_39_21_63aef81999216.jpg',
        //     ...
        // ]
        return $imageList;
    }

    /**
     * Get read time of the article, article may mix with Chinese characters and English words,
     * so I count Chinese characters and English words separately. The read time is calculated by
     * dividing the total number of words by different reading speeds for each language.
     */
    public static function getReadTime(string $body): int
    {
        if (in_array(trim($body), ['', '0'], true)) {
            return 1;
        }

        $body = html_entity_decode($body);
        $body = strip_tags($body);
        $body = trim($body);

        // English words including programming terms (variables, functions, CSS classes, etc.)
        $englishWordPattern = '/[\w\'-]+/';
        preg_match_all($englishWordPattern, $body, $matches);
        $englishWordCount = count($matches[0]);

        // Chinese characters pattern
        $chineseWordPattern = '/\p{Han}/u';
        preg_match_all($chineseWordPattern, $body, $matches);
        $chineseWordCount = count($matches[0]);

        // Different reading speeds: English 200 WPM, Chinese 300 characters per minute
        $englishReadTime = $englishWordCount / 200;
        $chineseReadTime = $chineseWordCount / 300;

        $totalMinutes = $englishReadTime + $chineseReadTime;

        // Always return at least 1 minute
        return max(1, intval(ceil($totalMinutes)));
    }
}
