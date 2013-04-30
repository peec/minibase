<?php
namespace Minibase\I18n;

/**
 * Must have 'gettext' extension for php.
 * This class allows i18n for Minibase apps.
 * 
 * @author Petter Kjelkenes <kjelkenes@gmail.com>
 * 
 */
class I18nGetText {
	
	/**
	 * The current locale.
	 * @var string
	 */
	public $locale;
	
	public $defaultDomain;
	
	public $domains = array();

	public $availableLanguages = array();
	
	
	/**
	 * Sets the available languages.
	 * @param array $availableLanguages
	 */
	public function setAvailableLanguages(array $availableLanguages) {
		$this->availableLanguages = $availableLanguages;
	}
	
	
	public function getDomains () {
		return $this->domains;
	}
	
	/**
	 * Sets the new locale
	 * @param string $locale The locale, example en_GB , en_US etc.
	 */
	public function setLocale ($locale) {
		if (!in_array($locale, $this->availableLanguages)) {
			throw new \Exception("$locale is not in the available languages.");
		}
		
		putenv('LC_ALL=' . $locale);
		setlocale(LC_ALL, $locale);
		$this->locale = $locale;
	}
	
	/**
	 * Loads a new domain.
	 * @param string $domain The unique domain name. A plugin should have its own.
	 * @param string $path Path to the locale folder
	 * @param string $locale The default locale that the scripts are written in.
	 * @param string $rootDirs Where to extract strings from, normally the app/plugin Path.
	 * @param string $charset The charset of the lang file, default is UTF-8.
	 */
	public function load ($domain, $path, $locale, $rootDirs = array(), $charset='UTF-8') {
		if (!is_dir($path)) {
			throw new \Exception ("$path is not a valid directory. The directory must be created and follow the gettext structure with LC_MESSAGES and proper locale / domain structure of po/mo files.");
		}
		$this->domains[$domain] = array(
				'path' => $path,
				'rootDirs' => $rootDirs,
				'charset' => $charset,
				'locale' => $locale
				);
		
		bindtextdomain($domain, $path);
		bind_textdomain_codeset($domain, $charset);
	}
	
	public function switchDomain ($domain) {
		textdomain($domain);
		$this->defaultDomain = $domain;
	}
	
	
}