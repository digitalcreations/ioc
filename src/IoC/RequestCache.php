<?php

namespace DC\IoC;

class RequestCache implements \DC\Cache\ICache
{

    private $repository = [];

    /**
     * Retrieve an item from cache
     *
     * @param string $key The key to store it under
     * @return mixed
     */
    function get($key)
    {
        return $this->repository[$key];
    }

    /**
     * Set an item in the cache
     *
     * @param string $key
     * @param mixed $value
     * @param int|\DateInterval|\DateTime $validity Number of seconds this is valid for (if int)
     * @return void
     */
    function set($key, $value, $validity = null)
    {
        $this->repository[$key] = $value;
    }

    /**
     * Try to get an item, and if missed call the fallback method to produce the value and store it.
     *
     * @param string $key
     * @param callable $fallback
     * @param int|\DateInterval|\DateTime|callback $validity Number of seconds this is valid for (if int)
     * @return mixed
     */
    function getWithFallback($key, callable $fallback, $validity = null)
    {
        if (!isset($this->repository[$key])) {
            $this->repository[$key] = $fallback();
        }
        return $this->get($key);
    }

    /**
     * Remove a key from the cache.
     *
     * @param string $key
     * @return void
     */
    function delete($key)
    {
        unset($this->repository[$key]);
    }

    /**
     * Remove all items from the cache (flush it).
     *
     * @return void
     */
    function deleteAll()
    {
        $this->repository = [];
    }
}