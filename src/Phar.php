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

    /**
     * Check whether the script is run from inside a Phar
     *
     * @return bool
     */
    static public function runsInPhar()
    {
        @list($protocol, $uri) = explode('://', __DIR__);

        return $protocol == 'phar';
    }
}
