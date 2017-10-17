<?php

/*
 * This file is part of CacheServiceProvider.
 *
 * (c) Quentin Aupetit <qaupetit@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Moust\Silex\Cache;

use Memcache;

class MemcacheCache extends AbstractCache
{
    /**
     * @var Memcache
     */
    private $_memcache;

    /**
     * {@inheritdoc}
     */
    public function __construct(array $options = array())
    {
        if (isset($options['memcache']) && is_callable($options['memcache'])) {
            $options['memcache'] = $options['memcache']();
        }

        if (!isset($options['memcache']) || !$options['memcache'] instanceof Memcache) {
            $options['memcache'] = new Memcache;
            $options['memcache']->connect('localhost', 11211);
        }

        $this->setMemcache($options['memcache']);
    }

    /**
     * Sets the Memcache instance to use.
     *
     * @param Memcache $memcache
     */
    public function setMemcache(\Memcache $memcache)
    {
        $this->_memcache = $memcache;
    }

    /**
     * Gets the Memcache instance used by the cache.
     *
     * @return Memcache
     */
    public function getMemcache()
    {
        return $this->_memcache;
    }

    /**
     * {@inheritdoc}
     */
    static function isSupported()
    {
        return extension_loaded('memcache');
    }

    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        return $this->_memcache->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function delete($key)
    {
        return $this->_memcache->delete($key);
    }

    /**
     * {@inheritdoc}
     */
    public function exists($key)
    {
        // $flags stays untouched if $key was not found on the server,
        // this way we can determine if bool(false) was stored
        $flags = false;

        $this->_memcache->get($key, $flags);

        return $flags !== false;
    }

    /**
     * {@inheritdoc}
     */
    public function fetch($key)
    {
        return $this->_memcache->get($key);
    }

    /**
     * {@inheritdoc}
     */
    public function store($key, $var = null, $ttl = 0)
    {
        // Memcache : Any time higher than 30 days is interpreted as a unix timestamp date.
        $ttl = ($ttl > 2628000) ? time() + $ttl : $ttl;
        return $this->_memcache->set($key, $var, 0, (int) $ttl);
    }
}
