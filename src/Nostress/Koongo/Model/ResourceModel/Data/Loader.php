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
 * Data loader resource model for export process
* @category Nostress
* @package Nostress_Koongo
*
*
*/

namespace Nostress\Koongo\Model\ResourceModel\Data;

use Magento\CatalogUrlRewrite\Model\CategoryUrlPathGenerator;

/**
 * Blog post mysql resource
 */
class Loader extends \Nostress\Koongo\Model\ResourceModel\AbstractResourceModel
{
    const CPF = 'cpf'; //Alias Catalog product flat
    const PCPF = 'pcpf'; //Alias Parent Catalog product flat
    const CCF = 'ccf'; //Alias Catalog category flat
    const PCCF = 'pccf'; //Alias Catalog category flat
    const CPE = 'cpe'; // Catalog product entity
    const PCPE = 'pcpe'; //Parent atalog product entity
    const CCP = 'ccp'; //Catalog category product
    const CCPI = 'ccpi'; //Catalog category product index
    const CPR = 'cpr';  //Catalog product relation
    const CRPP = 'crpp'; //catalogrule_product_price
    const CPIP = 'cpip'; //catalog_product_index_price
    const CPAMG = 'cpamg'; //Catelog product entity media gallery
    const CPAMGV = 'cpamgv'; //Catelog product entity media gallery value
    const CPEMGVTE = 'cpemgvte'; //Catalog product entity media gallery value to entity
    const WT = 'wt'; // Weee tax
    const CPETP = 'cpetp'; //Catalog product entity tier price
    const CPBS = 'cpbs'; //Catalog product bundle selection
    const CPBO = 'cpbo'; //Catalog product bundle option
    const R = 'r'; //Review
    const RD = 'rd'; //Review detail
    const ROV = 'rov'; //Rating option vote
    const IR = 'ir'; //Inventory reservation

    //Stock tables
    const CISS = 'ciss'; //Catalog inventory stock status
    const PCISS = 'pciss'; //Parent product join of Catalog inventory stock status
    const CISI = 'cisi'; //Catalog inventory stock item
    const PCISI = 'pcisi'; //Parent product - Catalog inventory stock item
    const ISX = 'isx'; //Inventory stock table with stock id in table name

    //Nostress koongo cache tables
    const NKCP = 'nkcp'; //Nostress koongo cache product
    const PNKCP = 'pnkcp'; //Parent Nostress koongo cache product
    const NKCPR = 'nkcpr'; //Nostress koongo cache price
    const NKCPRT = 'nkcprt'; //Nostress koongo cache price tier
    const NKCCP = 'nkccp'; //Nostress koongo cache category path
    const NKCT = 'nkct'; //Nostress koongo cache tax
    const NKCW = 'nkcw'; // Nostress koongo cache Weee
    const NKCPC = 'nkcpc'; //Nostress koongo cache profile category
    const NKCCHC = 'nkcchc'; //Nostress koongo cache channel category
    const NKTC = 'nktc'; //Nostress koongo taxonomy category
    const NKCMG = 'nkcmg'; //Nostress koongo cache media gallery
    const NKCR = 'nkcr'; //Nostress cache reviews
    const NKCS = 'nkcs'; //Nostress koongo cache stock

    const VALUE_COLUMN_SUFFIX = '_value';
    const MAIN_TABLE_SUBST = '{{main_table}}';
    const CATEGORY_ACTIVE = '1';
    /*
     * Default category path delimiter substitution
     */
    const DEF_CATEGORY_PATH_SUBST_DELIMITER = '/$#';

    const FLAT_TYPE_PRODUCT = 'product';
    const FLAT_TYPE_CATEGORY = 'category';

    //Weee parameters
    const WEEE_COLUMN_TOTAL = "total";

    //Loader parameters
    const TAXONOMY_CODE = "taxonomy_code";
    const TAXONOMY_LOCALE = "taxonomy_locale";
    const CONDITIONS = "conditions";
    const BATCH_SIZE = "batch_size";
    const DATETIME_FORMAT = "datetime_format";
    const CATEGORY_PATH_DELIMITER = "category_path_delimiter";
    const STOCK_STATUS_DEPENDENCE = "stock_status_dependence";
    const STOCK_WEBSITE_ID = "stock_website_id"; //Website id for stock data load
    const SORT_ATTRIBUTE = "sort_attribute";
    const SORT_ORDER = "sort_order";
    const CURRENCY = "currency";
    const PRICE_CUSTOMER_GROUP_ID = "price_customer_group_id";
    const MEDIA_URL = "media_url";
    const ALLOW_PLACEHOLDER = "allow_placeholder_images_export";
    const ALLOW_INACTIVE_CATEGORIES_EXPORT = "allow_inactive_categories_export";
    const CATEGORY_LOWEST_LEVEL = 'category_lowest_level';

    //Default values
    const DEF_BATCH_SIZE = 1000;
    const DEF_CATEGORY_PATH_DELIMITER = "/";
    const DEF_CUSTOMER_GROUP_NOT_LOGGED_IN = 0;
    const DEFAULT_STOCK_ID = 1;
    const SORT_ATTRIBUTE_ALIAS = "nsc_kng_sort_attribute";
    const INCLUDE_TAX_SUFFIX = "_include_tax";
    const EXCLUDE_TAX_SUFFIX = "_exclude_tax";

    /*
     *	\Nostress\Koongo\Model\Config\Source\Datetimeformat
     */
    protected $_datetimeformat;
    protected $_atttibutes = '*';
    protected $_defaultAttributes = [];
    protected $_data;
    /* @var Array for image placeholders */
    protected $_placeholders;
    /** @var \Magento\Store\Model\StoreManagerInterface */
    protected $storeManager;

    /*
     * Store
     * @var \Magento\Store\Model\Store
     */
    protected $_store;

    /*
     * Store id
    * @var int
    */
    protected $_storeId;

    /*
     * Website
     * @var \Magento\Store\Model\Website
     */
    protected $_website;

    /*
     * Website id
     * @var int
     */
    protected $_websiteId;

    /*
     * Profile id
    * @var int
    */
    protected $_profileId;
    /*
     * Loader Helper
     * @var \Nostress\Koongo\Helper\Data\Loader
     */
    protected $helper;

    /**
     * Tax helper
     *
     * @var \Magento\Tax\Model\Config
     */
    protected $_taxConfig;

    /**
     * @var \Magento\Weee\Helper\Data
     */
    protected $weeeData;

    /**
     * @var bool
     */
    protected ?bool $contentStagingAvailable;

    /**
     * Construct
     *
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Nostress\Koongo\Helper\Data\Loader $helper
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param Config $taxConfig
     * @param string|null $resourcePrefix
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Nostress\Koongo\Model\Config\Source\Datetimeformat $datetimeformat,
        \Nostress\Koongo\Helper\Data\Loader $helper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Tax\Model\Config $taxConfig,
        \Magento\Weee\Helper\Data $weeeData,
        $resourcePrefix = null
    ) {
        $this->_datetimeformat = $datetimeformat;
        $this->helper = $helper;
        $this->storeManager = $storeManager;
        $this->_taxConfig = $taxConfig;
        $this->weeeData = $weeeData;
        parent::__construct($context, $resourcePrefix);
    }
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('nostress_koongo_data_loader', 'entity_id');
    }

    /**
     * Is content staging module present?
     * @return void
     */
    public function isContentStagingAvailable()
    {
        if (!isset($this->contentStagingAvailable)) {
            $this->contentStagingAvailable = $this->helper->getModuleEnabled("Magento_Staging");
        }
        return $this->contentStagingAvailable;
    }

    public function init()
    {
        $this->defineColumns();
        $this->resetOffset();
        $select = $this->getSelect();

        return $this;
    }

    public function loadBatch()
    {
        $select = clone $this->getSelect();
        $batchSize = $this->getBatchSize();
        $offset = $this->getOffset();

        $select->limit($batchSize, $offset);
        $batch = $this->runSelectQuery($select, "Flat Table", "Load products or categories.");
        $this->setOffset($batchSize+$offset);

        return $batch;
    }

    public function loadAll()
    {
        $select = clone $this->getSelect();
        $batch = $this->runSelectQuery($select);
        return $batch;
    }

    public function getAllColumns()
    {
        if (!isset($this->_columns)) {
            $this->defineColumns();
        }

        $columns = [];
        foreach ($this->_columns as $tableColumns) {
            $columns = array_merge($columns, $tableColumns);
        }
        return $columns;
    }

    protected function defineColumns()
    {
        $this->_columns = [];

        /* Prepare product url link*/
        $categoryUrlSuffix = $this->getStoreConfig(CategoryUrlPathGenerator::XML_PATH_CATEGORY_URL_SUFFIX);

        if (!empty($categoryUrlSuffix)) {
            $firstChar = substr($categoryUrlSuffix, 0, 1);
            if ($firstChar != ".") {
                $categoryUrlSuffix = "." . $categoryUrlSuffix;
            }
        }

        $this->_columns[$this->getCategoryFlatTable(true)] = [ "category_id" => "entity_id",
                "category_name" => "name",
                "category_path_ids" => "path",
                "category_level" => "level",
                "category_parent_id" => "parent_id",
                "category_url_key" => "url_key",
                "category_path_url_key" => "(REPLACE(IFNULL({$this->getCategoryFlatTable(true)}.url_path,''),'" . self::DEF_CATEGORY_PATH_DELIMITER . "','-'))",
                "category_url" => "(CONCAT('{$this->getStore()->getBaseUrl()}',CONCAT(IFNULL({$this->getCategoryFlatTable(true)}.url_path,{$this->getCategoryFlatTable(true)}.url_key),'{$categoryUrlSuffix}')))"
        ];

        $this->_columns[self::NKCCP] = ["category_path" => "category_path",
                                               "category_root_name" => "category_root_name",
                                            "category_root_id" => "category_root_id"];
        $defaultCatPathDelim = self::DEF_CATEGORY_PATH_DELIMITER;
        $userCatPathDelim = $this->getCategoryPathDelimiter();
        if ($userCatPathDelim !== $defaultCatPathDelim) {
            $this->_columns[self::NKCCP]["category_path"] = "(REPLACE(" . self::NKCCP . ".category_path,'{$defaultCatPathDelim}','{$userCatPathDelim}'))";
        }

        //$this->defineCategoryTaxonomyColumns();
        $this->_columns[$this->getCategoryFlatTable(true, null, true)] = ["category_parent_name" => "name"];
        //$this->dispatchDefineColumnsEvent();
    }

    public function getSelect()
    {
        if (!isset($this->_select)) {
            $this->_select = $this->getEmptySelect();
        }
        return $this->_select;
    }

    protected function getEmptySelect()
    {
        $select = $this->getConnection()->select();
        $res = clone $select;
        $res->reset();
        return $res;
    }

    protected function getSubSelectTable($select)
    {
        return new \Zend_Db_Expr("(" . $select . ") ");
    }

    protected function getProductFlatTable($alias = false, $storeId = null, $parent = false)
    {
        return $this->getFlatTable(self::FLAT_TYPE_PRODUCT, $alias, $storeId, $parent);
    }

    protected function getCategoryFlatTable($alias = false, $storeId = null, $parent = false)
    {
        return $this->getFlatTable(self::FLAT_TYPE_CATEGORY, $alias, $storeId, $parent);
    }

    protected function getFlatTable($type, $alias = false, $storeId = null, $parent = false)
    {
        if (is_null($storeId)) {
            $storeId = $this->getStoreId();
        }

        if ($alias) {
            if ($parent) {
                $alias = self::PCCF;
                if ($type == self::FLAT_TYPE_PRODUCT) {
                    $alias = self::PCPF;
                }
            } else {
                $alias = self::CCF;
                if ($type == self::FLAT_TYPE_PRODUCT) {
                    $alias = self::CPF;
                }
            }
            return $alias . $storeId;
        } else {
            $storePrefix = "";
            if ($type == self::FLAT_TYPE_CATEGORY) {
                $storePrefix = "store_";
            }
            return $this->getTable("catalog_{$type}_flat_" . $storePrefix . $storeId);
        }
    }

    /**
     * Set store scope
     *
     * @param int|string|Mage_Core_Model_Store $storeId
     * @return Mage_Catalog_Model_Resource_Collection_Abstract
     */
    public function setStoreId($storeId)
    {
        if ($storeId instanceof \Magento\Store\Model\Store) {
            $storeId = $storeId->getId();
        }
        $this->_storeId = (int)$storeId;
        $this->resetStore();
        $this->resetWebsiteId();
        $this->resetStockId();
        return $this;
    }

    public function setProfileId($profileId)
    {
        $this->_profileId = $profileId;
    }

    public function setAttributes($attributes)
    {
        $defaultAttributes = $this->getDefaultAttributes();
        if (is_array($attributes) && !empty($defaultAttributes)) {
            $attributes = array_merge($attributes, $defaultAttributes);
        }
        $this->_atttibutes = $attributes;
    }

    public function getProfileId()
    {
        return $this->_profileId;
    }

    /**
     * Return current store id
     *
     * @return int
     */
    public function getStoreId()
    {
        return $this->_storeId;
    }

    protected function getAttributes()
    {
        return $this->_atttibutes;
    }

    protected function getDefaultAttributes()
    {
        return $this->_defaultAttributes;
    }

    protected function getStore()
    {
        if (empty($this->_store)) {
            $this->_store = $this->storeManager->getStore($this->getStoreId());
        }
        return $this->_store;
    }

    protected function getStoreConfig($configPath)
    {
        return $this->helper->getStoreConfig($this->getStoreId(), $configPath);
    }

    protected function resetWebsiteId()
    {
        $this->_websiteId = null;
        $this->_website = null;
    }

    protected function resetStockId()
    {
        $this->_stockId = null;
    }

    protected function resetStore()
    {
        $this->_store = null;
    }

    protected function getWebsiteId()
    {
        if (is_null($this->_websiteId)) {
            $this->_websiteId = $this->getStore()->getWebsiteId();
        }
        return $this->_websiteId;
    }

    protected function getWebsite()
    {
        if (empty($this->_website)) {
            $this->_website = $this->storeManager->getWebsite($this->getWebsiteId());
        }
        return $this->_website;
    }

    protected function getStockId()
    {
        if (empty($this->_stockId)) {
            $this->_stockId = $this->_getStockIdForWebsiteCode($this->getWebsite()->getCode());
        }
        return $this->_stockId;
    }

    ////////////////////////////////// JOIN TABLES /////////////////////////////////////

    public function joinCategoryPath()
    {
        return $this->_joinCategoryPath($this->getSelect());
    }

    protected function _joinCategoryPath($select, $addColumns = true, $mainTableAlias = null, $mainTableOnColumn = null)
    {
        if (!isset($mainTableAlias)) {
            $mainTableAlias = $this->getCategoryFlatTable(true);
        }
        if (!isset($mainTableOnColumn)) {
            $mainTableOnColumn = "entity_id";
        }

        $joinTableAlias = self::NKCCP;
        $joinTable = $this->getTable('nostress_koongo_cache_categorypath');
        $columns = null;
        if ($addColumns) {
            $columns = $this->getColumns($joinTableAlias);
        }

        $select->joinLeft(
            [$joinTableAlias => $joinTable],
            "{$joinTableAlias}.category_id ={$mainTableAlias}.{$mainTableOnColumn} AND {$joinTableAlias}.store_id ={$this->getStoreId()}",
            $columns
        );
        return $select;
    }

    public function joinParentCategory()
    {
        $mainTableAlias = $this->getCategoryFlatTable(true);
        $joinTableAlias = $this->getCategoryFlatTable(true, null, true);
        $joinTable =  $this->getCategoryFlatTable();

        $condition =  "{$joinTableAlias}.entity_id = " . self::MAIN_TABLE_SUBST . ".parent_id";
        $this->joinTable($mainTableAlias, $joinTableAlias, $joinTable, false, $condition);

        return $this;
    }

    ////////////////////////////////// COLUMN LOADERS //////////////////////////////////

    protected function getColumns($tableAlias, $defualt = null, $groupConcat = false, $addTablePrefix = false)
    {
        if (array_key_exists($tableAlias, $this->_columns)) {
            $result = $this->_columns[$tableAlias];
        } else {
            $result = $defualt;
        }

        if ($groupConcat) {
            $result = $this->helper->groupConcatColumns($result);
        }
        if ($addTablePrefix) {
            $result = $this->addTablePrefix($tableAlias, $result);
        }
        return $this->filterColumns($result);
    }

    protected function filterColumns($columns)
    {
        $attributes = $this->getAttributes();
        if ($attributes == '*') {
            return $columns;
        }

        if (!isset($columns) || !is_array($columns)) {
            return $columns;
        }

        if (isset($attributes) && is_array($attributes)) {
            foreach ($columns as $key => $column) {
                if (!in_array($key, $attributes)) {
                    unset($columns[$key]);
                }
            }
        }

        return $columns;
    }

    protected function addTablePrefix($tableAlias, $columns)
    {
        foreach ($columns as $key => $value) {
            if (isset($value[0]) && $value[0] == "(") {
                continue;
            }
            $columns[$key] = $tableAlias . "." . $value;
        }

        return $columns;
    }

    //**********************************PREAPARE PRODUCT FLAT COLUMNS PART*****************************************************

    /**
     * Prepare flat product catalog attributes as a select columns
     * @param unknown_type $attributes
     * @throws Exception
     * @return multitype:unknown
     */
    protected function prepareColumnsFromAttributes($attributesWithInfo)
    {
        $columns = [];
        foreach ($attributesWithInfo as $attributeCode => $info) {
            $value = $attributeCode;
            if (isset($info['type'])) {
                switch ($info['type']) {
                    case 'media_image':
                        $value = $this->mediaImageColumn($attributeCode);
                        break;
                    case 'select':
                        $value = $this->multiSelectColumn($attributeCode);
                        break;
                    case 'date':
                        $value = $this->dateColumn($attributeCode);
                        break;
                    case 'price':
                        $value = $this->priceColumn($attributeCode);
                        // no break
                    default:
                        break;
                }
            }

            $columns[$attributeCode] = $value;
        }
        return $columns;
    }

    protected function dateColumn($code)
    {
        return "DATE_FORMAT({$this->getProductFlatTable(true)}.{$code},'{$this->getSqlTimestampFormat(self::DATE_TIME)}')";
    }

    protected function mediaImageColumn($code)
    {
        $imageUrlPrefix = $this->getData(self::MEDIA_URL);

        $placeholderUrl = "";
        if ($this->getData(self::ALLOW_PLACEHOLDER, false)) {
            $placeholder = $this->getPlaceholder($code);
            if (!empty($placeholder)) {
                $placeholderUrl = $imageUrlPrefix . $placeholder;
            }
        }

        return "IF( {$this->getProductFlatTable(true)}.{$code} = 'no_selection','{$placeholderUrl}',CONCAT('{$imageUrlPrefix}', {$this->getProductFlatTable(true)}.{$code}))";
    }

    protected function multiSelectColumn($code)
    {
        return $code . self::VALUE_COLUMN_SUFFIX;
    }

    protected function priceColumn($code)
    {
        return $this->helper->getRoundSql($this->getProductFlatTable(true) . "." . $code);
    }

    //////////////////////////////////  BASIC SETTERS GETTERS //////////////////////////////////

    public function setData($data)
    {
        $this->_data = $data;
    }

    public function getData($index, $defalutValue = null)
    {
        if (isset($this->_data[$index])) {
            return $this->_data[$index];
        } else {
            return $defalutValue;
        }
    }

    public function getBatchSize()
    {
        $value = $this->getData(self::BATCH_SIZE);
        if (!$value) {
            $value = self::DEF_BATCH_SIZE;
        }
        return $value;
    }

    /**
     * Returns product type, category conditions.
     */
    public function getConditions()
    {
        $value = $this->getData(self::CONDITIONS);
        if (!$value) {
            $value = [];
        }
        return $value;
    }

    public function getCondition($name, $default = null)
    {
        $conditions = $this->getConditions();
        if (array_key_exists($name, $conditions)) {
            return $conditions[$name];
        } else {
            return $default;
        }
    }

    public function getCategoryPathDelimiter()
    {
        $value = $this->getData(self::CATEGORY_PATH_DELIMITER);
        if (!$value) {
            $value = self::DEF_CATEGORY_PATH_DELIMITER;
        }
        return $value;
    }

    public function getCurrency()
    {
        return $this->getData(self::CURRENCY);
    }

    public function getCustomerGroupId()
    {
        return $this->getData(self::PRICE_CUSTOMER_GROUP_ID, self::DEF_CUSTOMER_GROUP_NOT_LOGGED_IN);
    }

    protected function getPlaceholder($code)
    {
        if (empty($this->_placeholders[$code])) {
            $this->_placeholders[$code] =  $this->getStoreConfig("catalog/placeholder/{$code}_placeholder");
            if (!empty($this->_placeholders[$code])) {
                $this->_placeholders[$code] = "/placeholder/" . $this->_placeholders[$code];
            }
        }
        return $this->_placeholders[$code];
    }

    public function resetOffset()
    {
        $this->_offset = 0;
    }

    public function setOffset($offset)
    {
        $this->_offset = $offset;
    }

    public function getOffset()
    {
        return $this->_offset;
    }

    ////////////////////////////////// WEEE ///////////////////////////////////////

    protected function isWeeeEnabled()
    {
        return $this->weeeData->isEnabled($this->getStore());
    }

    protected function isWeeeTaxable()
    {
        return $this->weeeData->isTaxable($this->getStore());
    }

    protected function getWeeeListPriceDisplayType()
    {
        return $this->weeeData->getListPriceDisplayType($this->getStore());
    }

    protected function getPriceDisplayType()
    {
        return $this->weeeData->getPriceDisplayType($this->getStore());
    }

    protected function getWeeeAttributes()
    {
        $select = $this->getEmptySelect();
        $select->from(['ca' => $this->getTable('catalog_eav_attribute')], ['attribute_id'])
        ->join(
            ['ea' => $this->getTable('eav_attribute')],
            'ca.attribute_id = ea.attribute_id',
            ['attribute_code']
        );
        $select->where('ea.frontend_input = ?', 'weee');

        $collection = $this->runSelectQuery($select);
        $items = [];
        foreach ($collection as $item) {
            if (isset($item['attribute_id']) && isset($item['attribute_code'])) {
                $items[$item['attribute_id']] = $item['attribute_code'];
            }
        }
        return $items;
    }

    protected function convertWeeeAttributesToColumnNames($weeeAttributes = null)
    {
        if (empty($weeeAttributes)) {
            $weeeAttributes = array_values($this->getWeeeAttributes());
        }
        $attributes = [];
        foreach ($weeeAttributes as $code) {
            $attributes[] = $code;
        }
        return $attributes;
    }

    ////////////////////////////////// SQL HELPERS //////////////////////////////////
    protected function joinMainTable($joinTableAlias, $joinTable, $joinIfColumnsEmpty = false, $condition = null, $joinLeft = true)
    {
        $mainTableAlias = $this->getMainTable(true);
        return $this->joinTable($mainTableAlias, $joinTableAlias, $joinTable, $joinIfColumnsEmpty, $condition, $joinLeft);
    }

    protected function joinTable($mainTableAlias, $joinTableAlias, $joinTable, $joinIfColumnsEmpty = false, $condition= null, $joinLeft = true)
    {
        $selectColumns =  $this->getColumns($joinTableAlias);
        if (empty($selectColumns) && !$joinIfColumnsEmpty) {
            return false;
        }

        $select = $this->getSelect();

        $condition = str_replace(self::MAIN_TABLE_SUBST, $mainTableAlias, $condition);

        if ($joinLeft) {
            $select->joinLeft(
                [$joinTableAlias => $joinTable],
                $condition,
                $selectColumns
            );
        } else {
            $select->join(
                [$joinTableAlias => $joinTable],
                $condition,
                $selectColumns
            );
        }

        return true;
    }

    protected function getSqlTimestampFormat($type = self::DATE_TIME)
    {
        return $this->_datetimeformat->getSqlFormat($this->getData(self::DATETIME_FORMAT, \Nostress\Koongo\Model\Config\Source\Datetimeformat::STANDARD), $type);
    }

    protected function _isTableColumnPresent($table, $columnName)
    {
        $describe = $this->getConnection()->describeTable($table);
        return isset($describe[$columnName]);
    }

    protected function _getStockIdForWebsiteCode($websiteCode)
    {
        $select = $this->getEmptySelect();
        $mainTableAlias = "issc";
        $mainTable = null;

        $connection = $this->getConnection();
        $tableName = $connection->getTableName('inventory_stock_sales_channel');
        $isTableExist = $connection->isTableExists($tableName);
        if (!$isTableExist) {
            return self::DEFAULT_STOCK_ID;
        }

        $mainTable = $this->getTable('inventory_stock_sales_channel');

        if (!$mainTable) {
            return self::DEFAULT_STOCK_ID;
        }

        $columns = "*";
        $select->from([$mainTableAlias => $mainTable], $columns);
        $select->where("type = ?", "website");
        $select->where("code = ?", $websiteCode);
        $result = $this->runSelectQuery($select, "Inventory Stock Sales Channel", "Load stock id for website.");
        foreach ($result as $key => $item) {
            if (isset($item["stock_id"])) {
                return $item["stock_id"];
            }
        }
        return self::DEFAULT_STOCK_ID;
    }
}
