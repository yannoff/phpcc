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
     * Add the contents of a directory in the archive
     *
     * @param string $directory The directory to be included
     * @param string $filter    Optional filter on file extensions
     *
     * @return self
     */
    public function addDirectory(string $directory, string $filter = ''): self
    {
        $files = $this->find($directory, $filter);
        array_walk($files, function ($file) { $this->addFileContents($file); });

        return $this;
    }

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
     * Return the full list of files in the directory, optionally filtered by a REGEXP pattern
     *
     * @param string  $directory
     * @param ?string $pattern
     *
     * @return array
     */
    public function find(string $directory, string $pattern = null): array
    {
        $files = [];
        $root = array_filter(scandir($directory), function ($value) { return !in_array($value, ['.', '..']); });

        foreach ($root as $value) {
            $path = sprintf('%s/%s', rtrim($directory, '/'), $value);
            if (is_file($path)) {
                if (preg_match($pattern ?: '/.*/', $path)) {
                    $files[] = $path;
                }
                continue;
            }
            $files = array_merge($files, $this->find($path, $pattern));
        }

        return $files;
    }
}
