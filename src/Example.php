<?php

declare(strict_types=1);

namespace Ronanchilvers\Bundler;

/**
 * Example class used to demonstrate writing unit tests.
 *
 * This class is intentionally simple so you can start adding tests
 * immediately. Feel free to delete or replace it as your library
 * develops.
 */
final class Example
{
    /**
     * Stored arbitrary value.
     */
    private string $value;

    /**
     * @param string $value Optional initial value.
     */
    public function __construct(string $value = '')
    {
        $this->value = $value;
    }

    /**
     * Get the current value.
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * Set the current value.
     *
     * Fluent: returns $this for chaining.
     */
    public function setValue(string $value): self
    {
        $this->value = $value;
        return $this;
    }

    /**
     * Produce a greeting message incorporating the current value.
     *
     * Example:
     *   (new Example('from Bundler'))->greet('Ronan');
     *   // "Hello Ronan, from Bundler"
     */
    public function greet(string $name): string
    {
        $trimmed = trim($this->value);
        $suffix = $trimmed === '' ? '' : ', ' . $trimmed;

        return sprintf('Hello %s%s', $name, $suffix);
    }

    /**
     * Sum an arbitrary list of numeric values.
     *
     * If no numbers are provided, returns 0.
     *
     * Examples:
     *   Example::sum();          // 0
     *   Example::sum(1, 2, 3);   // 6
     *   Example::sum(1.5, 2);    // 3.5
     *
     * @param int|float ...$numbers
     * @return int|float
     */
    public static function sum(int|float ...$numbers): int|float
    {
        if ($numbers === []) {
            return 0;
        }

        $total = 0;
        foreach ($numbers as $n) {
            $total += $n;
        }

        // Preserve int if all inputs were int, else float
        $allInt = array_reduce(
            $numbers,
            static fn(bool $carry, $n): bool => $carry && is_int($n),
            true
        );

        return $allInt ? (int)$total : (float)$total;
    }
}
