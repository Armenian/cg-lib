<?php

declare(strict_types=1);

namespace CG\Generator;

use RuntimeException;

/**
 * A writer implementation.
 *
 * This may be used to simplify writing well-formatted code.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
class Writer
{
    private string $content = '';
    private int $indentationSpaces = 4;
    private int $indentationLevel = 0;

    public function indent(): Writer
    {
        ++$this->indentationLevel;

        return $this;
    }

    public function outdent(): Writer
    {
        --$this->indentationLevel;

        if ($this->indentationLevel < 0) {
            throw new RuntimeException('The identation level cannot be less than zero.');
        }

        return $this;
    }

    /**
     * @param string $content
     * @return Writer
     */
    public function writeln(string $content): Writer
    {
        $this->write($content."\n");

        return $this;
    }

    /**
     * @param string $content
     * @return Writer
     */
    public function write(string $content): Writer
    {
        $lines = explode("\n", $content);
        $c = count($lines);
        foreach ($lines as $i => $iValue) {
            if ($this->indentationLevel > 0
                && !empty($lines[$i])
                && (empty($this->content) || "\n" === substr($this->content, -1))) {
                $this->content .= str_repeat(' ', $this->indentationLevel * $this->indentationSpaces);
            }

            $this->content .= $iValue;

            if ($i+1 < $c) {
                $this->content .= "\n";
            }
        }

        return $this;
    }

    public function rtrim(): Writer
    {
        $addNl = "\n" === substr($this->content, -1);
        $this->content = rtrim($this->content);

        if ($addNl) {
            $this->content .= "\n";
        }

        return $this;
    }

    public function reset(): Writer
    {
        $this->content = '';
        $this->indentationLevel = 0;

        return $this;
    }

    public function getContent(): string
    {
        return $this->content;
    }
}
