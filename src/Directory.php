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

class Directory
{
    /**
     * Return the full list of files in the directory, optionally filtered by a REGEXP pattern
     *
     * @param string  $directory
     * @param ?string $pattern
     *
     * @return array
     */
    public static function find(string $directory, string $pattern = null): array
    {
        $files = [];

        foreach (self::scan($directory) as $value) {
            $path = sprintf('%s/%s', rtrim($directory, '/'), $value);
            if (is_file($path)) {
                if (preg_match($pattern ?: '/.*/', $path)) {
                    $files[] = $path;
                }
                continue;
            }
            $files = array_merge($files, self::find($path, $pattern));
        }

        return $files;
    }

    /**
     * Perform a scandir() and remove "dots" dirs from results
     *
     * @param string $directory
     *
     * @return array
     */
    public static function scan(string $directory): array
    {
        return array_filter(
            scandir($directory),
            function ($name) { return !in_array($name, ['.', '..']); }
        );
    }
}
