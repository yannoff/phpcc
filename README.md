# yannoff/phpcc

PHP Code compiler - Phar executable compiling utility

## Requirements

- `php` or `paw` 7.1+
- `phar.readonly` php config directive must be set to `Off`

## Install

_Get the latest release from Github_

> :bulb: `${BINDIR}` may be /usr/bin, /usr/local/bin or $HOME/bin

```bash
curl -Lo ${BINDIR}/phpcc https://github.com/yannoff/phpcc/releases/latest/download/phpcc
```
_Add execution permissions to the binary_

```bash
chmod +x ${BINDIR}/phpcc
```

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
    [-d dir [-d dir ...]] \
    [-f file [-f file ...]] \
    [-b <banner>]
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


### Examples

#### A concrete use-case: the `phpcc` self-compiling command

```bash
phpcc -d src:php -d vendor:php -e bin/compile.php -o bin/phpcc -b .banner
```
_More use cases can be found in the [examples](doc/examples.md) documentation._

## License

Licensed under the [MIT License](LICENSE).
