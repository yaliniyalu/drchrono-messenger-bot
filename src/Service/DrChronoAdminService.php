<?php

declare(strict_types=1);

namespace App\Service;

use Phpfastcache\Core\Pool\ExtendedCacheItemPoolInterface;

class DrChronoAdminService
{
    private \GuzzleHttp\Client $http;
    private ExtendedCacheItemPoolInterface $cache;

    public function __construct(DrChronoClient $client, ExtendedCacheItemPoolInterface $cache)
    {
        $this->http = $client->getHttp();
        $this->cache = $cache;
    }

    public function getDoctor(int $doctor)
    {
        $item = $this->cache->getItem("dr_doctor_$doctor");
        if ($item->isHit()) {
            return $item->get();
        }

        try {
            $response = $this->http->get("doctors/$doctor");
        } catch (\Exception $e) {
            return [];
        }

        $result = json_decode((string) $response->getBody(), true);

        $item->set($result);
        $item->expiresAfter(new \DateInterval("P1D"));

        $this->cache->save($item);

        return $result;
    }

    public function getOffice(int $office)
    {
        $item = $this->cache->getItem("dr_office_$office");
        if ($item->isHit()) {
            return $item->get();
        }

        $response = $this->http->get("offices/$office");
        $result = json_decode((string) $response->getBody(), true);

        $result['doctor'] = $this->getDoctor($result['doctor']);

        $item->set($result);
        $item->expiresAfter(new \DateInterval("P1D"));

        $this->cache->save($item);

        return $result;
    }

    public function listOffices()
    {
        $item = $this->cache->getItem("dr_office_list");
        if ($item->isHit()) {
            return $item->get();
        }

        $response = $this->http->get("offices");
        $result = json_decode((string) $response->getBody(), true)['results'];

        $item->set($result);
        $item->expiresAfter(new \DateInterval("P1D"));

        $this->cache->save($item);

        return $result;
    }

    public function listDoctors()
    {
        $item = $this->cache->getItem("dr_doctor_list");
        if ($item->isHit()) {
            return $item->get();
        }

        $response = $this->http->get("doctors");
        $result = json_decode((string) $response->getBody(), true)['results'];

        $item->set($result);
        $item->expiresAfter(new \DateInterval("P1D"));

        $this->cache->save($item);

        return $result;
    }
}
