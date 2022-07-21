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
 * ResourceModel for Koongo Connector price cache table
 *
 * @category Nostress
 * @package Nostress_Koongo
 *
 */

namespace Nostress\Koongo\Model\ResourceModel\Cache;

class Pricetier extends \Nostress\Koongo\Model\ResourceModel\Cache
{
    //Qty must be over defined value to load tier price
    const TIER_PRICE_QTY_THRESHOLD = 1;
    const CPETP_PERCENTAGE_COLUMN = 'percentage_value';

    protected $_cacheName = 'Pricetier';
    protected $_mainTableAlias = self::NKCPRT;

    /**
     * Cutromer group id for price cache
     * @var unknown_type
     */
    protected $_customerGroupId = self::DEF_CUSTOMER_GROUP_NOT_LOGGED_IN;

    /**
     * Initialize resource model
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('nostress_koongo_cache_pricetier', 'product_id');
    }

    protected function reloadTable()
    {
        $this->cleanMainTable();
        $this->insertRecords();
    }

    protected function cleanMainTable()
    {
        $this->helper->log(__("Clean nostress_koongo_cache_pricetierrecords for store #%1 and customer group #%s", $this->getStoreId(), $this->getCustomerGroupId()));
        $this->getConnection()->delete($this->getMainTable(), [	'store_id = ?' => $this->getStoreId(),
                                                                'customer_group_id = ?' => $this->getCustomerGroupId()]);
    }

    /*
     * Insert records with columns min_price, price, qty
    */
    protected function insertRecords()
    {
        $sql = $this->getInsertRecordsSql();
        $this->runQuery($sql, $this->getMainTable(), "Insert records. Filled columns: product_id, store_id, customer_group_id, unit_price, qty, discount_percent.");
    }

    public function setCustomerGroupId($customerGroupId)
    {
        if (isset($customerGroupId) && is_numeric($customerGroupId) && $customerGroupId >= 0) {
            $this->_customerGroupId = $customerGroupId;
        }
    }

    public function getCustomerGroupId()
    {
        return $this->_customerGroupId;
    }

    /************************************ Sql query builders ***************************************/

    /*
     * Insert records with columns min_price, price, qty
     */
    protected function getInsertRecordsSql()
    {
        $catalogProductEntityTable = $this->getTable("catalog_product_entity");
        $catalogProductEntityTableAlias = self::CPE;

        $catProdEntTierPriceAlias = self::CPETP;
        $catProdEntTierPrice = $this->getTable('catalog_product_entity_tier_price');

        $nscKngCachePriceTableAlias = self::NKCPR;
        $nscKngCachePriceTable = $this->getTable('nostress_koongo_cache_price');

        $select = $this->getEmptySelect();
        $currentCustomerGroupId = $this->getCustomerGroupId();

        $productIdColumn = $catProdEntTierPriceAlias . ".entity_id";
        if ($this->isContentStagingAvailable()) {
            $productIdColumn = $catalogProductEntityTableAlias . ".entity_id";
        }

        if ($this->_isTableColumnPresent($catProdEntTierPrice, self::CPETP_PERCENTAGE_COLUMN)) {
            $unitPriceColumn = "MIN( IF({$catProdEntTierPriceAlias}.value <= 0, ((100-{$catProdEntTierPriceAlias}.percentage_value)*0.01*{$nscKngCachePriceTableAlias}.price), {$catProdEntTierPriceAlias}.value))";
            $discountPercentColumn = "MAX(IFNULL({$catProdEntTierPriceAlias}.percentage_value,ROUND(({$nscKngCachePriceTableAlias}.price-{$catProdEntTierPriceAlias}.value)/({$nscKngCachePriceTableAlias}.price*0.01),0)))";
        } else {
            $unitPriceColumn = "MIN({$catProdEntTierPriceAlias}.value)";
            $discountPercentColumn = "MAX(ROUND(({$nscKngCachePriceTableAlias}.price-{$catProdEntTierPriceAlias}.value)/({$nscKngCachePriceTableAlias}.price*0.01)))";
        }

        $columns = [ "product_id" => $productIdColumn,
                        "store_id" => "({$this->getStoreId()})",
                        "customer_group_id" => "({$currentCustomerGroupId})",
                        "qty" => "ROUND({$catProdEntTierPriceAlias}.qty,0)",
                        "unit_price" => "({$unitPriceColumn})",
                        "discount_percent"	=> "({$discountPercentColumn})",
                        ];

        $select->from([$catProdEntTierPriceAlias => $catProdEntTierPrice], $columns);

        //Join entity table to select proper row versions if content staging is active
        if ($this->isContentStagingAvailable()) {
            $select->join(
                [$catalogProductEntityTableAlias => $catalogProductEntityTable],
                "{$catProdEntTierPriceAlias}.row_id = {$catalogProductEntityTableAlias}.row_id AND ({$catalogProductEntityTableAlias}.created_in <= UNIX_TIMESTAMP() AND {$catalogProductEntityTableAlias}.updated_in > UNIX_TIMESTAMP())",
                null
            );
        }

        $joinCondition = "{$nscKngCachePriceTableAlias}.product_id = {$productIdColumn}";
        $joinCondition .= " AND ({$nscKngCachePriceTableAlias}.customer_group_id = {$catProdEntTierPriceAlias}.customer_group_id OR {$catProdEntTierPriceAlias}.all_groups = 1)";
        $joinCondition .= " AND {$nscKngCachePriceTableAlias}.store_id = {$this->getStoreId()}";
        $select->join(
            [$nscKngCachePriceTableAlias => $nscKngCachePriceTable],
            $joinCondition,
            null
        );

        $select->where("{$nscKngCachePriceTableAlias}.customer_group_id=?", $this->getCustomerGroupId());
        $select->where("{$catProdEntTierPriceAlias}.qty>?", self::TIER_PRICE_QTY_THRESHOLD);

        $websiteIds = [0];
        if ($this->getWebsiteId() != 0) {
            $websiteIds[] = $this->getWebsiteId();
        }
        $select->where("{$catProdEntTierPriceAlias}.website_id IN (?) ", $websiteIds);

        $select->group($productIdColumn);
        $select->group("{$catProdEntTierPriceAlias}.qty");

        $sql = $this->getConnection()->insertFromSelect($select, $this->getMainTable(), array_keys($columns));

        //echo $sql; exit();
        return $sql;
    }
}
