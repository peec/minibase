# Command Line

Minibase implements Symfony Console interface.

You will need to create a php file that starts Minibase in CLI mode.

## Creating a cli.php file


Create a file named `cli.php`

```php
require(__DIR__ . "/app.php"); // Your app file that includes autoloader and configureMB function.
$mb = MB::cli();
configureMB($mb); // Just a function that configures $mb, so that MB::create() can use the same configuration.

$mb->start();
```

Now you can try the interface:

```bash
php cli.php
```

## Extend

It's possible to extend the cli interface by creating plugins for Minibase. See Events chapter. The event is `mb:console`.
