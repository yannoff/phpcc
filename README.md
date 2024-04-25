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
- [Pitfalls](#pitfalls)
    - [Shebang line in main script](#shebang-line-in-main-script)
    - [Local versus compiled files: missing file](#local-versus-compiled-files-missing-file)
    - [Local versus compiled files: name collision](#local-versus-compiled-files-name-collision)
    - [Size too big](#size-too-big)
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
    compile:
        name: Compile source files
        runs-on: ubuntu-latest
        steps:
            - name: Checkout repository
              uses: actions/checkout@v4

            - name: Install PHP & PHPCodeCompiler
              uses: yannoff/phpcc/actions/install@1.2.4
              with:
                  php-version: 8.0

            - name: Install dependencies
              run: composer install --no-dev --optimize-autoloader

            - name: Create virtual version
              run: echo $(date +"%Y-%m-%d %H:%M:%S") > ./version

            - name: Compile sources
              run: php -d phar.readonly=0 /usr/local/bin/phpcc -e bin/acme.php -d src -d vendor -f version -o bin/acme --quiet

            - name: Smoke test (show version)
              run: bin/acme --version
```

## Pitfalls

Here is a (non-exhaustive) list of the most common mistakes related to PHAR compiling.

### Shebang line in main script

Since the main (entrypoint) script will be **included** in the PHAR stub, it must not contain any shebang line, otherwise this line will be treated as text and printed to standard output when invoking the compiled PHAR.

_Example: Invoking a version of `phpcc` compiled with a shebang line in `bin/compile.php`_

```
$ bin/phpcc --version
#!/usr/bin/env php
PHP Code Compiler version 1.3.0-dev
```

### Local versus compiled files: missing file

Let's consider the following tree (all files required by the app)

```
bin/acme.php
src/Command/Acme.php
src/Command/SomeClass.php
lib/Ufo.php
```

Compile it (Oops... one Unknown File Object has not been included)

```
phpcc -e bin/acme.php -f bin/acme.php -d src/ -o bin/acme
```

#### Problem

Launching the `bin/acme` compiled archive should raise an error because of the missing file.

Well...not. What happens here then ?

If the `bin/acme` compiled archive stays in its place,the `lib/Ufo.php` can still be found from its point of view.

#### Solution

Always move the compiled executable **out** of the project's working directory before testing it.


### Local versus compiled files: name collision

Eg: 

```php
require "vendor/autoload.php"
```

Chances are, there might be such a `vendor/autoload.php` file in the project to be compiled.

#### Problem

From the compiled app point of view, `vendor/autoload.php` refers to a relative path in the PHAR archive.

#### Workaround

The phpcc execution dir (i.e the compiled project's top directory) must be added first in the include path.

For example in the main entrypoint script:

```php
// bin/main.php

// ensure we load the execution directory's autoload, not the
// one included in the compiled phar executable
set_include_path(getcwd() . PATH_SEPARATOR . get_include_path());

require 'vendor/autoload.php';

// ...
```

### Size too big

Many projects include some dev libraries, for unit test, local data seeding or code inspection.

Fact is, some of those libs have **A LOT** of dependencies... Hence the `vendor` directory, which is usually included in the archive is really **HUGE**.

Q: How to remediate then ?

A: Before compiling, ensure the `vendor` directory does not contains any dev library:

```
composer install --no-dev
phpcc -e bin/acme.php -f bin/acme.php -d src/:php -d vendor:php -d vendor:yaml -o bin/acme
```


## License

Licensed under the [MIT License](LICENSE).
