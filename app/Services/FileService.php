<?php

declare(strict_types=1);

namespace App\Services;

use Random\RandomException;

class FileService
{
    /**
     * @throws RandomException
     */
    public static function generateFileName(string $fileExtension): string
    {
        $bytes = random_bytes(6);
        $randomString = bin2hex($bytes);

        // format: 2023_01_01_10_18_21_63b0ed6d06d5.jpg
        return date('Y_m_d_H_i_s').'_'.$randomString.'.'.strtolower($fileExtension);
    }
}
