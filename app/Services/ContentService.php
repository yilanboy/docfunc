<?php

namespace App\Services;

use Dom\HTMLDocument;
use HTMLPurifier;
use HTMLPurifier_Config;

class ContentService
{
    /**
     * 生成用來優化 SEO 的 slug
     *
     * @param  string  $title  標題
     */
    public static function makeSlug(string $title): string
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
    public static function htmlPurifier(string $html): string
    {
        $config = HTMLPurifier_Config::createDefault();

        // default config
        $config->set('Core.Encoding', 'utf-8');
        $config->set('HTML.DefinitionID', 'content');
        $config->set('Cache.SerializerPath', config('purifier.cache.path'));

        // add target="_blank" and rel="nofollow noreferrer noopener" to all links
        $config->set('HTML.TargetBlank', true);
        $config->set('HTML.Nofollow', true);

        // disable cache in development
        if (! app()->isProduction()) {
            $config->set('Cache.DefinitionImpl', null);
        }

        $definition = $config->maybeGetRawHTMLDefinition();

        if (! is_null($definition)) {
            $definition->addElement('pre', 'Block', 'Flow', 'Common', [
                'data-language' => 'Text',
                'spellcheck' => 'Text',
            ]);
            $definition->addElement('figure', 'Block', 'Optional: (figcaption, Flow) | (Flow, figcaption) | Flow',
                'Common');
            $definition->addElement('figcaption', 'Inline', 'Flow', 'Common');
            $definition->addElement('oembed', 'Block', 'Flow', 'Common', ['url' => 'URI']);
        }

        $purifier = new HTMLPurifier($config);

        return $purifier->purify($html);
    }

    /**
     * 生成文章內容的摘錄
     */
    public static function makeExcerpt(string $body, int $length = 200): string
    {
        return str()->limit(strip_tags($body), $length);
    }

    /**
     * 取得文章中的圖片連結
     */
    public static function imagesInContent(string $body): array
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
}
