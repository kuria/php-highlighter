<?php declare(strict_types=1);

namespace Kuria\PhpHighlighter;

use Kuria\SimpleHtmlParser\SimpleHtmlParser;

/**
 * PHP code highlighter
 *
 * Example line ranges:
 *
 *      null        highlight all lines
 *      [20, 30]    highlight lines from 20 to 30 (absolute)
 *      [-5, 5]     highlight 5 lines around the active line
 *      [0, 0]      highlight the active line only
 */
abstract class PhpHighlighter
{
    /**
     * Preview code from a PHP file
     */
    static function file(string $file, ?int $activeLine = null, ?array $lineRange = null, ?string $className = null): string
    {
        return static::process((string) @highlight_file($file, true), $activeLine, $lineRange, $className);
    }

    /**
     * Preview code from a string containing PHP code
     */
    static function code(string $phpCode, ?int $activeLine = null, ?array $lineRange = null, ?string $className = null): string
    {
        return static::process((string) @highlight_string($phpCode, true), $activeLine, $lineRange, $className);
    }

    private static function process(string $html, ?int $activeLine, ?array $lineRange, ?string $className): string
    {
        $lines = static::split($html);

        [$start, $end] = static::normalizeLineRange($lineRange, $activeLine, sizeof($lines));

        $lineFixer = new HighlightedLineFixer();

        $output = '<ol'
            . ($start > 0 ? ' start="' . ($start + 1) . '"' : '')
            . ($className !== null ? ' class="' . $className . '"' : '')
            . ">\n";

        for ($i = $start; $i <= $end; ++$i) {
            $output .= '<li' . ($activeLine !== null && $i + 1 === $activeLine ? ' class="active"' : '') . '>'
                . $lineFixer->fix($lines[$i])
                . "</li>\n";
        }

        $output .= "</ol>\n";

        return $output;
    }

    private static function split(string $html): array
    {
        $parser = new SimpleHtmlParser($html);

        $parser->next();
        $parser->next();

        if (!$parser->valid()) {
            // should never happen unless markup of highlight_*() changes
            return [];
        }

        return explode('<br />', $parser->getSlice($parser->current()['end'], $parser->getLength() - 15));
    }

    private static function normalizeLineRange(?array $lineRange, ?int $activeLine, int $totalLines): array
    {
        $lastLine = $totalLines - 1;

        if ($lineRange === null) {
            return [0, $lastLine];
        }

        if ($lineRange[0] <= 0) {
            // relative line range
            if ($activeLine === null) {
                throw new \LogicException('Cannot specify relative line range without also specifying active line');
            }

            $start = $activeLine + $lineRange[0] - 1;
            $end = $activeLine + $lineRange[1] - 1;
        } else {
            // absolute line range
            $start = $lineRange[0] - 1;
            $end = $lineRange[1] - 1;
        }

        return [
            max(0, min($lastLine, $start)),
            max(0, min($lastLine, $end)),
        ];
    }
}
