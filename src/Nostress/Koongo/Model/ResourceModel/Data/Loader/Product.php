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

namespace Nostress\Koongo\Model\ResourceModel\Data\Loader;

use Magento\CatalogUrlRewrite\Model\ProductUrlPathGenerator;
use Nostress\Koongo\Helper\Data\Loader;
use Nostress\Koongo\Model\Config\Source;

class Product extends \Nostress\Koongo\Model\ResourceModel\Data\Loader
{
    const DEFAULT_VISIBILITY_VALUE = 4;
    const DEFAULT_STOCK_STATUS_VALUE = 1;

    const REVIEWS_URL_SUFFIX = "#reviews";

    /* @var Last used category table which should be joined by category flat*/
    protected $_lastCategoryProductTableAlias = null;

    /*
     * Dafault attributes, which are loaded with every select.
     */
    protected $_defaultAttributes = ["id","product_type","group_id","is_child"];

    /**
     * @var \Nostress\Koongo\Model\Rule
     */
    protected $rule;

    /**
     * Construct
     *
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Nostress\Koongo\Helper\Data\Loader $helper
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param Config $taxConfig
     * @param \Nostress\Koongo\Model\Rule $rule
     * @param string|null $resourcePrefix
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Nostress\Koongo\Model\Config\Source\Datetimeformat $datetimeformat,
        \Nostress\Koongo\Helper\Data\Loader $helper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Tax\Model\Config $taxConfig,
        \Magento\Weee\Helper\Data $weeeData,
        \Nostress\Koongo\Model\Rule $rule,
        $resourcePrefix = null
    ) {
        $this->rule = $rule;
        parent::__construct($context, $datetimeformat, $helper, $storeManager, $taxConfig, $weeeData, $resourcePrefix);
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('nostress_koongo_data_loader_product', 'entity_id');
    }

    /**
     * Initialize collection select
     */
    public function init()
    {
        parent::init();

        $select = $this->getSelect()->reset();
        $select->from([$this->getProductFlatTable(true) => $this->getProductFlatTable()], $this->_columns[$this->getProductFlatTable(true)]);
        $flatColumns = $this->prepareColumnsFromAttributes($this->getAttributes(false, true));

        //Exception for category ids, which should be loaded from cache table(instead of flat catalog)
        if (isset($flatColumns["category_ids"])) {
            $tableAlias = self::NKCP;
            //If category filter is adjusted
            $filterCategoryIds = $this->getCondition(Loader::CONDITION_CATEGORY_IDS, []);
            if (!empty($filterCategoryIds)) {
                $tableAlias = self::NKCPC;
            }

            $flatColumns["category_ids"] = $tableAlias . ".category_ids";
        }

        $select->columns($flatColumns);
        return $this;
    }

    public function getMainTable($alias = false)
    {
        return $this->getProductFlatTable($alias);
    }

    protected function defineColumns()
    {
        parent::defineColumns();

        $this->_columns[$this->getProductFlatTable(true)] =
        [  "id" => "entity_id",
                "product_type" => "type_id",
                //"visibility" => "visibility",
                "tax_class_id" => "tax_class_id",
                "attribute_set" => "attribute_set_id"
        ];

        /* Prepare product url link*/
        $productUrlSuffix = $this->getStoreConfig(ProductUrlPathGenerator::XML_PATH_PRODUCT_URL_SUFFIX); //CategoryUrlPathGenerator::XML_PATH_CATEGORY_URL_SUFFIX

        if (!empty($productUrlSuffix)) {
            $firstChar = substr($productUrlSuffix, 0, 1);
            if ($firstChar != "." && $firstChar != "/") {
                $productUrlSuffix = "." . $productUrlSuffix;
            }
        }

        /*
            url_key is the field you fill in the admin as it. (If there is nothing specified it create one by using the name of the product by lowercasing it and replace blank in it with hyphen).
            url_path will be a concatenation of url_key and and the Product URL Suffix defined under System > configuration > Catalog > Search Engine Optimization. It will also ensure that you do not have duplicate url_path by suffixing your url_key with an hyphen and the entity_id of the product if the same url_key already exists.
            update: url_path probably do not include .html suffix
        */
        //$this->_columns[$this->getProductFlatTable(true)]["url"] = "CONCAT('{$this->getStore()->getBaseUrl()}',IFNULL({$this->getProductFlatTable(true)}.url_path,{$this->getProductFlatTable(true)}.url_key),'{$productUrlSuffix}')";
        $this->_columns[$this->getProductFlatTable(true)]["url"] = "CONCAT('{$this->getStore()->getBaseUrl()}', {$this->getProductFlatTable(true)}.url_key, '{$productUrlSuffix}')";

        /* Parent product attributes */
        $this->_columns[self::PCPF] = ["parent_sku" => "sku"];

        $this->_columns[self::CISI] = [  	/*"qty" => $this->helper->getRoundSql(self::CISI.".qty",0),*/
            "minimum_qty_allowed_in_shopping_cart" => $this->helper->getRoundSql($this->getMinSaleQtyCondition(self::CISI), 0)];

        /* Catalog product entity */
        $this->_columns[self::CPE] = [
                "update_datetime" => "DATE_FORMAT(" . self::CPE . ".updated_at,'{$this->getSqlTimestampFormat()}')",
                "update_date" => "DATE_FORMAT(" . self::CPE . ".updated_at,'{$this->getSqlTimestampFormat(self::DATE)}')",
                "update_time" => "DATE_FORMAT(" . self::CPE . ".updated_at,'{$this->getSqlTimestampFormat(self::TIME)}')",
                "creation_datetime" => "DATE_FORMAT(" . self::CPE . ".created_at,'{$this->getSqlTimestampFormat()}')",
                "creation_date" => "DATE_FORMAT(" . self::CPE . ".created_at,'{$this->getSqlTimestampFormat(self::DATE)}')",
                "creation_time" => "DATE_FORMAT(" . self::CPE . ".created_at,'{$this->getSqlTimestampFormat(self::TIME)}')"
        ];

        $this->_columns[self::CPR] = ["parent_id" => "parent_id",
            "group_id" => "IFNULL(" . self::CPR . ".parent_id,{$this->getMainTable(true)}.entity_id)",
            "is_child" => "(" . self::CPR . ".parent_id IS NOT NULL)",
            "is_parent" => "(" . self::CPR . ".parent_id IS NULL)"];

        if ($this->isContentStagingAvailable()) {
            //If staging available then load data from the parent catalog product entity table
            $this->_columns[self::PCPE] = ["parent_id" => "entity_id",
                "group_id" => "IFNULL(" . self::PCPE . ".entity_id,{$this->getMainTable(true)}.entity_id)"];

            $this->_columns[self::CPR] = ["is_child" => "(" . self::CPR . ".parent_id IS NOT NULL)",
                "is_parent" => "(" . self::CPR . ".parent_id IS NULL)"];
        }

        $this->_columns[self::NKCT] = ["tax_percent" => "IFNULL({$this->helper->getRoundSql("tax_percent*100")},0)"];
        $this->_columns[self::NKCP] = ["qty" => "qty",
                                            "stock_status" => "stock_status",
                                            "media_gallery" => "media_gallery"];

        if ($this->getData(self::STOCK_STATUS_DEPENDENCE) == Source\Stockdependence::QTY) {
            $condition = $this->getManageStockCondition(self::CISI);
            $this->_columns[self::NKCP]["stock_status"] = "IF(" . self::NKCP . ".qty > 0 OR ({$condition}),1,0)";
        }
        /*Parent Stock status columns */
        $this->_columns[self::PNKCP] = ["parent_stock_status" => self::PNKCP . ".stock_status",
                                             "parent_qty" => self::PNKCP . ".qty"];

        //If category filter is adjusted
        $filterCategoryIds = $this->getCondition(Loader::CONDITION_CATEGORY_IDS, []);
        // 		var_dump($filterCategoryIds);exit();
        if (empty($filterCategoryIds)) {
            $this->_columns[self::NKCP]["categories"] = "(REPLACE(" . self::NKCP . ".categories,'" . self::DEF_CATEGORY_PATH_SUBST_DELIMITER . "','{$this->getCategoryPathDelimiter()}'))";
        } else {
            $this->_columns[self::NKCPC] = [ "categories" => "(REPLACE(" . self::NKCPC . ".categories,'" . self::DEF_CATEGORY_PATH_SUBST_DELIMITER . "','{$this->getCategoryPathDelimiter()}'))"];
        }

        $this->_columns[self::NKCW] = $this->getWeeeColumns();
        $this->definePriceColumns();

        $this->_columns[self::NKTC] = [	"taxonomy_name" => "name",
                "taxonomy_id" => "id",
                "taxonomy_path" => "path",
                "taxonomy_ids_path" => "ids_path",
                "taxonomy_level" => "level",
                "taxonomy_parent_name" => "parent_name",
                "taxonomy_parent_id" => "parent_id",
                "taxonomy_code1" => "code1",
                "taxonomy_code2" => "code2"
        ];

        $reviewsUrlSuffix = self::REVIEWS_URL_SUFFIX;
        $this->_columns[self::NKCR] = ["reviews" => "reviews",
                                            "reviews_url" => "CONCAT('{$this->getStore()->getBaseUrl()}', {$this->getProductFlatTable(true)}.url_key, '{$productUrlSuffix}{$reviewsUrlSuffix}')"];

        $this->addPrefixToOwnColumns();

        // 		$this->dispatchDefineColumnsEvent("_product");
    }

    /**
     * Add default prefix to product attributes defined by this module.
     */
    protected function addPrefixToOwnColumns()
    {
        foreach ($this->_columns as $tableAlias => $tableColumns) {
            foreach ($tableColumns as $alias => $value) {
                $aliasWithPrefix = \Nostress\Koongo\Model\Config\Source\Attributes::MODULE_PATTRIBUTE_PREFIX . $alias;
                $this->_columns[$tableAlias][$aliasWithPrefix] = $value;
                unset($this->_columns[$tableAlias][$alias]);
            }
        }
    }

    /**
     * Add default prefix to default attributes
     */
    protected function getDefaultAttributes()
    {
        $defaultAttributes =  $this->_defaultAttributes;
        $transformedAttributes = [];
        foreach ($defaultAttributes as $attributeCode) {
            $index = \Nostress\Koongo\Model\Config\Source\Attributes::MODULE_PATTRIBUTE_PREFIX . $attributeCode;
            $transformedAttributes[$index] = [];
        }
        return $transformedAttributes;
    }

    /**
     * Add default prefix to given attribute
     */
    protected function getAttributeWithProfix($attributeCode)
    {
        return \Nostress\Koongo\Model\Config\Source\Attributes::MODULE_PATTRIBUTE_PREFIX . $attributeCode;
    }

    protected function getAttributes($attributeCodesOnly = true, $flatCatalogOnly = false)
    {
        if (!$flatCatalogOnly || !is_array($this->_atttibutes)) {
            if ($attributeCodesOnly) {
                return array_keys($this->_atttibutes);
            } else {
                return $this->_atttibutes;
            }
        }

        $ownAttributes = array_keys($this->getAllColumns());
        $flatAttributes = [];
        foreach ($this->_atttibutes as $key => $attributeInfo) {
            if (!in_array($key, $ownAttributes)) {
                $flatAttributes[$key] = $attributeInfo;
            }
        }

        if ($attributeCodesOnly) {
            return array_keys($flatAttributes);
        } else {
            return $flatAttributes;
        }
    }

    //**********************************COMMON PART*****************************************************
    public function joinProductEntity()
    {
        $mainTableAlias = $this->getMainTable(true);
        $joinTableAlias = self::CPE;
        $joinTable =  $this->getTable('catalog_product_entity');

        $condition =  "{$joinTableAlias}.entity_id = " . self::MAIN_TABLE_SUBST . ".entity_id";

        //Is content staging mobul present
        if ($this->isContentStagingAvailable()) {
            $condition = "{$joinTableAlias}.entity_id = " . self::MAIN_TABLE_SUBST . ".entity_id AND {$joinTableAlias}.created_in <= UNIX_TIMESTAMP() AND {$joinTableAlias}.updated_in > UNIX_TIMESTAMP() ";
        }

        $this->joinTable($mainTableAlias, $joinTableAlias, $joinTable, false, $condition);
        return $this;
    }

    /**
     * Join parent catalog product entity table
     * @return $this
     */
    public function joinParentProductEntity()
    {
        $mainTableAlias = self::CPR;
        $joinTableAlias = self::PCPE;
        $joinTable =  $this->getTable('catalog_product_entity');

        $condition =  "{$joinTableAlias}.row_id = " . self::MAIN_TABLE_SUBST . ".parent_id AND {$joinTableAlias}.created_in <= UNIX_TIMESTAMP() AND {$joinTableAlias}.updated_in > UNIX_TIMESTAMP() ";

        $this->joinTable($mainTableAlias, $joinTableAlias, $joinTable, false, $condition);
        return $this;
    }

    /**
     * Joint export categoryproduct table
     */
    public function groupByProduct()
    {
        $select = $this->getSelect();
        $select->group($this->getMainTable(true) . ".entity_id");
    }

    public function addSortAttribute()
    {
        $sortAttribute = $this->getData(self::SORT_ATTRIBUTE);
        if (empty($sortAttribute)) {
            return;
        }
        $select = $this->getSelect();
        $sortAttributeWithTableAlias = $this->_getAttributeAliases($sortAttribute, $this->_columns);

        $tableAlias = self::CPR;
        $parentIdColumn = "parent_id";
        if ($this->isContentStagingAvailable()) {
            $tableAlias = self::PCPE;
            $parentIdColumn = "entity_id";
        }

        if (!$sortAttributeWithTableAlias) {
            $select->columns([self::SORT_ATTRIBUTE_ALIAS => "IF(" . $tableAlias . "." . $parentIdColumn . " IS NOT NULL," . self::PCPF . ".{$sortAttribute},{$this->getMainTable(true)}.{$sortAttribute})"]);
        } else {
            $select->columns([self::SORT_ATTRIBUTE_ALIAS => $sortAttributeWithTableAlias]);
        }
    }

    /**
     * Set sorting attributes
     */
    public function setProductsOrder()
    {
        $select = $this->getSelect();
        $sortAttribute = $this->getData(self::SORT_ATTRIBUTE);
        if (!empty($sortAttribute)) {//order condition is set
            //order by custom attribute
            $select->order("ISNULL(" . self::SORT_ATTRIBUTE_ALIAS . ") " . $this->getData(self::SORT_ORDER, "ASC"));
            $select->order(self::SORT_ATTRIBUTE_ALIAS . " " . $this->getData(self::SORT_ORDER, "ASC"));
        }

        //order by group id
        $select->order($this->getAttributeWithProfix("group_id"));
        //order by product type
        $select->order($this->getMainTable(true) . ".type_id");
    }

    public function joinProductRelation()
    {
        $joinTableAlias = self::CPR;
        $joinTable = $this->getTable("catalog_product_relation");

        $condition =  $joinTableAlias . '.child_id=' . self::MAIN_TABLE_SUBST . ".entity_id ";
        $condition .= "AND {$this->getMainTable(true)}.type_id = 'simple'";

        $this->joinMainTable($joinTableAlias, $joinTable, true, $condition);
        $this->addParentsCondition();

        return $this;
    }

    public function addParentsCondition()
    {
        if ($this->parentsOnly()) {
            $select = $this->getSelect();
            $select->where(self::CPR . ".parent_id IS NULL ");
        }
    }

    protected function parentsOnly()
    {
        if ($this->getCondition(Loader::CONDITION_PARENTS_CHILDS, 0) == Source\Parentschilds::PARENTS_ONLY) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Joint product table
     */
    protected function joinParentProductFlat()
    {
        $joinTableAlias = self::PCPF;
        $joinTable = $this->getProductFlatTable();

        if ($this->isContentStagingAvailable()) {
            $mainTableAlias = self::PCPE;
            $condition = $joinTableAlias . '.entity_id=' . self::MAIN_TABLE_SUBST . '.entity_id';
        } else {
            $mainTableAlias = self::CPR;
            $condition = $joinTableAlias . '.entity_id=' . self::MAIN_TABLE_SUBST . '.parent_id';
        }

        $this->joinTable($mainTableAlias, $joinTableAlias, $joinTable, true, $condition);
        return $this;
    }

    public function addVisibilityCondition()
    {
        $this->joinParentProductFlat();

        $select = $this->getSelect();

        $visibility = $this->getCondition(Loader::CONDITION_VISIBILITY);
        $parentVisibility = $this->getCondition(Loader::CONDITION_VISIBILITY_PARENT);

        $where = "";
        if (count($visibility)) {
            $where .= $select->getAdapter()->quoteInto(
                "{$this->getMainTable(true)}.visibility IN(?)",
                $visibility
            );
        }

        if (!$this->parentsOnly() && count($parentVisibility)) {
            if (!empty($where)) {
                $where .= " OR ";
            }
            $where .= $select->getAdapter()->quoteInto(
                self::PCPF . ".visibility  IN(?)",
                $parentVisibility
            );
        }

        if (!empty($where)) {
            $select->where($where);
        }
    }

    public function addTypeCondition()
    {
        $allTypes = $this->helper->getProductTypes();
        $types = $this->getCondition(Loader::CONDITION_TYPES, $allTypes);

        if (empty($types)) {
            return $this;
        }
        if (!is_array($types)) {
            $types = explode(",", $types);
        }

        if (count($allTypes) != count($types)) {
            $select = $this->getSelect();
            $select->where($this->getMainTable(true) . ".type_id IN (?)", $types);
        }

        return $this;
    }

    public function addAttributeFilter()
    {
        $conditions = $this->getCondition(Loader::CONDITION_ATTRIBUTE_FILTER_CONDITIONS, []);
        if (empty($conditions)) {
            return $this;
        }
        // copy columns
        $columns = $this->_columns;
        $columns[\Nostress\Koongo\Model\Rule\Condition\Product::DEFAULT_TABLE_ALIAS] = $this->getProductFlatTable(true);

        //Exception for category ids, which soulf be loaded from cache table(instead of flat catalog)
        //If category filter is adjusted
        $filterCategoryIds = $this->getCondition(Loader::CONDITION_CATEGORY_IDS, []);
        if (!empty($filterCategoryIds)) {
            $columns[self::NKCPC]["category_ids"] = "category_ids";
        } else {
            $columns[self::NKCP]["category_ids"] = "category_ids";
        }

        $this->rule->initConditions($conditions);
        $where = $this->rule->getConditions()->asSqlWhere($columns);

        //add to select
        if ($where !== false && $where != '()') {
            $this->getSelect()->where($where);
        }
        return $this;
    }

    /**
     * Add table alias for attribute.
     * Returns false if table alias is not found.
     *      *
     * @param string $attribute
     * @param arraz $columns
     * @return string or false
     */
    protected function _getAttributeAliases($attribute, $columns)
    {
        foreach ($columns as $tableAlias => $tableColumns) {
            foreach ($tableColumns as $columnAlias => $columnValue) {
                if ($attribute == $columnAlias) {
                    //If column value contains composed value - it means it contains table alias inside
                    if (strpos($columnValue, $tableAlias) !== false) {
                        return $columnValue;
                    } else {
                        return $tableAlias . "." . $columnValue;
                    }
                }
            }
        }
        return false;
    }

    //********************************** STOCK *****************************************************

    protected function getManageStockCondition($tableAlias)
    {
        $manageStockValue = (int)$this->getStoreConfig(\Magento\CatalogInventory\Model\Configuration::XML_PATH_MANAGE_STOCK);
        $manageStockCondition = "";
        if ($manageStockValue) {
            $manageStockCondition = "{$tableAlias}.use_config_manage_stock = '0' AND {$tableAlias}.manage_stock = '0' ";
        } else {
            $manageStockCondition = "{$tableAlias}.use_config_manage_stock = '1' OR ({$tableAlias}.use_config_manage_stock = '0' AND {$tableAlias}.manage_stock = '0') ";
        }
        return $manageStockCondition;
    }

    protected function getMinSaleQtyCondition($tableAlias)
    {
        $globalMinSaleQty = (int)$this->getStoreConfig(\Magento\CatalogInventory\Model\Configuration::XML_PATH_MIN_SALE_QTY);
        $minSaleQtyCondition = "IF({$tableAlias}.use_config_min_sale_qty = '1', '{$globalMinSaleQty}',{$tableAlias}.min_sale_qty) ";
        return $minSaleQtyCondition;
    }

    public function addStockCondition()
    {
        if ($this->getCondition(Loader::CONDITION_EXPORT_OUT_OF_STOCK, 0) == 0) {
            $this->joinParentStock();
            $select = $this->getSelect();

            $manageParentStockCondition = $this->getManageStockCondition(self::PCISI);
            $manageStockCondition = $this->getManageStockCondition(self::CISI);

            $condition = "";
            switch ($this->getData(self::STOCK_STATUS_DEPENDENCE)) {
                case Source\Stockdependence::QTY:	//hooked on qty only
                    $condition = self::NKCP . ".qty > 0 ";
                    $select->where("({$condition}) OR ({$manageStockCondition})");
                    break;
                case Source\Stockdependence::STOCK: //hooked on stock_status only
                    $condition = self::NKCP . ".stock_status = " . self::DEFAULT_STOCK_STATUS_VALUE . " " .
                            "AND (" . self::PNKCP . ".stock_status = " . self::DEFAULT_STOCK_STATUS_VALUE . " OR " . self::PNKCP . ".stock_status IS NULL )";// OR ({$manageParentStockCondition}))";
                    $select->where("({$condition})");
                    break;
                default:
                    //hooked on qty and Is in stock attribute
                    $condition = self::NKCP . ".stock_status = " . self::DEFAULT_STOCK_STATUS_VALUE . " AND (" . self::NKCP . ".qty > 0 OR {$this->getProductFlatTable(true)}.type_id <> 'simple') " .
                            "AND (" . self::PNKCP . ".stock_status = " . self::DEFAULT_STOCK_STATUS_VALUE . " OR " . self::PNKCP . ".stock_status IS NULL OR ({$manageParentStockCondition})) ";
                    $select->where("({$condition}) OR ({$manageStockCondition})");
                    break;
            }
        }
        return $this;
    }

    public function joinStock()
    {
        //Stock status is cached into product cache table
        //$this->joinNormalStockStatus();
        $this->joinNormalStockItem();
    }

    public function joinParentStock()
    {
        //Parent stock status is cached into product cache table
        //$this->joinParentStockStatus();
        $this->joinParentStockItem();
    }

    // protected function joinNormalStockStatus()
    // {
    // 	$mainTableAlias = $this->getMainTable(true);
    // 	$productIdColumnName = 'entity_id';
    // 	$joinTableAlias = self::CISS;
    // 	$this->joinStockStatus($mainTableAlias,$joinTableAlias,$productIdColumnName);
    // }

    // public function joinParentStockStatus()
    // {
    // 	$mainTableAlias = self::CPR;
    // 	$productIdColumnName = 'parent_id';
    // 	$joinTableAlias = self::PCISS;
    // 	$this->joinStockStatus($mainTableAlias,$joinTableAlias,$productIdColumnName);
    // }

    // protected function joinStockStatus($mainTableAlias,$joinTableAlias,$productIdColumnName)
    // {
    // 	//From version 2.1.0, website id in table cataloginventory_stock_status is 0 (in previous versions the website id was related to product's stores/websites)
    // 	$websiteId = $this->getData(self::STOCK_WEBSITE_ID,null);
    // 	if(!isset($websiteId))
    // 		$websiteId = $this->getWebsiteId();
    // 	$joinIfColumnsEmpty = $this->getCondition(Loader::CONDITION_EXPORT_OUT_OF_STOCK,0) == 0;
    // 	$joinTable = $this->getTable("cataloginventory_stock_status");
    // 	$condition = $joinTableAlias.'.product_id='.self::MAIN_TABLE_SUBST.".{$productIdColumnName} AND {$joinTableAlias}.website_id ={$websiteId} AND {$joinTableAlias}.stock_id = ".self::DEFAULT_STOCK_ID." ";

    // 	$this->joinTable($mainTableAlias,$joinTableAlias,$joinTable,$joinIfColumnsEmpty,$condition);
    // }

    protected function joinNormalStockItem()
    {
        $mainTableAlias = $this->getMainTable(true);
        $productIdColumnName = 'entity_id';
        $joinTableAlias = self::CISI;
        $this->joinStockItem($mainTableAlias, $joinTableAlias, $productIdColumnName);
    }

    public function joinParentStockItem()
    {
        $mainTableAlias = self::CPR;
        $productIdColumnName = 'parent_id';

        if ($this->isContentStagingAvailable()) {
            $mainTableAlias = self::PCPE;
            $productIdColumnName = 'entity_id'; //=parent_id
        }

        $joinTableAlias = self::PCISI;
        $this->joinStockItem($mainTableAlias, $joinTableAlias, $productIdColumnName);
    }

    protected function joinStockItem($mainTableAlias, $joinTableAlias, $productIdColumnName)
    {
        $joinIfColumnsEmpty = $this->getCondition(Loader::CONDITION_EXPORT_OUT_OF_STOCK, 0) == 0;
        if ($this->getData(self::STOCK_STATUS_DEPENDENCE) == Source\Stockdependence::QTY) {
            $joinIfColumnsEmpty = true;
        }
        $joinTable = $this->getTable("cataloginventory_stock_item");
        $condition = $joinTableAlias . '.product_id=' . self::MAIN_TABLE_SUBST . ".{$productIdColumnName} AND {$joinTableAlias}.stock_id = " . self::DEFAULT_STOCK_ID . " ";

        $this->joinTable($mainTableAlias, $joinTableAlias, $joinTable, $joinIfColumnsEmpty, $condition);
    }

    //********************************** CATEGORIES *****************************************************

    /**
     * Joint inner category table
     */
    public function joinCategoryFlat()
    {
        return $this->_joinCategoryFlat(false);
    }

    /**
     * Join left category table
     */
    public function joinLeftCategoryFlat()
    {
        return $this->_joinCategoryFlat(true);
    }

    /**
     * Joint category table
     */
    protected function _joinCategoryFlat($joinLeft = false)
    {
        $select = $this->getSelect();
        $mainTableAlias = $this->_lastCategoryProductTableAlias;
        $joinTableAlias = $this->getCategoryFlatTable(true);
        $joinTable = $this->getCategoryFlatTable();
        $columns = $this->getColumns($joinTableAlias);

        $onColumn = 'category_id';
        if ($mainTableAlias == self::NKCP || $mainTableAlias == self::NKCPC) {
            $onColumn = 'main_category_id';
        }

        if (!$joinLeft) {
            $select->join(
                [$joinTableAlias => $joinTable],
                $joinTableAlias . '.entity_id=' . $mainTableAlias . '.' . $onColumn,
                $columns
            );
        } else {
            $select->joinLeft(
                [$joinTableAlias => $joinTable],
                $joinTableAlias . '.entity_id=' . $mainTableAlias . '.' . $onColumn,
                $columns
            );
        }

        $this->joinCategoryPath();

        return $this;
    }

    public function joinTaxonomy()
    {
        $selectedCols = $this->getColumns(self::NKTC);
        $taxonomyCode = $this->getData(self::TAXONOMY_CODE);
        if (empty($selectedCols) || empty($taxonomyCode)) {
            return false;
        }

        //Left join cache Channel category
        $channelCacheTableAlias = self::NKCCHC;
        $channelCacheTable =  $this->getTable('nostress_koongo_cache_channelcategory');
        $condition =  "{$channelCacheTableAlias}.product_id = " . self::MAIN_TABLE_SUBST . ".entity_id AND {$channelCacheTableAlias}.profile_id = '{$this->getProfileId()}' ";
        $this->joinMainTable($channelCacheTableAlias, $channelCacheTable, true, $condition);

        //Left join channel category table
        $channelCategoriesTableAlias = self::NKTC;
        $channelCategoriesTable =  $this->getTable('nostress_koongo_taxonomy_category');
        $condition =  "{$channelCategoriesTableAlias}.hash = {$channelCacheTableAlias}.hash " .
                        "AND {$channelCategoriesTableAlias}.taxonomy_code = '{$taxonomyCode}' ";

        $taxonomyLocale = $this->getData(self::TAXONOMY_LOCALE);
        if (!empty($taxonomyLocale)) {
            $condition .= "AND {$channelCategoriesTableAlias}.locale = '{$taxonomyLocale}' ";
        }
        $this->joinMainTable($channelCategoriesTableAlias, $channelCategoriesTable, false, $condition);

        return true;
    }

    //********************************** PRODUCT CACHE(PRICE) AND TAX *****************************************************

    /**
     * Join fixed product tac table - only if fixed tax is enabled
     */
    public function joinWeee()
    {
        if (!$this->isWeeeEnabled()) {
            return $this;
        }

        $joinTableAlias = self::NKCW;
        $joinTable =  $this->getTable('nostress_koongo_cache_weee');
        $condition =  "{$joinTableAlias}.product_id = " . self::MAIN_TABLE_SUBST . ".entity_id AND {$joinTableAlias}.website_id = '{$this->getWebsiteId()}' ";
        $this->joinMainTable($joinTableAlias, $joinTable, true, $condition);
        return $this;
    }

    public function joinTax()
    {
        $joinTableAlias = self::NKCT;
        $joinTable =  $this->getTable('nostress_koongo_cache_tax');
        $condition =  "{$joinTableAlias}.tax_class_id = " . self::MAIN_TABLE_SUBST . ".tax_class_id AND {$joinTableAlias}.store_id = {$this->getStoreId()}";
        $joinIfColumnsEmpty = true;

        $this->joinMainTable($joinTableAlias, $joinTable, $joinIfColumnsEmpty, $condition);
        return $this;
    }

    public function joinProductCache()
    {
        $joinTableAlias = self::NKCP;
        $joinTable = $this->getTable('nostress_koongo_cache_product');
        $condition =   "{$joinTableAlias}.product_id = " . self::MAIN_TABLE_SUBST . ".entity_id AND {$joinTableAlias}.store_id = {$this->getStoreId()} ";
        $this->_lastCategoryProductTableAlias = $joinTableAlias;
        $this->joinMainTable($joinTableAlias, $joinTable, true, $condition);
        return $this;
    }

    public function joinParentProductCache()
    {
        $joinTableAlias = self::PNKCP;
        $joinTable = $this->getTable('nostress_koongo_cache_product');

        if ($this->isContentStagingAvailable()) {
            $mainTableAlias = self::PCPE;
            $condition = $joinTableAlias . '.product_id=' . self::MAIN_TABLE_SUBST . ".entity_id AND {$joinTableAlias}.store_id = {$this->getStoreId()}";
        } else {
            $mainTableAlias = self::CPR;
            $condition = $joinTableAlias . '.product_id=' . self::MAIN_TABLE_SUBST . ".parent_id AND {$joinTableAlias}.store_id = {$this->getStoreId()}";
        }

        $this->joinTable($mainTableAlias, $joinTableAlias, $joinTable, true, $condition);
        return $this;
    }

    public function joinPriceCache()
    {
        $joinTableAlias = self::NKCPR;
        $joinTable = $this->getTable('nostress_koongo_cache_price');
        $condition =   "{$joinTableAlias}.product_id = " . self::MAIN_TABLE_SUBST . ".entity_id AND {$joinTableAlias}.store_id = {$this->getStoreId()} AND {$joinTableAlias}.customer_group_id = {$this->getCustomerGroupId()}";
        $this->joinMainTable($joinTableAlias, $joinTable, true, $condition);
        return $this;
    }

    public function joinProfileCategoryCache()
    {
        $filterCategoryIds = $this->getCondition(Loader::CONDITION_CATEGORY_IDS, []);
        if (empty($filterCategoryIds)) {
            return $this;
        }

        $joinTableAlias = self::NKCPC;
        $joinTable = $this->getTable('nostress_koongo_cache_profilecategory');
        $condition =   "{$joinTableAlias}.product_id = " . self::MAIN_TABLE_SUBST . ".entity_id AND {$joinTableAlias}.profile_id = {$this->getProfileId()} ";
        $this->_lastCategoryProductTableAlias = $joinTableAlias;
        $this->joinMainTable($joinTableAlias, $joinTable, true, $condition, false);
        return $this;
    }

    public function joinReviewsCache()
    {
        $joinTableAlias = self::NKCR;
        $joinTable =  $this->getTable('nostress_koongo_cache_review');
        $condition = "{$joinTableAlias}.product_id =" . self::MAIN_TABLE_SUBST . ".entity_id AND {$joinTableAlias}.store_id = {$this->getStoreId()}";

        $this->joinMainTable($joinTableAlias, $joinTable, false, $condition);
        return $this;
    }

    protected function definePriceColumns()
    {
        $taxRateColumnName = self::NKCT . '.tax_percent';
        $weeeColumn = "";

        if ($this->isWeeeEnabled()) {
            $weeeColumn = self::NKCW . "." . self::WEEE_COLUMN_TOTAL;
        }

        $columns = ["price_final_exclude_tax" => $this->getPriceColumnFormat(self::NKCPR . "." . "min_price", false, $weeeColumn),
                "price_final_include_tax" => $this->getPriceColumnFormat(self::NKCPR . "." . "min_price", true, $weeeColumn),
                "price_original_exclude_tax" => $this->getPriceColumnFormat(self::NKCPR . "." . "price", false, $weeeColumn),
                "price_original_include_tax" => $this->getPriceColumnFormat(self::NKCPR . "." . "price", true, $weeeColumn),
                "price_discount_percent" => "ROUND(((" . self::NKCPR . ".price - " . self::NKCPR . ".min_price)*100)/" . self::NKCPR . ".price,0)",
                "price_discount_exclude_tax" => $this->getPriceColumnFormat("(" . self::NKCPR . "." . "price - " . self::NKCPR . "." . "min_price)", false),
                "price_discount_include_tax" => $this->getPriceColumnFormat("(" . self::NKCPR . "." . "price - " . self::NKCPR . "." . "min_price)", true),
                "tier_prices" => "tier_prices",
        ];

        $this->_columns[self::NKCPR] = $columns;
    }

    protected function getPriceColumnFormat($comunName, $includeTax, $weeeColumn = "")
    {
        $originalPricesIncludesTax = $this->_taxConfig->priceIncludesTax($this->getStore());
        $currencyRate = $this->helper->getStoreCurrencyRate($this->getStore(), $this->getCurrency());
        $taxRateColumnName = self::NKCT . '.tax_percent';

        $weeeTaxable = $this->isWeeeTaxable();

        $weeeColumnTaxable = $weeeColumnNonTaxable = "";
        if ($this->isWeeeEnabled() && !empty($weeeColumn)) {
            $weeeColumn = "IFNULL({$weeeColumn},0)";
            switch ($this->getPriceDisplayType()) {
                case \Magento\Weee\Model\Tax::DISPLAY_INCL:
                case \Magento\Weee\Model\Tax::DISPLAY_INCL_DESCR:
                    if ($includeTax && $weeeTaxable) {
                        $weeeColumnTaxable = $weeeColumn;
                    } elseif ($originalPricesIncludesTax && !$includeTax && $weeeTaxable) {
                        $weeeColumnTaxable = $weeeColumn;
                    } else {
                        $weeeColumnNonTaxable = $weeeColumn;
                    }
                    // no break
                case \Magento\Weee\Model\Tax::DISPLAY_EXCL_DESCR_INCL:
                case \Magento\Weee\Model\Tax::DISPLAY_EXCL:
                default:
                    $weeeTaxable = false;
                    break;
            }
        }

        $columnFormat = $this->helper->getPriceColumnFormat($comunName, $taxRateColumnName, $currencyRate, $originalPricesIncludesTax, $includeTax, true, $weeeColumnTaxable, $weeeColumnNonTaxable);
        return $columnFormat;
    }

    protected function getWeeeColumns()
    {
        $originalPricesIncludesTax = $this->_taxConfig->priceIncludesTax($this->getStore());
        $currencyRate = $this->helper->getStoreCurrencyRate($this->getStore(), $this->getCurrency());
        $taxRateColumnName = self::NKCT . '.tax_percent';
        $weeeTaxable = $this->isWeeeTaxable();

        $fptTotalInclTax = $this->helper->getWeeeColumnFormat(self::WEEE_COLUMN_TOTAL, $taxRateColumnName, $currencyRate, $originalPricesIncludesTax, $weeeTaxable, true);
        $fptTotalExclTax = $this->helper->getWeeeColumnFormat(self::WEEE_COLUMN_TOTAL, $taxRateColumnName, $currencyRate, $originalPricesIncludesTax, false, true);
        $columns = ["fixed_product_tax" . self::INCLUDE_TAX_SUFFIX => $fptTotalInclTax, "fixed_product_tax" . self::EXCLUDE_TAX_SUFFIX => $fptTotalExclTax];

        $attributes = $this->convertWeeeAttributesToColumnNames();
        foreach ($attributes as $code) {
            $columns[$code . self::INCLUDE_TAX_SUFFIX] =  $this->helper->getWeeeColumnFormat($code, $taxRateColumnName, $currencyRate, $originalPricesIncludesTax, $weeeTaxable, true);
            $columns[$code . self::EXCLUDE_TAX_SUFFIX] = $this->helper->getWeeeColumnFormat($code, $taxRateColumnName, $currencyRate, $originalPricesIncludesTax, false, true);
        }
        return $columns;
    }
}
