<?php

namespace Family\PhotoBundle\Service;

use League\Glide\ServerFactory;

class Processor
{
    /**
     * Source directory
     *
     * @var string
     */
    private $source;

    /**
     * Cache directory
     *
     * @var string
     */
    private $cache;

    /**
     * Glide Server
     *
     * @var Glide/Server
     */
    private $server;

    /**
     * Constructor
     *
     * @param string $source Source directory
     * @param string $cache Cache directory
     * @param array $presets An array of presets
     */
    public function __construct($source, $cache, array $presets)
    {
        $this->source = $source;
        $this->cache = $cache;
        $this->server = ServerFactory::create([
            'presets' => $presets,
            'source' => $this->source,
            'cache' => $this->cache,
        ]);
    }

    /**
     * Get source directory
     *
     * @return string
     */
    public function setSourceDirectory()
    {
        return $this->source;
    }

    /**
     * Get cache directory
     *
     * @return string
     */
    public function getCacheDirectory()
    {
        return $this->cache;
    }

    /**
     * Clear cache
     *
     * @param string $filename
     */
    public function clear($filename)
    {
        $this->server->deleteCache($filename);
    }

    /**
     * Process file with the given process
     *
     * @param string $filename File name
     * @param string|null $preset Preset name
     *
     * @return string Full file path
     */
    public function process($filename, $preset = null)
    {
        $path = sprintf('%s/%s', $this->source, $filename);

        if (!file_exists($path)) {
            return null;
        }

        if (!$preset) {
            return $path;
        }

        $file = $this->server->makeImage($filename, ['p' => $preset]);

        return sprintf('%s/%s', $this->getCacheDirectory(), $file);
    }
}
