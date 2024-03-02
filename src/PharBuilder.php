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

use LogicException;

/**
 * Phar archive builder class
 */
class PharBuilder
{
    /**
     * End-of-line character
     *
     * @var string
     */
    const EOL = "\n";

    /**
     * Property holding the PHAR object instance
     *
     * @var Phar
     */
    protected $archive;

    /**
     * Temporary name of the PHAR archive
     *
     * @var string
     */
    protected $pharname;

    /**
     * The stub's main entrypoint script filepath
     *
     * @var string
     */
    protected $main;

    /**
     * Optional banner/legal notice
     *
     * @var string
     */
    protected $banner;

    /**
     * PharBuilder factory method
     *
     * @param string $main Path to the main entrypoint script
     *
     * @return self
     */
    public static function create(string $main): self
    {
        return new static($main);
    }

    /**
     * Class constructor
     * Visibility is defined private to force using the factory method
     *
     * @param string $main Path to the main entrypoint script
     */
    private function __construct(string $main)
    {
        $this->pharname = uniqid() . '.phar';
        $this->main = $main;

        $this->init();
    }

    /**
     * Create the Phar archive instance and add the main entrypoint script
     *
     * @return self
     */
    public function init(): self
    {
        $this->archive = new Phar($this->pharname);
        $this->archive->startBuffering();

        return $this;
    }

    /**
     * Setter for the banner contents
     *
     * @param string $banner
     *
     * @return self
     */
    public function setBanner(string $banner): self
    {
        $this->banner = $banner;

        return $this;
    }

    /**
     * Compress files, generate the stub and save PHAR to the output file
     *
     * @param string $output      Path to the final output file
     * @param string $compression Compression type - "GZ" or "BZ2" (defaults to "GZ")
     */
    public function compile(string $output, string $compression = 'GZ')
    {
        // Check that entrypoint script contents has been added to the archive before proceeding
        if (!$this->archive->has($this->main)) {
            throw new LogicException("Main script {$this->main} contents must be added to the archive");
        }

        $c = constant('Phar::' . $compression);
        $this->archive->compressFiles($c);

        // NB: It's important to set the stub AFTER the files compression step so it is kept as plain text
        $this->archive->setStub($this->stub($this->main, $this->banner));
        // Create file on the disk
        $this->archive->stopBuffering();
        // Make file executable
        chmod($this->pharname, 0755);
        //
        rename($this->pharname, $output);
    }

    /**
     * Get the list of the archive files
     *
     * @return array
     */
    public function list(): array
    {
        return $this->archive->files ?? [];
    }

    /**
     * Generate the PHAR stub definition contents
     *
     * @param string  $main   The main entrypoint script
     * @param ?string $banner Optional legal notice text
     *
     * @return string
     */
    protected function stub(string $main, string $banner = null): string
    {
        $lines = [
            '#!/usr/bin/env php',
            '<?php',
        ];
        if ($banner) {
            $lines[] = $banner;
        }
        $lines[] = sprintf('// Compiled with PHP version %s', PHP_VERSION);
        $lines[] = sprintf('Phar::mapPhar("%s");', $this->pharname);
        // Add support for builtin phar flavoured require "vendor/autoload.php"
        // while still allowing the use of absolute path based requires
        // @see https://bugs.php.net/bug.php?id=63028
        $lines[] = sprintf('set_include_path("phar://%s/");', $this->pharname);
        $lines[] = sprintf('require "phar://%s/%s"; __HALT_COMPILER();', $this->pharname, $main);

        return implode(self::EOL, $lines);
    }

    /**
     * Add a single file to the archive, optionally minified
     *
     * @param string  $file   Path to the file
     * @param ?string $local  Optional file alias
     * @param bool    $minify Whether comments/spaces should be removed from contents
     */
    public function addFile(string $file, string $local = null, bool $minify = true)
    {
        $this->archive->addFileContents($file, $local, $minify);

        return $this;
    }
}
