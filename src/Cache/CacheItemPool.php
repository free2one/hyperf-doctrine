<?php

declare(strict_types=1);

namespace Hyperf\Doctrine\Cache;

use Closure;
use Hyperf\Cache\CacheManager;
use Hyperf\Context\ApplicationContext;
use Hyperf\Context\Context;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\SimpleCache\CacheInterface;

class CacheItemPool implements CacheItemPoolInterface
{
    public static array $contextKeys = [
        'cacheItemPool' => 'doctrine.orm.cacheItemPool',
    ];

    private array $config = [
        'driverName' => 'default',
        'ttl' => 60 * 60 * 24,
    ];

    private CacheInterface $cache;

    private static Closure $createCacheItem;

    public function __construct(array $config = [])
    {
        $this->config = array_merge($this->config, $config);
        $cacheManager = ApplicationContext::getContainer()->get(CacheManager::class);
        $this->cache = $cacheManager->getDriver($this->config['driverName']);

        self::$createCacheItem ?? self::$createCacheItem = Closure::bind(
            static function ($key, $value, $isHit) {
                $item = new CacheItem();
                $item->key = $key;
                $item->value = $value;
                $item->isHit = $isHit;

                return $item;
            },
            null,
            CacheItem::class
        );
    }

    public function getItem(string $key): CacheItemInterface
    {
        $value = $this->cache->get($key);
        if ($value == null) {
            return (self::$createCacheItem)($key, null, false);
        }

        return (self::$createCacheItem)($key, $value, true);
    }

    public function getItems(array $keys = []): iterable
    {
        $values = $this->cache->getMultiple($keys);
        foreach ($keys as $key) {
            if (! isset($values[$key]) || $values[$key] == null) {
                yield (self::$createCacheItem)($key, null, false);
            } else {
                yield (self::$createCacheItem)($key, $values[$key], true);
            }
        }
    }

    public function hasItem(string $key): bool
    {
        return $this->cache->has($key);
    }

    public function clear(): bool
    {
        return $this->cache->clear();
    }

    public function deleteItem(string $key): bool
    {
        return $this->cache->delete($key);
    }

    public function deleteItems(array $keys): bool
    {
        return $this->cache->deleteMultiple($keys);
    }

    public function save(CacheItemInterface $item): bool
    {
        $item = (array) $item;
        $key = $item["\0*\0key"];
        $value = $item["\0*\0value"];
        $expiry = $item["\0*\0expiry"] ?: $this->config['ttl'];

        return $this->cache->set($key, $value, $expiry);
    }

    public function saveDeferred(CacheItemInterface $item): bool
    {
        $items = Context::get(static::$contextKeys['cacheItemPool'], []);
        $items[$item->getKey()] = $item;
        Context::set(
            static::$contextKeys['cacheItemPool'],
            $items
        );

        return true;
    }

    public function commit(): bool
    {
        $items = Context::get(static::$contextKeys['cacheItemPool'], []);
        /**
         * @warning:框架未对批量操作进行优化,并且请勿相信返回值
         */
        return $this->cache->setMultiple($items, $this->config['ttl']);
    }
}
