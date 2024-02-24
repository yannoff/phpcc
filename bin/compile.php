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

error_reporting(E_ALL);

$VERSION = '1.0.0';

/**
 * BEWARE:
 *
 * While the home-made Phar::addDirectory() method accepts both absolute or
 * relative-to-content-root paths here, this is not the case when directories
 * were added via the PHP builtin Phar::buildFromDirectory() method.
 *
 * The builtin Phar engine only recognize the latter, ie: require 'vendor/autoload.php';
 *
 * @see https://bugs.php.net/bug.php?id=63028
 */
require 'vendor/autoload.php';

use Yannoff\Component\Console\Application;
use Yannoff\PhpCodeCompiler\Command\Compile;

$app = new Application('PHP Code Compiler', $VERSION, (new Compile()));
$app->run();
