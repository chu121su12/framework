<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Contracts\Cache;

use Psr\Cache\CacheItemPoolInterface;
use Psr\Cache\InvalidArgumentException;
use Psr\Log\LoggerInterface;

// Help opcache.preload discover always-needed symbols
class_exists(InvalidArgumentException::class);

class CacheTrait_doGet_class extends \InvalidArgumentException implements InvalidArgumentException { }
        
/**
 * An implementation of CacheInterface for PSR-6 CacheItemPoolInterface classes.
 *
 * @author Nicolas Grekas <p@tchwork.com>
 */
trait CacheTrait
{
    /**
     * {@inheritdoc}
     *
     * @return mixed
     */
    public function get(/*string */$key, callable $callback, /*float */$beta = null, array &$metadata = null)
    {
        $beta = backport_type_check('float', $beta);

        $key = backport_type_check('string', $key);

        return $this->doGet($this, $key, $callback, $beta, $metadata);
    }

    /**
     * {@inheritdoc}
     */
    public function delete(/*string */$key)/*: bool*/
    {
        $key = backport_type_check('string', $key);

        return $this->deleteItem($key);
    }

    private function doGet(CacheItemPoolInterface $pool, /*string */$key, callable $callback, /*?float */$beta, array &$metadata = null, LoggerInterface $logger = null)
    {
        $beta = backport_type_check('?float', $beta);

        $key = backport_type_check('string', $key);

        if (0 > $beta = (isset($beta) ? $beta : 1.0)) {
            throw new CacheTrait_doGet_class(
                sprintf('Argument "$beta" provided to "%s::get()" must be a positive number, %f given.', static::class, $beta)
            );
        }

        $item = $pool->getItem($key);
        $recompute = !$item->isHit() || \INF === $beta;
        $metadata = $item instanceof ItemInterface ? $item->getMetadata() : [];

        if (!$recompute && $metadata) {
            $expiry = isset($metadata[ItemInterface::METADATA_EXPIRY]) ? $metadata[ItemInterface::METADATA_EXPIRY] : false;
            $ctime = isset($metadata[ItemInterface::METADATA_CTIME]) ? $metadata[ItemInterface::METADATA_CTIME] : false;

            if ($recompute = $ctime && $expiry && $expiry <= ($now = microtime(true)) - $ctime / 1000 * $beta * log(random_int(1, \PHP_INT_MAX) / \PHP_INT_MAX)) {
                // force applying defaultLifetime to expiry
                $item->expiresAt(null);
                $logger && $logger->info('Item "{key}" elected for early recomputation {delta}s before its expiration', [
                    'key' => $key,
                    'delta' => sprintf('%.1f', $expiry - $now),
                ]);
            }
        }

        if ($recompute) {
            $save = true;
            $item->set($callback($item, $save));
            if ($save) {
                $pool->save($item);
            }
        }

        return $item->get();
    }
}
