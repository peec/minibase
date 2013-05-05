# Assetic

Minibase includes an optional plugin -[Assetic](https://github.com/kriswallsmith/assetic) **RECOMMENDED**. With assetic you don't have to wory about compiling with 3rdparty software, the `AsseticPlugin` includes command-line tools to dump your assets with only one command.


## Initialize the Minibase Assetic Plugin.

Before you can use assetic, you must add the plugin using either app.json method or raw php:

In this case, we also register two filters used for css (rewrite and compressor).

```php
$mb->initPlugins(array(
  'Minibase/Assetic/AsseticPlugin' => array(
    'rootDir' => __DIR__ . '/../www',
    'filters' => array(
      'cssrewrite' => array(
        'filter' => 'Assetic/Filter/CssRewriteFilter'
      ),
      'yui_css' => array(
        'filter' => 'Assetic/Filter/Yui/CssCompressorFilter',
        'args' => ["/path/to/yui_compressor.jar"]
      )
    )
  )
));
```



## Assetic functions in the view

You can call assetic functions in php views, here are these functions. Note the [minibase-plugin-twig](http://github.com/peec/minibase-plugin-twig) integrates the `Assetic Twig Plugin` aswell.


```php
/**
 * Returns an array of javascript URLs.
 *
 * @param array|string $inputs  Input strings
 * @param array|string $filters Filter names
 * @param array        $options An array of options
 *
 * @return array An array of javascript URLs
 */
function assetic_javascripts($inputs = array(), $filters = array(), array $options = array());

/**
 * Returns an array of stylesheet URLs.
 *
 * @param array|string $inputs  Input strings
 * @param array|string $filters Filter names
 * @param array        $options An array of options
 *
 * @return array An array of stylesheet URLs
 */
function assetic_stylesheets($inputs = array(), $filters = array(), array $options = array());

/**
 * Returns an image URL.
 *
 * @param string       $input   An input
 * @param array|string $filters Filter names
 * @param array        $options An array of options
 *
 * @return string An image URL
 */
function assetic_image($input, $filters = array(), array $options = array());

```



