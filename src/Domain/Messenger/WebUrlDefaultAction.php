<?php

declare(strict_types=1);

namespace App\Domain\Messenger;

use Kerox\Messenger\Model\Common\Button\WebUrl;

class WebUrlDefaultAction extends WebUrl
{
    public function toArray(): array
    {
        $array = parent::toArray();
        $array += [
            'url' => $this->url,
            'title' => null,
            'webview_height_ratio' => $this->webviewHeightRatio,
            'messenger_extensions' => $this->messengerExtension,
            'fallback_url' => $this->fallbackUrl,
            'webview_share_button' => $this->webviewShareButton,
        ];

        return array_filter($array);
    }
}
