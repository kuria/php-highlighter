<?php declare(strict_types=1);

namespace Kuria\PhpHighlighter;

use PHPUnit\Framework\TestCase;

class PhpHighlighterTest extends TestCase
{
    function testShouldHighlight()
    {
        $html = PhpHighlighter::code($this->getTestCode());

        $this->assertInternalType('string', $html);

        $this->assertContains('<ol>', $html);
        $this->assertContains('</ol>', $html);
        $this->assertContains('Example', $html);
        $this->assertContains('__construct', $html);
        $this->assertContains('sayHello', $html);
        $this->assertContains('echo', $html);

        $this->assertNotContains('class="active"', $html);
        $this->assertNotContains('start=', $html);
    }

    function testShouldHighlightEmptyString()
    {
        $this->assertSame("<ol>\n<li></li>\n</ol>\n", PhpHighlighter::code(''));
    }

    function testShouldHighlightWithCustomClass()
    {
        $html = PhpHighlighter::code($this->getTestCode(), null, null, 'custom');

        $this->assertInternalType('string', $html);

        $this->assertContains('class="custom"', $html);
        $this->assertNotContains('class="code-preview"', $html);
    }

    function testShouldHighlightWithActiveLine()
    {
        $html = PhpHighlighter::code($this->getTestCode(), 11);

        $this->assertInternalType('string', $html);

        $this->assertContains('<ol>', $html);
        $this->assertContains('</ol>', $html);
        $this->assertContains('Example', $html);
        $this->assertContains('__construct', $html);
        $this->assertContains('sayHello', $html);
        $this->assertContains('echo', $html);
        $this->assertRegExp('{class="active".+__construct}m', $html);

        $this->assertNotContains('start=', $html);
    }

    /**
     * @dataProvider provideLineRanges
     */
    function testShouldHighlightWithActiveLineAndRange(array $lineRange)
    {
        $html = PhpHighlighter::code($this->getTestCode(), 11, $lineRange);

        $this->assertInternalType('string', $html);

        $this->assertContains('<ol ', $html);
        $this->assertContains('</ol>', $html);
        $this->assertContains('start="9"', $html);
        $this->assertContains('protected', $html);
        $this->assertContains('__construct', $html);
        $this->assertContains('$this', $html);

        $this->assertRegExp('{class="active".+__construct}m', $html);
        $this->assertNotContains('@var', $html);
        $this->assertNotContains('Example', $html);
        $this->assertNotContains('Lorem', $html);
        $this->assertNotContains('sayHello', $html);
        $this->assertNotContains('echo', $html);
    }

    function testShouldThrowExceptionOnRelativeLineRangeWithoutActiveLine()
    {
        $this->expectException(\LogicException::class);

        PhpHighlighter::code($this->getTestCode(), null, [-1, 1]);
    }

    function provideLineRanges(): array
    {
        return [
            'Relative' => [[-2, 2]],
            'Absolute' => [[9, 13]],
        ];
    }

    function testShouldHighlightFile()
    {
        $html = PhpHighlighter::file(__FILE__, __LINE__, [-5, 5]);

        $this->assertInternalType('string', $html);

        $this->assertContains('<ol start="' . (__LINE__ - 4 - 5) . '">', $html);
        $this->assertContains('</ol>', $html);
        $this->assertRegExp('{class="active".+PhpHighlighter}m', $html);
        $this->assertContains('__FILE__', $html);
        $this->assertContains('__LINE__', $html);
    }

    private function getTestCode()
    {
        return <<<'CODE'
<?php

/**
 * Example class
 */
class Lorem extends Ipsum
{
    /** @var string */
    protected $name;

    function __construct(string $name)
    {
        $this->name = $name;
    }

    function sayHello(string $to): void
    {
        echo <<<MESSAGE
Hello {$to},
my name is {$this->name}.
MESSAGE;
    }
}

CODE;
    }
}
