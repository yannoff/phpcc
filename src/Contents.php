<?php

/**
 * This file is part of the PHP Code Compiler project
 *
 * Copyright (c) Yannoff (https://github.com/yannoff)
 *
 * @project   PHP Code Compiler (yannoff/phpcc)
 * @homepage  https://github.com/yannoff/phpcc
 * @license   https://github.com/yannoff/phpcc/blob/main/LICENSE
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Yannoff\PhpCodeCompiler;

/**
 * Example:
 *
 * $comments = Contents::load($banner)
 * ->prefix('* ')
 * or
 * ->map(function($line){ return '* ' . $line; })
 */

class Contents
{
    /**
     * @var string[]
     */
    protected $lines = [];

    /**
     * @param ?string[] $lines
     */
    public function __construct(array $lines = null)
    {
        $this->lines = $lines ?? [];
    }

    /**
     * Create a new instance from the given lines stack
     *
     * @param string[] $lines
     *
     * @return Contents
     */
    public static function load(array $lines = null): Contents
    {
        return new static($lines);
    }

    /**
     * Create a new instance from the given file contents
     *
     * @param string $file
     *
     * @return Contents
     */
    public static function file(string $file): Contents
    {
        $lines = file($file, FILE_IGNORE_NEW_LINES);

        return self::load($lines);
    }

    /**
     * Apply the given method to each line of the contents
     *
     * @param callable $callback
     *
     * @return self
     */
    public function map(callable $callback): self
    {
        $this->lines = array_map($callback, $this->lines);

        return $this;
    }

    /**
     * Add a new line at the end of the contents
     *
     * @param string $line
     *
     * @return self
     */
    public function push(string $line): self
    {
        $this->lines[] = $line;

        return $this;
    }

    /**
     * Insert a new line at the beginning of the contents
     *
     * @param string $line
     *
     * @return self
     */
    public function unshift(string $line): self
    {
        array_unshift($this->lines, $line);

        return $this;
    }

    /**
     * Render a concatenated representation of the contents
     *
     * @param string $glue
     *
     * @return string
     */
    public function join(string $glue = null): string
    {
        return implode($glue, $this->lines);
    }

    /**
     * Prefix each line of the contents with the given string
     *
     * @param string $prefix
     *
     * @return self
     */
    public function prefix(string $prefix): self
    {
        return $this->map(function($line) use ($prefix) { return $prefix . $line; });
    }

    /**
     * Getter for the contents stack
     *
     * @return string[]
     */
    public function all(): array
    {
        return $this->lines ?? [];
    }
}
