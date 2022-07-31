<?php

namespace Tests\Unit\Cache;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Kayrunm\Replay\Cache\Repository;
use Tests\TestCase;

class RepositoryTest extends TestCase
{
    public function test_get_returns_null_when_no_cache_entry_is_found(): void
    {
        Cache::shouldReceive('get')
            ->with(md5('abc:/'))
            ->once()
            ->andReturn(null);

        $request = new Request();
        $request->headers->set('X-Idempotency-Key', 'abc');

        $result = (new Repository())->get($request);

        $this->assertNull($result);
    }

    public function test_get_returns_the_cached_request(): void
    {
        Cache::shouldReceive('get')
            ->with(md5('abc:/'))
            ->once()
            ->andReturn($response = [
                'content' => 'Hello world',
                'status' => 200,
                'headers' => [],
            ]);

        $request = new Request();
        $request->headers->set('X-Idempotency-Key', 'abc');

        $result = (new Repository())->get($request);

        $this->assertSame($response, $result);
    }

    public function test_put_adds_request_to_cache(): void
    {
        Carbon::setTestNow('2022-07-31 10:48:00');

        Cache::shouldReceive('put')
            ->once()
            ->withArgs(function (string $key, array $value, Carbon $expiry) {
                return $key === md5('abc:/')
                    && $value['content'] === 'Hello world'
                    && $value['status'] === 200
                    && is_array($value['headers'])
                    && $expiry->is('2022-08-01 10:48:00');
            });

        $request = new Request();
        $request->headers->set('X-Idempotency-Key', 'abc');

        $response = (new Response())
            ->setContent('Hello world')
            ->setStatusCode(200)
            ->withHeaders([]);

        (new Repository())->put($request, $response);
    }
}
