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
* Export profile main class
*
* @category Nostress
* @package Nostress_Koongo
*
*/

namespace Nostress\Koongo\Model\Channel;

use Nostress\Koongo\Api\Data\Channel\ProfileInterface;

class Profile extends \Nostress\Koongo\Model\AbstractModel implements ProfileInterface
{
    /* Profile config json indexes*/
    const CONFIG_GENERAL = 'general';
    const CONFIG_FEED = 'feed';
    const CONFIG_COMMON = 'common';
    const CONFIG_CUSTOM_PARAMS = 'custom_params';
    const CONFIG_FTP = 'ftp';
    const CONFIG_CRON = 'cron';

    const CONFIG_PARAM = 'param';
    const CONFIG_CODE = 'code';
    const CONFIG_VALUE = 'value';
    const CONFIG_FILTER = 'filter';
    const CONFIG_CONDITIONS = 'conditions';
    const CONFIG_DATETIME_FORMAT = "datetime_format";
    const CONFIG_CURRENCY = "currency";
    const CONFIG_PRICE_FORMAT = "price_format";
    const CONFIG_DECIMAL_DELIMITER = "decimal_delimiter";
    const CONFIG_PRICE_CUSTOMER_GROUP_ID = "price_customer_group_id";
    const CONFIG_NEW_LINE = "new_line";
    const CONFIG_COMPRESS_FILE = "compress_file";
    const CONFIG_ENCODING = "encoding";
    const CONFIG_TAXONOMY_LOCALE = "taxonomy_locale";
    const CONFIG_STOCK = "stock";
    const CONFIG_STOCK_AVAILABILITY = "availability";
    const CONFIG_ATTRIBUTES = 'attributes';
    const CONFIG_CUSTOM_ATTRIBUTES = 'custom_attributes';
    const CONFIG_ATTRIBUTE = 'attribute';
    const CONFIG_ATTRIBUTE_CODE = 'code';
    const CONFIG_ATTRIBUTE_LABEL = 'label';
    const CONFIG_ATTRIBUTE_TYPE = 'type';
    const CONFIG_ATTRIBUTE_PATH = 'path';
    const CONFIG_ATTRIBUTE_MAGENTO = 'magento';
    const CONFIG_ATTRIBUTE_EPPAV = 'eppav';
    const CONFIG_ATTRIBUTE_PREFIX = 'prefix';
    const CONFIG_ATTRIBUTE_SUFFIX = 'suffix';
    const CONFIG_ATTRIBUTE_COMPOSED_VALUE = 'composed_value';
    const CONFIG_ATRIBUTE_CONVERT = 'convert';
    const CONFIG_ATTRIBUTE_DESCRIPTION = 'description';
    const CONFIG_STOCK_STATUS_DEPENDENCE = 'stock_status_dependence';
    const CONFIG_ALL_IMAGES = 'all_images';
    const CONFIG_ALL_CATEGORIES = 'all_categories';
    const CONFIG_TIER_PRICES = 'tier_prices';
    const CONFIG_SORT_ATTRIBUTE = "sort_attribute";
    const CONFIG_SORT_ORDER = "sort_order";
    const CONFIG_CATEGORY_TREE = 'category_tree';
    const CONFIG_ALLOW_CUSTOM_ATTRIBUTES = 'allow_custom_attributes';
    const CONFIG_SHIPPING = 'shipping';
    const CONFIG_SHIPPING_DEPENDENT_ATTRIBUTE = "dependent_attribute";
    const CONFIG_SHIPPING_METHOD_NAME = "method_name";
    const CONFIG_SHIPPING_COST_SETUP = "cost_setup";
    const CONFIG_FILTER_PARENTS_CHILDS = 'parents_childs';
    const CONFIG_FILTER_CATEGORIES = 'categories';
    const CONFIG_FILTER_TYPES = 'types';
    const CONFIG_FILTER_STOCK_STATUS_DEPENDENCE = 'stock_status_dependence';
    const CONFIG_FILTER_EXPORT_OUT_OF_STOCK = 'export_out_of_stock';
    const CONFIG_FILTER_VISIBILITY = 'visibility';
    const CONFIG_FILTER_VISIBILITY_PARENT = 'visibility_parent';
    const CONFIG_FILTER_CONDITIONS = 'conditions';
    const CONFIG_ATRIBUTE_TYPE = "type";
    const CONFIG_ATRIBUTE_TYPE_DISABLED = "disabled";
    const CONFIG_ATRIBUTE_TYPE_CSV_DISABLED = "csv_disabled";
    const CONFIG_ATRIBUTE_TYPE_CUSTOM = "custom";
    const CONFIG_ATRIBUTE_POST_PROCESS = "postproc";
    const CONFIG_ATRIBUTE_POST_PROCESS_CDATA = "cdata";
    const CONFIG_ATRIBUTE_TAG = "tag";
    const CONFIG_CRON_RULES = "rules";

    //Export multi attribtues options
    const CONFIG_MULTI_ATRIBS_OPTION_EXPORT_PARENT_VALUE = '3'; // eppav_1 - Export parent ptoduct attribtue value
    const CONFIG_MULTI_ATRIBS_OPTION_EXPORT_PARENT_VALUE_IF_EMPTY = '4'; //eppav_2 - Export parent ptoduct attribtue value

    /**#@+
     * Profile's Statuses
     */
    const STATUS_NEW = 0;
    const STATUS_RUNNING = 1;
    const STATUS_INTERRUPTED = 2;
    const STATUS_ERROR = 3;
    const STATUS_FINISHED = 4;
    const STATUS_ENABLED = 5;
    const STATUS_DISABLED = 6;
    /**#@-*/

    // 	/**
    // 	 * CMS page cache tag
    // 	 */
    // 	const CACHE_TAG = 'nostress_koongo';

    // 	/**
    // 	 * Prefix of model events names
    // 	 *
    // 	 * @var string
    // 	 */
    // 	protected $_eventPrefix = 'nostress_koongo';

    /**
     * Channel instance related to current profile.
     * @var unknown_type
     */
    protected $_channel;

    /**
     * Feed instance related to current profile.
     * @var unknown_type
     */
    protected $_feed;

    /*
     * Feed config attributes which souldn't be included into product loader params
     * @var array
     */
    protected $excludedLoaderParams = ["all_images","all_categories","encoding","decimal_delimiter","price_format","link_to_specification","allow_custom_attributes","stock","custom_params","shipping", "attributes","load_product_reviews"];

    /**
     * Data transformation parameters
     * @var array
     */
    protected $_transformParams;

    /**
     * Store dependent parameters.
     * @var unknown_type
     */
    protected $_storeDependentParams;

    /**
     * Profile configuration magento attribute codes list
     */
    protected $_configAttributes;

    /*
     * @var \Nostress\Koongo\Model\Channel\FeedFactory
    */
    protected $feedFactory;

    /*
     * @var \Nostress\Koongo\Model\ChannelFactory
    */
    protected $channelFactory;

    /*
     * @var \Nostress\Koongo\Model\Config\Source\Attributes
     */
    protected $attributeSource;

    /*
     *	\Nostress\Koongo\Model\Config\Source\Datetimeformat
    */
    protected $_datetimeformat;

    /*
     * @var \Nostress\Koongo\Model\Cache\Product
    */
    protected $cacheProduct;

    /**
     * @var \Nostress\Koongo\Helper\Profile
     */
    protected $profileHelper;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Nostress\Koongo\Helper\Data $helper
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Nostress\Koongo\Model\Translation $translation
     * @param \Nostress\Koongo\Model\Channel\FeedFactory $feedFactory
     * @param \Nostress\Koongo\Model\ChannelFactory $channelFactory
     * @param \Nostress\Koongo\Model\Config\Source\Attributes $attributeSource
     * @param \Nostress\Koongo\Model\Config\Source\Datetimeformat $datetimeformat
     * @param \Nostress\Koongo\Model\Cache\Product $cacheProduct
     * @param \Nostress\Koongo\Helper\Profile $profileHelper
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Nostress\Koongo\Helper\Data $helper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Nostress\Koongo\Model\Translation $translation,
        \Nostress\Koongo\Model\Channel\FeedFactory $feedFactory,
        \Nostress\Koongo\Model\ChannelFactory $channelFactory,
        \Nostress\Koongo\Model\Config\Source\Attributes $attributeSource,
        \Nostress\Koongo\Model\Config\Source\Datetimeformat $datetimeformat,
        \Nostress\Koongo\Model\Cache\Product $cacheProduct,
        \Nostress\Koongo\Helper\Profile $profileHelper,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->feedFactory = $feedFactory;
        $this->channelFactory = $channelFactory;
        $this->attributeSource = $attributeSource;
        $this->_datetimeformat = $datetimeformat;
        $this->cacheProduct = $cacheProduct;
        $this->profileHelper = $profileHelper;
        parent::__construct($context, $registry, $helper, $storeManager, $translation, $resource, $resourceCollection, $data);
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Nostress\Koongo\Model\ResourceModel\Channel\Profile');
    }

    /**
     * Update profile data
     */
    public function updateData($data)
    {
        $columns = ["entity_id","name","filename"];
        foreach ($columns as $index) {
            if (isset($data[$index])) {
                $value = $data[$index];

                if ($index == "filename") {
                    $this->changeFilename($value);
                } else {
                    $this->setData($index, $value);
                }

                unset($data[$index]);
            }
        }

        $data = $this->preprocessShippingCost($data);
        $data= $this->preprocessNewLineCharacter($data);

        //copy values from data to config
        $config = $this->getConfig();
        $config = $this->copyConfigData($data, $config);
        $this->setConfig($config);

        $this->adjustMultiAttributes();
        $this->compressFeedFile();
        $this->resetUrl();
    }

    public function exportCategoryTree($feedConfig = null)
    {
        if (!isset($feedConfig)) {
            $feedConfig = $this->getConfigItem(self::CONFIG_FEED, true, self::CONFIG_COMMON);
        }

        return $this->getArrayField(self::CONFIG_CATEGORY_TREE, $feedConfig, false);
    }

    public function exportCustomAttributes($feedConfig = null)
    {
        if (!isset($feedConfig)) {
            $feedConfig = $this->getConfigItem(self::CONFIG_FEED, true, self::CONFIG_COMMON);
        }

        return $this->getArrayField(self::CONFIG_ALLOW_CUSTOM_ATTRIBUTES, $feedConfig, false);
    }

    public function exportProducts()
    {
        $attributes = $this->getAttributesFromConfig();
        if (isset($attributes) && !empty($attributes)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @return \Nostress\Koongo\Model\Channel\Feed
     */
    public function getFeed()
    {
        if (!isset($this->_feed)) {
            $this->_feed = $this->feedFactory->create()->getFeedByCode($this->getFeedCode());
        }
        return $this->_feed;
    }

    public function getChannel()
    {
        if (!isset($this->_channel)) {
            $this->_channel = $this->channelFactory->create();
            $this->_channel->setChannelCode($this->getFeed()->getChannelCode());
        }
        return $this->_channel;
    }

    public function getTaxonomyLabel()
    {
        if ($this->getFeed()->hasTaxonomy()) {
            return $this->getChannel()->getLabel();
        } else {
            return "";
        }
    }

    public function setFeed($feed)
    {
        $this->_feed = $feed;
    }

    /**
     * Creates new filename or adds suffix to an existing filename.
     */
    public function defineFilename($channelCode = null, $filename = null, $resetUrl = true)
    {
        if (empty($channelCode)) {
            $channelCode = $this->getFeed()->getChannelCode();
        }

        if (!isset($filename)) {
            $fileIndex = rand(0, 10000);
            $filename = $channelCode . "_" . $fileIndex;
        }
        $filename .= "." . $this->getFeed()->getFileType();
        $this->setFilename($filename);
        if ($resetUrl) {
            $this->resetUrl();
        }
    }

    /*
     *  Returns feed filname
     *  @param  $includeDirPath
     *  @param $addDate - Add current date to filename - only if filename contains date substitutional character
     *  @param $fileSuffix If null, than filname is returned with original suffix.
     */
    public function getFilename($includeDirPath = false, $addDate = true, $fileSuffix = null)
    {
        $filename = parent::getFilename();

        if ($addDate) {
            $lastRunTime = $this->getLastRunTime();
            $timestamp = $lastRunTime === null ? strtotime('now') : strtotime($this->getLastRunTime());
            $filename = $this->helper->addDateToString($filename, $timestamp);
        }
        if (isset($fileSuffix)) {
            $filename = $this->helper->changeFileSuffix($filename, $fileSuffix);
        }

        if ($includeDirPath) {
            $feedDir = $this->getFeedDirectoryName();
            $dirPath = $this->helper->getFeedStorageDirPath("", $feedDir);
            $this->helper->createDirectory($dirPath);
            $filename = $dirPath . $filename;
        }
        return $filename;
    }

    public function resetUrl()
    {
        $this->setUrl($this->getFileUrl());
    }

    public function getDefaultConfig($config = null)
    {
        if (!isset($config)) {
            $config = [];
        }
        $config[self::CONFIG_GENERAL] = [self::CONFIG_COMPRESS_FILE =>"0"];
        $config[self::CONFIG_FILTER] = [
            self::CONFIG_FILTER_TYPES => "",
            self::CONFIG_FILTER_PARENTS_CHILDS => "0",
            self::CONFIG_FILTER_STOCK_STATUS_DEPENDENCE => \Nostress\Koongo\Model\Config\Source\Stockdependence::STOCK,
            self::CONFIG_FILTER_EXPORT_OUT_OF_STOCK => "0",
            self::CONFIG_FILTER_VISIBILITY => ["4"],
            self::CONFIG_FILTER_VISIBILITY_PARENT => ["4"],
            self::CONFIG_FILTER_CONDITIONS => ""
        ];

        $common = 	[
                self::CONFIG_SORT_ATTRIBUTE => "",
                self::CONFIG_SORT_ORDER => "ASC",
                self::CONFIG_ALL_IMAGES => "1",
                self::CONFIG_ALL_CATEGORIES => "1",
                self::CONFIG_TIER_PRICES => "1",
                //set default shipping dependent attribute
                self::CONFIG_SHIPPING => [self::CONFIG_SHIPPING_DEPENDENT_ATTRIBUTE => $this->attributeSource->addModuleAttributePrefix("price_final_include_tax")]
                    ];
        $config[self::CONFIG_FEED] = [self::CONFIG_COMMON => $common];

        //Default cron rule
        $rule = [	"days_interval" => \Nostress\Koongo\Model\Config\Source\Scheduledays::EVERY_DAY,
                    "times_interval" => \Nostress\Koongo\Model\Config\Source\Scheduletimes::EVERY_4H,
                    "time_hours" => "1",
                    "time_minutes" => "0",
                    "enabled" => "on"];
        $config[self::CONFIG_CRON] = [self::CONFIG_CRON_RULES => [$rule] ];
        return $config;
    }

    public function setStatus($status, $setLastRunTime = false)
    {
        $id = $this->getId();
        if (!$id) {
            return;
        }
        $this->log("Export profile {$id} {$status}");
        $this->setData(self::STATUS, $status);
        if ($setLastRunTime) {
            $this->setLastRunTime($this->_datetimeformat->getDateTime(null, true));
        }
        $this->save();
    }

    public function setMessageStatusError($message, $status, $errorLink = "")
    {
        $this->setMessage($message);
        $this->setStatus($status);
        $this->save();
    }

    public function setConfig($config)
    {
        parent::setConfig(json_encode($config));
    }

    public function getConfig()
    {
        $id = $this->getId();
        if (!isset($id)) {
            return null;
        }
        $config = json_decode(parent::getConfig(), true);
        return $config;
    }

    public function getMagentoAttributes()
    {
        return $this->getAttributesFromConfig(true);
    }

    public function getAttributesWithDescription()
    {
        $profileAttributes = $this->getConfigItem(Profile::CONFIG_FEED, true, Profile::CONFIG_ATTRIBUTES);
        $feedAttributes = $this->getFeed()->getFeedAttributes();
        $indexedFeedAttributes = [];
        foreach ($feedAttributes as $feedAttribute) {
            if (isset($feedAttribute[self::CONFIG_ATTRIBUTE_CODE])) {
                $code = $feedAttribute[self::CONFIG_ATTRIBUTE_CODE];
            } else {
                $code = $this->helper->createCode($feedAttribute[self::CONFIG_ATTRIBUTE_LABEL]);
            }
            $indexedFeedAttributes[$code] = $feedAttribute;
        }

        foreach ($profileAttributes as $index => $attribute) {
            if (isset($attribute[self::CONFIG_ATTRIBUTE_CODE]) && isset($indexedFeedAttributes[$attribute[self::CONFIG_ATTRIBUTE_CODE]])) {
                $profileAttributes[$index][self::CONFIG_ATTRIBUTE_DESCRIPTION] = $indexedFeedAttributes[$attribute[self::CONFIG_ATTRIBUTE_CODE]][self::CONFIG_ATTRIBUTE_DESCRIPTION];
            }
        }

        return $profileAttributes;
    }

    public function getCustomAttributes()
    {
        $customAttributes = $this->getConfigItem(Profile::CONFIG_FEED, false, Profile::CONFIG_CUSTOM_ATTRIBUTES);
        if (empty($customAttributes)) {
            $customAttributes = [];
        }

        return $customAttributes;
    }

    public function getWriterParams()
    {
        $params = $this->getConfigItem(self::CONFIG_GENERAL);
        $params["full_filename"] = $this->getFilename(true, true);
        $params["filename"] = $this->getFilename(false, true);
        $params["zip_filename"] = $this->getFilename(true, true, \Nostress\Koongo\Helper\Data::FILE_TYPE_ZIP);
        return $params;
    }

    public function getLoaderParams()
    {
        $filterConfig = $this->getConfigItem(self::CONFIG_FILTER);
        $feedCommonConfig = $this->getConfigItem(self::CONFIG_FEED, true, self::CONFIG_COMMON);
        $feed = $this->getFeed();
        $attributes = $this->getAttributesFromConfig();
        $attributes = $this->attributeSource->filterStaticAttributes($attributes);
        $attributes = $this->addInfoToAttributes($attributes);

        $params = [];
        //params from profile and feed columns
        $params["profile_id"] = $this->getId();
        $params["store_id"] = $this->getStoreId();
        $params["taxonomy_code"] = $feed->getTaxonomyCode();
        $params["taxonomy_locale"] = $this->getConfigItem(self::CONFIG_GENERAL, false, self::CONFIG_TAXONOMY_LOCALE);

        //params from profile configuration
        $params["stock_status_dependence"] = $this->getArrayField(self::CONFIG_STOCK_STATUS_DEPENDENCE, $filterConfig, \Nostress\Koongo\Model\Config\Source\Stockdependence::STOCK);
        $params["conditions"] = $filterConfig;

        //load other feed common config parameters
        foreach ($feedCommonConfig as $index => $item) {
            if (!in_array($index, $this->excludedLoaderParams)) {
                $params[$index] = $item;
            }
        }

        $params = $this->getTransformStoreParams($params);

        //load all categories condition
        $catCond = $this->getArrayField(self::CONFIG_ALL_CATEGORIES, $feedCommonConfig, "1");
        if (empty($catCond)) {
            unset($attributes[$this->attributeSource->addModuleAttributePrefix("categories")]);
        }

        //load all images condition
        $imgCond = $this->getArrayField(self::CONFIG_ALL_IMAGES, $feedCommonConfig, "1");

        if (empty($imgCond)) { //If empty string or 0
            unset($attributes[$this->attributeSource->addModuleAttributePrefix("media_gallery")]);
        }

        //load tier prices condition
        $tierPriceCond = $this->getArrayField(self::CONFIG_TIER_PRICES, $feedCommonConfig, "1");

        if (empty($tierPriceCond)) { //If empty string or 0
            unset($attributes[$this->attributeSource->addModuleAttributePrefix("tier_prices")]);
        }

        $params["attributes"] = $attributes;

        //params from module configuration
        $params["batch_size"] = $this->helper->getModuleConfig(\Nostress\Koongo\Helper\Data::PARAM_BATCH_SIZE);
        $params["allow_placeholder_images_export"] = $this->helper->getModuleConfig(\Nostress\Koongo\Helper\Data::PARAM_ALLOW_PLACEHOLDER, false, false, $this->getStoreId());
        $params["allow_inactive_categories_export"] = $this->helper->getModuleConfig(\Nostress\Koongo\Helper\Data::PARAM_ALLOW_INACTIVE_CATEGORIES_EXPORT, false, false, $this->getStoreId());
        $params["category_lowest_level"] = $this->helper->getModuleConfig(\Nostress\Koongo\Helper\Data::PARAM_CATEGORY_LOWEST_LEVEL, false, false, $this->getStoreId());
        $params["stock_website_id"] = $this->helper->getStockWebsiteId();

        return $params;
    }

    /**
    * Get parameters for XSL and XML tranformation
     */
    public function getTransformParams()
    {
        if (!isset($this->_transformParams)) {
            $filterConfig = $this->getConfigItem(self::CONFIG_FILTER);

            $params = $this->getConfigItem(self::CONFIG_FEED, true, self::CONFIG_COMMON);
            $params[self::CONFIG_ATTRIBUTES] = $this->getConfigItem(self::CONFIG_FEED, true, self::CONFIG_ATTRIBUTES);
            $params[self::CONFIG_CUSTOM_ATTRIBUTES] = $this->getConfigItem(self::CONFIG_FEED, false, self::CONFIG_CUSTOM_ATTRIBUTES);
            if (empty($params[self::CONFIG_CUSTOM_ATTRIBUTES])) {
                $params[self::CONFIG_CUSTOM_ATTRIBUTES] = [];
            }

            $params["file_type"] = $this->getFeed()->getFileType();
            $params["file_url"] = $this->getFileUrl();
            $params["store_id"] = $this->getStoreId();
            $params["profile_id"] = $this->getId();
            $params[self::CONFIG_FILTER_PARENTS_CHILDS] = $this->getArrayField(self::CONFIG_FILTER_PARENTS_CHILDS, $filterConfig, "0");
            $params["xslt"] = $this->getFeed()->getTrnasformationXslt();
            $params["is_debug_mode"] = $this->helper->isDebugMode();
            $params = $this->getTransformStoreParams($params);
            $params = $this->getXmlAndXsltTransformParams($params);
            $params = $this->getMultiAttributeColumns($params);

            $attributesFromConfig = $this->getAttributesFromConfig();
            $multiselectAttributesOptionArray = $this->attributeSource->getMultiSelectAttributeOptions($attributesFromConfig);

            $attributeSetCode = $this->attributeSource->addModuleAttributePrefix("attribute_set");
            if (in_array($attributeSetCode, $attributesFromConfig)) {
                $multiselectAttributesOptionArray[$attributeSetCode] = $this->attributeSource->getProductAttributeSetOptions();
            }

            $params["multiselect_attributes_option_array"] = $multiselectAttributesOptionArray;
            $params["default_directory_name"] = $dirPath = $this->helper->getFeedStorageDirPath();
            $this->helper->createDirectory($dirPath);
            $this->_transformParams = $params;
        }
        return $this->_transformParams;
    }

    public function getFtpParams()
    {
        return $this->getConfigItem(self::CONFIG_FEED, false, self::CONFIG_FTP);
    }

    public function getConfigItem($index, $exception = true, $subIndex = null, $subSubIndex = null)
    {
        $config = $this->getConfig();

        $item = $this->getArrayField($index, $config);
        if (!isset($item) && $exception) {
            $this->logAndException($this->translation->getErrorByCode(2), $index);
        }

        if (isset($subIndex)) {
            $subItem = $this->getArrayField($subIndex, $item);
            if (!isset($subItem) && $exception) {
                $this->logAndException($this->translation->getErrorByCode(2), $index . "/" . $subIndex);
            }

            if (isset($subSubIndex)) {
                $subSubItem = $this->getArrayField($subSubIndex, $subItem);
                if (!isset($subSubItem) && $exception) {
                    $this->logAndException($this->translation->getErrorByCode(2), $index . "/" . $subIndex . "/" . $subSubIndex);
                }
                return $subSubItem;
            } else {
                return $subItem;
            }
        } else {
            return $item;
        }
    }

    public function getPreview()
    {
        $fileNameWithPath = $this->getFileName(true, true);

        $config = $this->getConfigItem(Profile::CONFIG_FEED, true, Profile::COMMON);
        $encoding = isset($config[self::CONFIG_ENCODING]) ? $config[self::CONFIG_ENCODING] : 'utf-8';

        if ($this->getFeed()->getFileType() == 'xml') {
            $preview = $this->profileHelper->readXmlPreview($fileNameWithPath, $encoding);
        } else {
            $cd = isset($config[self::COLUMN_DELIMITER]) ? $config[self::COLUMN_DELIMITER] : ';';
            $enc = isset($config[self::TEXT_ENCLOSURE]) ? $config[self::TEXT_ENCLOSURE] : '"';

            $preview = $this->profileHelper->readCsvPreview($fileNameWithPath, $cd, $enc, $encoding);
        }

        return $preview;
    }

    protected function changeFilename($newFilename)
    {
        $currentFilename = $this->getFilename(true, true);
        $currentFilenameCompressed = $this->getFilename(true, true, \Nostress\Koongo\Helper\Data::FILE_TYPE_ZIP);
        $this->defineFilename(null, $newFilename, false);

        $newFilename = $this->getFilename(true, true);
        $this->helper->renameFile($currentFilename, $newFilename);

        if (file_exists($currentFilenameCompressed)) {
            $newFilenameCompressed = $this->getFilename(true, true, \Nostress\Koongo\Helper\Data::FILE_TYPE_ZIP);
            $this->helper->renameFile($currentFilenameCompressed, $newFilenameCompressed);
        }
    }

    protected function getMultiAttributeColumns($params = [])
    {
        $multiAttributeColumns = [];

        $index = $this->attributeSource->addModuleAttributePrefix("categories");
        $multiAttributeColumns[$index] = $this->cacheProduct->getCacheColumns("category");

        $index = $this->attributeSource->addModuleAttributePrefix("media_gallery");
        $multiAttributeColumns[$index] = $this->cacheProduct->getCacheColumns("media_gallery");

        $index = $this->attributeSource->addModuleAttributePrefix("tier_prices");
        $multiAttributeColumns[$index] = $this->cacheProduct->getCacheColumns("tier_price");

        $index = $this->attributeSource->addModuleAttributePrefix("reviews");
        $multiAttributeColumns[$index] = $this->cacheProduct->getCacheColumns("review");

        $params["multi_attribute_columns"] = $multiAttributeColumns;
        return $params;
    }

    /**
     * Copy config data from source to destination array
     */
    protected function copyConfigData($src, $dst)
    {
        $forceCopyItems = ['cost_setup', 'conditions', 'types', 'visibility', 'visibility_parent', 'convert','custom_attributes','rules'];

        foreach ($src as $key => $item) {
            if (!is_array($item) || in_array($key, $forceCopyItems, true)) {
                $dst[$key] = $item;
            } else {
                $newDst = [];
                if (isset($dst[$key])) {
                    $newDst = $dst[$key];
                }
                $dst[$key] = $this->copyConfigData($item, $newDst);
            }
        }
        return $dst;
    }

    /*
     * Preprocess config shipping cost setup.
     */
    protected function preprocessShippingCost($data)
    {
        if (!isset($data[self::CONFIG_FEED][self::CONFIG_COMMON][self::CONFIG_SHIPPING])) {
            return $data;
        }

        if (!isset($data[self::CONFIG_FEED][self::CONFIG_COMMON][self::CONFIG_SHIPPING][self::CONFIG_SHIPPING_COST_SETUP])) {
            $setup = [];
        } else {
            $setup = $data[self::CONFIG_FEED][self::CONFIG_COMMON][self::CONFIG_SHIPPING][self::CONFIG_SHIPPING_COST_SETUP];
        }

        foreach ($setup as $key => $item) {
            if (isset($item['delete']) && $item['delete'] == "1") {
                unset($setup[$key]);
            } else {
                if (isset($item['delete'])) {
                    unset($setup[$key]['delete']);
                }
                if (isset($item['order'])) {
                    unset($setup[$key]['order']);
                }
            }
        }

        $data[self::CONFIG_FEED][self::CONFIG_COMMON][self::CONFIG_SHIPPING][self::CONFIG_SHIPPING_COST_SETUP] = $setup;
        return $data;
    }

    /**
     * Adjust multi attritues according to profile settings
     *
     * @return void
     */
    protected function adjustMultiAttributes()
    {
        $commonConfig = $this->getConfigItem(self::CONFIG_FEED, false, self::CONFIG_COMMON);
        $exportAllImagesSetting = $this->getArrayField(self::CONFIG_ALL_IMAGES, $commonConfig, 0);

        if (empty($exportAllImagesSetting) || $exportAllImagesSetting == "1") {
            $exportAllImagesSettingEppav = "0";
        } else { //Transform to eppav setting ($exportAllImagesSetting = 3 or 4)
            $exportAllImagesSettingEppav = $exportAllImagesSetting - 2;
        } //EPPAV - 1 = Yes, 2 = If empty, 3-2 => 1, 4 - 2 => 2

        $exportAllCategoriesSetting = $this->getArrayField(self::CONFIG_ALL_CATEGORIES, $commonConfig, 0);
        if (empty($exportAllCategoriesSetting) || $exportAllCategoriesSetting == "1") {
            $exportAllCategoriesSettingEppav = "0";
        } else { //Transform to eppav setting ($exportAllCategoriesSetting = 3 or 4)
            $exportAllCategoriesSettingEppav = $exportAllCategoriesSetting - 2;
        } //EPPAV - 1 = Yes, 2 = If empty, 3-2 => 1, 4 - 2 => 2

        $exportTierPricesSetting = $this->getArrayField(self::CONFIG_TIER_PRICES, $commonConfig, 0);
        if (empty($exportTierPricesSetting) || $exportTierPricesSetting == "1") {
            $exportTierPricesSettingEppav = "0";
        } else { //Transform to eppav setting ($exportTierPricesSetting = 3 or 4)
            $exportTierPricesSettingEppav = $exportTierPricesSetting - 2;
        } //EPPAV - 1 = Yes, 2 = If empty, 3-2 => 1, 4 - 2 => 2

        $exportReviewsSettingEppav = 2; //2 = If empty

        $profileAttributes = $this->getConfigItem(Profile::CONFIG_FEED, true, Profile::CONFIG_ATTRIBUTES);

        $mediaGalleryAttributeCode = $this->attributeSource->addModuleAttributePrefix("media_gallery");
        $categoriesAttributeCode = $this->attributeSource->addModuleAttributePrefix("categories");
        $tierPricesAttributeCode = $this->attributeSource->addModuleAttributePrefix("tier_prices");
        $reviewsAttributeCode = $this->attributeSource->addModuleAttributePrefix("reviews");

        $updateConfig = false;
        foreach ($profileAttributes as $index => $attribute) {
            if (isset($attribute[self::CONFIG_ATTRIBUTE_MAGENTO])) {
                if ($attribute[self::CONFIG_ATTRIBUTE_MAGENTO] == $mediaGalleryAttributeCode) {
                    $profileAttributes[$index][self::CONFIG_ATTRIBUTE_EPPAV] = $exportAllImagesSettingEppav;
                    $updateConfig = true;
                } elseif ($attribute[self::CONFIG_ATTRIBUTE_MAGENTO] == $categoriesAttributeCode) {
                    $profileAttributes[$index][self::CONFIG_ATTRIBUTE_EPPAV] = $exportAllCategoriesSettingEppav;
                    $updateConfig = true;
                } elseif ($attribute[self::CONFIG_ATTRIBUTE_MAGENTO] == $tierPricesAttributeCode) {
                    $profileAttributes[$index][self::CONFIG_ATTRIBUTE_EPPAV] = $exportTierPricesSettingEppav;
                    $updateConfig = true;
                } elseif ($attribute[self::CONFIG_ATTRIBUTE_MAGENTO] == $reviewsAttributeCode) {
                    $profileAttributes[$index][self::CONFIG_ATTRIBUTE_EPPAV] = $exportReviewsSettingEppav;
                    $updateConfig = true;
                }
            }
        }

        if ($updateConfig) {
            $config = $this->getConfig();
            $config[Profile::CONFIG_FEED][Profile::CONFIG_ATTRIBUTES] = $profileAttributes;
            $this->setConfig($config);
        }
    }

    /**
     * Decode space characters at new line option
     */
    protected function preprocessNewLineCharacter($data)
    {
        if (isset($data[self::CONFIG_FEED][self::CONFIG_COMMON][self::CONFIG_NEW_LINE])) {
            $data[self::CONFIG_FEED][self::CONFIG_COMMON][self::CONFIG_NEW_LINE] = $this->helper->decodeSpaceCharacters($data[self::CONFIG_FEED][self::CONFIG_COMMON][self::CONFIG_NEW_LINE]);
        }

        return $data;
    }

    /**
     * Copmress file if compression sessings changed
     */
    protected function compressFeedFile()
    {
        $compress = $this->getConfigItem(self::CONFIG_GENERAL, false, self::CONFIG_COMPRESS_FILE);

        if ($compress) {
            $zipFilename = $this->getFilename(true, true, \Nostress\Koongo\Helper\Data::FILE_TYPE_ZIP);
            if (!file_exists($zipFilename)) {
                $fullFilename = $this->getFilename(true, true);
                $filename = $this->getFilename(false, true);
                $this->helper->createZip([$filename => $fullFilename], $zipFilename, true);
            }
        }
    }

    /*
     * Load following parameters: media_url, store_locale, store_language, store_country, current_date, current_datetime, current_time, currency_symbol
    */
    protected function getTransformStoreParams($params = [])
    {
        if (!isset($this->_storeDependentParams)) {
            $newParamsArray = [];
            $store = $this->storeManager->getStore($this->getStoreId());

            $newParamsArray["store_locale"] = $this->helper->getStoreLocale($store);
            $newParamsArray["store_language"] = $this->helper->getStoreLanguage($store);
            $newParamsArray["store_country"] = $this->helper->getStoreCountry($store);

            $mediaUrl = $this->helper->getMediaBaseUrl($store);
            $mediaUrl .= $this->helper->getModuleConfig(\Nostress\Koongo\Helper\Data::PARAM_IMAGE_FOLDER);
            $newParamsArray["media_url"] = $mediaUrl;
            $newParamsArray["remove_illegal_chars_reg_expression"] = $this->helper->getModuleConfig(\Nostress\Koongo\Helper\Data::PARAM_REGEXP_ILLEGAL_CHARS, false, false, $this->getStoreId());

            if (!empty($params["currency"])) {
                $newParamsArray["currency_symbol"] = $this->helper->getCurrencySymbol($params["currency"]);
            }

            $format = \Nostress\Koongo\Model\Config\Source\Datetimeformat::STANDARD;
            if (!empty($params["datetime_format"])) {
                $format = $params["datetime_format"];
            }
            $newParamsArray["current_date"] = $this->_datetimeformat->getDate(null, $format);
            $newParamsArray["current_date_time"] = $this->_datetimeformat->getDateTime(null, $format);
            $newParamsArray["current_time"] = $this->_datetimeformat->getTime(null, $format);
            $this->_storeDependentParams = $newParamsArray;
        }

        $params = array_merge($params, $this->_storeDependentParams);
        return $params;
    }

    protected function getXmlAndXsltTransformParams($params = [])
    {
        $cdataSectionElements = [];
        $customColumnsHeader = [];
        $columnsHeader = [];

        //process params
        $attributes = $params[self::CONFIG_ATTRIBUTES];
        if (isset($attributes) && is_array($attributes)) {
            foreach ($attributes as $key => $attribute) {
                if ($attribute[self::CONFIG_ATTRIBUTE_TYPE] != self::DISABLED && $attribute[self::CONFIG_ATTRIBUTE_TYPE] != self::CSV_DISABLED) {
                    $columnsHeader[] = $attribute[self::CONFIG_ATTRIBUTE_LABEL];
                }

                //Prepare CDATA section elements
                if (array_key_exists(self::CONFIG_ATRIBUTE_POST_PROCESS, $attribute) && strpos($attribute[self::CONFIG_ATRIBUTE_POST_PROCESS], self::CONFIG_ATRIBUTE_POST_PROCESS_CDATA) !== false) {
                    $cdataSectionElements[] =  $attribute[self::CONFIG_ATTRIBUTE_LABEL];
                }
            }
        }
        $params[self::CONFIG_ATTRIBUTES] = $attributes;

        //process custom params
        $customAttributes = $params[self::CONFIG_CUSTOM_ATTRIBUTES];
        if (isset($customAttributes) && is_array($customAttributes)) {
            foreach ($customAttributes as $key => $attribute) {
                //Load product attribute label
                if (empty($attribute[self::CONFIG_ATTRIBUTE_LABEL]) && !empty($attribute[self::CONFIG_ATTRIBUTE_MAGENTO])) {
                    $productAttribute = $this->attributeSource->getCatalogProductAttribute($attribute[self::CONFIG_ATTRIBUTE_MAGENTO]);
                    if (empty($productAttribute)) {
                        $attribute[self::CONFIG_ATTRIBUTE_LABEL] = $attribute[self::CONFIG_ATTRIBUTE_MAGENTO];
                    } else {
                        $attribute[self::CONFIG_ATTRIBUTE_LABEL] = $this->attributeSource->getCatalogProductAttributeLabel($productAttribute, $this->getStoreId());
                    }
                }
                //Prepare custom columns header
                $attribute[self::CONFIG_ATRIBUTE_TAG] = $this->helper->createCode($attribute[self::CONFIG_ATTRIBUTE_LABEL], "_", false, ":-");
                $customColumnsHeader[] = $attribute[self::CONFIG_ATTRIBUTE_LABEL];
                //Prepare CDATA section elements
                if (array_key_exists(self::CONFIG_ATRIBUTE_POST_PROCESS, $attribute) && strpos($attribute[self::CONFIG_ATRIBUTE_POST_PROCESS], self::CONFIG_ATRIBUTE_POST_PROCESS_CDATA) !== false) {
                    if (isset($attribute[self::CONFIG_ATRIBUTE_TAG])) {
                        $cdataSectionElements[] = $attribute[self::CONFIG_ATRIBUTE_TAG];
                    } else {
                        $cdataSectionElements[] =  $attribute[self::CONFIG_ATTRIBUTE_LABEL];
                    }
                }
                $customAttributes[$key] = $attribute;
            }
        }
        $params[self::CONFIG_CUSTOM_ATTRIBUTES] = $customAttributes;

        if (!empty($customColumnsHeader)) {
            $params[\Nostress\Koongo\Model\Data\Transformation\Xslt::DATA_CUSTOM_COLUMNS_HEADER] = $customColumnsHeader;
        }
        if (!empty($cdataSectionElements)) {
            $params[\Nostress\Koongo\Model\Data\Transformation\Xslt::DATA_CDATA_SECTION_ELEMENTS] = $cdataSectionElements;
        }
        if (!empty($columnsHeader)) {
            $params[\Nostress\Koongo\Model\Data\Transformation\Xslt::DATA_BASIC_ATTRIBUTES_COLUMN_HEADER] = $columnsHeader;
        }

        return $params;
    }

    protected function getFileUrl()
    {
        $compress = $this->getConfigItem(self::CONFIG_GENERAL, false, self::CONFIG_COMPRESS_FILE);

        $filename = $this->getFilename();

        if ($compress == "1") {
            $filename = $this->getFilename(false, true, \Nostress\Koongo\Helper\Data::FILE_TYPE_ZIP);
        }

        $feedDir = $this->getFeedDirectoryName();
        $filename = $this->helper->getFeedStorageUrl($filename, $feedDir, $this->storeManager->getStore($this->getStoreId()));
        return $filename;
    }

    protected function getFeedDirectoryName()
    {
        return $this->getFeed()->getChannelCode();
    }

    protected function getAttributesFromConfig($loadFilterAttributes = false)
    {
        if (!empty($this->_configAttributes) && $loadFilterAttributes == false) {
            return $this->_configAttributes;
        }

        $feedConfig = $this->getConfigItem(self::CONFIG_FEED, true);
        $feedCommonConfig = $this->getConfigItem(self::CONFIG_FEED, true, self::CONFIG_COMMON);
        $attributeInfoArray =  $this->getArrayField(self::CONFIG_ATTRIBUTES, $feedConfig, []);
        $customAttributeInfoArray =  $this->getArrayField(self::CONFIG_CUSTOM_ATTRIBUTES, $feedConfig, []);
        if ($customAttributeInfoArray == "") {
            $customAttributeInfoArray = [];
        }

        $attributes = [];
        if (empty($attributeInfoArray) && empty($customAttributeInfoArray)) {
            if ($this->exportCategoryTree($feedCommonConfig)) {
                return $attributes;
            }

            $this->logAndException($this->translation->getErrorByCode(3));
        }

        //standard attriutes
        $attributeArray = $this->getMagentoAttributesFromConfigAttributes($attributeInfoArray);
        $attributes = array_merge($attributes, $attributeArray);

        //custom attributes
        $customAttributeArray = $this->getMagentoAttributesFromConfigAttributes($customAttributeInfoArray);
        $attributes = array_merge($attributes, $customAttributeArray);

        //attribute from stock setup
        $availabilityAttribute = $this->getArrayField(self::CONFIG_STOCK, $feedCommonConfig, "", self::CONFIG_STOCK_AVAILABILITY);
        if (!empty($availabilityAttribute)) {
            $attributes[] = $availabilityAttribute;
        }

        //attribute from shipping setup
        $dependentAttribute = $this->getArrayField(self::CONFIG_SHIPPING, $feedCommonConfig, "", self::CONFIG_SHIPPING_DEPENDENT_ATTRIBUTE);
        if (!empty($dependentAttribute)) {
            $attributes[] = $dependentAttribute;
        }

        //load filer attributes
        if ($loadFilterAttributes) {
            $filterAttributes = $this->getConfigFilterAttributes();
            $attributes = array_merge($attributes, $filterAttributes);
        }

        $attributes = array_unique($attributes);

        //remove unexisting attributes
        $allAttributes= $this->attributeSource->getProductAttributeCodes($this->getStoreId(), true, true);
        $attributes = array_intersect($attributes, $allAttributes);

        if ($loadFilterAttributes == false) {
            $this->_configAttributes = $attributes;
        }
        return $attributes;
    }

    protected function getMagentoAttributesFromConfigAttributes($attributeArray)
    {
        $attributes = [];
        foreach ($attributeArray as $attribute) {
            $magentoAttribute = $this->getArrayField(self::CONFIG_ATTRIBUTE_MAGENTO, $attribute);
            if (!empty($magentoAttribute)) {
                $attributes[] = $magentoAttribute;
            }

            //load attributes from composed value
            $composedValue = $this->getArrayField(self::CONFIG_ATTRIBUTE_COMPOSED_VALUE, $attribute, "");
            $composedValueAttributes = $this->helper->grebVariables($composedValue);
            if (!empty($composedValueAttributes)) {
                $attributes = array_merge($attributes, $composedValueAttributes);
            }
        }
        return $attributes;
    }

    protected function getConfigFilterAttributes()
    {
        $attributes = [];
        $filterConfig = $this->getConfigItem(self::CONFIG_FILTER);
        if (isset($filterConfig[self::CONFIG_CONDITIONS])) {
            $conditions = $filterConfig[self::CONFIG_CONDITIONS];
            if (isset($conditions[self::CONFIG_CONDITIONS]) && is_array($conditions[self::CONFIG_CONDITIONS])) {
                $attributes = $this->getConditionAttributes($conditions);
            }
        }
        return $attributes;
    }

    /**
     * Returns attriute codes of all used attriutes
     * @param unknown_type $condition
     * @return multitype:Ambigous <multitype:unknown , multitype:> Ambigous <unknown, mixed>
     */
    protected function getConditionAttributes($condition)
    {
        $attributes = [];
        foreach ($condition[self::CONFIG_CONDITIONS] as $key => $conditionItem) {
            if (isset($conditionItem[self::CONFIG_CONDITIONS]) && is_array($conditionItem[self::CONFIG_CONDITIONS])) {
                $res = $this->getConditionAttributes($conditionItem);
                $attributes = array_merge($attributes, $res);
                $attributes = array_unique($attributes);
            } else {
                if (isset($conditionItem['attribute'])) {
                    $attributes[] = $conditionItem['attribute'];
                }
            }
        }

        return $attributes;
    }

    protected function addInfoToAttributes($attributeCodes)
    {
        $attributesCollection = $this->attributeSource->getCatalogProductAttribute($attributeCodes);
        $attributes = [];
        foreach ($attributeCodes as $code) {
            $attributes[$code] = [];
        }

        foreach ($attributesCollection as $item) {
            $code = $item->getAttributeCode();
            $input = $item->getFrontendInput();
            $attributes[$code]["type"] = $input;
        }
        return $attributes;
    }

    /**
     * Get Profile ID
     *
     * @return int|null
     */
    public function getProfileId()
    {
        return $this->getData(self::PROFILE_ID);
    }

    /**
     * Get Store ID
     *
     * @return int|null
    */
    public function getStoreId()
    {
        return $this->getData(self::STORE_ID);
    }

    /**
     * Get profile name
     *
     * @return string
    */
    public function getName()
    {
        return $this->getData(self::NAME);
    }

    /**
     * Get feed url
     *
     * @return string
    */
    public function getUrl()
    {
        return $this->getData(self::URL);
    }

    /**
     * Get feed code
     *
     * @return string
    */
    public function getFeedCode()
    {
        return $this->getData(self::FEED_CODE);
    }

    /**
     * Get profile status
     *
     * @return string|enum
    */
    public function getStatus()
    {
        return $this->getData(self::STATUS);
    }

    /**
     * Get message
     *
     * @return string
    */
    public function getMessage()
    {
        return $this->getData(self::MESSAGE);
    }

    /**
     * Get profile creation time
     *
     * @return string
    */
    public function getCreationTime()
    {
        return $this->getData(self::CREATION_TIME);
    }

    /**
     * Get feed update time
     *
     * @return string
    */
    public function getUpdateTime()
    {
        return $this->getData(self::UPDATE_TIME);
    }
}
