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
    /** @var SimpleHtmlParser */
    protected $parser;
    /** @var string */
    protected $line;
    /** @var string[] */
    protected $openSpans = [];
    /** @var int */
    protected $remainingOpenSpansOnLine;
    /** @var int */
    protected $contentOffset;

    function fix(string $line): string
    {
        $this->parser = new SimpleHtmlParser($line);
        $this->line = '';
        $this->contentOffset = 0;
        $this->remainingOpenSpansOnLine = 0;

        for (; $this->parser->valid(); $this->parser->next()) {
            $elem = $this->parser->current();

            if ($elem['type'] === SimpleHtmlParser::OPENING_TAG && $elem['name'] === 'span') {
                $this->handleSpanOpen($elem);
            } elseif ($elem['type'] === SimpleHtmlParser::CLOSING_TAG && $elem['name'] === 'span') {
                if (!empty($this->openSpans)) {
                    $this->handleSpanClose($elem);
                } else {
                    $this->appendContent($elem['start'], $elem['end']);
                }
            }
        }

        $this->finishLine();
        $line = $this->line;

        $this->parser = null;
        $this->line = null;

        return $line;
    }

    protected function handleSpanOpen(array $span): void
    {
        $this->appendContent($span['start'], $span['end']);
        $spanHtml = $this->parser->getHtml($span);

        $this->line .= $spanHtml;
        $this->openSpans[] = $spanHtml;
        ++$this->remainingOpenSpansOnLine;
    }

    protected function handleSpanClose(array $spanClose): void
    {
        $openSpan = array_pop($this->openSpans);

        $this->appendContent($spanClose['start'], $spanClose['end']);

        if ($this->remainingOpenSpansOnLine > 0) {
            // <span> was opened on this line
            --$this->remainingOpenSpansOnLine;
            $this->line .= '</span>';
        } elseif ($this->line !== '') {
            // <span> from one of previous lines
            $this->line = $openSpan . $this->line . '</span>';
        }
    }

    protected function finishLine(): void
    {
        $this->line .= $this->parser->getSlice($this->contentOffset, $this->parser->getLength());

        $this->line = trim($this->line);

        if ($this->line !== '') {
            for ($i = sizeof($this->openSpans) - $this->remainingOpenSpansOnLine - 1; isset($this->openSpans[$i]); --$i) {
                $this->line = $this->openSpans[$i] . $this->line;
            }

            $this->line .= str_repeat('</span>', sizeof($this->openSpans));
        }
    }

    protected function appendContent(int $until, int $newContentOffset): void
    {
        $this->line .= $this->parser->getSlice($this->contentOffset, $until);
        $this->contentOffset = $newContentOffset;
    }
}
