# Minibase



## Configuration


### Set view path

By default there is no default view path where your views are stored when using the `HtmlResponse`, you can set a view path like so:

```php
$mb->setConfig(MB::CFG_VIEWPATH, __DIR__ . '/views/');
```
