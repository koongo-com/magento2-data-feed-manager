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
 * Resource model for Koongo Connector profile category cache table
 *
 * @category Nostress
 * @package Nostress_Koongo
 *
 */

namespace Nostress\Koongo\Model\ResourceModel\Cache;

class Profilecategory extends \Nostress\Koongo\Model\ResourceModel\Cache\Product
{
    protected $_cacheName = 'Profile category';
    protected $_mainTableAlias = self::NKCPC;
    protected $_lowestLevel = 0;
    protected $_allowInactiveCategoriesExport = "1";
    protected $_profileId = 0;
    protected $_categoryIds = [];

    /**
     * Initialize resource model
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('nostress_koongo_cache_profilecategory', 'product_id');
    }

    protected function defineColumns()
    {
        parent::defineColumns();

        $this->_columns[self::NKCCP]["category_path"] = "(REPLACE(" . self::NKCCP . ".category_path,'" . self::DEF_CATEGORY_PATH_DELIMITER . "','" . self::DEF_CATEGORY_PATH_SUBST_DELIMITER . "'))";
    }

    protected function reloadTable()
    {
        $this->cleanMainTable();

        $this->insertRecords();
        $this->updateProductCategoryId();
        $this->updateProductCategories();
        $this->updateParentToChildsCategoryInfo();
    }

    protected function cleanMainTable()
    {
        $this->helper->log(__("Clean nostress_koongo_cache_profilecategory records for profile #%1", $this->_profileId));
        $this->getConnection()->delete($this->getMainTable(), ['profile_id = ?' => $this->_profileId]);
    }

    /*
     * Insert records with columns min_price, price, qty
    */
    protected function insertRecords()
    {
        $sql = $this->getInsertRecordsSql();
        $this->runQuery($sql, $this->getMainTable(), "Insert records. Filled columns: profile_id, product_id, main_category_max_level .");
    }

    protected function updateProductCategoryId()
    {
        $sql = $this->getMaxLevelCategoryIdSql();
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
        $sql = $this->getInsertChildProductsRecordsSql();
        $this->runQuery($sql, $this->getMainTable(), "Update category info from parent to child.");
    }

    public function setLowestLevel($level)
    {
        if (isset($level) && is_numeric($level) && $level >= 0) {
            $this->_lowestLevel = $level;
        }
    }

    public function setProfileId($profileId)
    {
        $this->_profileId = $profileId;
    }

    public function setCategoryIds($categoryIds)
    {
        $this->_categoryIds = $categoryIds;
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

    /************************************ Sql query builders ***************************************/

    /*
     * Insert records
     */
    protected function getInsertRecordsSql()
    {
        $select = $this->getSelectCategoryMaxLevelSql($this->_categoryIds, $this->_profileId);
        $sql = $this->getConnection()->insertFromSelect($select, $this->getMainTable(), ["profile_id", "product_id","main_category_max_level"]);
        return $sql;
    }

    /**
     * Returns updates query for max level column at cache product table
     * @return string
     */
    protected function getSelectCategoryMaxLevelSql($categoryIds, $profileId)
    {
        $mainTableAlias = $this->getProductFlatTable(true);
        $mainTable = $this->getProductFlatTable();

        $catProdIndexTableAlias = self::CCPI;
        $catProdIndexTable = $this->getCategoryProductIndexTableName();

        $catProdTableAlias = self::CCP;
        $catProdTable = $this->getTable('catalog_category_product');

        $catTableAlias = $this->getCategoryFlatTable(true);
        $catTable = $this->getCategoryFlatTable();

        $select = $this->getEmptySelect();
        $select->from([$mainTableAlias => $mainTable], ["profile_id" => "('{$profileId}')", "product_id" => "entity_id"]);

        $select->joinLeft(
            [$catProdIndexTableAlias => $catProdIndexTable],
            $catProdIndexTableAlias . '.product_id=' . $mainTableAlias . '.entity_id AND ' . $catProdIndexTableAlias . '.store_id = ' . $this->getStoreId(),
            null
        );

        $select->joinLeft(
            [$catProdTableAlias => $catProdTable],
            $catProdTableAlias . '.product_id=' . $mainTableAlias . '.entity_id',
            null
        );

        $select->joinLeft(
            [$catTableAlias => $catTable],
            $catProdIndexTableAlias . '.category_id=' . $catTableAlias . '.entity_id OR ' . $catProdTableAlias . '.category_id=' . $catTableAlias . '.entity_id',
            ['main_category_max_level' => 'MAX(level)']
        );

        $select->where($catProdIndexTableAlias . '.category_id IN (?) OR ' . $catProdTableAlias . '.category_id IN (?)', explode(",", $categoryIds));
        $select->group($mainTableAlias . '.entity_id');
        return $select;
    }

    /**
     * Returns updates query for category it column, category with max level is selected.
     * @return string
     */
    protected function getMaxLevelCategoryIdSql()
    {
        $categoryIds = $this->_categoryIds;

        $mainTableAlias = 'main_table';
        $mainTable = $this->getMainTable();

        $catProdIndexTableAlias = self::CCPI;
        $catProdIndexTable = $this->getCategoryProductIndexTableName();

        $catProdTableAlias = self::CCP;
        $catProdTable = $this->getTable('catalog_category_product');

        $catTableAlias = $this->getCategoryFlatTable(true);
        $catTable = $this->getCategoryFlatTable();

        $sql = "UPDATE {$mainTable} AS {$mainTableAlias} ";
        $sql .= "LEFT JOIN {$catProdIndexTable} AS {$catProdIndexTableAlias} ON {$catProdIndexTableAlias}.product_id = {$mainTableAlias}.product_id AND {$catProdIndexTableAlias}.store_id = {$this->getStoreId()} ";
        $sql .= "LEFT JOIN {$catProdTable} AS {$catProdTableAlias} ON {$catProdTableAlias}.product_id = {$mainTableAlias}.product_id ";
        $sql .= "INNER JOIN {$catTable} AS {$catTableAlias} ON ({$catTableAlias}.entity_id = {$catProdIndexTableAlias}.category_id OR {$catTableAlias}.entity_id = {$catProdTableAlias}.category_id )AND {$catTableAlias}.level = {$mainTableAlias}.main_category_max_level ";
        $sql .= "SET {$mainTableAlias}.main_category_id = {$catTableAlias}.entity_id ";
        $sql .= "WHERE {$mainTableAlias}.profile_id = {$this->_profileId} ";
        $sql .= "AND ({$catProdIndexTableAlias}.category_id IN ({$categoryIds}) OR {$catProdTableAlias}.category_id IN ({$categoryIds}))";

        return $sql;
    }

    //Original function befor modification
    // protected function getProductCategoriesSql()
    // {
    // 	$mainTable = $this->getCategoryProductIndexTableName();
    // 	$mainTableAlias = self::CCPI;
    // 	$joinTableAlias = $this->getCategoryFlatTable(true);
    // 	$joinTable = $this->getCategoryFlatTable();

    // 	$select = $this->getEmptySelect();
    // 	$select->from(array($mainTableAlias => $mainTable), array( "product_id","({$this->getStoreId()}) as store_id"));
    // 	$select->join(
    // 			array($joinTableAlias => $joinTable),
    // 			$joinTableAlias.'.entity_id='.$mainTableAlias.'.category_id AND '.$mainTableAlias.'.category_id IN ('.$this->_categoryIds.')',
    // 			null
    // 	);
    // 	$select->where($mainTableAlias.'.store_id=?', $this->getStoreId());
    // 	$select->where($joinTableAlias.'.level>=?', $this->_lowestLevel);

    // 	if(!$this->allowInactiveCategoriesExport())
    // 		$select->where($joinTableAlias.'.is_active=?', self::CATEGORY_ACTIVE);

    // 	$select->group('product_id');
    // 	$select = $this->_joinCategoryPath($select,false,$mainTableAlias,"category_id");

    // 	//define concat columns
    // 	$columns = $this->getCacheColumns('category');
    // 	$select->columns($this->helper->groupConcatColumns($columns));
    // 	$select->columns($this->helper->groupConcatColumns(["{$joinTableAlias}.entity_id"],",","category_ids"));

    // 	$updateSql = "UPDATE {$this->getMainTable()} AS {$this->_mainTableAlias} ";
    // 	$updateSql .= "INNER JOIN ( {$select->__toString()} ) ";
    // 	$updateSql .= "AS categories ON {$this->_mainTableAlias}.product_id = categories.product_id AND  {$this->_mainTableAlias}.profile_id = {$this->_profileId} ";
    // 	$updateSql .= "SET  {$this->_mainTableAlias}.categories =  categories.concat_colum, {$this->_mainTableAlias}.category_ids =  categories.category_ids ";
    // 	return $updateSql;
    // }

    //Modified function
    protected function getProductCategoriesSql()
    {
        $catProdTableAlias = self::CCP;
        $catProdTable = $this->getTable('catalog_category_product');

        $joinTableAlias = $this->getCategoryFlatTable(true);
        $joinTable = $this->getCategoryFlatTable();

        $select = $this->getEmptySelect();
        $select->from([$catProdTableAlias => $catProdTable], [ "product_id","({$this->getStoreId()}) as store_id"]);
        $select->join(
            [$joinTableAlias => $joinTable],
            $joinTableAlias . '.entity_id=' . $catProdTableAlias . '.category_id AND ' . $catProdTableAlias . '.category_id IN (' . $this->_categoryIds . ')',
            null
        );
        //$select->where($catProdTableAlias.'.store_id=?', $this->getStoreId());
        $select->where($joinTableAlias . '.level>=?', $this->_lowestLevel);

        if (!$this->allowInactiveCategoriesExport()) {
            $select->where($joinTableAlias . '.is_active=?', self::CATEGORY_ACTIVE);
        }

        $select->group('product_id');
        $select = $this->_joinCategoryPath($select, false, $catProdTableAlias, "category_id");

        //define concat columns
        $columns = $this->getCacheColumns('category');
        $select->columns($this->helper->groupConcatColumns($columns));
        $select->columns($this->helper->groupConcatColumns(["{$joinTableAlias}.entity_id"], ",", "category_ids"));

        $updateSql = "UPDATE {$this->getMainTable()} AS {$this->_mainTableAlias} ";
        $updateSql .= "INNER JOIN ( {$select->__toString()} ) ";
        $updateSql .= "AS categories ON {$this->_mainTableAlias}.product_id = categories.product_id AND  {$this->_mainTableAlias}.profile_id = {$this->_profileId} ";
        $updateSql .= "SET  {$this->_mainTableAlias}.categories =  categories.concat_colum, {$this->_mainTableAlias}.category_ids =  categories.category_ids ";
        return $updateSql;
    }

    //Modification for load from Catalog Category Product Table + Category Product Index Table
    //Not used at the moment
    // protected function getProductCategoriesSql()
    // {
    // 	$prodTableAlias = $this->getProductFlatTable(true);
    // 	$prodTable = $this->getProductFlatTable();

    // 	$catTableAlias = $this->getCategoryFlatTable(true);
    // 	$catTable = $this->getCategoryFlatTable();

    // 	//$mainTableAlias = $this->getCategoryFlatTable(true);
    // 	//$mainTable = $this->getCategoryFlatTable();

    // 	$joinTableAlias = self::CCPI;
    // 	$joinTable = $this->getCategoryProductIndexTableName();

    // 	$catProdTableAlias = self::CCP;
    // 	$catProdTable = $this->getTable('catalog_category_product');

    // 	$catPathTableAlias = self::NKCCP;
    // 	$catPathTable = $this->getTable('nostress_koongo_cache_categorypath');

    // 	$select = $this->getEmptySelect();
    // 	$select->from(array($prodTableAlias => $prodTable), array( "({$prodTableAlias}.entity_id) as product_id","({$this->getStoreId()}) as store_id"));
    // 	$select->joinLeft(
    // 			array($joinTableAlias => $joinTable),
    // 			$prodTableAlias.'.entity_id='.$joinTableAlias.'.product_id AND '.$joinTableAlias.'.store_id = '.$this->getStoreId(),
    // 			null
    // 	);

    // 	$select->joinLeft(
    // 		array($catProdTableAlias => $catProdTable),
    // 		$prodTableAlias.'.entity_id='.$catProdTableAlias.'.product_id ',
    // 		null
    // 	);

    // 	$select->joinLeft(
    // 			array($catPathTableAlias => $catPathTable),
    // 			"({$catPathTableAlias}.category_id ={$joinTableAlias}.category_id OR {$catPathTableAlias}.category_id = {$catProdTableAlias}.category_id) AND {$catPathTableAlias}.store_id ={$this->getStoreId()}",
    // 			null
    // 	);

    // 	$select->join(
    // 		array($catTableAlias => $catTable),
    // 		"{$catPathTableAlias}.category_id ={$catTableAlias}.entity_id",
    // 		null
    // 	);

    // 	$select->where($joinTableAlias.'.category_id IN (?) OR '.$catProdTableAlias.'.category_id IN (?)', explode(",",$this->_categoryIds));
    // 	$select->where($catTableAlias.'.level>=?', $this->_lowestLevel);

    // 	if(!$this->allowInactiveCategoriesExport())
    // 		$select->where($catTableAlias.'.is_active=?', self::CATEGORY_ACTIVE);
    // 	$select->group($catProdTableAlias.'.product_id');

    // 	//define concat columns
    // 	$columns = $this->getCacheColumns('category');
    // 	$select->columns($this->helper->groupConcatColumns($columns));
    // 	$select->columns($this->helper->groupConcatColumns(["{$catTableAlias}.entity_id"],",","category_ids"));

    // 	echo $select->__toString();
    // 	exit();

    // 	$updateSql = "UPDATE {$this->getMainTable()} AS {$this->_mainTableAlias} ";
    // 	$updateSql .= "INNER JOIN ( {$select->__toString()} ) ";
    // 	$updateSql .= "AS categories ON {$this->_mainTableAlias}.product_id = categories.product_id AND  {$this->_mainTableAlias}.profile_id = {$this->_profileId} ";
    // 	$updateSql .= "SET  {$this->_mainTableAlias}.categories =  categories.concat_colum, {$this->_mainTableAlias}.category_ids =  categories.category_ids ";
    // 	return $updateSql;
    // }
    //TODO - musi se udelat join pres produkty
    // SELECT `ccpi`.`product_id`, (1) AS `store_id`, GROUP_CONCAT(ccf1.entity_id,'||',ccf1.name,'||',ccf1.path,'||',ccf1.level,'||',ccf1.parent_id,'||',ccf1.url_key,'||',(REPLACE(IFNULL(ccf1.url_path,''),'/','-')),'||',(CONCAT('http://127.0.0.1/m23/index.php/',CONCAT(IFNULL(ccf1.url_path,ccf1.url_key),'.html'))),'||',(REPLACE(nkccp.category_path,'/','/$#')),'||',nkccp.category_root_name,'||',nkccp.category_root_id SEPARATOR ';;') as concat_colum, GROUP_CONCAT(ccf1.entity_id SEPARATOR ',') as category_ids
    // FROM `catalog_category_flat_store_1` AS `ccf1`
    // INNER JOIN `catalog_category_product_index_store1` AS `ccpi` ON ccf1.entity_id=ccpi.category_id AND ccpi.category_id IN (4)
    // LEFT JOIN `nostress_koongo_cache_categorypath` AS `nkccp` ON nkccp.category_id =ccpi.category_id AND nkccp.store_id =1
    // WHERE (ccpi.store_id=1) AND (ccf1.level>='2') GROUP BY `product_id`

    /*
     * Insert records
    */
    protected function getInsertChildProductsRecordsSql()
    {
        $select = $this->getSelectChildProductRecordSql();
        $sql = $select->insertIgnoreFromSelect($this->getMainTable(), [ "profile_id","product_id","main_category_id","main_category_max_level","categories","category_ids"]);
        return $sql;
    }

    protected function getSelectChildProductRecordSql()
    {
        $mainTableAlias = 'main_table';
        $mainTable = $this->getMainTable();

        $catProdRelAlias = self::CPR;
        $catProdRel = $this->getTable('catalog_product_relation');

        $pcpeTableAlias = self::PCPE;
        $pcpeTable = $this->getTable('catalog_product_entity');

        $parentCacheAlias = "pnkcpc";

        $select = $this->getEmptySelect();
        $select->from(
            [$mainTableAlias => $mainTable],
            [ "profile_id" => "('{$this->_profileId}')","product_id" => "{$catProdRelAlias}.child_id","main_category_id","main_category_max_level","categories","category_ids"]
        );

        if ($this->isContentStagingAvailable()) {
            $condition =  "{$mainTableAlias}.product_id = {$pcpeTableAlias}.entity_id ";
            $condition .= "AND {$pcpeTableAlias}.created_in <= UNIX_TIMESTAMP() AND {$pcpeTableAlias}.updated_in > UNIX_TIMESTAMP() ";

            $select->join(
                [$pcpeTableAlias => $pcpeTable],
                $condition,
                null
            );

            $select->join(
                [$catProdRelAlias => $catProdRel],
                "{$catProdRelAlias}.parent_id = {$pcpeTableAlias}.row_id",
                null
            );
        } else {
            $select->join(
                [$catProdRelAlias => $catProdRel],
                "{$catProdRelAlias}.parent_id = {$mainTableAlias}.product_id",
                null
            );
        }

        $select->where($mainTableAlias . '.profile_id=?', $this->_profileId);
        return $select;
    }

    /**
     * Get Catalog Category Product Index Table
     * Table name changed in version 2.2.5
     */
    protected function getCategoryProductIndexTableName()
    {
        if ($this->helper->isMagentoVersionEqualOrGreaterThan("2.2.5")) {
            $catProdTable = $this->getTable('catalog_category_product_index_store' . $this->getStoreId());
        } else {
            $catProdTable = $this->getTable('catalog_category_product_index');
        }

        return $catProdTable;
    }
}
