<?php

declare(strict_types=1);

if (! function_exists('get_gravatar')) {
    /**
     * Get either a Gravatar URL or complete image tag for a specified email address.
     *
     * @param  string  $email  The email address
     * @param  int  $size  Size in pixels, defaults to 64px [ 1 - 2048 ]
     * @param  string  $default_image_type  Default imageset to use [ 404 | mp | identicon | monsterid | wavatar ]
     * @param  bool  $force_default  Force default image always. By default false.
     * @param  string  $rating  Maximum rating (inclusive) [ g | pg | r | x ]
     * @param  bool  $return_image  True to return a complete IMG tag False for just the URL
     * @param  array  $html_tag_attributes  Optional, additional key/value attributes to include in the IMG tag
     * @return string containing either just a URL or a complete image tag
     *
     * @source https://gravatar.com/site/implement/images/php/
     */
    function get_gravatar(
        string $email,
        int $size = 64,
        string $default_image_type = 'mp',
        bool $force_default = false,
        string $rating = 'g',
        bool $return_image = false,
        array $html_tag_attributes = []
    ): string {
        // Prepare parameters.
        $params = [
            's' => htmlentities((string) $size),
            'd' => htmlentities($default_image_type),
            'r' => htmlentities($rating),
        ];
        if ($force_default) {
            $params['f'] = 'y';
        }

        // Generate url.
        $base_url = 'https://www.gravatar.com/avatar';
        $hash = hash('sha256', strtolower(trim($email)));
        $query = http_build_query($params);
        $url = sprintf('%s/%s?%s', $base_url, $hash, $query);

        // Return image tag if necessary.
        if ($return_image) {
            $attributes = '';
            foreach ($html_tag_attributes as $key => $value) {
                $value = htmlentities($value, ENT_QUOTES, 'UTF-8');
                $attributes .= sprintf('%s="%s" ', $key, $value);
            }

            return sprintf('<img src="%s" %s/>', $url, $attributes);
        }

        return $url;
    }
}
