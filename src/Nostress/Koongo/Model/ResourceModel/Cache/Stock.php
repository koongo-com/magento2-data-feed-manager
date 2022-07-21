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
 * ResourceModel for Koongo Connector stock cache table
 *
 * @category Nostress
 * @package Nostress_Koongo
 *
 */

namespace Nostress\Koongo\Model\ResourceModel\Cache;

use Magento\Bundle\Api\ProductLinkManagementInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\App\ObjectManager;

class Stock extends \Nostress\Koongo\Model\ResourceModel\Cache\Product
{
    protected $_cacheName = 'Stock';
    protected $_mainTableAlias = self::NKCS;

    /**
     * Initialize resource model
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('nostress_koongo_cache_stock', 'product_id');
    }

    public function reloadItem($storeId, $productId): void
    {
        $this->logStatus(self::STARTED, $storeId . " Product " . $productId);
        $this->setStoreId($storeId);
        $this->init();
        $this->reloadProduct($productId);
        $this->logStatus(self::FINISHED, $storeId . " Product " . $productId);
    }

    protected function reloadTable(): void
    {
        $this->cleanMainTable();
        $this->insertRecords();
        $this->updateConfigurableQty();
        $this->updateBundleQty();
        if ($this->inventoryReservationsTableExists()) {
            $this->updateSalableQty();
        }
    }

    protected function reloadProduct($productId): void
    {
        $productIds = [$productId];
        $productSkus = [];
        if (isset($productId)) {
            $objectManager = ObjectManager::getInstance();
            $product = $objectManager->create(Product::class)->load($productId);
            $productSkus[] = $product->getSku();
            if ($product->getTypeId() === 'configurable') {
                $childProducts = $product->getTypeInstance()->getUsedProducts($product);
                if (!empty($childProducts)) {
                    foreach ($childProducts as $relatedProduct) {
                        $productIds[] = $relatedProduct->getId();
                        $productSkus[] = $relatedProduct->getSku();
                    }
                }
            } elseif ($product->getTypeId() === 'bundle') {
                $productLinkManagement = $objectManager->create(ProductLinkManagementInterface::class);
                $childProducts = $productLinkManagement->getChildren($product->getSku());
                if (!empty($childProducts)) {
                    foreach ($childProducts as $relatedProduct) {
                        $productIds[] = $relatedProduct->getEntityId();
                        $productSkus[] = $relatedProduct->getSku();
                    }
                }
            }
        }

        $this->cleanProductData($productIds);
        $this->insertRecords($productIds);

        $this->updateConfigurableQty($productId);
        $this->updateBundleQty($productId);

        if ($this->inventoryReservationsTableExists()) {
            $this->updateSalableQty($productIds, $productSkus);
        }
    }

    protected function cleanMainTable(): void
    {
        $this->helper->log(__("Clean nostress_koongo_cache_stock records for store #%1", $this->getStoreId()));
        $this->getConnection()->delete($this->getMainTable(), ['store_id = ?' => $this->getStoreId()]);
    }

    protected function cleanProductData($productIds): void
    {
        $this->helper->log(__("Clean nostress_koongo_cache_stock records for store #%1 and products #%2", $this->getStoreId(), implode(",", $productIds)));
        $this->getConnection()->delete($this->getMainTable(), ['store_id = ?' => $this->getStoreId(), 'product_id IN (?)' => $productIds]);
    }

    /**
     * Check if reservations table exists
     *
     * @return boolean
     */
    protected function inventoryReservationsTableExists(): bool
    {
        $connection = $this->getConnection();
        $tableName = $connection->getTableName('inventory_reservation');
        return $connection->isTableExists($tableName);
    }

    /**
     * Insert records with columns qty
     */
    protected function insertRecords($productIds = null): void
    {
        $sql = $this->getInsertRecordsSql($productIds);
        $this->runQuery($sql, $this->getMainTable(), "Insert records. Filled columns: product_id, sku, store_id, type_id, qty, stock_status.");
    }

    /**
     * Update records with columns salable_qty
     */
    protected function updateSalableQty($productIds = null, $productSkus = null): void
    {
        $sql = $this->getSalableQtySql($productIds, $productSkus);
        $this->runQuery($sql, $this->getMainTable(), "Update records. Filled columns: salable_qty.");
    }

    /**
     * Update qty for configurable products only if stock_id = 1
     */
    protected function updateConfigurableQty($productId = null): void
    {
        $stockId = $this->getStockId();
        if ($stockId != self::DEFAULT_STOCK_ID) {
            return;
        }

        $sql = $this->getConfigurableQtySql($productId);
        $this->runQuery($sql, $this->getMainTable(), "Update qty for configurable products.");
    }

    /**
     * Update qty for bundle products only if stock_id = 1
     */
    protected function updateBundleQty($productId = null): void
    {
        $stockId = $this->getStockId();
        if ($stockId != self::DEFAULT_STOCK_ID) {
            return;
        }

        $sql = $this->getBundleQtySql($productId);
        $this->runQuery($sql, $this->getMainTable(), "Update qty for bundle products.");
    }

    /**
     * Insert records with columns min_price, price, qty
     */
    protected function getInsertRecordsSql($productIds = null)
    {
        $mainTableAlias = self::CPE;
        $mainTable = $this->getTable('catalog_product_entity');

        $select = $this->getEmptySelect();
        $columns = ["product_id" => $mainTableAlias . ".entity_id",
            "sku" => $mainTableAlias . ".sku",
            "store_id" => "({$this->getStoreId()})",
            "type_id" => $mainTableAlias . ".type_id"];

        $stockId = $this->getStockId();

        if ($stockId == self::DEFAULT_STOCK_ID) {
            $catInvStockStatusAlias = self::CISS;
            $catInvStockStatus = $this->getTable('cataloginventory_stock_status');

            $columns["qty"] = $this->helper->getRoundSql("{$catInvStockStatusAlias}.qty", 0);
            $columns["salable_qty"] = $columns["qty"];
            $columns["stock_status"] = "{$catInvStockStatusAlias}.stock_status";

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
            $columns["salable_qty"] = $columns["qty"];
            $columns["stock_status"] = "IFNULL({$inventoryStockAlias}.is_salable,0)";
            $select->from([$mainTableAlias => $mainTable], $columns);

            $select->joinLeft(
                [$inventoryStockAlias => $inventoryStock],
                "{$mainTableAlias}.sku = {$inventoryStockAlias}.sku",
                null
            );
        }

        if (!empty($productIds)) {
            $select->where("{$mainTableAlias}.entity_id IN (?)", $productIds);
        }
        //echo $select->__toString(); exit();
        $sql = $this->getConnection()->insertFromSelect($select, $this->getMainTable(), array_keys($columns));

        return $sql;
    }

    /**
     * Returns updates query for salable qty column at cache stock table
     * @return string
     */
    protected function getSalableQtySql($productIds = null, $productSkus = null): string
    {
        $stockId = $this->getStockId();

        $mainTableAlias = $this->_mainTableAlias;
        $mainTable = $this->getMainTable();

        $catInvStockItemTableAlias = self::CISI;
        $catInvStockItemTable = $this->getTable('cataloginventory_stock_item');

        $invResTableAlias = self::IR;
        $invResTable = $this->getTable('inventory_reservation');

        $select = $this->getEmptySelect();
        $select->from([$mainTableAlias => $mainTable], ["product_id" => "product_id", "store_id" => "({$this->getStoreId()})"]);

        // LEFT JOIN cataloginventory_stock_item AS csi ON nkcs.product_id = csi.product_id AND csi.stock_id = X
        $select->joinLeft(
            [$catInvStockItemTableAlias => $catInvStockItemTable],
            $catInvStockItemTableAlias . '.product_id=' . $mainTableAlias . '.product_id AND ' . $catInvStockItemTableAlias . '.stock_id=' . $stockId,
            null
        );

        // LEFT JOIN (SELECT sku,stock_id,SUM(quantity) as quantity_reserverd FROM `inventory_reservation` WHERE stock_id = 1 GROUP BY sku, stock_id) AS ir ON cpe.sku = ir.sku AND ir.stock_id = 1
        //Preapare sum of reservations
        $subSelect = $this->getEmptySelect();
        $subSelect->from([$invResTableAlias => $invResTable], ["sku" => "sku", "stock_id" => "stock_id", "qty_reserverd" => "(SUM(quantity))"]);
        $subSelect->where("stock_id = ?", $stockId);
        if (!empty($productSkus)) {
            $subSelect->where("sku IN (?)", $productSkus);
        }
        $subSelect->group(["sku", "stock_id"]);

        $invResSubselectTable = $this->getSubSelectTable($subSelect);
        $invResSubselectTableAlias = $invResTableAlias . "_sum";

        $select->joinLeft(
            [$invResSubselectTableAlias => $invResSubselectTable],
            $invResSubselectTableAlias . '.sku=' . $mainTableAlias . '.sku AND ' . $invResSubselectTableAlias . '.stock_id =' . $stockId,
            ["salable_qty" => "({$mainTableAlias}.qty - IFNULL({$catInvStockItemTableAlias}.min_qty,0) + IFNULL({$invResSubselectTableAlias}.qty_reserverd,0))"]
        );

        if (!empty($productIds)) {
            $select->where("{$mainTableAlias}.product_id IN (?)", $productIds);
        }
        // echo $select->__toString(); exit();

        //Prepare update query
        $mainTable = $this->getMainTable();
        $mainTableAlias = self::NKCP;

        $updateSql = "UPDATE  {$mainTable} AS {$mainTableAlias} ";
        $updateSql .= "INNER JOIN ( {$select->__toString()} ) ";
        $updateSql .= "AS salable_qty_table ON {$mainTableAlias}.product_id = salable_qty_table.product_id AND  {$mainTableAlias}.store_id = salable_qty_table.store_id ";
        $updateSql .= "SET  {$mainTableAlias}.salable_qty =  salable_qty_table.salable_qty";

        return $updateSql;
    }

    /**
     * Returns updates query for update qty for configurable products.
     * @return string
     */
    protected function getConfigurableQtySql($productId = null): string
    {
        $cissTable = $this->getTable("cataloginventory_stock_status");
        $cissTableAlias = self::CISS;

        $cprTable = $this->getTable("catalog_product_relation");
        $cprTableAlias = self::CPR;

        $pcpeTable = $this->getTable("catalog_product_entity");
        $pcpeTableAlias = self::PCPE;

        $select = $this->getEmptySelect();
        $select->from(
            [$cissTableAlias => $cissTable],
            [  "product_id" => $pcpeTableAlias . ".entity_id",
                                "store_id" => "({$this->getStoreId()})", "sum_qty" => "SUM({$cissTableAlias}.qty)"]
        );
        $select->join(
            [$cprTableAlias => $cprTable],
            "{$cprTableAlias}.child_id = {$cissTableAlias}.product_id AND {$cprTableAlias}.parent_id IS NOT NULL",
            null
        );

        if ($this->isContentStagingAvailable()) {
            $condition =  "{$pcpeTableAlias}.row_id = {$cprTableAlias}.parent_id ";
            $condition .= "AND {$pcpeTableAlias}.type_id = 'configurable' ";
            $condition .= "AND {$pcpeTableAlias}.created_in <= UNIX_TIMESTAMP() AND {$pcpeTableAlias}.updated_in > UNIX_TIMESTAMP() ";
        } else {
            $condition = "{$pcpeTableAlias}.entity_id = {$cprTableAlias}.parent_id AND {$pcpeTableAlias}.type_id = 'configurable'";
        }

        $select->join(
            [$pcpeTableAlias => $pcpeTable],
            $condition,
            null
        );
        $select->group($pcpeTableAlias . '.entity_id'); //== parent id
        if (isset($productId)) {
            $select->where("{$pcpeTableAlias}.entity_id = ?", $productId);
        }

        $selectTable = $this->getSubSelectTable($select);
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
    protected function getBundleQtySql($productId = null): string
    {
        $cpbsTable = $this->getTable("catalog_product_bundle_selection");
        $cpbsTableAlias = self::CPBS;

        $cpboTable = $this->getTable("catalog_product_bundle_option");
        $cpboTableAlias = self::CPBO;

        $requiredOptionsOnly = $this->getBundleOptionsRequiredOnly();
        $defaultOptionItemsOnly = $this->getBundleOptionsDefaultItmesOnly();

        //Prepare subselect for options - calculate minimal qty and if at least one item for each option is in stock (and has always_in_stock)
        $subSelect = $this->getEmptySelect();

        $qtyPerSelectionQuery = "IF({$cpbsTableAlias}.selection_qty > 1, {$this->_mainTableAlias}.qty DIV {$cpbsTableAlias}.selection_qty , {$this->_mainTableAlias}.qty)";
        $optionQqtyQuery = "(IF({$cpboTableAlias}.type IN ('select','radio'),SUM({$qtyPerSelectionQuery}),MIN({$qtyPerSelectionQuery})))";

        $optionInStockQuery = "(BIT_OR({$this->_mainTableAlias}.stock_status))";
        //$optionAlwaysInStock = "(BIT_OR({$this->_mainTableAlias}.always_in_stock))";
        if ($defaultOptionItemsOnly) {
            $optionInStockQuery = "(BIT_AND({$this->_mainTableAlias}.stock_status))";
            //$optionAlwaysInStock = "(BIT_AND({$this->_mainTableAlias}.always_in_stock))";
        }

        $subSelect->from(
            [$this->_mainTableAlias => $this->getMainTable()],
            ["product_id" => $this->_mainTableAlias . ".product_id",
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

        if (isset($productId)) {
            $subSelect->where("{$cpbsTableAlias}.parent_product_id = ?", $productId);
        }
        $subSelect->group($cpbsTableAlias . '.option_id');

        $optionsSubselectTable = $this->getSubSelectTable($subSelect);
        $optionsSubselectTableAlias = 'bundle_options';

        //Summarize options prices to get total minimal and standard price for bundle item
        $select = $this->getEmptySelect();
        $columns = ["product_id" => $optionsSubselectTableAlias . ".parent_product_id",
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
