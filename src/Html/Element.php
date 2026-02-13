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

    /**
     * @param string $tag The HTML tag name
     * @param array<string,string> $attributes Key-value pairs of attributes
     * @param string|null $content The inner content of the element (if not self-closing)
     * @param bool $selfClosing Whether the tag is self-closing (e.g. <img />, <br />)
     */
    public function __construct(string $tag, array $attributes = [], ?string $content = null, bool $selfClosing = false)
    {
        $this->tag = $tag;
        $this->attributes = $attributes;
        $this->content = $content;
        $this->selfClosing = $selfClosing;
    }

    /**
     * Set the inner content of the element
     * @param string|null $content The content to set, or null to clear
     * @return $this Fluent interface
     */
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
