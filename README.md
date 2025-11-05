# yannoff/phpcc

PHP Code compiler - Phar executable compiling utility

## Help Contents

- [Usage](#usage)
    - [Synopsis](#synopsis)
    - [Options/Arguments](#optionsarguments)
    - [Examples](#examples)
        - [A concrete use-case: the `phpcc` self-compiling command](#a-concrete-use-case-the-phpcc-self-compiling-command)
        - [Example 1: PHP sources located in several directories](#example-1-php-sources-located-in-several-directories)
        - [Example 2: Multiple extensions in the same directory](#example-2-multiple-extensions-in-the-same-directory)
        - [Example 3: Standalone php script](#example-3-standalone-php-script)
        - [Example 4: Add sparse single PHP files](#example-4-add-sparse-single-php-files)
        - [Example 5: Adding metadata to the archive](#example-5-adding-metadata-to-the-archive)
- [Install](#install)
    - [Requirements](#requirements)
    - [Quick install](#quick-install)
    - [Github Action](#github-action)
- [License](#license)

## Usage

### Synopsis

```
phpcc --help
phpcc --version
```

```
phpcc \
    -e <main> \
    -o <output> \
    [-d <dir> [-d <dir> ...]] \
    [-f <file> [-f <file> ...]] \
    [-b <banner>] \
    [-m <metadata> [-m <metadata> ...]]
    [--debug]
```

### Options/Arguments

> The output and entrypoint scripts are mandatory.

Name /  Shorthand   |  Type | Description                                                                                                                                                                             |Required
--------------------|:-----:|-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|:-------:
`--output`, `-o`    | value | The Phar archive output file                                                                                                                                                            |y
`--main`, `-e`      | value | The main application entrypoint script                                                                                                                                                  |y
`--banner`, `-b`    | value | Specify the filepath to the legal notice banner<br/>_Will be included in the human-readable part of the stub._                                                                          |n
`--file`, `-f`      | multi | Adds a single file to the archive                                                                                                                                                       |n
`--dir`,  `-d`      | multi | Adds a sources directory to the archive<br/>_Possible dir spec formats:<br/>- `$dir` => include all files in directory<br/>- `$dir:$extension` => filter files on a specific extension_ |n
`--meta`, `-m`      | multi | Adds a metadata to the archive<br/>_Metadata must be specified in the `$key:$value` format_                                                                                             |n
`--no-minify`, `-n` | flag  | Don't minify PHP source files<br/>_Useful for debugging the compiled executable in case of runtime errors_                                                                              |n
`--shebang-less`    | flag  | Produce a stub deprived of the shebang directive<br/>_Useful when the phar is meant to be included instead of being executed directly_                                                  |n
`--debug`           | flag  | Turn on debug mode<br/>*Set php error reporting level to `E_ALL` at compilation time*                                                                                                   |n
`--quiet`, `-q`     | flag  | Reduce output messages amount: set verbosity level to `INFO` instead of default `DEBUG`                                                                                                 |n


### Examples

#### A concrete use-case: the `phpcc` self-compiling command

```bash
phpcc -d src:php -d vendor:php -e bin/compile.php -o bin/phpcc -b .banner
```

#### Example 1: PHP sources located in several directories

- Add all `*.php` files from `src/` and `vendor/` dirs
- Define `main.php` as the stub main entrypoint script
- Save compiled phar executable to `bin/foobar`

```bash
phpcc -d src:php -d vendor:php -e main.php -o bin/foobar
```
#### Example 2: Multiple extensions in the same directory

- Add all `*.php` and `*.phtml` files from `src/` dir
- Define `main.php` as the stub main entrypoint script
- Save compiled phar executable to `bin/foobar`

```bash
phpcc -d src:php -d src:phtml -e main.php -o bin/foobar
```

#### Example 3: Standalone php script

- Define `app.php` as the stub main entrypoint script
- Save compiled phar executable to `foobar.phar`
- Use `LICENSE` file contents as legal notice banner

```bash
phpcc -e app.php -o foobar.phar -b LICENSE
```

#### Example 4: Add sparse single PHP files

- Define `app.php` as the stub main entrypoint script
- Save compiled phar executable to `foobar.phar`
- Add `foo.php` and `bar.php` files to the archive

```bash
phpcc -e app.php -o foobar.phar -f foo.php -f bar.php
```

#### Example 5: Adding metadata to the archive

- Define `app.php` as the stub main entrypoint script
- Save compiled phar executable to `bin/acme`
- Add the `license` & `author` metadata to the archive

```bash
phpcc -e app.php -o bin/acme -m license:MIT -m author:yannoff
```

## Install

### Requirements

- `php` or `paw` 7.1+
- `phar.readonly` php config directive must be set to `Off`

### Quick install

_Get the latest release from Github_

> :bulb: `${BINDIR}` may be /usr/bin, /usr/local/bin or $HOME/bin

```bash
curl -Lo ${BINDIR}/phpcc https://github.com/yannoff/phpcc/releases/latest/download/phpcc
```
_Add execution permissions to the binary_

```bash
chmod +x ${BINDIR}/phpcc
```

### Github Action

A [github action](actions/install/action.yaml) is available for integration in CI scripts.

The action will install PHP (in the desired version), and the `phpcc` binary.

Synopsis: use `yannoff/phpcc/actions/install@<release>`.

#### Integration example

_Installing phpcc version 1.2.4 / PHP 8.0_

```yaml
# ...
jobs:
    job1:
        steps:
            - name: Checkout repository
              uses: actions/checkout@v4

            - name: Install PHP & PHPCodeCompiler
              uses: yannoff/phpcc/actions/install@1.2.4
              with:
                  php-version: 8.0

            - name: Compile ACME application
              run: phpcc -q -e bin/acme.php -d src/Acme:php -d vendor/ -f LICENSE -o bin/acme

            - name: Smoke test (show version)
              run: bin/acme --version
```

## License

Licensed under the [MIT License](LICENSE).
