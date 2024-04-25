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
- [Pitfalls](#pitfalls)
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

### Local versus compiled files

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

Guess what ?

If the `bin/acme` compiled archive stays in its place, it won't fail, because `lib/Ufo.php` can still be found from its point of view.

### Size too big

Many projects include some dev libraries, for unit test, local data seeding or code inspection.

Fact is, some of those libs have **A LOT** of dependencies... Hence the `vendor` directory, which is usually included in the archive is really **HUGE**.

Q: How do we remediate then ?

A: Before compiling, we ensure the `vendor` directory does not contains any dev library:

```
composer install --no-dev
phpcc -e bin/acme.php -f bin/acme.php -d src/:php -d vendor:php -d vendor:yaml -o bin/acme
```


## License

Licensed under the [MIT License](LICENSE).
