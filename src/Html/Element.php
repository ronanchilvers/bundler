<?php

declare(strict_types=1);

namespace Ronanchilvers\Bundler\Html;

/**
 * This class represents an HTML element
 *
 * @author Ronan Chilvers <ronan@thelittledot.com>
 */
class Element
{
    protected string $tag;
    protected array $attributes = [];
    protected ?string $content = null;
    protected bool $selfClosing = false;

    public function __construct(string $tag, array $attributes = [], ?string $content = null, bool $selfClosing = false)
    {
        $this->tag = $tag;
        $this->attributes = $attributes;
        $this->content = $content;
        $this->selfClosing = $selfClosing;
    }

    public function setContent(?string $content): static
    {
        $this->content = $content;

        return $this;
    }

    public function content(): ?string
    {
        return $this->content;
    }

    public function setAttribute(string $key, string $value): static
    {
        $this->attributes[$key] = $value;

        return $this;
    }

    public function removeAttribute(string $key): static
    {
        unset($this->attributes[$key]);

        return $this;
    }

    public function attribute(string $key): ?string
    {
        return $this->attributes[$key] ?? null;
    }

    public function render(): string
    {
        $attrString = '';
        foreach ($this->attributes as $key => $value) {
            $attrString .= ' ' . htmlspecialchars($key) . '="' . htmlspecialchars($value) . '"';
        }

        if ($this->selfClosing) {
            return '<' . htmlspecialchars($this->tag) . $attrString . ' />';
        }

        return '<' . htmlspecialchars($this->tag) . $attrString . '>' .
            ($this->content !== null ? htmlspecialchars($this->content) : '') .
            '</' . htmlspecialchars($this->tag) . '>';
    }
}
