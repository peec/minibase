# Internationalization (I18n)

Internationalization hereby refered to as I18n is extremely important for those who are making products that might be used for multiple purposes or just needs to handle many languages. Minibase supports [gettext](http://php.net/gettext) by default. Gettext is the fastest i18n you can get for your PHP app and is widely used and tested. You will need to have php `gettext` plugin installed for your environment if the simple integration should work.


## Configuration

Gettext supports multiple domains, in minibase by convention we use a unique domain per app/plugin. You will need to setup some configuration in order to register your domains. The `$mb` object has a property named `trans`, trans is a instance of `Minibase\I18n\I18nGetText`. 

Note that it's also possible to configure i18n with a JSON file.


### Setting available languages

First, for your application you would need to set what languages that you want to be available for your application:

```php
$mb->trans->setAvailableLanguages(array('en_GB', 'nb_NO'));
```

### Set the language to use.

Minibase wants to know what language to use (if it exists), else some other language is used.

```php
$mb->trans->setLocale('en_GB');
```

### Register a new domain

Minibase wants to know some information about the `domain` you are going to register for your i18n configuration. Note that there are built in commands that also use this information, thereby we need all this information:


This is one method call, but we explain each argument with comments.

```php
// Load a new domain to gettext.
$mb->trans->load(

// The domain name to use, short is good, you will use this everytime you need to get a lang var.
'coolApp',

// Path to where to store the language files (you don't have to create the language files yourself.)
__DIR__ . '/locale', 

// The locale that you will be using first. Normally you might use english, but if you create your app in forexample
// norwegian you must change this var.
'en_GB',

// Rootdirs contains information of where to look for dgettext, gettext etc functions. It uses a type:path to use a
// specific parser based on the type. By default, Minibase includes the "php" parser. The parser will find files recursivly
// from what you specify.
array(
  'php:' . __DIR__, 
  // Twig extension adds "twig:" support.
  'twig:' . __DIR__ . '/views'
),

// Charset  (default is UTF-8)
'UTF-8'
);
```


### Update / create .pot file.

When you want to translate your app, there is a standard workflow, it gets tendious without some tools. Minibase provides your with a commandline tool that helps you (`mb:generate-pot`).

```bash
php cli.php mb:generate-pot
```

This command will extract the pot files to your plugins / your app in the correct folders.






