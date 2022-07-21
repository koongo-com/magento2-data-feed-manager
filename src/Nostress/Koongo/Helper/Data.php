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

namespace Nostress\Koongo\Helper;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\State;
use Magento\Tax\Model\Config;

/**
 * Main Koongo connector Helper.
 *
 * @category Nostress
 * @package Nostress_Koongo
 *
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /* Koongo log file */
    const LOG_FILE = "koongo.txt";

    const BRACE_DOUBLE_OPEN = "{{";
    const BRACE_DOUBLE_CLOSE = "}}";
    const PATH_DELIMITER = '/';
    const DEF_FILE_PERMISSION = 0777;
    const LANGUAGE_CODE_LENGTH = 2;
    const KOONGO_SYSTEM_INTEGRATION_NAME = "Koongo";
    const KOONGO_SYSTEM_INTEGRATION_NAME_OAUTH = "KoongoOauth";
    const KOONGO_SYSTEM_INTEGRATION_PERMISSON_SCOPES = "Nostress_Koongo::koongo,Magento_Sales::sales,Magento_Catalog::catalog,Magento_Backend::stores";
    const REGISTRY_KEY_KOONGO_SERVIVE_CONNECTION_STEP_NUMBER = "koongo_service_connection_step_number";

    //config paths
    const PATH_MODULE_CONFIG = 'koongo_config/';
    const PATH_STORE_LOCALE = 'general/locale/code';
    const PATH_STORE_COUNTRY = 'general/country/default';

    const PARAM_SERVER_CONFIG_ERROR = 'general/server_config_error';
    const MAX_SERVER_CONFIG_ERROR = 1;
    const PARAM_SERVER_CONFIG_LAST_UPDATE = 'general/server_config_last_update';

    //params
    const PARAM_FEEDS_DIRECTORY = 'general/feeds_directory';
    const PARAM_BATCH_SIZE = 'general/batch_size';
    const PARAM_INCLUDE_PUB = 'general/include_pub_into_media_links';
    const PARAM_IMAGE_FOLDER = 'general/image_folder';
    const PARAM_REGEXP_ILLEGAL_CHARS = 'general/remove_illegal_chars_reg_expression';
    const PARAM_CATEGORY_LOWEST_LEVEL = 'general/category_lowest_level';
    const PARAM_CRON_LAST_RUN = 'general/cron_last_run';
    const PARAM_BUNDLE_OPTIONS_REQUIRED_ONLY = 'general/bundle_options_required_only';
    const PARAM_BUNDLE_OPTIONS_DEFAULT_ITEMS_ONLY = 'general/bundle_options_default_itmes_only';
    const PARAM_IMAGE_ATTRIBUTE_SOURCE = 'general/image_attribute_source';

    const PARAM_ALLOW_PLACEHOLDER = "basic/allow_placeholder_images_export";
    const PARAM_DEBUG_MODE = 'basic/debug_mode';
    const PARAM_ALLOW_EXCLUDED_IMAGES_EXPORT = 'basic/allow_excluded_images_export';
    const PARAM_SHOW_BLOG_NEWS = 'basic/show_blog_news';
    const PARAM_SHOW_KAAS_INFO = 'basic/show_kaas_info';
    const PARAM_ALLOW_INACTIVE_CATEGORIES_EXPORT = "basic/allow_inactive_categories_export";

    //static params
    const PARAM_SUPPORT_EMAIL = 'help/support_email';
    const PARAM_PRESENT_WEB_URL =  "help/presentation_website_url";
    const PARAM_PRESENT_RESOURCES_URL =  "help/presentation_resources_url";
    const HELP_FLAT_CATALOG = 'help/flat_catalog';
    const HELP_FEED_COLLECTIONS = 'help/feed_collections';
    const HELP_LICENSE_CONDITIONS = 'help/license_conditions';
    const HELP_PRIVACY_POLICY = 'help/privacy_policy';
    const HELP_TERMS = 'help/terms';

    //Cache keys
    const CACHE_KEY_CRON_LAST_RUN = 'koongo_cron_last_run';
    const CACHE_LIFETIME_KEY_CRON_LAST_RUN = 7200;

    const FILE_TYPE_XML = "xml";
    const FILE_TYPE_CSV = "csv";
    const FILE_TYPE_ZIP = "zip";
    const FILE_TYPE_TXT = "txt";
    const FILE_TYPE_HTML = "html";
    protected $_fileTypes = [self::FILE_TYPE_CSV, self::FILE_TYPE_XML, self::FILE_TYPE_ZIP,self::FILE_TYPE_HTML,self::FILE_TYPE_TXT];

    /**
     * Currently selected store ID if applicable
     *
     * @var int
     */
    protected $_storeId;

    /**
     * @var \Magento\Framework\App\Config\ReinitableConfigInterface
     */
    protected $_coreConfig;

    /**
     * @var \Magento\Framework\App\Cache\TypeListInterface
     */
    protected $_cacheTypeList;

    /** @var \Magento\Catalog\Model\Product\Type */
    protected $productType;
    /**
     * Currency factory
     *
     * @var \Magento\Directory\Model\CurrencyFactory
     */
    protected $_currencyFactory;

    /**
     * Logging instance
     * @var \Nostress\Koongo\Model\Logger
     */
    protected $logger;

    /**
     * @var \Magento\Config\Model\ResourceModel\Config
     */
    protected $resourceConfig;

    /** @var \Magento\Store\Model\StoreManagerInterface */
    protected $storeManager;

    /** @var \Magento\Framework\App\Config\ValueFactory */
    protected $configDataFactory;

    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $filesystem;

    /**
     * @var \Nostress\Koongo\Model\Config\Source\Datetimeformat
     */
    protected $_datetimeFormat;

    /**
     * @var Magento\Framework\App\CacheInterface
     */
    protected $_cache;

    /**
     * @var \Magento\Framework\Module\ModuleList
     */
    protected $moduleList;

    /**
     * @var \Magento\Eav\Model\Config
     */
    protected $_eavConfig;

    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    protected $_productMetadataInterface;

    /**
     *  Object for Magento mode detection - production/developer
     * @var \Magento\Framework\App\State
     */
    protected $_appState;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Catalog\Model\Product\Type $productType
     * @param \Nostress\Koongo\Model\Logger $logger,
     * @param \Magento\Directory\Model\CurrencyFactory $currencyFactory
     * @param \Magento\Config\Model\ResourceModel\Config $resourceConfig
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\Config\ValueFactory $configDataFactory
     * @param \Magento\Framework\Filesystem
     * @param \Magento\Framework\App\Config\ReinitableConfigInterface
     * @param \Magento\Framework\App\Cache\TypeListInterface
     * @param \Nostress\Koongo\Model\Config\Source\Datetimeformat $datetimeformat,
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Framework\App\CacheInterface $cache
     * @param \Magento\Framework\Module\ModuleList $moduleList
     * @param \Magento\Framework\App\ProductMetadataInterface $productMetadataInterface
     * @param \Magento\Framework\App\State $appState
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Catalog\Model\Product\Type $productType,
        \Nostress\Koongo\Model\Logger $logger,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        \Magento\Config\Model\ResourceModel\Config $resourceConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ValueFactory $configDataFactory,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\App\Config\ReinitableConfigInterface $coreConfig,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Nostress\Koongo\Model\Config\Source\Datetimeformat $datetimeformat,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Framework\App\CacheInterface $cache,
        \Magento\Framework\Module\ModuleList $moduleList,
        \Magento\Framework\App\ProductMetadataInterface $productMetadataInterface,
        \Magento\Framework\App\State $appState
    ) {
        $this->_coreConfig = $coreConfig;
        $this->_cacheTypeList = $cacheTypeList;
        $this->productType = $productType;
        $this->logger = $logger;
        $this->_currencyFactory = $currencyFactory;
        $this->resourceConfig = $resourceConfig;
        $this->storeManager = $storeManager;
        $this->configDataFactory = $configDataFactory;
        $this->filesystem = $filesystem;
        $this->_datetimeFormat = $datetimeformat;
        $this->_eavConfig = $eavConfig;
        $this->_cache = $cache;
        $this->moduleList = $moduleList;
        $this->_productMetadataInterface = $productMetadataInterface;
        $this->_appState = $appState;

        parent::__construct($context);
    }

    public function isServerConfigUpdateNeeded()
    {
        if (!$this->isServerConfigUpdatable(true)) {
            return false;
        }

        $configTest = $this->getModuleConfig(\Nostress\Koongo\Model\Api\Client::PARAM_TAXONOMY_SOURCE_URL);
        // if empty configuration - redirect to update server config
        if (empty($configTest)) {
            return true;
        }

        $serverConfigLastUpdate = $this->getModuleConfig(self::PARAM_SERVER_CONFIG_LAST_UPDATE);
        $currentMonth = $this->_datetimeFormat->getMonthOfYear() . "-" . $this->_datetimeFormat->getYear();
        if (empty($serverConfigLastUpdate) || $serverConfigLastUpdate != $currentMonth) {
            $this->setModuleConfig(self::PARAM_SERVER_CONFIG_LAST_UPDATE, $currentMonth);
            return true;
        }

        return false;
    }

    public function isServerConfigUpdatable($checkEqual = false)
    {
        if ($checkEqual) {
            $result =  ($this->getModuleConfig(self::PARAM_SERVER_CONFIG_ERROR) <= self::MAX_SERVER_CONFIG_ERROR);
        } else {
            $result =  ($this->getModuleConfig(self::PARAM_SERVER_CONFIG_ERROR) < self::MAX_SERVER_CONFIG_ERROR);
        }
        if (!$result) {
            $this->setModuleConfig(\Nostress\Koongo\Helper\Data::PARAM_DEBUG_MODE, "1");
        }
        return $result;
    }

    public function incrementServerConfigError()
    {
        $sc = $this->getModuleConfig(self::PARAM_SERVER_CONFIG_ERROR);
        if (empty($sc)) {
            $sc = 0;
        }
        $sc++;
        $this->setModuleConfig(self::PARAM_SERVER_CONFIG_ERROR, $sc);
    }

    public function clearServerConfigError()
    {
        $this->setModuleConfig(self::PARAM_SERVER_CONFIG_ERROR, 0);
    }

    /**
     * Returns default Store ID
     *
     * @return int
     */
    public function getDefaultStoreId()
    {
        return \Magento\Store\Model\Store::DEFAULT_STORE_ID;
    }

    public function getStore($id)
    {
        return $this->storeManager->getStore($id);
    }

    /**
     * Param e.g. \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
     * @param \Magento\Framework\UrlInterface $type
     */
    public function getBaseUrl($type = null)
    {
        return $this->storeManager->getStore($this->getDefaultStoreId())->getBaseUrl($type);
    }

    /**
     * This function was added because function $this->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA); return "pub"
     * in url address even if production mode is set to yes.(than pub shouldn't be included into the url).
     * Pub folder is removed if production mode is enabled.
     * @param unknown_type $store
     * @return string
     */
    public function getMediaBaseUrl($store = null)
    {
        $storeId = null;
        if (isset($store)) {
            $url = $store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
            $storeId = $store->getId();
        } else {
            $url = $this->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        }

        $includePub = $this->getModuleConfig(self::PARAM_INCLUDE_PUB, false, false, $storeId);

        switch ($includePub) {
            case \Nostress\Koongo\Model\Config\Source\Includepub::YES:
                if (strpos($url, DirectoryList::PUB) === false) {
                    $url = str_replace("/media", "/" . DirectoryList::PUB . "/media", $url);
                }
                break;
            case \Nostress\Koongo\Model\Config\Source\Includepub::NO:
                $url = str_replace("/" . DirectoryList::PUB . "/", "/", $url); //Remove PUB from media directory
                break;
            default:
                break;
        }

        return $url;

//     	if(isset($store))
//     	{
//     		$url = $store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
//     		if ($this->_appState->getMode() == State::MODE_PRODUCTION) //Remove pub folder
//     		{
//     			//If production mode is enabled and it Document root target to pub directory
//     			$pubDirExist = file_exists(DirectoryList::PUB);
//     			if(!$pubDirExist)
//     			{
//     				$url = str_replace("/".DirectoryList::PUB."/", "/", $url);
//     			}
//     		}
//     		return $url;
//     	}
//     	else
//     		return $this->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
    }

    public function getProductTypes()
    {
        $types = $this->productType->getTypes();
        return array_keys($types);
    }

    public function array_unique_tree($array_tree, $index = "label")
    {
        $temp = [];
        $result = [];
        $labels = [];
        foreach ($array_tree as $key => $array) {
            if (!in_array($array[$index], $temp)) {
                $labels[] = $array[$index];
                $temp[$key] = $array[$index];
                $result[] = $array;
            }
        }
        array_multisort($labels, SORT_ASC, SORT_REGULAR, $result);
        return $result;
    }

    /**
     * From version 2.1.0, website_id in table cateloginventory_stock_status is 0 (in previous versions it is similar to website_ids of particulatr websites where product is defined)
     * @return string|NULL
     */
    public function getStockWebsiteId()
    {
        if ($this->isMagentoVersionEqualOrGreaterThan("2.1.0")) {
            return "0";
        } else {
            return null;
        }
    }

    public function getKoongoIntegrationName()
    {
        return $this->isMagentoVersion244OrNewer() ? self::KOONGO_SYSTEM_INTEGRATION_NAME_OAUTH : self::KOONGO_SYSTEM_INTEGRATION_NAME;
    }

    public function isMagentoVersionEqualOrGreaterThan($version)
    {
        $currentVersion = $this->getMagentoVersion();

        $versionArray = explode(",", $version);
        $currentVersionArray = explode(",", $currentVersion);

        foreach ($currentVersionArray as $index => $currentVersionItem) {
            if (!isset($versionArray[$index])) {
                return null;
            } elseif ($versionArray[$index] > $currentVersionItem) {
                return false;
            }
        }
        return true;
    }

    public function getMagentoVersion()
    {
        return $this->_productMetadataInterface->getVersion();
    }

    public function isMagentoVersion244OrNewer()
    {
        return $this->isMagentoVersionEqualOrGreaterThan("2.4.4");
    }

    public function getMagentoEdition()
    {
        return $this->_productMetadataInterface->getEdition();
    }

    public function getModuleEnabled($moduleName)
    {
        $conf = $this->moduleList->getOne($moduleName);
        if (!isset($conf)) {
            return false;
        }
        return true;
    }

    //*********************** STRING - OPERATION**********************************
    public function createCode($input, $delimiter = '_', $toLower = true, $skipChars = "")
    {
        $input = $this->removeDiacritic($input);
        if ($toLower) {
            $input = strtolower($input);
        }

        //replace characters which are not number or letters by space
        $input = preg_replace("/[^0-9a-zA-Z{$skipChars}]/", ' ', $input);
        $input = trim($input);
        //replace one or more spaces by delimiter
        $input = preg_replace('/\s+/', $delimiter, $input);

        return $input;
    }

    public function codeToLabel($input, $delimiter = '_')
    {
        $input = str_replace($delimiter, " ", $input);
        $input = ucwords($input);
        return $input;
    }

    public function changeEncoding($dstEnc, $input, $srcEnc=null)
    {
        if ($srcEnc == $dstEnc) {
            return $input;
        }

        if (!is_array($input)) {
            return $this->_changeEncoding($dstEnc, $input, $srcEnc);
        }

        $result = [];
        foreach ($input as $key => $item) {
            $result[$key] = $this->_changeEncoding($dstEnc, $item, $srcEnc);
        }
        return $result;
    }

    /*
     * Returns encoded string.
    */
    protected function _changeEncoding($dstEnc, $input, $srcEnc=null)
    {
        if (!isset($input) || empty($input)) {
            return $input;
        }

        $originalInput = $input;
        $extension = "mbstring";

        if (!isset($srcEnc)) {
            if (!extension_loaded($extension)) {
                throw new \Exception(__('PHP Extension "%1" must be loaded', $extension) . '.');
            } else {
                $srcEnc = mb_detect_encoding($input);
            }
        }
        try {
            $input = iconv($srcEnc, $dstEnc . '//TRANSLIT', $input);
        } catch (\Exception $e) {
            try {
                $input = iconv($srcEnc, $dstEnc . '//IGNORE', $input);
                //$input = mb_convert_encoding($input,$dstEnc,$srcEnc);
            } catch (\Exception $e) {
                //echo $input;
                throw $e;
            }
        }
        if ($input == false) {
            throw new \Exception('Conversion from encoding ' . $srcEnc . ' to ' . $dstEnc . ' failure. Following string can not be converted:<BR>' . $originalInput);
        }

        return $input;
    }

    public function dS($input)
    {
        return base64_decode($input);
    }

    protected function removeDiacritic($input)
    {
        $transTable = [
                'Ă¤'=>'a',
                'Ă„'=>'A',
                'Ăˇ'=>'a',
                'Ă�'=>'A',
                'Ă '=>'a',
                'Ă€'=>'A',
                'ĂŁ'=>'a',
                'Ă�'=>'A',
                'Ă˘'=>'a',
                'Ă‚'=>'A',
                'ÄŤ'=>'c',
                'ÄŚ'=>'C',
                'Ä‡'=>'c',
                'Ä†'=>'C',
                'ÄŹ'=>'d',
                'ÄŽ'=>'D',
                'Ä›'=>'e',
                'Äš'=>'E',
                'Ă©'=>'e',
                'Ă‰'=>'E',
                'Ă«'=>'e',
                'Ă‹'=>'E',
                'Ă¨'=>'e',
                'Ă�'=>'E',
                'ĂŞ'=>'e',
                'ĂŠ'=>'E',
                'Ă­'=>'i',
                'ĂŤ'=>'I',
                'ĂŻ'=>'i',
                'ĂŹ'=>'I',
                'Ă¬'=>'i',
                'ĂŚ'=>'I',
                'Ă®'=>'i',
                'ĂŽ'=>'I',
                'Äľ'=>'l',
                'Ä˝'=>'L',
                'Äş'=>'l',
                'Äą'=>'L',
                'Ĺ„'=>'n',
                'Ĺ�'=>'N',
                'Ĺ�'=>'n',
                'Ĺ‡'=>'N',
                'Ă±'=>'n',
                'Ă‘'=>'N',
                'Ăł'=>'o',
                'Ă“'=>'O',
                'Ă¶'=>'o',
                'Ă–'=>'O',
                'Ă´'=>'o',
                'Ă”'=>'O',
                'Ă˛'=>'o',
                'Ă’'=>'O',
                'Ăµ'=>'o',
                'Ă•'=>'O',
                'Ĺ‘'=>'o',
                'Ĺ�'=>'O',
                'Ĺ™'=>'r',
                'Ĺ�'=>'R',
                'Ĺ•'=>'r',
                'Ĺ”'=>'R',
                'Ĺˇ'=>'s',
                'Ĺ '=>'S',
                'Ĺ›'=>'s',
                'Ĺš'=>'S',
                'ĹĄ'=>'t',
                'Ĺ¤'=>'T',
                'Ăş'=>'u',
                'Ăš'=>'U',
                'ĹŻ'=>'u',
                'Ĺ®'=>'U',
                'ĂĽ'=>'u',
                'Ăś'=>'U',
                'Ăą'=>'u',
                'Ă™'=>'U',
                'Ĺ©'=>'u',
                'Ĺ¨'=>'U',
                'Ă»'=>'u',
                'Ă›'=>'U',
                'Ă˝'=>'y',
                'Ăť'=>'Y',
                'Ĺľ'=>'z',
                'Ĺ˝'=>'Z',
                'Ĺş'=>'z',
                'Ĺą'=>'Z'
        ];
        return strtr($input, $transTable);
    }

    public function decodeSpaceCharacters($input)
    {
        $input = str_replace("\\n", "\n", $input);
        $input = str_replace("\\r", "\r", $input);
        $input = str_replace("\\t", "\t", $input);
        return $input;
    }

    public function encodeSpaceCharacters($input)
    {
        $input = str_replace("\n", "\\n", $input);
        $input = str_replace("\r", "\\r", $input);
        $input = str_replace("\t", "\\t", $input);
        return $input;
    }

    public function grebVariables($string, $replaceBraces = true, $asIndexedArray = false)
    {
        $pattern = "/" . self::BRACE_DOUBLE_OPEN . "[^}]*" . self::BRACE_DOUBLE_CLOSE . "/";
        $matches = [];
        $num = preg_match_all($pattern, $string, $matches);
        $matches = $matches[0];
        $result = [];
        if ($num) {
            foreach ($matches as $key => $data) {
                $withoutBraces = preg_replace("/[" . self::BRACE_DOUBLE_OPEN . "|" . self::BRACE_DOUBLE_CLOSE . "]/", "", $data);

                if ($asIndexedArray) {
                    $result[$data] = $withoutBraces;
                } else {
                    if ($replaceBraces) {
                        $result[$key] = $withoutBraces;
                    } else {
                        $result[$key] = $data;
                    }
                }
            }
        }
        return $result;
    }

    //*********************** ARRAY - OPERATION**********************************
    public function updateArray($src, $dst, $force = true)
    {
        if (!isset($dst)) {
            $dst = [];
        }

        if (!is_array($src)) {
            return $dst;
        }
        foreach ($src as $key => $node) {
            if (!is_array($node)) {
                if ($force || $node != "" || !array_key_exists($key, $dst)) {
                    $dst[$key] = $node;
                }
            } else {
                $tmpDst = null;
                if (isset($dst[$key])) {
                    $tmpDst = $dst[$key];
                }
                $res = $this->updateArray($node, $tmpDst, $force);
                $dst[$key] = $res;
            }
        }
        return $dst;
    }

    //*********************** CONFIG - START**********************************

    public function getStoreConfig($storeId = null, $configPath = null)
    {
        if (!isset($storeId)) {
            return $this->scopeConfig->getValue($configPath);
        } else {
            return $this->scopeConfig->getValue(
                $configPath,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $storeId
            );
        }
    }

    public function setStoreConfig($path, $value, $storeId = null)
    {
        $this->_setConfig($path, $value, $storeId);
    }

    public function getStoreConfigFlag($storeId = null, $configPath = null)
    {
        if (!isset($storeId)) {
            return $this->scopeConfig->isSetFlag($configPath);
        } else {
            return $this->scopeConfig->isSetFlag(
                $configPath,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $storeId
            );
        }
    }

    public function getWebsiteConfig($websiteId, $configPath)
    {
        return $this->scopeConfig->getValue(
            $configPath,
            \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE,
            $websiteId
        );
    }

    /**
     * Return store config value for key
     *
     * @param   string $key
     * @return  string
     */
    public function getModuleConfig($key, $flag=false, $asArray=false, $storeId = null)
    {
        $path = self::PATH_MODULE_CONFIG . $key;
        return $this->_getConfig($path, $flag, $asArray, $storeId);
    }

    public function setModuleConfig($key, $value)
    {
        $path = self::PATH_MODULE_CONFIG . $key;
        return $this->_setConfig($path, $value);
    }

    protected function _getConfig($path, $flag=false, $asArray=false, $storeId = null)
    {
        $result = null;
        if ($flag) {
            $result = $this->getStoreConfigFlag($storeId, $path);
        } else {
            $result = $this->getStoreConfig($storeId, $path);
        }

        if ($asArray) {
            $result = explode(",", $result);
        }
        return $result;
    }

    protected function _setConfig($path, $value, $storeId = null, $reinit = true)
    {
        if (!isset($storeId)) {
            $this->resourceConfig->saveConfig($path, $value, \Magento\Framework\App\Config\ScopeConfigInterface::SCOPE_TYPE_DEFAULT, 0);
        } else {
            $this->resourceConfig->saveConfig($path, $value, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
        }

        if ($reinit) {
            // reinit configuration
            $this->_reinitConfig();
        }
    }

    protected function _reinitConfig()
    {
        //This solution is slower. It takes 0.08 sec on test
        $this->_cacheTypeList->cleanType(\Magento\Framework\App\Cache\Type\Config::TYPE_IDENTIFIER);
        $this->_cacheTypeList->cleanType(\Magento\PageCache\Model\Cache\Type::TYPE_IDENTIFIER);

        //This solution is faster. It takes 0.003 sec on test
        //Takes to much time for some merchants
        // $this->_coreConfig->reinit();
        // $this->storeManager->reinitStores();
    }

    public function saveModuleConfigs(array $config)
    {
        $path = self::PATH_MODULE_CONFIG;

        foreach ($config as $group => $items) {
            foreach ($items as $key => $value) {
                $this->_setConfig($path . $group . '/' . $key, $value, null, false);
            }
        }

        $this->_reinitConfig();
    }

    protected function getConfig($configPath)
    {
        return $this->scopeConfig->getNode($configPath);
    }

    public function getHelp($key)
    {
        return $this->getModuleConfig('help/' . $key);
    }

//     public function setFieldsetTemplate( $form) {

//         $form->getFieldsetRenderer()->setTemplate( 'Nostress_Koongo::widget/form/renderer/fieldset.phtml');
//     }

    public function renderTooltip($tooltipKey)
    {
        $tooltip = $this->getHelp($tooltipKey);

        if (!empty($tooltip)) {
            return
            "<div class='admin__field-tooltip tooltip'>
                <a href='$tooltip' onclick=\"this.target='_blank'\" title='" . __('Get Help!') . "'
                    class='admin__field-tooltip-action action-help'>
                    <span>" . __('Get Help!') . "</span></a>
            </div>";
        }
    }

    /**
    * Save data to cache
    *
    * @param string $data
    * @param string $identifier
    * @param array $tags
    * @param int $lifeTime In seconds
    * @return bool
    */
    public function saveCache($data, $identifier, $tags = [], $lifeTime = null)
    {
        $this->_cache->save($data, $identifier, $tags, $lifeTime);
    }

    /**
     * Load data from cache by id
     *
     * @param  string $identifier
     * @return string
     */
    public function loadCache($identifier)
    {
        return $this->_cache->load($identifier);
    }

    /*********************************** CURRENCY *********************************/

    public function getStoreCurrencyRate($store, $toCurrencyCode = null)
    {
        $from = $store->getBaseCurrencyCode();
        if (empty($toCurrencyCode)) {
            $toCurrencyCode = $store->getCurrentCurrencyCode();
        }
        if ($from == $toCurrencyCode) {
            return null;
        } else {
            return $this->getCurrencyRate($from, $toCurrencyCode);
        }
    }

    protected function getCurrencyRate($from, $to)
    {
        $rate = $this->_currencyFactory->create()->load($from)->getRate($to);
        if (!$rate) {
            $rate = 1;
        }
        return $rate;
    }

    public function getCurrencySymbol($currencyCode)
    {
        return $this->_currencyFactory->create()->load($currencyCode)->getCurrencySymbol();
    }

    /*********************************** LOG *********************************/
    public function log($message)
    {
        $this->logger->info(__($message));
    }

    /********************************** TIME FUNCTIONS ***************************/

    public function getProcessorTime()
    {
        $mtime = microtime();
        $mtime = explode(" ", $mtime);
        $mtime = $mtime[1] + $mtime[0];
        return $mtime;
    }

    /**
     * Get current date time by adjusted time zone
     *
     * @return String
     */
    public function getNow()
    {
        return $this->_datetimeFormat->getDateTime(null, null);
    }

    /**
     * Get  date time by adjusted time zone
     *
     * @param String $input E.g. -1 day
     * @return void
     */
    public function getTime($input = null)
    {
        return $this->_datetimeFormat->getDateTime($input, null);
    }

    /**
     * Return date time for now
     * @param DateTimeFormat $format, DateTime::ATOM =  2005-08-15T15:52:01+00:00, DateTime::ISO8601 = 2005-08-15T15:52:01+0000
     * @param int $timestamp
     * @return string
     */
    public function getDatetimeString($format = \DateTime::ATOM, $timestamp = null)
    {
        return $this->_datetimeFormat->formatDatetime($timestamp, $format);
    }

    /**
     * Convert datetime string from orignal format into defined format
     * @param String $datetime
     * @param string $format The format of the outputted date string. See the formatting options below. There are also several predefined date constants that may be used instead, so for example DATE_RSS contains the format string 'D, d M Y H:i:s'.
     * @return string
     */
    public function formatDatetime($datetime = "", $format = \DateTime::ATOM)
    {
        return $this->_datetimeFormat->getDateTime($datetime, $format);
    }

    //*********************** FILE FUNCTIONS******************************

    public function createDirectory($dir)
    {
        if (!is_dir($dir)) {
            mkdir($dir, self::DEF_FILE_PERMISSION, true);
        }
    }

    public function createFile($file, $content)
    {
        if (!file_exists($file)) {
            file_put_contents($file, $content);
            chmod($file, self::DEF_FILE_PERMISSION);
        }
    }

    /* creates a compressed zip file */
    public function createZip($files = [], $destination = '', $overwrite = false)
    {
        //if the zip file already exists and overwrite is false, return false
        if (file_exists($destination) && !$overwrite) {
            return false;
        }
        //vars
        $valid_files = [];
        //if files were passed in...
        if (is_array($files)) {
            //cycle through each file
            foreach ($files as $localName => $file) {
                //make sure the file exists
                if (file_exists($file)) {
                    $valid_files[$localName] = $file;
                }
            }
        }

        //if we have good files...
        if (count($valid_files)) {
            //create the archive
            $zip = new \ZipArchive();
            $openZip = $zip->open($destination, $overwrite ? \ZIPARCHIVE::OVERWRITE : \ZIPARCHIVE::CREATE);
            if ($openZip !== true) {
                file_put_contents($destination, "");
                $openZip = $zip->open($destination, \ZIPARCHIVE::OVERWRITE);
                if ($openZip !== true) {
                    return false;
                }
            }

            //add the files
            foreach ($valid_files as $localName => $file) {
                if (is_numeric($localName)) {
                    $localName = $file;
                }
                $zip->addFile($file, $localName);
            }
            //debug
            //echo 'The zip archive contains ',$zip->numFiles,' files with a status of ',$zip->status;

            //close the zip -- done!
            $zip->close();

            //check to make sure the file exists
            return file_exists($destination);
        } else {
            return false;
        }
    }

    /**
     * Renamse feed files.
     * @param $oldFileName
     * @param $newFileName
     */
    public function renameFile($originalFile, $newFile)
    {
        //rename file
        if (is_file($originalFile)) {
            rename($originalFile, $newFile);
        }
    }

    /*
     * Deletes specified xml file.
    */
    public function deleteFile($file)
    {
        if ($file == null || $file === '') {
            return;
        }

        if (file_exists($file)) {
            unlink($file);
        }
    }

    public function changeFileSuffix($filename, $suffix = self::FILE_TYPE_ZIP)
    {
        if (isset($suffix) && !empty($suffix)) {
            $newSuffix = "." . $suffix;
        } else {
            $newSuffix = $suffix;
        }

        foreach ($this->_fileTypes as $type) {
            $oldSuffix = "." . $type;
            if (strpos($filename, $oldSuffix) !== false) {
                $filename = str_replace($oldSuffix, $newSuffix, $filename);
                return $filename;
            }
        }
        return $filename;
    }

    public function addDateToString($string, $timestamp = null)
    {
        if (!isset($timestamp)) {
            $timestamp = time();
        }

        $numMatches =  preg_match_all('/{{[^}]+}}/', $string, $matches);
        if ($numMatches == 0) {
            return $string;
        } else {
            foreach ($matches[0] as $match) {
                $datetimeTemplate = trim($match, "{}");
                $datetimeString = date($datetimeTemplate, $timestamp);
                $string = str_replace($match, $datetimeString, $string);
            }
        }

        return $string;
    }

    /*
    * Return path of default feed storage directory
    * @param $fileName Name of file to added as a suffix of the path
    * @param $subDirectory Subdirectory to added as a suffix of the path
    */
    public function getFeedStorageDirPath($fileName = null, $subDirectory = null)
    {
        $path =  $this->getModuleConfig(self::PARAM_FEEDS_DIRECTORY) . self::PATH_DELIMITER;

        $reader = $this->filesystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
        $absolutePath = $reader->getAbsolutePath($path);
        if (!is_dir($absolutePath)) {
            mkdir($absolutePath);
        }

        if (isset($subDirectory)) {
            $path .= $subDirectory . self::PATH_DELIMITER;
            $absolutePath = $reader->getAbsolutePath($path);
            if (!is_dir($absolutePath)) {
                mkdir($absolutePath);
            }
        }

        if (isset($fileName)) {
            $path.= $fileName;
        }

        $reader = $this->filesystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
        $path = $reader->getAbsolutePath($path);

        //$path = str_replace('//',self::PATH_DELIMITER, $path);
        return $path;
    }

    /*
     * Returns xml files url path.
    */
    public function getFeedStorageUrl($fileName  = null, $subDirectory = null, $store = null)
    {
        $url = $this->getMediaBaseUrl($store);
        $url .=  $this->getModuleConfig(self::PARAM_FEEDS_DIRECTORY) . self::PATH_DELIMITER;

        if (isset($subDirectory)) {
            $url .= $subDirectory . self::PATH_DELIMITER;
        }

        if (isset($fileName)) {
            $url.= $fileName;
        }

        //$url = str_replace('//',self::PATH_DELIMITER, $url);
        return $url;
    }

    //*****************************XML READ FUNCTIONS***********************************************
    public function stringToXml($input)
    {
        //return new SimpleXMLElement($input,LIBXML_NOCDATA);
        return simplexml_load_string($input);
    }

    public function importDom($simplexml)
    {
        return dom_import_simplexml($simplexml);
    }

    public function XMLnodeToArray($node)
    {
        $result = [];
        if (count($node[0]->children())==0) {
            return (string)$node[0];
        }

        $isArray = false;
        foreach ($node[0]->children() as $child) {
            if (!isset($result[$child->getName()])) {
                $result[$child->getName()] = self::XMLnodeToArray($child);
            } elseif (!$isArray) {
                $oldValue = $result[$child->getName()];
                unset($result[$child->getName()]);
                $result[$child->getName()] = [];

                $result[$child->getName()][] = $oldValue;
                $result[$child->getName()][] = self::XMLnodeToArray($child);
                $isArray = true;
            } elseif ($isArray) {
                $result[$child->getName()][] = self::XMLnodeToArray($child);
            }
        }
        return $result;
    }

    /*********************************** EAV ATTRIBUTE SETTINGS ****************************************/

    public function setEavAttributesPropertyValue($attributeCodes, $optionIndex, $value, $entityTypeCode = 'catalog_product')
    {
        foreach ($attributeCodes as $code) {
            $this->setEavAttributePropertyValue($code, $optionIndex, $value, $entityTypeCode);
        }
    }

    public function setEavAttributePropertyValue($attributeCode, $optionIndex, $value, $entityTypeCode = 'catalog_product')
    {
        $attribute = $this->_eavConfig->getAttribute($entityTypeCode, $attributeCode);
        $attribute->setData($optionIndex, $value);
        $attribute->save();
    }

    /*********************************** STORE SETTINGS ****************************************/
    public function getAllStoresLocale()
    {
        $allStores = $this->storeManager->getStores(true);
        $localeArray = [];
        foreach ($allStores as $_eachStoreId => $store) {
            $locale = $this->getStoreLocale($store);
            if (!in_array($locale, $localeArray)) {
                $localeArray[] = $locale;
            }
        }
        return $localeArray;
    }

    public function getLocaleConfig()
    {
        $configCollection =  $this->configDataFactory->create()->getCollection();
        $configCollection->addFieldToFilter('path', self::PATH_STORE_LOCALE);
        $configCollection->load();
        $configArray = [];

        foreach ($configCollection as $configItem) {
            $scope = $configItem->getScope();
            if (!array_key_exists($scope, $configArray)) {
                $configArray[$scope] = [];
            }
            $configArray[$scope][$configItem->getScopeId()] = $configItem->getValue();
        }
        return $configArray;
    }

    public function getStoresWithLocale()
    {
        $configArray = $this->getLocaleConfig();
        $allStores = $this->storeManager->getStores(true);

        foreach ($allStores as $store) {
            $storeId = $store->getId();
            $webId = $store->getWebsiteId();
            if (isset($configArray['stores'][$storeId])) {
                $allStores[$storeId]['locale']  = $configArray['stores'][$storeId];
            } elseif (isset($configArray['websites'][$webId])) {
                $allStores[$storeId]['locale']  = $configArray['websites'][$webId];
            } else {
                $allStores[$storeId]['locale']  = $configArray['default'][$this->getDefaultStoreId()];
            }
        }
        return $allStores;
    }

    public function getStoreCurrenciesOptionArray($storeId)
    {
        $store = $this->storeManager->getStore($storeId);
        $codes = $store->getAvailableCurrencyCodes(true);
        sort($codes);
        $baseCurrencyCode = $store->getBaseCurrencyCode();
        $defaultCurrencyCode = $store->getDefaultCurrencyCode();

        $index = array_search($defaultCurrencyCode, $codes);
        if ($index !== false) {
            unset($codes[$index]);
            $options = [$defaultCurrencyCode => $defaultCurrencyCode . " (" . __("Default Display Currency") . ")"];
        } else {
            $options = [];
        }

        foreach ($codes as $code) {
            $label = $code;
            if ($code == $baseCurrencyCode) {
                $label .= " (" . __("Base Currency") . ")";
            }
            $options[$code] = $label;
        }

        return $options;
    }

    public function getStoreLocale($store)
    {
        return $store->getConfig(self::PATH_STORE_LOCALE);
    }

    public function getStoreLanguage($store)
    {
        $locale = $this->getStoreLocale($store);
        if (empty($locale)) {
            return "";
        }
        $lang = substr($locale, 0, self::LANGUAGE_CODE_LENGTH);
        $lang = strtoupper($lang);
        return $lang;
    }

    public function getStoreCountry($store)
    {
        return $store->getConfig(self::PATH_STORE_COUNTRY);
    }

    public function getKoongoWebsiteUrl()
    {
        return $this->getModuleConfig(self::PARAM_PRESENT_WEB_URL);
    }

    public function getKoongoResourcesUrl()
    {
        return $this->getModuleConfig(self::PARAM_PRESENT_RESOURCES_URL);
    }

    public function getKoongoServiceConnectionUrl()
    {
        $url = $this->getModuleConfig(\Nostress\Koongo\Model\Api\Client\Simple::PARAM_KOONGO_SERVICE_CONNECTION_URL);
        if (empty($url)) {
            $url = \Nostress\Koongo\Model\Api\Client\Simple::DEFAULT_KOONGO_SERVICE_CONNECTION_URL;
        }
        return $url;
    }

    public function getKoongoServiceEndpointUrl()
    {
        $url = $this->getModuleConfig(\Nostress\Koongo\Model\Api\Client\Simple::PARAM_KOONGO_SERVICE_ENDPOINT_URL);
        if (empty($url)) {
            $url = \Nostress\Koongo\Model\Api\Client\Simple::DEFAULT_KOONGO_SERVICE_ENDPOINT_URL;
        }
        return $url;
    }

    public function getKoongoServiceIdentityLinkUrl()
    {
        $url = $this->getModuleConfig(\Nostress\Koongo\Model\Api\Client\Simple::PARAM_KOONGO_SERVICE_IDENTITY_LINK_URL);
        if (empty($url)) {
            $url = \Nostress\Koongo\Model\Api\Client\Simple::DEFAULT_KOONGO_SERVICE_IDENTITY_LINK_URL;
        }
        return $url;
    }

    public function getBlogNews()
    {
        try {
            $rss = new \DOMDocument();
            $rss->load($this->getModuleConfig(\Nostress\Koongo\Model\Api\Client\Simple::PARAM_KOONGO_BLOG_FEED_URL));
            $feed = [];
            foreach ($rss->getElementsByTagName('item') as $node) {
                $item = [
                        'title' => $node->getElementsByTagName('title')->item(0)->nodeValue,
                        'desc' => $node->getElementsByTagName('description')->item(0)->nodeValue,
                        'link' => $node->getElementsByTagName('link')->item(0)->nodeValue,
                        'date' => $node->getElementsByTagName('pubDate')->item(0)->nodeValue,
                ];
                array_push($feed, $item);
            }
        } catch (\Exception $e) {
            $this->log(__("Error during Koongo Blog RSS display:") . " " . $e->getMessage());
            $feed = [];
        }
        return $feed;
    }

    public function isDebugMode()
    {
        return $this->getModuleConfig(self::PARAM_DEBUG_MODE);
    }

    public function removeAccent($string)
    {
        if (!preg_match('/[\x80-\xff]/', $string)) {
            return $string;
        }

        $chars = [
                // Decompositions for Latin-1 Supplement
                chr(195) . chr(128) => 'A', chr(195) . chr(129) => 'A',
                chr(195) . chr(130) => 'A', chr(195) . chr(131) => 'A',
                chr(195) . chr(132) => 'A', chr(195) . chr(133) => 'A',
                chr(195) . chr(135) => 'C', chr(195) . chr(136) => 'E',
                chr(195) . chr(137) => 'E', chr(195) . chr(138) => 'E',
                chr(195) . chr(139) => 'E', chr(195) . chr(140) => 'I',
                chr(195) . chr(141) => 'I', chr(195) . chr(142) => 'I',
                chr(195) . chr(143) => 'I', chr(195) . chr(145) => 'N',
                chr(195) . chr(146) => 'O', chr(195) . chr(147) => 'O',
                chr(195) . chr(148) => 'O', chr(195) . chr(149) => 'O',
                chr(195) . chr(150) => 'O', chr(195) . chr(153) => 'U',
                chr(195) . chr(154) => 'U', chr(195) . chr(155) => 'U',
                chr(195) . chr(156) => 'U', chr(195) . chr(157) => 'Y',
                chr(195) . chr(159) => 's', chr(195) . chr(160) => 'a',
                chr(195) . chr(161) => 'a', chr(195) . chr(162) => 'a',
                chr(195) . chr(163) => 'a', chr(195) . chr(164) => 'a',
                chr(195) . chr(165) => 'a', chr(195) . chr(167) => 'c',
                chr(195) . chr(168) => 'e', chr(195) . chr(169) => 'e',
                chr(195) . chr(170) => 'e', chr(195) . chr(171) => 'e',
                chr(195) . chr(172) => 'i', chr(195) . chr(173) => 'i',
                chr(195) . chr(174) => 'i', chr(195) . chr(175) => 'i',
                chr(195) . chr(177) => 'n', chr(195) . chr(178) => 'o',
                chr(195) . chr(179) => 'o', chr(195) . chr(180) => 'o',
                chr(195) . chr(181) => 'o', chr(195) . chr(182) => 'o',
                chr(195) . chr(182) => 'o', chr(195) . chr(185) => 'u',
                chr(195) . chr(186) => 'u', chr(195) . chr(187) => 'u',
                chr(195) . chr(188) => 'u', chr(195) . chr(189) => 'y',
                chr(195) . chr(191) => 'y',
                // Decompositions for Latin Extended-A
                chr(196) . chr(128) => 'A', chr(196) . chr(129) => 'a',
                chr(196) . chr(130) => 'A', chr(196) . chr(131) => 'a',
                chr(196) . chr(132) => 'A', chr(196) . chr(133) => 'a',
                chr(196) . chr(134) => 'C', chr(196) . chr(135) => 'c',
                chr(196) . chr(136) => 'C', chr(196) . chr(137) => 'c',
                chr(196) . chr(138) => 'C', chr(196) . chr(139) => 'c',
                chr(196) . chr(140) => 'C', chr(196) . chr(141) => 'c',
                chr(196) . chr(142) => 'D', chr(196) . chr(143) => 'd',
                chr(196) . chr(144) => 'D', chr(196) . chr(145) => 'd',
                chr(196) . chr(146) => 'E', chr(196) . chr(147) => 'e',
                chr(196) . chr(148) => 'E', chr(196) . chr(149) => 'e',
                chr(196) . chr(150) => 'E', chr(196) . chr(151) => 'e',
                chr(196) . chr(152) => 'E', chr(196) . chr(153) => 'e',
                chr(196) . chr(154) => 'E', chr(196) . chr(155) => 'e',
                chr(196) . chr(156) => 'G', chr(196) . chr(157) => 'g',
                chr(196) . chr(158) => 'G', chr(196) . chr(159) => 'g',
                chr(196) . chr(160) => 'G', chr(196) . chr(161) => 'g',
                chr(196) . chr(162) => 'G', chr(196) . chr(163) => 'g',
                chr(196) . chr(164) => 'H', chr(196) . chr(165) => 'h',
                chr(196) . chr(166) => 'H', chr(196) . chr(167) => 'h',
                chr(196) . chr(168) => 'I', chr(196) . chr(169) => 'i',
                chr(196) . chr(170) => 'I', chr(196) . chr(171) => 'i',
                chr(196) . chr(172) => 'I', chr(196) . chr(173) => 'i',
                chr(196) . chr(174) => 'I', chr(196) . chr(175) => 'i',
                chr(196) . chr(176) => 'I', chr(196) . chr(177) => 'i',
                chr(196) . chr(178) => 'IJ',chr(196) . chr(179) => 'ij',
                chr(196) . chr(180) => 'J', chr(196) . chr(181) => 'j',
                chr(196) . chr(182) => 'K', chr(196) . chr(183) => 'k',
                chr(196) . chr(184) => 'k', chr(196) . chr(185) => 'L',
                chr(196) . chr(186) => 'l', chr(196) . chr(187) => 'L',
                chr(196) . chr(188) => 'l', chr(196) . chr(189) => 'L',
                chr(196) . chr(190) => 'l', chr(196) . chr(191) => 'L',
                chr(197) . chr(128) => 'l', chr(197) . chr(129) => 'L',
                chr(197) . chr(130) => 'l', chr(197) . chr(131) => 'N',
                chr(197) . chr(132) => 'n', chr(197) . chr(133) => 'N',
                chr(197) . chr(134) => 'n', chr(197) . chr(135) => 'N',
                chr(197) . chr(136) => 'n', chr(197) . chr(137) => 'N',
                chr(197) . chr(138) => 'n', chr(197) . chr(139) => 'N',
                chr(197) . chr(140) => 'O', chr(197) . chr(141) => 'o',
                chr(197) . chr(142) => 'O', chr(197) . chr(143) => 'o',
                chr(197) . chr(144) => 'O', chr(197) . chr(145) => 'o',
                chr(197) . chr(146) => 'OE',chr(197) . chr(147) => 'oe',
                chr(197) . chr(148) => 'R',chr(197) . chr(149) => 'r',
                chr(197) . chr(150) => 'R',chr(197) . chr(151) => 'r',
                chr(197) . chr(152) => 'R',chr(197) . chr(153) => 'r',
                chr(197) . chr(154) => 'S',chr(197) . chr(155) => 's',
                chr(197) . chr(156) => 'S',chr(197) . chr(157) => 's',
                chr(197) . chr(158) => 'S',chr(197) . chr(159) => 's',
                chr(197) . chr(160) => 'S', chr(197) . chr(161) => 's',
                chr(197) . chr(162) => 'T', chr(197) . chr(163) => 't',
                chr(197) . chr(164) => 'T', chr(197) . chr(165) => 't',
                chr(197) . chr(166) => 'T', chr(197) . chr(167) => 't',
                chr(197) . chr(168) => 'U', chr(197) . chr(169) => 'u',
                chr(197) . chr(170) => 'U', chr(197) . chr(171) => 'u',
                chr(197) . chr(172) => 'U', chr(197) . chr(173) => 'u',
                chr(197) . chr(174) => 'U', chr(197) . chr(175) => 'u',
                chr(197) . chr(176) => 'U', chr(197) . chr(177) => 'u',
                chr(197) . chr(178) => 'U', chr(197) . chr(179) => 'u',
                chr(197) . chr(180) => 'W', chr(197) . chr(181) => 'w',
                chr(197) . chr(182) => 'Y', chr(197) . chr(183) => 'y',
                chr(197) . chr(184) => 'Y', chr(197) . chr(185) => 'Z',
                chr(197) . chr(186) => 'z', chr(197) . chr(187) => 'Z',
                chr(197) . chr(188) => 'z', chr(197) . chr(189) => 'Z',
                chr(197) . chr(190) => 'z', chr(197) . chr(191) => 's'
        ];

        $string = strtr($string, $chars);

        return $string;
    }
}
