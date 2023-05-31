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
 * ResourceModel for Koongo Connector categorypath cache table
 *
 * @category Nostress
 * @package Nostress_Koongo
 *
 */

namespace Nostress\Koongo\Model\ResourceModel\Cache;

class Categorypath extends \Nostress\Koongo\Model\ResourceModel\Cache
{
    protected $_cacheName = 'Category path';

    const DEF_MAX_LEVEL = 10;
    const DEF_FIRST_LEVEL_TO_INCLUDE = 2;
    const LEVEL = 'level';
    protected $_lowestLevel = null;

    public function _construct()
    {
        $this->_init('nostress_koongo_cache_categorypath', 'category_id');
    }

    public function setLowestLevel($level)
    {
        if (isset($level) && is_numeric($level) && $level >= 0) {
            $this->_lowestLevel = $level;
        }
    }

    protected function defineColumns()
    {
        parent::defineColumns();
        $this->_columns[$this->getCategoryFlatTable(true)] =
                [ 	"category_id" => "entity_id",
                        "store_id" => "({$this->getStoreId()})",
                        "category_path" => "('')",
                        "category_root_name" => "('')",
                        "category_root_id" => "('')",
                        "ids_path" => "path",
                        "level" => "level"];
    }

    protected function reloadTable()
    {
        $firstLevelToInclude = self::DEF_FIRST_LEVEL_TO_INCLUDE;
        if (isset($this->_lowestLevel)) {
            $firstLevelToInclude = $this->_lowestLevel;
        }

        $maxLevel = $this->getCategoryMaxLevel();
        $this->getCategoryPathUpdateSql($maxLevel, $firstLevelToInclude);
    }

    protected function getCategoryPathUpdateSql($maxLevel, $firstLevelToInclude)
    {
        $storeId = $this->getStoreId();
        $this->cleanMainTable();

        $i = 0;
        for ($i;$i<$firstLevelToInclude;$i++) {
            $sql = $this->getInsertCategoryPathSql($i, true);
            $this->runQuery($sql, $this->getMainTable(), __("Insert base category path records for store #%1 and level #%2", $storeId, $i));
        }

        $i = $firstLevelToInclude;
        for ($i;$i<=$maxLevel;$i++) {
            $sql = $this->getInsertCategoryPathSql($i, false);
            $this->runQuery($sql, $this->getMainTable(), __("Insert category path records for store #%1 and level #%2", $storeId, $i));
        }
        return $sql;
    }

    protected function getInsertCategoryPathSql($level, $base = false)
    {
        $storeId = $this->getStoreId();
        $sql =  "INSERT INTO {$this->getMainTable()} ";

        $mainTable = $this->getMainTable();
        $mainTableAlias = self::NKCCP;
        $tableAlias = $this->getCategoryFlatTable(true);
        $table = $this->getCategoryFlatTable();

        $select = $this->getEmptySelect();
        $select->from([$tableAlias => $table], null);
        $select->distinct();
        $columns = $this->getColumns($tableAlias);

        if (!$base) {
            $select->join(
                [$mainTableAlias => $mainTable],
                "{$mainTableAlias}.level = ({$tableAlias}.level-1) AND {$tableAlias}.path LIKE CONCAT({$mainTableAlias}.ids_path,'" . self::DEF_CATEGORY_PATH_DELIMITER . "%') AND {$mainTableAlias}.store_id ={$storeId} ",
                null
            );

            $columns["category_path"] = "IF({$mainTableAlias}.category_path <> '',CONCAT({$mainTableAlias}.category_path,'" . self::DEF_CATEGORY_PATH_DELIMITER . "',{$tableAlias}.name),{$tableAlias}.name)";
            $columns["category_root_name"] = "IF({$mainTableAlias}.category_root_name <> '',{$mainTableAlias}.category_root_name,{$tableAlias}.name)";
            $columns["category_root_id"] = "IF({$mainTableAlias}.category_root_name <> '',{$mainTableAlias}.category_root_id,{$tableAlias}.entity_id)";
        }
        $select->columns($columns);
        $select->where($tableAlias . ".level=?", $level);

        $sql .= $this->getSubSelectTable($select) . ";";
        return $sql;
    }

    protected function getCategoryMaxLevel()
    {
        $result = $this->runOneRowSelectQuery($this->getCategoryPathMaxLevelSql());

        if (array_key_exists(self::LEVEL, $result)) {
            return $result[self::LEVEL];
        } else {
            return self::DEF_MAX_LEVEL;
        }
    }

    protected function getCategoryPathMaxLevelSql()
    {
        return "SELECT MAX(" . self::LEVEL . ") as " . self::LEVEL . " FROM {$this->getCategoryFlatTable()}; ";
    }

    protected function cleanMainTable()
    {
        $this->helper->log(__("Clean nostress_koongo_cache_categorypath records for store #%1", $this->getStoreId()));
        $this->getConnection()->delete($this->getMainTable(), ['store_id = ?' => $this->getStoreId()]);
    }
}
