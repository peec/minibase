# Assetic

Minibase includes an optional plugin -[Assetic](https://github.com/kriswallsmith/assetic) **RECOMMENDED**. With assetic you don't have to wory about compiling with 3rdparty software, the `AsseticPlugin` includes command-line tools to dump your assets with only one command.


## Initialize the Minibase Assetic Plugin.

Before you can use assetic, you must add the plugin using either app.json method or raw php:

In this case, we also register two filters used for css (rewrite and compressor).


**PHP:**

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

**JSON:**

```json
"plugins": [
                {
                        "name": "Minibase/Assetic/AsseticPlugin",
                        "config": {
                                "rootDir": "${APP_DIR}/../www",
                                "filters": {
                                        "cssrewrite": {
                                                "filter": "Assetic/Filter/CssRewriteFilter"
                                        },
                                        "yui_css": {
                                          "filter": "Assetic/Filter/Yui/CssCompressorFilter",
                                          "args": ["/path/to/yui_compressor.jar"]
                                        }
                                }
                        }
                }
]

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




## Dumping assets

You will need to dump your assets the first time and after changing assets, this is done with the `mb:assetic:dump` command.

Run the cli.php:

```bash
php cli.php mb:assetic:dump
```

Assets will then be written to directory based on the asset configuration defined in your views.

```
Writing assets to build/css/all.css
---- assets/css/style2.css >> build/css/all_part_1_style2_1.css
---- assets/css/style.css >> build/css/all_part_1_style_2.css
Wrote compiled assets to /home/peec/projects/minibase-sample/app/../www.
```



