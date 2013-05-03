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
	
	
	public function getAvailableLanguages () {
		return $this->availableLanguages;
	}
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
		
		putenv("LANG=$locale");
		
		$tries = array("$locale.utf8", "$locale.UTF-8", $locale, "$locale.UTF8");
		
		$foundLocale = false;
		foreach($tries as $loc) {
			if (setlocale(LC_ALL, $loc)) {
				$foundLocale = true;
				break;
			}
		}
		if (!$foundLocale) {
			throw new \Exception ("Could not set LOCALE to any of (".implode(', ', $tries)."). Server doesn't seem to support this locale, see the output of 'locale -a'. Create the locale with locale-gen.");
		}
		
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
				'domain' => $domain,
				'path' => $path,
				'rootDirs' => $rootDirs,
				'charset' => $charset,
				'locale' => $locale,
				'potPath' => "{$path}/{$locale}/LC_MESSAGES"
				);
		
		$this->domains[$domain]['potFile'] = "{$this->domains[$domain]['potPath']}/{$domain}.pot";
		
		bindtextdomain($domain, $path);
		bind_textdomain_codeset($domain, $charset);
	}
	
	public function switchDomain ($domain) {
		textdomain($domain);
		$this->defaultDomain = $domain;
	}
	
	
}