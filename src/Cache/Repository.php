<?php

namespace Kayrunm\Replay\Cache;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class Repository
{
    /** @return array<string, mixed> */
    public function get(Request $request): ?array
    {
        return Cache::get($this->getKey($request));
    }

    public function put(Request $request, Response $response): void
    {
        Cache::put($this->getKey($request), [
            'content' => $response->getContent(),
            'status' => $response->getStatusCode(),
            'headers' => $response->headers->all(),
        ], Carbon::now()->addHours(24));
    }

    private function getKey(Request $request): string
    {
        $key = is_array($key = $request->header('X-Idempotency-Key'))
            ? $key[0]
            : $key;

        return md5("$key:{$request->path()}");
    }
}
