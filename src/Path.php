<?php

declare(strict_types=1);

namespace Ronanchilvers\Bundler;

class Path
{
    public static function placeholders(
        string $path,
    ): string {
        $root = null;
        $thisDir = './';
        while (true) {
            $test = realpath(__DIR__ . DIRECTORY_SEPARATOR . $thisDir . 'vendor');
            if ('/' == $test) {
                break;
            }
            if ($test && is_dir($test)) {
                $root = dirname($test);
                break;
            }
            $thisDir .= '../';
        }
        $replacements = [
            '{root}' => $root,
        ];
        return str_replace(
            array_keys($replacements),
            array_values($replacements),
            $path,
        );
    }

    protected $data = [];

    public function __construct(string $realpath, string $path)
    {
        $this->data = [
            'realpath' => $realpath,
            'path' => $path,
        ];
    }

    public function realpath(): string
    {
        return $this->data['realpath'];
    }

    public function setRealpath(string $realpath): static
    {
        $this->data['realpath'] = $realpath;

        return $this;
    }

    public function path(): string
    {
        return $this->data['path'];
    }

    public function setPath(string $path): static
    {
        $this->data['path'] = $path;

        return $this;
    }
}
