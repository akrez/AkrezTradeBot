<?php

namespace App\Support;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;

class BitpinApi
{
    private $cache = [];

    public function getMarkets()
    {
        $cacheKey = 'BitpinApi.getMarkets';

        if (! isset($this->cache[$cacheKey])) {

            if (0) {
                $response = File::json(storage_path('api.bitpin.ir.json'))['v1/mkt/markets'];
            } else {
                $response = (array) Http::get('https://api.bitpin.ir/v1/mkt/markets/')->json();
            }

            if (isset($response['results']) and is_array($response['results'])) {
                $this->cache[$cacheKey] = $response['results'];
            } else {
                $this->cache[$cacheKey] = [];
            }
        }

        return $this->cache[$cacheKey];
    }

    public function getMarketsArray()
    {
        $cacheKey = 'BitpinApi.getMarketsArray';

        $markets = $this->getMarkets();

        if (! isset($this->cache[$cacheKey])) {
            $result = [];
            foreach ($markets as $market) {
                $markeCodeParts = explode('_', $market['code']);
                $result[implode('', $markeCodeParts)] = $market['id'];
            }
            $this->cache[$cacheKey] = $result;
        }

        return $this->cache[$cacheKey];
    }

    public function getMarketsKeyboard($target = null)
    {
        $cacheKey = 'BitpinApi.getMarketsKeyboard.'.$target;

        $markets = $this->getMarkets();

        if (! isset($this->cache[$cacheKey])) {
            $result = [];
            foreach ($markets as $market) {
                $markeCodeParts = explode('_', $market['code']);
                if ($target === null or $markeCodeParts[1] === $target) {
                    $result[] = '+'.implode('', $markeCodeParts);
                }
            }
            $this->cache[$cacheKey] = $result;
        }

        return $this->cache[$cacheKey];
    }

    public function getMarketId($symbol)
    {
        $array = $this->getMarketsArray();

        return empty($array[$symbol]) ? null : $array[$symbol];
    }

    public function getCoin($coin, $iteration, $useCache = true)
    {
        $cacheKey = 'BitpinApi.getCoin.'.$coin.'.'.$iteration;

        if ($useCache and isset($this->cache[$cacheKey])) {
            return $this->cache[$cacheKey];
        }

        if (false) {
            $this->cache[$cacheKey] = File::json(storage_path('api.bitpin.ir.json'))['v1/mkt/markets/charts'];
        } else {
            $url = 'https://api.bitpin.ir/v1/mkt/markets/charts/'.$coin.'/'.$iteration.'/';
            $this->cache[$cacheKey] = (array) Http::get($url)->json();
        }

        return $this->cache[$cacheKey];
    }
}
