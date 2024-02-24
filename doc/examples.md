# PHP Code Compiler Examples

## Example 1: PHP sources located in several directories

- Add all `*.php` files from `src/` and `vendor/` dirs
- Define `main.php` as the stub main entrypoint script
- Save compiled phar executable to `bin/foobar`

```bash
phpcc -d src:php -d vendor:php -e main.php -o bin/foobar
```
## Example 2: Multiple extensions in the same directory

- Add all `*.php` and `*.phtml` files from `src/` dir
- Define `main.php` as the stub main entrypoint script
- Save compiled phar executable to `bin/foobar`

```bash
phpcc -d src:php -d src:phtml -e main.php -o bin/foobar
```

## Example 3: Standalone php script

- Define `app.php` as the stub main entrypoint script
- Save compiled phar executable to `foobar.phar`
- Use `LICENSE` file contents as legal notice banner

```bash
phpcc -e app.php -o foobar.phar -b LICENSE
```
