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
 * ResourceModel for Koongo Connector product cache table
 *
 * @category Nostress
 * @package Nostress_Koongo
 *
 */

namespace Nostress\Koongo\Model\ResourceModel\Cache;

class Product extends \Nostress\Koongo\Model\ResourceModel\Cache
{
    const DEF_BUNDLE_OPTIONS_REQUIRED_ONLY = 1;
    const DEF_BUNDLE_OPTIONS_DEFAULT_ITEMS_ONLY = 0;

    const CATEGORY_PREFIX = 'category_';

    protected $_cacheName = 'Product';
    protected $_mainTableAlias = self::NKCP;
    protected $_lowestLevel = 0;
    protected $_allowInactiveCategoriesExport = "1";

    /**
     * Fetch only required options during bundle product stock and price calculation.
     * @var unknown_type
     */
    protected $_bundleOptionsRequiredOnly = self::DEF_BUNDLE_OPTIONS_REQUIRED_ONLY;

    /**
     * Fetch only default option items during bundle product stock and price calculation.
     * @var unknown_type
     */
    protected $_bundleOptionsDefaultItmesOnly = self::DEF_BUNDLE_OPTIONS_DEFAULT_ITEMS_ONLY;

    /**
     * Website id for stock status table
     * @var unknown_type
     */
    protected $_stockWebsiteId;

    /**
     * Initialize resource model
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('nostress_koongo_cache_product', 'product_id');
    }

    protected function defineColumns()
    {
        parent::defineColumns();

        $this->_columns[self::CPAMG] =
            [  "product_id" => "entity_id",
                    "store_id" => "({$this->getStoreId()})",];
        $this->_columns[self::CPAMGV] = [  "label" => "label"];
        $this->_columns[self::NKCCP]["category_path"] = "(REPLACE(" . self::NKCCP . ".category_path,'" . self::DEF_CATEGORY_PATH_DELIMITER . "','" . self::DEF_CATEGORY_PATH_SUBST_DELIMITER . "'))";
    }

    protected function reloadTable()
    {
        $this->cleanMainTable();
        $this->insertRecords();
        $this->updateMediaGallery();
        $this->updateProductCategoryMaxLevel();
        $this->updateProductCategoryId();
        $this->updateProductCategories();
        $this->updateParentToChildsCategoryInfo();
        $this->updateConfigurableQty();
        $this->updateBundleQty();
    }

    protected function cleanMainTable()
    {
        $this->helper->log(__("Clean nostress_koongo_cache_product records for store #%1", $this->getStoreId()));
        $this->getConnection()->delete($this->getMainTable(), ['store_id = ?' => $this->getStoreId()]);
    }

    /*
     * Insert records with columns qty
    */
    protected function insertRecords()
    {
        $sql = $this->getInsertRecordsSql();
        $this->runQuery($sql, $this->getMainTable(), "Insert records. Filled columns: product_id, store_id, qty.");
    }

    protected function updateMediaGallery()
    {
        $sql = $this->getMediaGallerySql();
        $this->runQuery($sql, $this->getMainTable(), "Update media gallery.");
    }

    protected function updateProductCategoryMaxLevel()
    {
        $sql = $this->getCategoryMaxLevelSql();
        $this->runQuery($sql, $this->getMainTable(), "Update category max level.");
    }

    protected function updateProductCategoryId()
    {
        $sql = $this->getMaxLevelCategoryIdSql();
        //$sql = $select->crossUpdateFromSelect($this->getMainTable());
        $this->runQuery($sql, $this->getMainTable(), "Update category id. Category with max level is selected.");
    }

    protected function updateProductCategories()
    {
        $sql = $this->getProductCategoriesSql();
        $this->runQuery($sql, $this->getMainTable(), "Update product categories");
    }

    /**
     * Updates category info from parent to child.
     * @return string
     */
    protected function updateParentToChildsCategoryInfo()
    {
        $sql = $this->getParentToChildsCategoryInfoSql();
        $this->runQuery($sql, $this->getMainTable(), "Update category info from parent to child.");
    }

    /**
     * Update qty for configurable products only if stock_id = 1
     * @return string
     */
    protected function updateConfigurableQty()
    {
        $stockId = $this->getStockId();
        if ($stockId != self::DEFAULT_STOCK_ID) {
            return;
        }

        $sql = $this->getConfigurableQtySql();
        $this->runQuery($sql, $this->getMainTable(), "Update qty for configurable products.");
    }

    /**
     * Update qty for bundle products only if stock_id = 1
     * @return string
     */
    protected function updateBundleQty()
    {
        $stockId = $this->getStockId();
        if ($stockId != self::DEFAULT_STOCK_ID) {
            return;
        }

        $sql = $this->getBundleQtySql();
        $this->runQuery($sql, $this->getMainTable(), "Update qty for bundle products.");
    }

    public function getCacheColumns($type = null)
    {
        parent::getCacheColumns();
        switch ($type) {
            case "media_gallery":
                $columns = ["label"=>"label","value"=>"value"];
                break;
            case "tier_price":
                $columns = ["qty"=>"qty","unit_price"=>"unit_price","discount_percent"=>"discount_percent"];
                break;
            case "category":
                $columns = array_merge($this->getColumns($this->getCategoryFlatTable(true), null, false, true), $this->getColumns(self::NKCCP, null, false, true)/*,$this->getColumns(self::CCUR,null,false,true)*/);
                $columns = $this->removeColumnPrefix($columns);
                break;
            case "review":
                $defaultTimeFormatForSql = \Nostress\Koongo\Model\Config\Source\Datetimeformat::STANDARD_DATETIME_SQL;
                $columns = ["review_id"=>self::R . ".review_id",
                    "review_title" => self::RD . ".title",
                    "review_detail" => self::RD . ".detail",
                    "review_created_at" => new \Zend_Db_Expr("DATE_FORMAT(" . self::R . ".created_at,'{$defaultTimeFormatForSql}')"),
                    "reviewer_name" => "IFNULL(" . self::RD . ".nickname ,'')",
                    "review_customer_id" => "IFNULL(" . self::RD . ".customer_id ,'')",
                    "review_rating" => "review_rating.rating",
                ];
            break;
            default:
                $columns = [];
        }

        return $columns;
    }

    protected function removeColumnPrefix($columns)
    {
        $result = [];
        foreach ($columns as $alias => $column) {
            $alias = str_replace(self::CATEGORY_PREFIX, "", $alias);
            $result[$alias] = $column;
        }
        return $result;
    }

    public function setLowestLevel($level)
    {
        if (isset($level) && is_numeric($level) && $level >= 0) {
            $this->_lowestLevel = $level;
        }
    }

    public function setAllowInactiveCategoriesExport($status)
    {
        if (isset($status)) {
            $this->_allowInactiveCategoriesExport = $status;
        }
    }

    protected function allowInactiveCategoriesExport()
    {
        return $this->_allowInactiveCategoriesExport;
    }

    public function setStockWebsiteId($stockWebsiteId)
    {
        $this->_stockWebsiteId = $stockWebsiteId;
    }

    protected function getStockWebsiteId()
    {
        if (isset($this->_stockWebsiteId)) {
            return $this->_stockWebsiteId;
        } else {
            return $this->getWebsiteId();
        }
    }

    public function setBundleOptionsRequiredOnly($bundleOptionsRequiredOnly)
    {
        if (isset($bundleOptionsRequiredOnly)) {
            $this->_bundleOptionsRequiredOnly = $bundleOptionsRequiredOnly;
        }
    }

    public function setBundleOptionsDefaultItmesOnly($bundleOptionsDefaultItmesOnly)
    {
        if (isset($bundleOptionsDefaultItmesOnly)) {
            $this->_bundleOptionsDefaultItmesOnly = $bundleOptionsDefaultItmesOnly;
        }
    }

    protected function getBundleOptionsRequiredOnly()
    {
        return $this->_bundleOptionsRequiredOnly;
    }

    protected function getBundleOptionsDefaultItmesOnly()
    {
        return $this->_bundleOptionsDefaultItmesOnly;
    }

    /************************************ Sql query builders ***************************************/

    /*
     * Insert records with columns min_price, price, qty
     */
    protected function getInsertRecordsSql()
    {
        $mainTableAlias = $this->getProductFlatTable(true);
        $mainTable = $this->getProductFlatTable();

        $select = $this->getEmptySelect();
        $columns = [ "product_id" => $mainTableAlias . ".entity_id",
                        "store_id" => "({$this->getStoreId()})"];

        $stockId = $this->getStockId();

        if ($stockId == self::DEFAULT_STOCK_ID) {
            $catInvStockStatusAlias = self::CISS;
            $catInvStockStatus = $this->getTable('cataloginventory_stock_status');

            $columns["qty"] = $this->helper->getRoundSql("{$catInvStockStatusAlias}.qty", 0);
            $columns["stock_status"] = "{$catInvStockStatusAlias}.stock_status";
            $columns["qty_decimal"] = "{$catInvStockStatusAlias}.qty";

            $select->from([$mainTableAlias => $mainTable], $columns);

            $select->joinLeft(
                [$catInvStockStatusAlias => $catInvStockStatus],
                "{$mainTableAlias}.entity_id = {$catInvStockStatusAlias}.product_id AND {$catInvStockStatusAlias}.stock_id = " . self::DEFAULT_STOCK_ID . " AND {$catInvStockStatusAlias}.website_id = {$this->getStockWebsiteId()}",
                null
            );
        } else {
            $inventoryStockAlias = self::ISX . "_" . $stockId;
            $inventoryStock = $this->getTable('inventory_stock_' . $stockId);
            $columns["qty"] = $this->helper->getRoundSql("IFNULL({$inventoryStockAlias}.quantity,0)", 0);
            $columns["stock_status"] = "IFNULL({$inventoryStockAlias}.is_salable,0)";
            $columns["qty_decimal"] = "IFNULL({$inventoryStockAlias}.quantity,0)";
            $select->from([$mainTableAlias => $mainTable], $columns);

            $select->joinLeft(
                [$inventoryStockAlias => $inventoryStock],
                "{$mainTableAlias}.sku = {$inventoryStockAlias}.sku",
                null
            );
        }
        //echo $select->__toString(); exit();
        $sql = $this->getConnection()->insertFromSelect($select, $this->getMainTable(), array_keys($columns));

        return $sql;
    }

    protected function getMediaGallerySql()
    {
        $select = $this->getEmptySelect();

        $mediaGalleryCacheTable = $this->getTable('nostress_koongo_cache_mediagallery');
        $mediaGalleryCacheTableAlias = self::NKCMG;

        $select->from([$mediaGalleryCacheTableAlias =>  $mediaGalleryCacheTable], ["product_id","store_id"]);
        $select->columns($this->helper->groupConcatColumns($this->getCacheColumns("media_gallery")));
        $select->group("product_id");
        $select->where("store_id = ?", $this->getStoreId());

        //Prepare update query
        $mainTable = $this->getMainTable();
        $mainTableAlias = self::NKCP;

        $updateSql = "UPDATE  {$mainTable} AS {$mainTableAlias} ";
        $updateSql .= "INNER JOIN ( {$select->__toString()} ) ";
        $updateSql .= "AS media ON {$mainTableAlias}.product_id = media.product_id AND  {$mainTableAlias}.store_id = media.store_id ";
        $updateSql .= "SET  {$mainTableAlias}.media_gallery =  media.concat_colum";

        return $updateSql;
    }

    /**
     * Returns updates query for max level column at cache product table
     * @return string
     */
    protected function getCategoryMaxLevelSql()
    {
        $mainTableAlias = $this->getProductFlatTable(true);
        $mainTable = $this->getProductFlatTable();

        $catProdTableAlias = self::CCP;
        $catProdTable = $this->getTable('catalog_category_product');

        $catTableAlias = $this->getCategoryFlatTable(true);
        $catTable = $this->getCategoryFlatTable();

        $select = $this->getEmptySelect();
        $select->from([$mainTableAlias => $mainTable], ["product_id" => "entity_id" ,"store_id" => "({$this->getStoreId()})"]);

        $select->joinLeft(
            [$catProdTableAlias => $catProdTable],
            $catProdTableAlias . '.product_id=' . $mainTableAlias . '.entity_id ',
            null
        );

        $select->joinLeft(
            [$catTableAlias => $catTable],
            $catProdTableAlias . '.category_id=' . $catTableAlias . '.entity_id',
            ['max_level' => 'MAX(level)']
        );

        $select->group($mainTableAlias . '.entity_id');

        //Prepare update query
        $mainTable = $this->getMainTable();
        $mainTableAlias = self::NKCP;

        $updateSql = "UPDATE  {$mainTable} AS {$mainTableAlias} ";
        $updateSql .= "INNER JOIN ( {$select->__toString()} ) ";
        $updateSql .= "AS category_max ON {$mainTableAlias}.product_id = category_max.product_id AND  {$mainTableAlias}.store_id = category_max.store_id ";
        $updateSql .= "SET  {$mainTableAlias}.main_category_max_level =  category_max.max_level";

        return $updateSql;
    }

    /**
     * Returns updates query for category it column, category with max level is selected.
     * @return string
     */
    protected function getMaxLevelCategoryIdSql()
    {
        $mainTableAlias = 'main_table';
        $mainTable = $this->getMainTable();

        $catProdTableAlias = self::CCP;
        $catProdTable = $this->getTable('catalog_category_product');

        $catTableAlias = $this->getCategoryFlatTable(true);
        $catTable = $this->getCategoryFlatTable();

        $sql = "UPDATE {$mainTable} AS {$mainTableAlias} ";
        $sql .= "LEFT JOIN {$catProdTable} AS {$catProdTableAlias} ON {$catProdTableAlias}.product_id = {$mainTableAlias}.product_id ";
        $sql .= "INNER JOIN {$catTable} AS {$catTableAlias} ON {$catTableAlias}.entity_id = {$catProdTableAlias}.category_id AND {$catTableAlias}.level = {$mainTableAlias}.main_category_max_level ";
        $sql .= "SET {$mainTableAlias}.main_category_id = {$catTableAlias}.entity_id";
        return $sql;
    }

    protected function getProductCategoriesSql()
    {
        $mainTable = $this->getTable('catalog_category_product');
        $mainTableAlias = self::CCP;
        $joinTableAlias = $this->getCategoryFlatTable(true);
        $joinTable = $this->getCategoryFlatTable();

        $select = $this->getEmptySelect();
        $select->from([$mainTableAlias => $mainTable], [ "product_id","({$this->getStoreId()}) as store_id"]);
        $select->join(
            [$joinTableAlias => $joinTable],
            $joinTableAlias . '.entity_id=' . $mainTableAlias . '.category_id',
            null
        );
        $select->where($joinTableAlias . '.level>=?', $this->_lowestLevel);

        if (!$this->allowInactiveCategoriesExport()) {
            $select->where($joinTableAlias . '.is_active=?', self::CATEGORY_ACTIVE);
        }

        $select->group('product_id');
        $select = $this->_joinCategoryPath($select, false, $mainTableAlias, "category_id");

        //define concat columns
        $columns = $this->getCacheColumns('category');
        $select->columns($this->helper->groupConcatColumns($columns));
        $select->columns($this->helper->groupConcatColumns(["{$joinTableAlias}.entity_id"], ",", "category_ids"));

        $updateSql = "UPDATE {$this->getMainTable()} AS {$this->_mainTableAlias} ";
        $updateSql .= "INNER JOIN ( {$select->__toString()} ) ";
        $updateSql .= "AS categories ON {$this->_mainTableAlias}.product_id = categories.product_id AND  {$this->_mainTableAlias}.store_id = categories.store_id ";
        $updateSql .= "SET  {$this->_mainTableAlias}.categories =  categories.concat_colum, {$this->_mainTableAlias}.category_ids =  categories.category_ids ";

        return $updateSql;
    }

    /**
     * Returns updates query for update category info from parent to child.
     * @return string
     */
    protected function getParentToChildsCategoryInfoSql()
    {
        $mainTableAlias = 'main_table';
        $mainTable = $this->getMainTable();

        $catProdRelAlias = self::CPR;
        $catProdRel = $this->getTable('catalog_product_relation');

        $pcpeTableAlias = self::PCPE;
        $pcpeTable = $this->getTable('catalog_product_entity');

        $parentCacheAlias = "pnkcp";

        $sql = "UPDATE {$mainTable} AS {$mainTableAlias} ";
        $sql .= "INNER JOIN {$catProdRel} AS {$catProdRelAlias} ON {$catProdRelAlias}.child_id = {$mainTableAlias}.product_id ";
        if ($this->isContentStagingAvailable()) {
            $sql .= "INNER JOIN {$pcpeTable} AS {$pcpeTableAlias} ON  {$catProdRelAlias}.parent_id = {$pcpeTableAlias}.row_id AND {$pcpeTableAlias}.created_in <= UNIX_TIMESTAMP() AND {$pcpeTableAlias}.updated_in > UNIX_TIMESTAMP() ";
            $sql .= "INNER JOIN {$mainTable} AS {$parentCacheAlias} ON  {$pcpeTableAlias}.entity_id = {$parentCacheAlias}.product_id AND {$mainTableAlias}.store_id = {$parentCacheAlias}.store_id ";
        } else {
            $sql .= "INNER JOIN {$mainTable} AS {$parentCacheAlias} ON  {$catProdRelAlias}.parent_id = {$parentCacheAlias}.product_id AND {$mainTableAlias}.store_id = {$parentCacheAlias}.store_id ";
        }

        $sql .= " SET  {$mainTableAlias}.main_category_id = {$parentCacheAlias}.main_category_id,
    {$mainTableAlias}.main_category_max_level = {$parentCacheAlias}.main_category_max_level,
    {$mainTableAlias}.categories = {$parentCacheAlias}.categories,
    {$mainTableAlias}.category_ids = {$parentCacheAlias}.category_ids ";
        $sql .= "WHERE {$mainTableAlias}.main_category_id IS NULL AND  {$mainTableAlias}.store_id = {$this->getStoreId()}";

        return $sql;
    }

    /**
    * Returns updates query for update qty for configurable products.
    * @return string
    */
    protected function getConfigurableQtySql()
    {
        $cissTable = $this->getTable("cataloginventory_stock_status");
        $cissTableAlias = self::CISS;

        $cprTable = $this->getTable("catalog_product_relation");
        $cprTableAlias = self::CPR;
        $parentIdColumn = $cprTableAlias . ".parent_id";
        if ($this->isContentStagingAvailable()) {
            $pcpeTableAlias = self::PCPE;
            $pcpeTable = $this->getTable('catalog_product_entity');
            $parentIdColumn = $pcpeTableAlias . ".entity_id";
        }

        $cpfTable = $this->getProductFlatTable();
        $cpfTableAlias = $this->getProductFlatTable(true);

        $select = $this->getEmptySelect();
        $select->from(
            [$cissTableAlias => $cissTable],
            [  "product_id" => $parentIdColumn,
                    "store_id" => "({$this->getStoreId()})",
                    "sum_qty" => "SUM({$cissTableAlias}.qty)"]
        );
        $select->join(
            [$cprTableAlias => $cprTable],
            "{$cprTableAlias}.child_id = {$cissTableAlias}.product_id AND {$cprTableAlias}.parent_id IS NOT NULL",
            null
        );

        if ($this->isContentStagingAvailable()) {
            $condition =  "{$pcpeTableAlias}.row_id = {$cprTableAlias}.parent_id ";
            $condition .= "AND {$pcpeTableAlias}.created_in <= UNIX_TIMESTAMP() AND {$pcpeTableAlias}.updated_in > UNIX_TIMESTAMP() ";

            $select->join(
                [$pcpeTableAlias => $pcpeTable],
                $condition,
                null
            );
        }

        $select->join(
            [$cpfTableAlias => $cpfTable],
            "{$cpfTableAlias}.entity_id = {$parentIdColumn} AND {$cpfTableAlias}.type_id = 'configurable'",
            null
        );
        $select->group($parentIdColumn);

        $selectTable =  $this->getSubSelectTable($select);
        $updateSql = "UPDATE {$this->getMainTable()} AS {$this->_mainTableAlias} ";
        $updateSql .= "INNER JOIN ( {$selectTable} ) ";
        $updateSql .= "AS config_qty ON {$this->_mainTableAlias}.product_id = config_qty.product_id AND  {$this->_mainTableAlias}.store_id = config_qty.store_id ";
        $updateSql .= "SET  {$this->_mainTableAlias}.qty =  config_qty.sum_qty;";

        return $updateSql;
    }

    /**
    * Returns updates query for update qty for configurable products.
    * @return string
    */
    protected function getBundleQtySql()
    {
        $cpbsTable = $this->getTable("catalog_product_bundle_selection");
        $cpbsTableAlias = self::CPBS;

        $cpboTable = $this->getTable("catalog_product_bundle_option");
        $cpboTableAlias = self::CPBO;

        $requiredOptionsOnly = $this->getBundleOptionsRequiredOnly();
        $defaultOptionItemsOnly = $this->getBundleOptionsDefaultItmesOnly();

        //Prepare subselect for options - calculate minimal qty and if at least one item for each option is in stock (and has always_in_stock)
        $subSelect = $this->getEmptySelect();

        $qtyPerSelectionQuery =  "IF({$cpbsTableAlias}.selection_qty > 1, {$this->_mainTableAlias}.qty DIV {$cpbsTableAlias}.selection_qty , {$this->_mainTableAlias}.qty)";
        $optionQqtyQuery = "(IF({$cpboTableAlias}.type IN ('select','radio'),SUM({$qtyPerSelectionQuery}),MIN({$qtyPerSelectionQuery})))";

        $optionInStockQuery = "(BIT_OR({$this->_mainTableAlias}.stock_status))";
        //$optionAlwaysInStock = "(BIT_OR({$this->_mainTableAlias}.always_in_stock))";
        if ($defaultOptionItemsOnly) {
            $optionInStockQuery = "(BIT_AND({$this->_mainTableAlias}.stock_status))";
            //$optionAlwaysInStock = "(BIT_AND({$this->_mainTableAlias}.always_in_stock))";
        }

        $subSelect->from(
            [$this->_mainTableAlias => $this->getMainTable()],
            [ "product_id" => $this->_mainTableAlias . ".product_id",
                       "parent_product_id" => $cpbsTableAlias . ".parent_product_id",
                       "option_id" => self::CPBS . ".option_id", //Bundle option id
                       "option_qty" => $optionQqtyQuery,
                       "option_in_stock" => $optionInStockQuery//,
                       //"option_always_in_stock" => $optionAlwaysInStock
                ]
        );
        $subSelect->join(
            [$cpbsTableAlias => $cpbsTable],
            "{$this->_mainTableAlias}.product_id = {$cpbsTableAlias}.product_id",
            null
        );

        $subSelect->join(
            [$cpboTableAlias => $cpboTable],
            "{$cpbsTableAlias}.option_id = {$cpboTableAlias}.option_id",
            null
        );

        if ($requiredOptionsOnly) {
            $subSelect->where($cpboTableAlias . '.required = 1');
        }

        if ($defaultOptionItemsOnly) {
            $subSelect->where($cpbsTableAlias . '.is_default = 1');
        }

        $subSelect->where($this->_mainTableAlias . '.store_id=?', $this->getStoreId());
        $subSelect->group($cpbsTableAlias . '.option_id');

        $optionsSubselectTable = $this->getSubSelectTable($subSelect);
        $optionsSubselectTableAlias = 'bundle_options';

        //Summarize options prices to get total minimal and standard price for bundle item
        $select = $this->getEmptySelect();
        $columns = [ "product_id" => $optionsSubselectTableAlias . ".parent_product_id",
                "qty" => "(MIN({$optionsSubselectTableAlias}.option_qty))",
                "stock_status" => "(BIT_AND({$optionsSubselectTableAlias}.option_in_stock))"//,
                //"always_in_stock" => "(BIT_AND({$optionsSubselectTableAlias}.option_always_in_stock))"
        ];

        $select->from([$optionsSubselectTableAlias => $optionsSubselectTable], $columns);
        $select->group($optionsSubselectTableAlias . '.parent_product_id');

        $bundleSubselectTable = $this->getSubSelectTable($select);
        $bundleSubselectTableAlias = 'bundle_stock';
        $updateSql = "UPDATE {$this->getMainTable()} AS {$this->_mainTableAlias} ";
        $updateSql .= "INNER JOIN {$bundleSubselectTable} AS {$bundleSubselectTableAlias} ";
        $updateSql .= "ON {$bundleSubselectTableAlias}.product_id = {$this->_mainTableAlias}.product_id ";
        $updateSql .= "SET  {$this->_mainTableAlias}.qty = {$bundleSubselectTableAlias}.qty, {$this->_mainTableAlias}.stock_status = {$bundleSubselectTableAlias}.stock_status ";
        //$updateSql .= "{$this->_mainTableAlias}.always_in_stock = {$bundleSubselectTableAlias}.always_in_stock ";
        $updateSql .= "WHERE {$this->_mainTableAlias}.store_id = {$this->getStoreId()}; ";

        //echo $updateSql;//$select->__toString();
        //exit();
        return $updateSql;
    }
}
