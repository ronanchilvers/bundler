<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Ronanchilvers\Bundler\Example;

#[CoversClass(Example::class)]
final class ExampleTest extends TestCase
{
    public function testDefaultValueIsEmptyString(): void
    {
        $example = new Example();
        $this->assertSame('', $example->getValue());
    }

    public function testSetAndGetValue(): void
    {
        $example = new Example();
        $example->setValue('foo');
        $this->assertSame('foo', $example->getValue());

        // Fluent interface
        $example->setValue('bar')->setValue('baz');
        $this->assertSame('baz', $example->getValue());
    }

    public function testGreetWithoutValue(): void
    {
        $example = new Example();
        $this->assertSame('Hello Ronan', $example->greet('Ronan'));
    }

    public function testGreetWithValue(): void
    {
        $example = new Example('from Bundler');
        $this->assertSame('Hello World, from Bundler', $example->greet('World'));
    }

    public function testSumWithNoArguments(): void
    {
        $this->assertSame(0, Example::sum());
    }

    public function testSumWithIntegersReturnsInt(): void
    {
        $result = Example::sum(1, 2, 3);
        $this->assertSame(6, $result);
        $this->assertIsInt($result);
    }

    public function testSumWithFloatsReturnsFloat(): void
    {
        $result = Example::sum(1.5, 2, 3.5);
        $this->assertSame(7.0, $result);
        $this->assertIsFloat($result);
    }
}
