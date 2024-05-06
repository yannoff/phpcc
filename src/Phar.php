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

use Phar as BuiltinPhar;

/**
 * Replacement for the `php_strip_whitespace()` native method,
 * which mistakenly treat php attributes as bash-like comments
 *
 * @param string $file
 *
 * @return string
 */
function php_strip_whitespace(string $file): string
{
    $lines = file($file, FILE_IGNORE_NEW_LINES);

    // First pass, one-line comments
    // Processing is easier using a per-line approach
    $lines = array_map(function ($line) {
        if (preg_match('!^(//|#[^\[]).*$!', $line)) {
            return null;
        }
        // Prevent processing non-comment lines to be mistakenly treated as such
        // eg: context uris containing "php://" or "http://", or this regexp
        return preg_replace('!([^:(])(//|#[^\[]).*$!', '$1', $line);
    }, $lines);

    // Second pass: multi-line comments
    // At this point we can use a token approach
    $contents = implode(" ", $lines);
    $tokens = explode(" ", $contents);

    $tokens = array_filter($tokens, static function ($token) {
        static $isMultiLineComment = false;

        if ($token == '*/' && $isMultiLineComment) {
            $isMultiLineComment = false;
            return false;
        }

        if ($isMultiLineComment)
            return false;

        if (trim($token) == '/**' || trim($token) == '/*') {
            $isMultiLineComment = true;
            return false;
        }

        return true;
    });

    $text = implode(" ", $tokens);
    $text = preg_replace('/\s\s+/', ' ', $text);

    // Restore compatibility for heredoc blocks
    // NOTE: Line-breaks inside heredoc are not preserved
    preg_match("/.*<<<" . "'([A-Z]+)'/", $text, $m);
    for ($i = 1; $i < count($m); $i++) {
        $boundary = $m[$i];
        $text = str_replace("<<<'$boundary'", "<<<'$boundary'\n", $text);
        $text = str_replace("$boundary;", "\n$boundary;\n", $text);
    }

    return $text;
}

class Phar extends BuiltinPhar
{
    public $files = [];

    /**
     * Similar to Phar::addFile(), with optional minifying
     *
     * @param string  $filename
     * @param ?string $localName
     * @param bool    $minify
     */
    public function addFileContents(string $filename, string $localName = null, bool $minify = true)
    {
        $key = $localName ?? $filename;

        $this->files[] = $key;

        $contents = $minify ? php_strip_whitespace($filename) : file_get_contents($filename);

        $this[$key] = $contents;
    }

    /**
     * Check whether the given file exists in archive
     *
     * @param string $file
     *
     * @return bool
     */
    public function has(string $file): bool
    {
        return $this->offsetExists($file);
    }
}
