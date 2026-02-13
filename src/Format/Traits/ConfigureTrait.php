<?php

declare(strict_types=1);

namespace Ronanchilvers\Bundler\Format\Traits;

trait ConfigureTrait
{
    protected array $config = [];

    public function setConfig(string $key, mixed $value): static
    {
        $this->config[$key] = $value;

        return $this;
    }

    /**
     * Get a configuration value by key
     * @param string $key
     * @param mixed $default
     * @throws \RuntimeException if the key is not set
     * @return mixed
     */
    public function getConfig(string $key, $default = null): mixed
    {
        if (!isset($this->config[$key])) {
            if (!is_null($default)) {
                return $default;
            }

            throw new \RuntimeException(sprintf(
                'Class %s expects configuration key "%s" to be set',
                static::class,
                $key
            ));
        }

        return $this->config[$key];
    }
}
