<?php

declare(strict_types=1);

namespace App\Service;

class UrlHelper
{
    public static function webview(string $url, $id = ''): string
    {
        return $_ENV['WEBVIEW_URL'] . $url . $id;
    }

    public static function webviewFallback(): string
    {
        return $_ENV['WEBVIEW_URL'] . "/fallback";
    }
}
