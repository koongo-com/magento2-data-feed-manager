<?php
/**
 * Magento Module developed by NoStress Commerce
 *
 * NOTICE OF LICENSE
 *
 * This program is licensed under the Koongo software licence (by NoStress Commerce).
 * With the purchase, download of the software or the installation of the software
 * in your application you accept the licence agreement. The allowed usage is outlined in the
 * Koongo software licence which can be found under https://docs.koongo.com/display/koongo/License+Conditions
 *
 * Any modification or distribution is strictly forbidden. The license
 * grants you the installation in one application. For multiuse you will need
 * to purchase further licences at https://store.koongo.com/.
 *
 * See the Koongo software licence agreement for more details.
 * @copyright Copyright (c) 2017 NoStress Commerce (http://www.nostresscommerce.cz, http://www.koongo.com/)
 *
 */

/**
* Class for taxonomy setup
*
* @category Nostress
* @package Nostress_Koongo
*
*/

namespace Nostress\Koongo\Model\Taxonomy;

class Setup extends \Nostress\Koongo\Model\AbstractModel
{
    const COL_NAME = 'name';
    const COL_CODE = 'code';
    const COL_TYPE = 'type';
    const COL_SETUP = 'setup';

    const ALL_LOCALES = 'all';

    const DOWNLOAD = 'download';
    const COMMON = 'common';
    const LOCALE = 'locale';
    const DELIMITER = 'delimiter';
    const VARIABLE = 'variable';
    const DEFAULT_LOCALE = 'default';
    const LOCALE_OPTIONS = 'options';
    const TRANSLATE = 'rewrite';
    const CATEGORY_PATH_DELIMITER = 'category_path_delimiter';

    const SRC = 'src';
    //const PATH = 'path';
    const FILENAME = 'filename';
    const DEFAULT_LOCALE_DELIMITER = "_";

    /**
     * Preprocessed config item
     * @var unknown_type
     */
    protected $_prepocessedConfig;

    public function _construct()
    {
        $this->_init('Nostress\Koongo\Model\ResourceModel\Taxonomy\Setup');
    }

    public function getTaxonomyByCode($code)
    {
        $collection = $this->getCollection();
        $collection->addFieldToFilter(self::COL_CODE, $code);
        $collection->getSelect();
        $collection->load();
        foreach ($collection as $item) {
            return $item;
        }
        return null;
    }

    public function getDefaultLocale()
    {
        $config = $this->prepareTaxonomyConfig();
        $defaultLocale = self::ALL_LOCALES;
        if (isset($config[self::ALL_LOCALES][self::DOWNLOAD][self::DEFAULT_LOCALE])) {
            $defaultLocale = $config[self::ALL_LOCALES][self::DOWNLOAD][self::DEFAULT_LOCALE];
        }
        return $defaultLocale;
    }

    public function getAvailableLocales()
    {
        $config = $this->prepareTaxonomyConfig();
        $locales = array_keys($config);
        if (isset($config[self::ALL_LOCALES][self::DOWNLOAD][self::LOCALE_OPTIONS])) {
            $localeString = $config[self::ALL_LOCALES][self::DOWNLOAD][self::LOCALE_OPTIONS];
            $locales = explode(",", $localeString);
        }
        return $locales;
    }

    public function getCategoryPathDelimiter()
    {
        $config = $this->prepareTaxonomyConfig();
        $delimiter = " > ";
        $decodedSetup = $this->getDecodedSetup();

        $common = $this->getArrayItem($decodedSetup, self::COMMON, null, true);
        $sourceConfig = $this->getArrayItem($common, self::SRC, []);
        $confDelimiter = $this->getArrayItem($sourceConfig, self::CATEGORY_PATH_DELIMITER);

        if (!empty($confDelimiter)) {
            $delimiter = $confDelimiter;
        }
        return $delimiter;
    }

    protected function getDecodedSetup()
    {
        $setup = $this->getSetup();
        $setup = $this->helper->dS($setup);
        $setup = $this->helper->stringToXml($setup);
        $setup = $this->helper->XMLnodeToArray($setup);
        return $setup;
    }

    protected function prepareTaxonomyConfig()
    {
        if (!isset($this->_prepocessedConfig)) {
            $decodedSetup = $this->getDecodedSetup();

            $common = $this->getArrayItem($decodedSetup, self::COMMON);
            $locales = $this->getArrayItem($decodedSetup, self::LOCALE);
            foreach ($locales as $key => $locale) {
                $locales[$key] = $this->helper->updateArray($locale, $common);
            }
            $this->_prepocessedConfig = $locales;
        }
        return $this->_prepocessedConfig;
    }

    /********************************** RELOAD TAXONOMY FUNCTIONS ***********************************/
    public function prepareLocalesSourceConfig($locales)
    {
        $taxonomyConfig = $this->prepareTaxonomyConfig();

        $localeConfigArray = [];
        foreach ($taxonomyConfig as $localeCode => $config) {
            try {
                if ($localeCode == self::ALL_LOCALES) {
                    if ($this->hasArrayItem($config, self::DOWNLOAD)) {
                        $localeConfigArray = $this->processLocales($locales, $config);
                        break;
                    } else {
                        $localeConfigArray[$localeCode] = $this->getArrayItem($config, self::SRC);
                    }
                } elseif (in_array($localeCode, $locales)) {
                    $localeConfigArray[$localeCode] = $this->getArrayItem($config, self::SRC);
                }
            } catch (\Exception $e) {
                $this->log(__("Taxonomy: {$this->getName()} Locale: {$localeCode} -- {$e} "));
            }
        }

        return $localeConfigArray;
    }

    /**
     * Prepare taxonomy config for Google taxonomies
     * @param unknown_type $locales
     * @param unknown_type $config
     * @return multitype:mixed
     */
    protected function processLocales($locales, $config)
    {
        $localeConfigArray = [];
        $sourceConfig = $this->getArrayItem($config, self::SRC, []);
        $sourceFile = $this->getArrayItem($sourceConfig, self::FILENAME);

        $localeConfig = $this->getArrayItem($config, self::DOWNLOAD);
        $delimiter = $this->getArrayItem($localeConfig, self::DELIMITER);
        $variable = $this->getArrayItem($localeConfig, self::VARIABLE);
        $defLoc = $this->getArrayItem($localeConfig, self::DEFAULT_LOCALE, self::ALL_LOCALES);
        $translate = $this->getArrayItem($localeConfig, self::TRANSLATE, null, true);

        if (!in_array($defLoc, $locales)) {
            $locales[] = $defLoc;
        }

        foreach ($locales as $locale) {
            $sourceConfig[self::FILENAME] = $this->prepareSrcFilename($sourceFile, $locale, $variable, $delimiter, $translate);
            $localeConfigArray[$locale] = $sourceConfig;
        }
        return $localeConfigArray;
    }

    protected function prepareSrcFilename($src, $locale, $variable, $delimiter, $translate)
    {
        if (isset($translate[$locale])) {
            $locale = $translate[$locale];
        }

        $locale = str_replace(self::DEFAULT_LOCALE_DELIMITER, $delimiter, $locale);
        $src = str_replace($variable, $locale, $src);
        return $src;
    }

    /***************************** COMMON FUNCTIONS **********************************/

    protected function hasArrayItem($array, $index)
    {
        if (isset($array[$index])) {
            return true;
        } else {
            return false;
        }
    }

    protected function getArrayItem($array, $index, $default = null, $allowNull = false)
    {
        if (isset($array[$index])) {
            return $array[$index];
        } elseif ($allowNull) {
            return null;
        } elseif (!isset($array[$default])) {
            throw new \Exception(__("Missing taxonomy config node '{$index}' "));
            return null;
        } else {
            return $array[$default];
        }
    }
}
