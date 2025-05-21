<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * App_object_cache Class
 *
 * This class provides caching functionality for objects in the application
 */
class App_object_cache
{
    /**
     * The cache data
     *
     * @var array
     */
    private $cache = [];

    /**
     * CI Instance
     *
     * @var object
     */
    private $CI;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->CI = &get_instance();
    }

    /**
     * Add data to the cache
     *
     * @param string $key
     * @param mixed $data
     * @return void
     */
    public function add($key, $data)
    {
        $this->cache[$key] = $data;
    }

    /**
     * Set data in the cache (alias for add)
     *
     * @param string $key
     * @param mixed $data
     * @return void
     */
    public function set($key, $data)
    {
        $this->add($key, $data);
    }

    /**
     * Get data from the cache
     *
     * @param string $key
     * @return mixed
     */
    public function get($key)
    {
        return isset($this->cache[$key]) ? $this->cache[$key] : null;
    }

    /**
     * Check if a key exists in the cache
     *
     * @param string $key
     * @return boolean
     */
    public function has($key)
    {
        return isset($this->cache[$key]);
    }

    /**
     * Remove a key from the cache
     *
     * @param string $key
     * @return void
     */
    public function remove($key)
    {
        if (isset($this->cache[$key])) {
            unset($this->cache[$key]);
        }
    }

    /**
     * Flush the entire cache
     *
     * @return void
     */
    public function flush()
    {
        $this->cache = [];
    }
}
