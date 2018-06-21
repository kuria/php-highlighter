<?php declare(strict_types=1);

namespace Kuria\PhpHighlighter;

use Kuria\SimpleHtmlParser\SimpleHtmlParser;

/**
 * Fixes individual HTML lines from the output of highlight_file() or highlight_string()
 *
 * Preserves colors of multiline tokens.
 */
class HighlightedLineFixer
{
    /** @var string */
    private $line;

    /** @var string[] */
    private $openSpans = [];

    /** @var int */
    private $remainingOpenSpansOnLine;

    /** @var int */
    private $contentOffset;

    function fix(string $line): string
    {
        $parser = new SimpleHtmlParser($line);
        $this->line = '';
        $this->contentOffset = 0;
        $this->remainingOpenSpansOnLine = 0;

        for (; $parser->valid(); $parser->next()) {
            /** @var array $elem */
            $elem = $parser->current();

            if ($elem['type'] === SimpleHtmlParser::OPENING_TAG && $elem['name'] === 'span') {
                $this->handleSpanOpen($parser, $elem);
            } elseif ($elem['type'] === SimpleHtmlParser::CLOSING_TAG && $elem['name'] === 'span') {
                if (!empty($this->openSpans)) {
                    $this->handleSpanClose($parser, $elem);
                } else {
                    $this->appendContent($parser, $elem['start'], $elem['end']);
                }
            }
        }

        $this->finishLine($parser);
        $line = $this->line;

        $this->line = '';

        return $line;
    }

    private function handleSpanOpen(SimpleHtmlParser $parser, array $span): void
    {
        $this->appendContent($parser, $span['start'], $span['end']);
        $spanHtml = $parser->getHtml($span);

        $this->line .= $spanHtml;
        $this->openSpans[] = $spanHtml;
        ++$this->remainingOpenSpansOnLine;
    }

    private function handleSpanClose($parser, array $spanClose): void
    {
        $openSpan = array_pop($this->openSpans);

        $this->appendContent($parser, $spanClose['start'], $spanClose['end']);

        if ($this->remainingOpenSpansOnLine > 0) {
            // <span> was opened on this line
            --$this->remainingOpenSpansOnLine;
            $this->line .= '</span>';
        } elseif ($this->line !== '') {
            // <span> from one of previous lines
            $this->line = $openSpan . $this->line . '</span>';
        }
    }

    private function finishLine(SimpleHtmlParser $parser): void
    {
        $this->line .= $parser->getSlice($this->contentOffset, $parser->getLength());

        $this->line = trim($this->line);

        if ($this->line !== '') {
            for ($i = count($this->openSpans) - $this->remainingOpenSpansOnLine - 1; isset($this->openSpans[$i]); --$i) {
                $this->line = $this->openSpans[$i] . $this->line;
            }

            $this->line .= str_repeat('</span>', count($this->openSpans));
        }
    }

    private function appendContent(SimpleHtmlParser $parser, int $until, int $newContentOffset): void
    {
        $this->line .= $parser->getSlice($this->contentOffset, $until);
        $this->contentOffset = $newContentOffset;
    }
}
