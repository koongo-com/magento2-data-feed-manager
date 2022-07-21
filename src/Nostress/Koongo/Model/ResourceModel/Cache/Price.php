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

class Price extends \Nostress\Koongo\Model\ResourceModel\Cache\Product
{
    const MINIMAL_PRICE_CONDITION_SBST_STRING = "{{koongo_minimal_price_condition}}";
    const PRICE_CONDITION_SBST_STRING = "{{koongo_price_condition}}";
    const CPETP_PERCENTAGE_COLUMN = 'percentage_value';

    protected $_cacheName = 'Price';
    protected $_mainTableAlias = self::NKCPR;

    /**
     * Cutromer group id for price cache
     * @var unknown_type
     */
    protected $_customerGroupId = self::DEF_CUSTOMER_GROUP_NOT_LOGGED_IN;

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
        $this->_init('nostress_koongo_cache_price', 'product_id');
    }

    protected function reloadTable()
    {
        $this->cleanMainTable();
        $this->insertRecords();
        $this->updateConfigurablePrices();
    }

    public function reloadTierPrices()
    {
        $this->updateTierPrices();
    }

    protected function cleanMainTable()
    {
        $this->helper->log(__("Clean nostress_koongo_cache_price records for store #%1 and customer group #%s", $this->getStoreId(), $this->getCustomerGroupId()));
        $this->getConnection()->delete($this->getMainTable(), [	'store_id = ?' => $this->getStoreId(),
                                                                'customer_group_id = ?' => $this->getCustomerGroupId()]);
    }

    /*
     * Insert records with columns min_price, price, qty
    */
    protected function insertRecords()
    {
        $sql = $this->getInsertRecordsSql();
        $this->runQuery($sql, $this->getMainTable(), "Insert records. Filled columns: product_id, store_id, customer_group_id, min_price, price.");
    }

    /**
     * Update prices for configurable products.
     * @return string
     */
    protected function updateConfigurablePrices()
    {
        $sql = $this->getConfigurablePriceSql();
        $this->runQuery($sql, $this->getMainTable(), "Update prices for configurable products.");
    }

    /**
     * Update tier prices for all products.
     * @return string
     */
    protected function updateTierPrices()
    {
        $sql = $this->getTierPricesSql();
        $this->runQuery($sql, $this->getMainTable(), "Update tier prices for all  products.");
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
        $mainTableAlias = $this->getProductFlatTable(true);
        $mainTable = $this->getProductFlatTable();

        $catRulePriceAlias = self::CRPP;
        $catRulePrice = $this->getTable('catalogrule_product_price');

        $catProdIdxPriceAlias = self::CPIP;
        $catProdIdxPrice = $this->getTable('catalog_product_index_price');

        $catProdEntTierPriceAlias = self::CPETP;
        $catProdEntTierPrice = $this->getTable('catalog_product_entity_tier_price');

        $defaultCatProdEntTierPriceAlias = self::CPETP . "_default";
        $defaultCatProdEntTierPrice = $this->getTable('catalog_product_entity_tier_price');

        $select = $this->getEmptySelect();
        $currentCustomerGroupId = $this->getCustomerGroupId();

        $customerGroupIsDefault = ($currentCustomerGroupId == self::DEF_CUSTOMER_GROUP_NOT_LOGGED_IN);

        $additionalMinPriceCondition = "";
        //If customer group is not default => export tier price first
        if (!$customerGroupIsDefault) {
            if ($this->_isTableColumnPresent($catProdEntTierPrice, self::CPETP_PERCENTAGE_COLUMN)) {
                $additionalMinPriceCondition = "
				WHEN {$catProdEntTierPriceAlias}.value IS NOT NULL && {$catProdEntTierPriceAlias}.value > 0 THEN {$catProdEntTierPriceAlias}.value
                WHEN {$catProdEntTierPriceAlias}.percentage_value IS NOT NULL THEN ROUND({$catProdEntTierPriceAlias}.percentage_value*0.01*{$catProdIdxPriceAlias}.price,2)";

                if ($this->getWebsiteId() != 0) {
                    $additionalMinPriceCondition .= "
				WHEN {$defaultCatProdEntTierPriceAlias}.value IS NOT NULL && {$defaultCatProdEntTierPriceAlias}.value > 0 THEN {$defaultCatProdEntTierPriceAlias}.value
                WHEN {$defaultCatProdEntTierPriceAlias}.percentage_value IS NOT NULL THEN ROUND({$defaultCatProdEntTierPriceAlias}.percentage_value*0.01*{$catProdIdxPriceAlias}.price,2)";
                }
            } else {
                $additionalMinPriceCondition = "
				WHEN {$catProdEntTierPriceAlias}.value IS NOT NULL && {$mainTableAlias}.type_id <> 'bundle' THEN {$catProdEntTierPriceAlias}.value
                WHEN {$catProdEntTierPriceAlias}.value IS NOT NULL && {$mainTableAlias}.type_id = 'bundle' THEN ROUND({$catProdEntTierPriceAlias}.value*0.01*{$catProdIdxPriceAlias}.min_price,2)";

                if ($this->getWebsiteId() != 0) {
                    $additionalMinPriceCondition .= "
                WHEN {$defaultCatProdEntTierPriceAlias}.value IS NOT NULL && {$mainTableAlias}.type_id <> 'bundle' THEN {$defaultCatProdEntTierPriceAlias}.value
                WHEN {$defaultCatProdEntTierPriceAlias}.value IS NOT NULL && {$mainTableAlias}.type_id = 'bundle' THEN ROUND({$defaultCatProdEntTierPriceAlias}.value*0.01*{$catProdIdxPriceAlias}.min_price,2)";
                }
            }
        }

        /* Select minimal price for each product*/
        $minPriceCondition = "CASE
		{$additionalMinPriceCondition}
		WHEN {$mainTableAlias}.type_id = 'bundle' && {$mainTableAlias}.special_price IS NOT NULL && {$mainTableAlias}.price IS NOT NULL THEN ROUND({$mainTableAlias}.special_price*0.01*{$mainTableAlias}.price,2)
		WHEN {$catRulePriceAlias}.rule_price IS NULL && {$catProdIdxPriceAlias}.min_price > 0 THEN {$catProdIdxPriceAlias}.min_price		
		WHEN {$catRulePriceAlias}.rule_price IS NOT NULL && {$catProdIdxPriceAlias}.min_price <= 0  THEN {$catRulePriceAlias}.rule_price
		WHEN {$catRulePriceAlias}.rule_price <= {$catProdIdxPriceAlias}.min_price THEN {$catRulePriceAlias}.rule_price
		WHEN {$catRulePriceAlias}.rule_price > {$catProdIdxPriceAlias}.min_price THEN {$catProdIdxPriceAlias}.min_price
		WHEN {$mainTableAlias}.price IS NULL && {$catProdIdxPriceAlias}.min_price > 0 THEN {$catProdIdxPriceAlias}.min_price
		ELSE {$mainTableAlias}.price
		END ";
        /*Pokud se podminka pro minimal price vlozila primo do select->from tak script zkoncil bez chybove hlasky - je nutne vlozit podminku az dodatecne*/

        /* Select tier price for each product - only if customer group price <> 0 */
        // $priceCondition = "CASE
        // WHEN {$catProdEntTierPriceAlias}.value IS NOT NULL && {$mainTableAlias}.type_id <> 'bundle' THEN {$catProdEntTierPriceAlias}.value
        // WHEN {$catProdEntTierPriceAlias}.value IS NOT NULL && {$mainTableAlias}.type_id = 'bundle' THEN ROUND({$catProdEntTierPriceAlias}.value*0.01*{$catProdIdxPriceAlias}.max_price,2)
        // WHEN {$mainTableAlias}.price IS NOT NULL THEN {$mainTableAlias}.price
        // ELSE {$catProdIdxPriceAlias}.max_price
        // END ";

        //Load price or max price even if customer group is different
        $priceColumn = "IFNULL({$mainTableAlias}.price, {$catProdIdxPriceAlias}.max_price)";
        //If customer group is not default => try to export tier price (otherwise export standard price)
        // if(!$customerGroupIsDefault)
        // 	$priceColumn = "(".self::PRICE_CONDITION_SBST_STRING.")";

        $columns = [ "product_id" => $mainTableAlias . ".entity_id",
                        "store_id" => "({$this->getStoreId()})",
                        "customer_group_id" => "({$currentCustomerGroupId})",
                        "min_price" => "(" . self::MINIMAL_PRICE_CONDITION_SBST_STRING . ")",
                        "price"	=> $priceColumn,
                        ];

        $select->from([$mainTableAlias => $mainTable], $columns);

        $select->joinLeft(
            [$catRulePriceAlias => $catRulePrice],
            "{$mainTableAlias}.entity_id = {$catRulePriceAlias}.product_id AND {$catRulePriceAlias}.rule_date = CURDATE() AND {$catRulePriceAlias}.website_id = {$this->getWebsiteId()} AND {$catRulePriceAlias}.customer_group_id = " . $currentCustomerGroupId,
            null
        );

        $select->joinLeft(
            [$catProdIdxPriceAlias => $catProdIdxPrice],
            "{$mainTableAlias}.entity_id = {$catProdIdxPriceAlias}.entity_id AND {$catProdIdxPriceAlias}.website_id = {$this->getWebsiteId()} AND {$catProdIdxPriceAlias}.customer_group_id = " . $currentCustomerGroupId,
            null
        );

        if (!$customerGroupIsDefault) {
            //Load tier price only if qty = 1 for selected website
            $select->joinLeft(
                [$catProdEntTierPriceAlias => $catProdEntTierPrice],
                "{$mainTableAlias}.entity_id = {$catProdEntTierPriceAlias}.entity_id  AND {$catProdEntTierPriceAlias}.website_id = {$this->getWebsiteId()} " .
                "AND {$catProdEntTierPriceAlias}.customer_group_id = {$currentCustomerGroupId} " .
                "AND {$catProdEntTierPriceAlias}.qty = 1",
                null
            );

            //Load tier price only if qty = 1 for default website
            if ($this->getWebsiteId() != 0) {
                $select->joinLeft(
                    [$defaultCatProdEntTierPriceAlias => $defaultCatProdEntTierPrice],
                    "{$mainTableAlias}.entity_id = {$defaultCatProdEntTierPriceAlias}.entity_id  AND {$defaultCatProdEntTierPriceAlias}.website_id = 0 " .
                    "AND {$defaultCatProdEntTierPriceAlias}.customer_group_id = {$currentCustomerGroupId} " .
                    "AND {$defaultCatProdEntTierPriceAlias}.qty = 1",
                    null
                );
            }
        }

        $sql = $this->getConnection()->insertFromSelect($select, $this->getMainTable(), array_keys($columns));
        $sql = str_replace(self::MINIMAL_PRICE_CONDITION_SBST_STRING, $minPriceCondition, $sql);
        // if(!$customerGroupIsDefault)
        // 	$sql = str_replace(self::PRICE_CONDITION_SBST_STRING, $priceCondition,$sql);
        // echo $sql; exit();
        return $sql;
    }

    /**
     * Returns updates query for update price for configurable products.
     * If config product price is 0 than lowest price from child products is taken.
     * @return string
     */
    protected function getConfigurablePriceSql()
    {
        $cpfTable = $this->getProductFlatTable();
        $cpfTableAlias = $this->getProductFlatTable(true);

        $cprTable = $this->getTable("catalog_product_relation");
        $cprTableAlias = self::CPR;

        $parentIdColumn = $cprTableAlias . ".parent_id";
        if ($this->isContentStagingAvailable()) {
            $pcpeTableAlias = self::PCPE;
            $pcpeTable = $this->getTable('catalog_product_entity');
            $parentIdColumn = $pcpeTableAlias . ".entity_id";
        }

        $select = $this->getEmptySelect();
        $select->from(
            [$this->_mainTableAlias => $this->getMainTable()],
            [ "product_id" => $parentIdColumn,
                        "lowest_child_min_price" => "(MIN({$this->_mainTableAlias}.min_price))",
                        "lowest_child_price" => "(MIN({$this->_mainTableAlias}.price))"]
        );
        $select->join(
            [$cprTableAlias => $cprTable],
            "{$cprTableAlias}.child_id = {$this->_mainTableAlias}.product_id AND {$cprTableAlias}.parent_id IS NOT NULL",
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

        $select->where($this->_mainTableAlias . '.store_id=?', $this->getStoreId());
        $select->where($this->_mainTableAlias . '.customer_group_id=?', $this->getCustomerGroupId());
        $select->group($cprTableAlias . '.parent_id');

        $selectTable =  $this->getSubSelectTable($select);
        $selectTableAlias = 'lowest_price_table';

        $updateSql = "UPDATE {$this->getMainTable()} AS {$this->_mainTableAlias} ";
        $updateSql .= "INNER JOIN {$cpfTable} AS {$cpfTableAlias} ON {$cpfTableAlias}.entity_id = {$this->_mainTableAlias}.product_id AND {$cpfTableAlias}.type_id = 'configurable'";
        $updateSql .= "INNER JOIN {$selectTable} ";
        $updateSql .= "AS {$selectTableAlias} ON {$this->_mainTableAlias}.product_id = {$selectTableAlias}.product_id ";
        $updateSql .= "SET  {$this->_mainTableAlias}.min_price = {$selectTableAlias}.lowest_child_min_price, {$this->_mainTableAlias}.price = {$selectTableAlias}.lowest_child_price ";
        $updateSql .= "WHERE {$this->_mainTableAlias}.store_id = {$this->getStoreId()} ";
        $updateSql .= "AND {$this->_mainTableAlias}.customer_group_id = {$this->getCustomerGroupId()};";
        return $updateSql;
    }

    protected function getTierPricesSql()
    {
        $select = $this->getEmptySelect();

        $tierPriceCacheTable = $this->getTable('nostress_koongo_cache_pricetier');
        $tierPriceCacheTableAlias = self::NKCPRT;

        $select->from([$tierPriceCacheTableAlias =>  $tierPriceCacheTable], ["product_id","store_id","customer_group_id"]);
        $select->columns($this->helper->groupConcatColumns($this->getCacheColumns("tier_price")));
        $select->group("product_id");
        $select->where("store_id = ?", $this->getStoreId());
        $select->where("customer_group_id = ?", $this->getCustomerGroupId());

        //Prepare update query
        $mainTable = $this->getMainTable();
        $mainTableAlias = self::NKCPR;

        $updateSql = "UPDATE  {$mainTable} AS {$mainTableAlias} ";
        $updateSql .= "INNER JOIN ( {$select->__toString()} ) ";
        $updateSql .= "AS tier_prices ON {$mainTableAlias}.product_id = tier_prices.product_id AND  {$mainTableAlias}.store_id = tier_prices.store_id AND  {$mainTableAlias}.customer_group_id = tier_prices.customer_group_id ";
        $updateSql .= "SET  {$mainTableAlias}.tier_prices =  tier_prices.concat_colum";
        return $updateSql;
    }
}
