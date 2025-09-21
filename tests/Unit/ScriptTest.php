<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Ronanchilvers\Bundler\Output\Element\Script;
use Ronanchilvers\Bundler\Path\Bundle;

#[CoversClass(Script::class)]
final class ScriptTest extends TestCase
{
    public function testRenderEmptyBundleReturnsEmptyString(): void
    {
        $script = new Script();
        $bundle = new Bundle(); // empty
        $this->assertSame('', $script->render($bundle));
    }

    public function testRenderSingleScriptNoAttributes(): void
    {
        $script = new Script();
        $bundle = new Bundle(['js/app.js']);

        $html = $script->render($bundle);

        // Expect exactly one tag
        $this->assertSame(1, substr_count($html, '<script '));
        // There is a deliberate space before the closing '>' even with no attributes
        $this->assertStringContainsString('<script src="js/app.js" ></script>', $html);
    }

    public function testRenderMultipleScriptsWithEscapedPath(): void
    {
        $script = new Script();
        $bundle = new Bundle([
            'js/app.js',
            'js/vendor&v=2.js', // contains special char (&) that should be escaped
        ]);

        $html = $script->render($bundle);

        $this->assertSame(2, substr_count($html, '<script '));
        $this->assertStringContainsString('src="js/app.js"', $html);
        // & should be escaped to &amp;
        $this->assertStringContainsString('src="js/vendor&amp;v=2.js"', $html);
    }

    public function testRenderAttributesAreRenderedAndEscaped(): void
    {
        $script = new Script();
        $bundle = new Bundle(['js/special.js']);

        // Add attributes, including one needing escaping
        $bundle->setAttribute('js/special.js', 'defer', 'defer');
        $bundle->setAttribute('js/special.js', 'data-note', 'He said "Hello" & left');

        $html = $script->render($bundle);

        // defer attribute present
        $this->assertStringContainsString('defer="defer"', $html);
        // data-note value should have quotes and ampersand escaped
        $this->assertStringContainsString('data-note="He said &quot;Hello&quot; &amp; left"', $html);
    }

    public function testAttributeOrderPreserved(): void
    {
        $script = new Script();
        $bundle = new Bundle(['js/order.js']);

        $bundle->setAttribute('js/order.js', 'async', 'async');
        $bundle->setAttribute('js/order.js', 'crossorigin', 'anonymous');

        $html = $script->render($bundle);

        $this->assertSame(1, substr_count($html, '<script '));
        $asyncPos = strpos($html, 'async="async"');
        $crossPos = strpos($html, 'crossorigin="anonymous"');
        $this->assertNotFalse($asyncPos);
        $this->assertNotFalse($crossPos);
        $this->assertTrue($asyncPos < $crossPos, 'async attribute should precede crossorigin attribute');
    }
}
