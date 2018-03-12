<?php declare(strict_types=1);

namespace Kuria\PhpHighlighter;

use PHPUnit\Framework\TestCase;

class HighlightedLineFixerTest extends TestCase
{
    /** @var HighlightedLineFixer */
    private $fixer;

    protected function setUp()
    {
        $this->fixer = new HighlightedLineFixer();
    }

    function testFixEmpty()
    {
        $this->assertSame('', $this->fixer->fix(''));
        $this->assertSame('', $this->fixer->fix(' '));
    }

    function testFixSimpleLine()
    {
        $this->assertSame('<span style="color: red">red</span>', $this->fixer->fix('<span style="color: red">red</span>'));
        $this->assertSame('foo', $this->fixer->fix(' foo '));
    }

    function testShouldDiscardUnmatchedSpanClosingTag()
    {
        $this->assertSame('foobar', $this->fixer->fix('foo</span>bar'));
    }

    function testShouldReplicateMultilineSpan()
    {
        $this->assertSame('<span style="color: blue">foo</span>', $this->fixer->fix('<span style="color: blue">foo'));
        $this->assertSame('<span style="color: blue">bar</span>', $this->fixer->fix('bar'));
        $this->assertSame('<span style="color: blue">baz</span>', $this->fixer->fix('baz</span>'));
    }

    function testShouldReplicateMultipleMultilineSpans()
    {
        $this->assertSame(
            '<span style="color: red">foo</span>',
            $this->fixer->fix('<span style="color: red">foo')
        );

        $this->assertSame(
            '<span style="color: red"><span style="color: green">bar</span></span>',
            $this->fixer->fix('<span style="color: green">bar')
        );

        $this->assertSame(
            '<span style="color: red"><span style="color: green"><span style="color: blue">baz</span></span></span>',
            $this->fixer->fix('<span style="color: blue">baz')
        );

        $this->assertSame(
            '',
            $this->fixer->fix('</span></span></span>')
        );

        $this->assertSame(
            'mlem',
            $this->fixer->fix('mlem')
        );
    }
}
