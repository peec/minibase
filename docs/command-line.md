# Command Line

Minibase implements Symfony Console interface.

You will need to create a php file that starts Minibase in CLI mode.

## Creating a cli.php file


Create a file named `cli.php`

```php
require __DIR__ . '/vendor/autoload.php';
$mb = Minibase\MB::cli()->loadConfigFile(__DIR__ . '/app/app.json', __DIR__ . '/app');
$mb->start();
```

Now you can try the interface:

```bash
php cli.php
```

## Extend

It's possible to extend the cli interface by creating plugins for Minibase. See Events chapter. The event is `mb:console`.
