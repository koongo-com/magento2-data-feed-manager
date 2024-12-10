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
 * Abstract Model for Koongo connector
 *
 * @category Nostress
 * @package Nostress_Koongo
 *
 */

namespace Nostress\Koongo\Model;

abstract class AbstractModel extends \Magento\Framework\Model\AbstractModel
{
    const ATTRIBUTE = 'attribute';
    const ATTRIBUTE_FILTER = 'attribute_filter';
    const ATTRIBUTES = 'attributes';
    const CONSTANT = 'constant';
    const SUFFIX = 'suffix';
    const PREFIX = 'prefix';
    const PARENT = 'parent';
    const CHILD = 'child';
    const PARENT_ATTRIBUTE_VALUE = 'eppav';
    const POST_PROCESS = 'postproc';
    const LABEL = 'label';
    const CODE = 'code';
    const TAG = 'tag';
    const VALUE = 'value';
    const LIMIT = 'limit';
    const TYPE = "type";
    const TYPES = "types";
    const PRODUCT_TYPE = "product_type";
    const XML = 'xml';
    const CSV = 'csv';
    const TXT = 'txt';
    const HTML = 'html';
    // const URL = 'url';
    const XSLT = 'xslt';
    const FEED = 'feed';
    const PRODUCT = 'product';
    const UPLOAD = 'upload';
    const GENERAL = 'general';
    const PARAM = 'param';
    const LOCALE = 'locale';
    const LANGUAGE = 'language';
    const COUNTRY = 'country';
    const COUNTRY_CODE = 'country_code';
    const TIME = 'time';
    const DATE = 'date';
    const DATE_TIME = 'date_time';
    const TEXT_ENCLOSURE = 'text_enclosure';
    const COLUMN_DELIMITER = 'column_delimiter';
    const NEWLINE = 'new_line';
    const CUSTOM_ATTRIBUTE = 'custom_attribute';
    const CURRENCY = 'currency';
    const PATH = 'path';
    const PATH_IDS = 'path_ids';
    const PARENT_ID = 'parent_id';
    const ID = 'id';
    const LEVEL = 'level';
    const CHILDREN = 'children';
    const DELETE = 'delete';
    const CATEGORY_PATH = 'category_path';
    const CDATA_SECTION_ELEMENTS = "cdata_section_elements";
    const CUSTOM_COLUMNS_HEADER = "custom_columns_header";
    const COLUMNS_HEADER = "columns_header";
    const BASIC_ATTRIBUTES_COLUMNS_HEADER = "basic_attributes_columns_header";
    const DISABLED = 'disabled';
    const CSV_DISABLED = 'csv_disabled';
    const CDATA = 'cdata';
    const COMMON = 'common';
    const STOCK = 'stock';
    const STOCK_STATUS = 'stock_status';
    const CUSTOM_PARAMS = 'custom_params';
    const MULTISELECT_OPTIONS = "mo";
    const PREFIX_VARS = 'pv';
    const SUFFIX_VARS = 'sv';
    const CRON_DAYS = 'cron_days';
    const CRON_TIMES = 'cron_times';
    const AUTO_ADD_NEW_PRODUCTS_USE_DEFAULT = 'automatically_add_new_products_use_default';
    const AUTO_ADD_NEW_PRODUCTS = 'automatically_add_new_products';
    const MEDIA_URL = 'media_url';

    const POSTPROC_DELIMITER = ",";

    const STOCK_STATUS_INSTOCK = '1';
    const STOCK_STATUS_OUTSTOCK = '0';

    const TYPE_HTML = 'html';
    const TYPE_TEXT = 'txt';
    const TYPE_CSV = 'csv';
    const TYPE_XML = 'xml';

    const FILE_PATH = "path";
    const FILE_NAME = "filename";
    const FILE_TYPE = "type";
    const FILE_URL = "file_url";

    const PHP = "php";
    const SQL = "sql";
    const PLATFORM_ATTRIBUTE_MEDIA = 'media_gallery';
    const PLATFORM_ATTRIBUTE_CATEGORIES = 'categories';
    const PLATFORM_ATTRIBUTE_TIER_PRICES = 'tier_prices';
    const PLATFORM_ATTRIBUTE_REVIEWS = 'reviews';
    const PLATFORM_ATTRIBUTE_ALIAS = 'magento';
    const PLATFORM_ATTRIBUTE_TYPE = 'attribute_type';

    //shipping settings
    const SHIPPING = "shipping";
    const SHIPPING_METHOD_NAME = "shipping_method_name";
    const SHIPPING_COST = "shipping_cost";
    const METHOD_NAME = "method_name";
    const COST_SETUP = "cost_setup";
    const DEPENDENT_ATTRIBUTE = "dependent_attribute";
    const PRICE_FROM = "price_from";
    const PRICE_TO = "price_to";
    const COST = "cost";
    const SHIPPING_INTERVAL_MAX = 1000000;
    const SHIPPING_INTERVAL_MIN = 0;

    const SUBMISSION = 'submission';
    const FTP= 'ftp';

    /**
     *
     * @var \Nostress\Koongo\Helper
     */
    public $helper;

    /** @var \Magento\Store\Model\StoreManagerInterface */
    protected $storeManager;

    /**
     *
     * @var \Nostress\Koongo\Model\Translation
     */
    protected $translation;

    /**
     * @var \Magento\Framework\Filesystem\DriverInterface
     */
    protected \Magento\Framework\Filesystem\DriverInterface $driver;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Nostress\Koongo\Helper\Data $helper
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Nostress\Koongo\Model\Translation $translation
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
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        \Magento\Framework\Filesystem\DriverInterface $driver,
        array $data = []
    ) {
        $this->helper = $helper;
        $this->storeManager = $storeManager;
        $this->translation = $translation;
        $this->driver = $driver;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    protected function log($message)
    {
        $this->helper->log($message);
    }

    protected function logAndException($message, $param = null)
    {
        if (is_array($message)) {
            if (isset($message['message'])) {
                $message = $message['message'];
            } else {
                $message = "Error message not specified";
            }
        }

        $translatedMessage = __($message, $param);
        $this->helper->log($translatedMessage);
        throw new \Exception($translatedMessage);
    }

    protected function getArrayField($index, $array, $default = null, $subIndex = null)
    {
        if (!is_array($array)) {
            return $default;
        }

        if (array_key_exists($index, $array)) {
            if (!isset($subIndex)) {
                return $array[$index];
            } elseif (!isset($array[$index][$subIndex])) {
                return $default;
            } else {
                return $array[$index][$subIndex];
            }
        } else {
            return $default;
        }
    }

    public function getHelp($key)
    {
        return $this->helper->getHelp($key);
    }
}
