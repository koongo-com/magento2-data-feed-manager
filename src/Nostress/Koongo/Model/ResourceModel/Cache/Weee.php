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
 * ResourceModel for Koongo Connector weee cache table
 *
 * @category Nostress
 * @package Nostress_Koongo
 *
 */

namespace Nostress\Koongo\Model\ResourceModel\Cache;

class Weee extends \Nostress\Koongo\Model\ResourceModel\Cache
{
    const DISCOUNT_TABLE_ALIAS_SUFFIX = "_discount";

    protected $_cacheName = 'Weee';
    protected $_commonColumns = ["product_id","website_id",self::WEEE_COLUMN_TOTAL];

    public function _construct()
    {
        $this->_init('nostress_koongo_cache_weee', 'product_id');
    }

    public function reloadWebsite($websiteId, $storeId)
    {
        $this->_websiteId = $websiteId;
        $this->_storeId = $storeId;
        $this->logStatus(self::STARTED, $this->_websiteId);
        $this->init();
        $this->reloadTable();
        $this->logStatus(self::FINISHED, $this->_websiteId);
    }

    protected function logStatus($status, $websiteId)
    {
        $this->helper->log(__("{$this->_cacheName} cache reload has %1 for website %2", $status, $websiteId));
    }

    protected function reloadTable()
    {
        //Weee tax enabled
        if (!$this->isWeeeEnabled()) {
            return;
        }

        $this->cleanMainTable();
        $this->updateTableStructure();
        $this->updateTable();
        $this->updateWeeeTotal();
    }

    /**
     * Returns sql which:
     * Clear table rows by website.
     * Add and drop columns.
     */
    protected function updateTableStructure()
    {
        $querys = $this->getUpdateTableColumnsSql();
        $this->helper->log($this->getMainTable() . " " . __("Update column structure"));
        foreach ($querys as $sql) {
            $this->runQuery($sql, $this->getMainTable(), __("Add/Delete column"), false);
        }
    }

    protected function updateTable()
    {
        $sql = $this->getUpdateTableSql();
        $this->runQuery($sql, $this->getMainTable(), __("Insert records for website #%1", $this->getWebsiteId()));
    }

    /**
     * Returns select insert command for table rows update.
     */
    protected function getUpdateTableSql()
    {
        $select = $this->getSelectWeeeSql();

        $columns = array_merge($this->_commonColumns, $this->convertWeeeAttributesToColumnNames());
        $sql = $this->getConnection()->insertFromSelect($select, $this->getMainTable(), $columns);
        return $sql;
    }

    protected function updateWeeeTotal()
    {
        $sql = $this->getUpdateWeeeTotalSql();
        $this->runQuery($sql, $this->getMainTable(), __("Update weee total for store #%1", $this->getStoreId()));
    }

    protected function getUpdateWeeeTotalSql()
    {
        $columns =  $this->getTableColumnsByString();
        if (empty($columns)) {
            return "";
        }

        $columns = implode(" + ", $columns);

        return "UPDATE {$this->getMainTable()} SET " . self::WEEE_COLUMN_TOTAL . " = ({$columns}) WHERE website_id = '{$this->getWebsiteId()}';";
    }

    protected function getSelectWeeeSql()
    {
        $mainTable = $this->getTable('weee_tax');
        $mainTableAlias = self::WT;

        $select = $this->getEmptySelect();
        $select->from([$mainTableAlias => $mainTable], ['product_id' => 'entity_id','website_id' => "({$this->getWebsiteId()})",self::WEEE_COLUMN_TOTAL => "(0.00)"]);
        $select->group("{$mainTableAlias}.entity_id");

        $weeeAttributes = $this->getWeeeAttributes();
        foreach ($weeeAttributes as $id => $code) {
            $select = $this->joinWeeeAttribute($select, $code, $id);
        }
        return $select;
    }

    protected function joinWeeeAttribute($select, $attributeCode, $attributeId)
    {
        $defaultCountry = $this->helper->getDefaultTaxCountry($this->getStoreId());
        $defaultState = $this->helper->getDefaultTaxRegion($this->getStoreId());

        $joinTableAlias = self::WT . "_{$attributeCode}";
        $select = $this->_joinWeeeAttribute($select, $attributeId, $joinTableAlias, $this->getWebsiteId(), $defaultCountry, $defaultState);

        $joinTableAliasDefWebsite = $joinTableAlias . "_defWebsite";
        $select = $this->_joinWeeeAttribute($select, $attributeId, $joinTableAliasDefWebsite, '0', $defaultCountry, $defaultState);

        $joinTableAliasDefState = $joinTableAlias . "_defState";
        $select = $this->_joinWeeeAttribute($select, $attributeId, $joinTableAliasDefState, $this->getWebsiteId(), $defaultCountry, '*');

        $joinTableAliasDefWebsiteDefState = $joinTableAliasDefWebsite . "_defState";
        $select = $this->_joinWeeeAttribute($select, $attributeId, $joinTableAliasDefWebsiteDefState, '0', $defaultCountry, '*');

        $value = "(IFNULL({$joinTableAlias}.value,(IFNULL({$joinTableAliasDefWebsite}.value,IFNULL({$joinTableAliasDefState}.value,IFNULL({$joinTableAliasDefWebsiteDefState}.value,'0'))))))";
        $select->columns([$attributeCode => $value]);

        return $select;
    }

    protected function _joinWeeeAttribute($select, $attributeId, $joinTableAlias, $websiteId, $country, $state)
    {
        if ($state == '0') {
            $state = "*";
        }

        $mainTableAlias = self::WT;
        $joinTable = $this->getTable('weee_tax');
        $condition = "{$joinTableAlias}.entity_id = {$mainTableAlias}.entity_id AND {$joinTableAlias}.website_id = '{$websiteId}' ";
        $condition .= "AND {$joinTableAlias}.country = '{$country}' AND {$joinTableAlias}.state = '{$state}' AND {$joinTableAlias}.attribute_id = {$attributeId}";
        $select->joinLeft(
            [$joinTableAlias => $joinTable],
            $condition,
            null
        );

        return $select;
    }

    protected function getUpdateTableColumnsSql()
    {
        $tableColumns = $this->getAllTableColumns(true);
        $weeeAttributes = $this->convertWeeeAttributesToColumnNames();

        //cmp attributes and update columns
        $columnsToDelete = array_diff($tableColumns, $weeeAttributes);
        $columnsToAdd = array_diff($weeeAttributes, $tableColumns);
        $sql = [];
        foreach ($columnsToDelete as $column) {
            $sql[] = $this->getDropColumnSql($column);
        }

        foreach ($columnsToAdd as $column) {
            $sql[] = $this->getAddColumnSql($column);
        }
        return $sql;
    }

    protected function cleanMainTable()
    {
        $this->helper->log(__("Clean nostress_koongo_cache_wee records for website #%1", $this->getWebsiteId()));
        $this->getConnection()->delete($this->getMainTable(), ['website_id = ?' => $this->getWebsiteId()]);
    }

    protected function getAddColumnSql($column)
    {
        return "ALTER TABLE {$this->getMainTable()} ADD COLUMN `{$column}` decimal(12,4) default '0.00' AFTER `" . self::WEEE_COLUMN_TOTAL . "`; ";
    }

    protected function getDropColumnSql($column)
    {
        return "ALTER TABLE {$this->getMainTable()} DROP `{$column}`; ";
    }

    protected function getAllTableColumns($removeCommonColumns = false)
    {
        $describe = $this->getConnection()->describeTable($this->getMainTable());
        $columns = array_keys($describe);
        if ($removeCommonColumns) {
            $columns = array_diff($columns, $this->_commonColumns);
        }
        return $columns;
    }

    protected function getTableColumnsByString($containsString = "")
    {
        $columns = $this->getAllTableColumns(true);
        if (empty($columns)) {
            return [];
        }
        if (empty($containsString)) {
            return array_values($this->getWeeeAttributes());
        }

        $resultColumns = [];
        foreach ($columns as $column) {
            if (strpos($column, $containsString) !== false) {
                $resultColumns[] = $column;
            }
        }
        return $resultColumns;
    }
}
