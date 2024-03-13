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

namespace Yannoff\PhpCodeCompiler\Command;

use Yannoff\Component\Console\Command;
use Yannoff\Component\Console\Definition\Option;
use Yannoff\Component\Console\Exception\RuntimeException;
use Yannoff\PhpCodeCompiler\Directory;
use Yannoff\PhpCodeCompiler\PharBuilder;

class Compile extends Command
{
    /**
     * @var PharBuilder
     */
    protected $builder;

    /**
     * {@inheritdoc}
     */
    public function configure()
    {
        $this
            ->setHelp('PHP Code compiler - Phar executable compiling utility')
            ->addOption('main', 'e', Option::VALUE, 'Set the PHAR stub\'s main entrypoint script')
            ->addOption('dir', 'd', Option::MULTI, 'Add directory contents ("-d $dir") optionally filtered on a specific file extension ("$dir:$extension")')
            ->addOption('file', 'f', Option::MULTI, 'Add a single file to the archive')
            ->addOption('meta', 'm', Option::MULTI, 'Add a metadata property (eg: "-m $key:$value")')
            ->addOption('output', 'o', Option::VALUE, 'Set the compiled archive output name')
            ->addOption('banner', 'b', Option::VALUE, 'Load legal notice from the given banner file')
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $banner = $this->getOption('banner') ?? '';

        $dirs = $this->getOption('dir') ?? [];
        $files = $this->getOption('file') ?? [];
        $meta = $this->getOption('meta') ?? [];

        $main = $this->require('main');
        $output = $this->require('output');

        $this
            ->initBuilder($main)
            ->addFiles($files)
            ->addDirectories($dirs)
            ->setNotice($banner)
            ->addMetadata($meta)
            ->publish($output)
            ->info('Build complete.')
        ;
    }

    /**
     * Creates & store the Phar builder instance
     *
     * @param string $main The main entrypoint script
     *
     * @return self
     */
    protected function initBuilder(string $main): self
    {
        $this->info('Initializing Phar builder...');
        $this->builder = PharBuilder::create($main);
        $this->info('Adding stub entrypoint script contents...');
        $this->addFile($main);

        return $this;
    }

    /**
     * Add files found in the directory to the builder, optionally filtered by extension
     *
     * @param string  $directory  The directory to scan for contents
     * @param ?string $extensions Filter on extension, may be "php" or "(php|phtml)"
     *
     */
    protected function addDirectory(string $directory, string $extensions = null)
    {
        $filter = ($extensions) ? sprintf('/\.%s$/', $extensions) : '';
        $files = Directory::find($directory, $filter);

        array_walk($files, function ($file) { $this->addFile($file); });
    }

    /**
     * Add a single file to the archive builder
     *
     * @param string $file A relative or absolute file path
     *
     */
    protected function addFile(string $file)
    {
        $fullpath = $this->fullpath($file);

        $this->info('+ ' . $file, 'grey');

        // Only minify pure PHP source files, other files such as
        // code templates for instance, should be left as-is
        $minify = (pathinfo($file, PATHINFO_EXTENSION) === 'php');

        $this->builder->addFile($fullpath, $file, $minify);
    }

    /**
     * Add a list of directory specifications to the archive builder
     *
     * @param string[] $dirs A list of specs in the form "$dir" or "$dir:$extension"
     *
     * @return self
     */
    protected function addDirectories(array $dirs): self
    {
        foreach ($dirs as $spec) {
            list($directory, $extensions) = explode(':', $spec);

            $wildcard = $extensions ? "*.$extensions" : 'all';
            $this->info("Scanning directory <strong>$directory</strong> for <strong>$wildcard</strong> files...");

            $this->addDirectory($directory, $extensions);
        }

        return $this;
    }

    /**
     * Add a list of single files to the archive builder
     *
     * @param string[] $files A list of relative or absolute file paths
     *
     * @return self
     */
    protected function addFiles(array $files): self
    {
        foreach ($files as $file) {
            $this->info("Adding single file <strong>$file</strong>...");
            $this->addFile($file);
        }

        return $this;
    }

    /**
     * Add a list of metadata properties to the archive builder
     *
     * @param string[] $definitions A list of $key:$value pairs
     *
     * @return self
     */
    protected function addMetadata(array $definitions): self
    {
        foreach ($definitions as $definition) {
            list($name, $value) = explode(':', $definition);
            $this->info("Adding <strong>$name</strong> metadata property");
            $this->info("-> $name: $value", 'grey');
            $this->builder->addMetadata($name, $value);
        }

        return $this;
    }

    /**
     * Add banner file contents to the archive builder
     *
     * @param ?string $banner Path to the banner file
     *
     * @return self
     */
    protected function setNotice(string $banner = null): self
    {
        if (is_file($banner)) {
            $this->info("Loading banner contents from <strong>$banner</strong> file...");
            $contents = file_get_contents($banner);
            $header = $this->phpdocize($contents);

            $this->info($header, 'grey');
            $this->builder->setBanner($header);
        }

        return $this;
    }

    /**
     * Compile the Phar archive and write contents to disk
     *
     * @param string $output      Path to the phar archive output file
     * @param string $compression Compression type - "GZ" or "BZ2"
     *
     * @return self
     */
    protected function publish(string $output, string $compression = 'GZ'): self
    {
        $this->info("Writing Phar archive to <strong>$output</strong>...");
        $this->builder->compile($output, $compression);

        return $this;
    }

    /**
     * Get the full path to the file from the current working dir
     *
     * @param string $file
     *
     * @return string
     */
    protected function fullpath(string $file): string
    {
        // If it's an absolute path, let it unchanged
        if (realpath($file) === $file) {
            return $file;
        }

        return getcwd() . '/' . $file;
    }

    /**
     * Try to get the required option, raise an exception if not set
     *
     * @param string $option The option name
     *
     * @return mixed
     *
     * @throws RuntimeException If the required option is not set
     */
    protected function require(string $option)
    {
        $value = $this->getOption($option);

        if (null === $value) {
            throw new RuntimeException("Mandatory option --{$option} is missing");
        }

        return $value;
    }

    /**
     * Return the contents wrapped in a comments block
     *
     * @param string $contents
     *
     * @return string
     */
    protected function phpdocize(string $contents): string
    {
        $lines = array_map(
            function($line) { return sprintf(' * %s', $line); },
            explode("\n", $contents)
        );

        array_unshift($lines, '/**');
        array_push($lines, ' */');

        return implode("\n",$lines);
    }

    /**
     * Print a message to STDERR, optionally encapsuled by styling tags
     *
     * @param string $message
     * @param string $tag
     *
     * @return self
     */
    protected function info(string $message, string $tag = ''): self
    {
        $otag = $tag ? "<$tag>" : '';
        $ctag = $tag ? "</$tag>" : '';
        $this->error(sprintf('%s%s%s', $otag, $message, $ctag));

        return $this;
    }
}
