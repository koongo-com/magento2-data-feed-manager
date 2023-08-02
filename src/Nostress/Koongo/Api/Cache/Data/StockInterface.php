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
* Interface for stock
*
* @category Nostress
* @package Nostress_Koongo
*
*/

namespace Nostress\Koongo\Api\Cache\Data;

interface StockInterface
{
    /**
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const PRODUCT_ID = 'product_id';
    const SKU = 'sku';
    const STORE_ID = 'store_id';
    const TYPE_ID = 'type_id';
    const QTY = 'qty';
    const SALABLE_QTY = 'salable_qty';
    const STOCK_STATUS = 'stock_status';
    const QTY_DECIMAL = 'qty_decimal';

    /**
     * Gets the ID for the Product.
     *
     * @return int|null Product ID.
     */
    public function getProductId();

    /**
     * Gets the Sku for the Product.
     *
     * @return string|null Sku.
     */
    public function getSku();

    /**
     * Gets the Store id for the stock record.
     *
     * @return int|null Store id.
     */
    public function getStoreId();

    /**
     * Gets the type id for the stock record.
     *
     * @return string|null Type id.
     */
    public function getTypeId();

    /**
     * Gets the Qty for the stock record.
     *
     * @return int Qty
     */
    public function getQty();

    /**
     * Gets the Salable Qty for the stock record.
     *
     * @return int Salable Qty
     */
    public function getSalableQty();

    /**
     * Gets the Salable Qty for the stock record.
     *
     * @return float Salable Qty
     */
    public function getQtyDecimal();

    /**
     * Gets the Stock Status for the stock record.
     *
     * @return int Stock status.
     */
    public function getStockStatus();

    /**
     * Sets product ID.
     *
     * @param int Product Id
     * @return $this
     */
    public function setProductId($productId);

    /**
     * Sets the Sku for the Product.
     *
     * @return string|null Sku.
     */
    public function setSku($sku);

    /**
     * Sets the Store id for the stock record.
     *
     * @return $this
     */
    public function setStoreId($storeId);

    /**
     * Sets the Type id for the stock record.
     * @param string Type Id
     * @return $this
     */
    public function setTypeId($typeId);

    /**
     * Sets the qty for the stock record.
     *
     * @return $this
     */
    public function setQty($qty);

    /**
     * Sets the salable qty for the stock record.
     *
     * @return $this
     */
    public function setSalableQty($salableQty);

    /**
     * Sets the Stock Status for the stock record.
     *
     * @return $this
     */
    public function setStockStatus($stockStatus);

    /**
     * Gets the Salable Qty for the stock record.
     *
     * @return int Salable Qty
     */
    public function setQtyDecimal();
}
