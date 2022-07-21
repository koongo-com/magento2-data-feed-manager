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
 * Interface for Koongo API webhooks repository
 *
 * @category Nostress
 * @package Nostress_Koongo
 * @api
 */

namespace Nostress\Koongo\Api;

interface WebhookRepositoryInterface
{
    /**
    * Create webhook
    *
    * @param \Nostress\Koongo\Api\Data\WebhookInterface $product
    * @return \Nostress\Koongo\Api\Data\WebhookInterface
    * @throws \Magento\Framework\Exception\InputException
    * @throws \Magento\Framework\Exception\StateException
    * @throws \Magento\Framework\Exception\CouldNotSaveException
    */
    public function save(\Nostress\Koongo\Api\Data\WebhookInterface $webhook);

    /**
     * Delete webhook
     *
     * @param \Nostress\Koongo\Api\Data\WebhookInterface $product
     * @return bool Will returned True if deleted
     * @throws \Magento\Framework\Exception\StateException
     */
    public function delete(\Nostress\Koongo\Api\Data\WebhookInterface $webhook);

    /**
     * @param string $id
     * @return bool Will returned True if deleted
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\StateException
     */
    public function deleteById($id);

    /**
     * Get webhook list
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Nostress\Koongo\Api\Data\WebhookSearchResultsInterface
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);
}
