<?php

declare(strict_types=1);

namespace App\Service;

use GuzzleHttp\Client;
use Phpfastcache\Core\Pool\ExtendedCacheItemPoolInterface;
use Symfony\Component\DomCrawler\Crawler;

class DrChronoProblemImageService
{
    private Client $http;
    private ExtendedCacheItemPoolInterface $cache;

    public function __construct(ExtendedCacheItemPoolInterface $cache)
    {
        $this->http = new Client();
        $this->cache = $cache;
    }

    public function get(string $url)
    {
        $item = $this->cache->getItem('dr_img_problem_' . md5($url));
        if ($item->isHit()) {
            return $item->get();
        }

        try {
            $response = $this->http->get($url);
            $html = (string) $response->getBody();

            $crawler = new Crawler($html, $url);
            $image = $crawler->filter('#problems_imagetopic > a > img')->image();
            $src = $image->getUri();

            $item->set($src);
            $item->expiresAfter(new \DateInterval('P1M'));
            $this->cache->save($item);

            return $src;
        } catch (\Exception $e) {
            error_log($e->getMessage());
            return null;
        }
    }
}
