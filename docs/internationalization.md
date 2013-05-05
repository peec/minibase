# Internationalization (I18n)

Internationalization hereby refered to as I18n is extremely important for those who are making products that might be used for multiple purposes or just needs to handle many languages. Minibase supports [gettext](http://php.net/gettext) by default. Gettext is the fastest i18n you can get for your PHP app and is widely used and tested. You will need to have php `gettext` plugin installed for your environment if the simple integration should work.


## Configuration

Gettext supports multiple domains, in minibase by convention we use a *unique domain per app/plugin*. You will need to setup some configuration in order to register your domains. The `$mb` object has a property named `trans`, trans is a instance of `Minibase\I18n\I18nGetText`. 

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

#### Server must support the locale.

Exception might be thrown from setLocale if the server does not support your new language. It's really easy to install new locales. 

On linux mint / ubuntu:

```bash
sudo locale-gen nb_NO
sudo locale-gen nb_NO.utf8
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
// norwegian first this should be nb_NO.
'en_GB',

// Rootdirs contains information of where to look for dgettext, gettext etc functions. It uses a type:path to use a
// specific parser based on the type. By default, Minibase includes the "php" parser. The parser will find files recursivly
// from what you specify.
array(
  // Minibase got support for php files.
  'php:' . __DIR__, 
  // Twig extension adds "twig:" support.
  'twig:' . __DIR__ . '/views'
),

// Charset  (default is UTF-8)
'UTF-8'
);
```


## Workflow

There is a specific workflow when dealing with gettext. Minibase have built in commands to make your job easier.

Say you have registered the above locale (domain `coolApp`). This app have some `dgettext` statements in the php files (both in the views and controllers forexample). Now we want to translate our app to norwegian -bokmaal (nb_NO). 


#### Extract gettext statements to POT file.

First, we generate the `POT` file. This will be used to generate PO files for new languages.

```bash
php cli.php mb:lang:extract coolApp
```


#### Generate a new language

We create our new language `PO` file, this gets stuff from the `POT` file we have generated.

```bash
php cli.php mb:lang:new-language coolApp nb_NO
```

Time to translate.

Now, we can open `locale/nb_NO/LC_MESSAGES/coolApp.po` in a text editor and translate the  `msgid` to `msgstr`. This can also be done with various of tools such as `POEdit`.


#### Compile a MO file

Now, we're finished, we translated the po file, so we compile it to machine code.

```bash
php cli.php mb:lang:compile coolApp
```


#### Updating

Ok, say we changed some statements in our php code, and added some more texts wrapped in `dgettext` statements. Now we want to  extract to POT again and do a merge to all our other PO files.


```bash
php cli.php mb:lang:extract coolApp
php cli.php mb:lang:merge coolApp
```

Now, we can go into `locale/nb_NO/LC_MESSAGES/coolApp.po` and translate and compile to MO file again.

```bash
php cli.php mb:lang:compile coolApp
```











