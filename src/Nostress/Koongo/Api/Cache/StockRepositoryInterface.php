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
 * Interface for Koongo API stock repository
 *
 * @category Nostress
 * @package Nostress_Koongo
 * @api
 */

namespace Nostress\Koongo\Api\Cache;

interface StockRepositoryInterface
{
    /**
     * Reload stock data for given store
     *
     * @param int $storeId
     * @return string Json config.
     */
    public function getReload($storeId);

    /**
     * Get stock data list
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Nostress\Koongo\Api\Cache\Data\StockSearchResultsInterface
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

    /**
     * Reload stock data for given store and product and return the item data
     *
     * @param int $storeId
     * @param int $productId
     * @return string Json config.
     */
    public function getProductStock($storeId, $productId);
}
