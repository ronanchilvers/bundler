<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Ronanchilvers\Bundler\Output\Element\Stylesheet;
use Ronanchilvers\Bundler\Path\Bundle;

#[CoversClass(Stylesheet::class)]
final class StylesheetTest extends TestCase
{
    public function testRenderEmptyBundleReturnsEmptyString(): void
    {
        $stylesheet = new Stylesheet();
        $bundle = new Bundle(); // empty
        $this->assertSame('', $stylesheet->render($bundle));
    }

    public function testRenderSingleStylesheetNoAttributes(): void
    {
        $stylesheet = new Stylesheet();
        $bundle = new Bundle(['css/app.css']);

        $html = $stylesheet->render($bundle);

        // Expect exactly one tag
        $this->assertSame(1, substr_count($html, '<link '));
        // There is a deliberate space before closing '>' even with no attributes
        $this->assertStringContainsString('<link rel="stylesheet" href="css/app.css" >', $html);
    }

    public function testRenderMultipleStylesheetsWithEscapedPath(): void
    {
        $stylesheet = new Stylesheet();
        $bundle = new Bundle([
            'css/app.css',
            'css/theme&v=1.css', // contains special char (&) that should be escaped
        ]);

        $html = $stylesheet->render($bundle);

        $this->assertSame(2, substr_count($html, '<link '));
        $this->assertStringContainsString('href="css/app.css"', $html);
        // & should be escaped to &amp;
        $this->assertStringContainsString('href="css/theme&amp;v=1.css"', $html);
    }

    public function testRenderAttributesAreRenderedAndEscaped(): void
    {
        $stylesheet = new Stylesheet();
        $bundle = new Bundle(['css/special.css']);

        // Add attributes, including one needing escaping
        $bundle->setAttribute('css/special.css', 'media', 'screen');
        $bundle->setAttribute('css/special.css', 'data-note', 'He said "Hello" & left');

        $html = $stylesheet->render($bundle);

        // media attribute present
        $this->assertStringContainsString('media="screen"', $html);
        // data-note value should have quotes and ampersand escaped
        $this->assertStringContainsString('data-note="He said &quot;Hello&quot; &amp; left"', $html);
    }

    public function testAttributeOrderPreserved(): void
    {
        $stylesheet = new Stylesheet();
        $bundle = new Bundle(['css/order.css']);

        $bundle->setAttribute('css/order.css', 'media', 'print');
        $bundle->setAttribute('css/order.css', 'integrity', 'sha384-XYZ');

        $html = $stylesheet->render($bundle);

        // Extract the single tag
        $this->assertSame(1, substr_count($html, '<link '));
        // Ensure media appears before integrity
        $mediaPos = strpos($html, 'media="print"');
        $integrityPos = strpos($html, 'integrity="sha384-XYZ"');
        $this->assertNotFalse($mediaPos);
        $this->assertNotFalse($integrityPos);
        $this->assertTrue($mediaPos < $integrityPos, 'media attribute should precede integrity attribute');
    }
}
