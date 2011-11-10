<?php
/*
* 2007-2011 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2011 PrestaShop SA
*  @version  Release: $Revision$
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

class InstallLanguages
{
	const DEFAULT_ISO = 'en';
	/**
	 * @var array List of available languages
	 */
	protected $languages;

	/**
	 * @var string Current language
	 */
	protected $language;

	/**
	 * @var InstallLanguage Default language (english)
	 */
	protected $default;

	protected static $_instance;

	public static function getInstance()
	{
		if (!self::$_instance)
			self::$_instance = new self();
		return self::$_instance;
	}

	public function __construct()
	{
		// English language is required
		if (!file_exists(_PS_INSTALL_LANGS_PATH_.'en/language.xml'))
			throw new PrestashopInstallerException('English language is missing');

		$this->languages = array(
			self::DEFAULT_ISO => new InstallLanguage(self::DEFAULT_ISO),
		);

		// Load other languages
		foreach (scandir(_PS_INSTALL_LANGS_PATH_) as $lang)
			if ($lang[0] != '.' && is_dir(_PS_INSTALL_LANGS_PATH_.$lang) && $lang != self::DEFAULT_ISO)
				$this->languages[$lang] = new InstallLanguage($lang);
	}

	/**
	 * Set current language
	 *
	 * @param string $iso Language iso
	 */
	public function setLanguage($iso)
	{
		if (!in_array($iso, $this->getIsoList()))
			throw new PrestashopInstallerException('Language '.$iso.' not found');

		$this->language = $iso;
	}

	/**
	 * Get current language
	 *
	 * @return string
	 */
	public function getLanguageIso()
	{
		return $this->language;
	}

	/**
	 * Get current language
	 *
	 * @return InstallLanguage
	 */
	public function getLanguage($iso = null)
	{
		if (!$iso)
			$iso = $this->language;
		return $this->languages[$iso];
	}

	public function getIsoList()
	{
		return array_keys($this->languages);
	}

	/**
	 * Get list of languages iso supported by installer
	 *
	 * @return array
	 */
	public function getLanguages()
	{
		return $this->languages;
	}

	/**
	 * Get translated string
	 *
	 * @param string $str String to translate
	 * @param ... All other params will be used with sprintf
	 * @return string
	 */
	public function l($str)
	{
		$args = func_get_args();
		$translation = $this->getLanguage()->getTranslation($args[0]);
		if (is_null($translation))
		{
			$translation = $this->getLanguage(self::DEFAULT_ISO)->getTranslation($args[0]);
			if (is_null($translation))
				$translation = $args[0];
		}

		$args[0] = $translation;
		return call_user_func_array('sprintf', $args);
	}

	/**
	 * Get an information from language (phone, links, etc.)
	 *
	 * @param string $key Information identifier
	 */
	public function getInformation($key, $with_default = true)
	{
		$information = $this->getLanguage()->getTranslation($key, 'informations');
		if (is_null($information) && $with_default)
			return $this->getLanguage(self::DEFAULT_ISO)->getTranslation($key, 'informations');
		return false;
	}

	/**
	 * Get list of countries for current language
	 *
	 * @return array
	 */
	public function getCountries()
	{
		static $countries = null;

		if (is_null($countries))
		{
			$countries = array();
			$countries_lang = $this->getLanguage()->getCountries();
			$countries_default = $this->getLanguage(self::DEFAULT_ISO)->getCountries();
			$xml = simplexml_load_file(_PS_INSTALL_DATA_PATH_.'xml/country.xml');
			foreach ($xml->entities->country as $country)
			{
				$iso = strtolower((string)$country['iso_code']);
				$countries[$iso] = isset($countries_lang[$iso]) ? $countries_lang[$iso] : $countries_default[$iso];
			}
		}

		return $countries;
	}

	public function getFixtureTranslation($iso, $key)
	{
		$translation = $this->getLanguage($iso)->getFixtureTranslation($key);
		if (is_null($translation))
			$translation = $this->getLanguage(self::DEFAULT_ISO)->getFixtureTranslation($key);
		return $translation;
	}
}
