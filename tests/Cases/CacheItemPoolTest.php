<?php

declare(strict_types=1);

namespace HyperfTest\Cases;

use Hyperf\Doctrine\Cache\CacheItem;
use Hyperf\Doctrine\Cache\CacheItemPool;

/**
 * @internal
 * @coversNothing
 */
class CacheItemPoolTest extends AbstractTestCase
{
    public static function configProvider(): array
    {
        return [
            // Redis
            [
                ['driverName' => 'default', 'ttl' => 1],
                [
                    [
                        'key' => 'file-test-key-1',
                        'value' => 'file-test-value-1',
                    ],
                    [
                        'key' => 'file-test-key-2',
                        'value' => 'file-test-value-2',
                    ],
                ],
            ],
            // File
            [
                ['driverName' => 'file', 'ttl' => 1],
                [
                    [
                        'key' => 'file-test-key-1',
                        'value' => 'file-test-value-1',
                    ],
                    [
                        'key' => 'file-test-key-2',
                        'value' => 'file-test-value-2',
                    ],
                ],
            ],
        ];
    }

    /**
     * @dataProvider configProvider
     * @param mixed $config
     * @param mixed $cacheData
     */
    public function testSave(array $config, array $cacheData)
    {
        $cacheItemPool = new CacheItemPool($config);
        foreach ($cacheData as $data) {
            $item = $cacheItemPool->getItem($data['key']);
            $item->set($data['value']);
            $res = $cacheItemPool->save($item);
            $this->assertTrue($res);
        }
    }

    /**
     * @dataProvider configProvider
     * @depends      testSave
     */
    public function testGetItems(array $config, array $cacheData)
    {
        $cacheItemPool = new CacheItemPool($config);
        $keys = [];
        foreach ($cacheData as $data) {
            $keys[] = $data['key'];
        }
        /** @var CacheItem $item */
        foreach ($cacheItemPool->getItems($keys) as $item) {
            $this->assertTrue($item->isHit());
        }
    }

    /**
     * @dataProvider configProvider
     * @depends      testGetItems
     */
    public function testDeleteItem(array $config, array $cacheData)
    {
        $cacheItemPool = new CacheItemPool($config);
        foreach ($cacheData as $data) {
            $this->assertTrue($cacheItemPool->deleteItem($data['key']));
            $item = $cacheItemPool->getItem($data['key']);
            $this->assertFalse($item->isHit());
        }
    }

    /**
     * @dataProvider configProvider
     * @depends      testDeleteItem
     */
    public function testSaveDeferred(array $config, array $cacheData)
    {
        $cacheItemPool = new CacheItemPool($config);
        foreach ($cacheData as $data) {
            $item = $cacheItemPool->getItem($data['key']);
            $item->set($data['value']);
            $cacheItemPool->saveDeferred($item);
            $item = $cacheItemPool->hasItem($data['key']);
            $this->assertFalse($item);
        }
        $cacheItemPool->commit();
        $this->testGetItems($config, $cacheData);
    }

    /**
     * @dataProvider configProvider
     * @depends      testSaveDeferred
     */
    public function testDeleteItems(array $config, array $cacheData)
    {
        $cacheItemPool = new CacheItemPool($config);
        $keys = [];
        foreach ($cacheData as $data) {
            $keys[] = $data['key'];
        }
        $this->assertTrue($cacheItemPool->deleteItems($keys));
        /** @var CacheItem $item */
        foreach ($cacheItemPool->getItems($keys) as $item) {
            $this->assertFalse($item->isHit());
        }
    }

    /**
     * @dataProvider configProvider
     * @depends      testDeleteItems
     */
    public function testTTL(array $config, array $cacheData)
    {
        $cacheItemPool = new CacheItemPool($config);
        $this->testSave($config, $cacheData);
        usleep($config['ttl'] * 1000000 + 1000000);
        foreach ($cacheData as $data) {
            $keys[] = $data['key'];
        }
        /** @var CacheItem $item */
        foreach ($cacheItemPool->getItems($keys) as $item) {
            $this->assertFalse($item->isHit());
        }
    }

    /**
     * @dataProvider configProvider
     * @depends      testDeleteItems
     */
    public function testClear(array $config, array $cacheData)
    {
        $cacheItemPool = new CacheItemPool($config);
        $this->testSave($config, $cacheData);
        $this->testGetItems($config, $cacheData);
        $this->assertTrue($cacheItemPool->clear());
        $keys = [];
        foreach ($cacheData as $data) {
            $keys[] = $data['key'];
        }
        /** @var CacheItem $item */
        foreach ($cacheItemPool->getItems($keys) as $item) {
            $this->assertFalse($item->isHit());
        }
    }
}
