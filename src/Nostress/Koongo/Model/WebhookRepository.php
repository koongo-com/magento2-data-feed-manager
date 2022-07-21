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
 * Model for Koongo API webhooks repository
 *
 * @category Nostress
 * @package Nostress_Koongo
 *
 */

namespace Nostress\Koongo\Model;

use Magento\Framework\Api\SortOrder;
use Nostress\Koongo\Api\WebhookRepositoryInterface;

class WebhookRepository implements WebhookRepositoryInterface
{
    /**
     * Webhook factory
     *
     * @var \Nostress\Koongo\Model\WebhookFactory
     */
    protected $_webhookFactory;

    /**
     * Webhook resource model
     *
     * @var \Nostress\Koongo\Model\ResourceModel\Webhook
     */
    protected $_resourceModel;

    /**
     * @var \Nostress\Koongo\Api\Data\WebhookSearchResultsInterfaceFactory
     */
    protected $_searchResultsFactory;

    /**
     * @param \Nostress\Koongo\Model\WebhookFactory $webhookFactory
     * @param \Nostress\Koongo\Model\ResourceModel\Webhook $resourceModel,
     */
    public function __construct(
        \Nostress\Koongo\Model\WebhookFactory $webhookFactory,
        \Nostress\Koongo\Model\ResourceModel\Webhook $resourceModel,
        \Nostress\Koongo\Api\Data\WebhookSearchResultsInterfaceFactory $searchResultsFactory
    ) {
        $this->_webhookFactory = $webhookFactory;
        $this->_resourceModel = $resourceModel;
        $this->_searchResultsFactory = $searchResultsFactory;
    }

    /**
     * Create webhook
     *
     * @param \Nostress\Koongo\Api\Data\WebhookInterface $product
     * @return \Nostress\Koongo\Api\Data\WebhookInterface
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\StateException
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function save(\Nostress\Koongo\Api\Data\WebhookInterface $webhook)
    {
        if ($this->_webhookExists($webhook->getStoreId(), $webhook->getTopic(), $webhook->getUrl())) {
            throw new \Exception(__("Webhook with given parameters already exists."));
        }

        try {
            $this->_resourceModel->save($webhook);
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\CouldNotSaveException(__('Unable to save webhook'));
        }
        return $webhook;
    }

    /**
     * Delete webhook
     *
     * @param \Nostress\Koongo\Api\Data\WebhookInterface $product
     * @return bool Will returned True if deleted
     * @throws \Magento\Framework\Exception\StateException
     */
    public function delete(\Nostress\Koongo\Api\Data\WebhookInterface $webhook)
    {
        try {
            $this->_resourceModel->delete($webhook);
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\StateException(
                __('Unable to remove webhook %1', $webhook->getEntityId())
            );
        }
        return true;
    }

    /**
     * @param string $id
     * @return bool Will returned True if deleted
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\StateException
     */
    public function deleteById($id)
    {
        $webhook = $this->_webhookFactory->create()->load($id);
        if (!$webhook->getEntityId()) {
            throw new \Magento\Framework\Exception\StateException(__('Unable to find webhook %1', $id));
        }

        try {
            $webhook->delete();
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\StateException(
                __('Unable to remove webhook %1', $id)
            );
        }
        return true;
    }

    /**
     * Get webhook list
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Nostress\Koongo\Api\Data\WebhookSearchResultsInterface
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria)
    {
        /** @var \Nostress\Koongo\Model\ResourceModel\Webhook\Collection $collection */
        $collection = $this->_webhookFactory->create()->getCollection();

        /** @var SortOrder $sortOrder */
        foreach ((array)$searchCriteria->getSortOrders() as $sortOrder) {
            $field = $sortOrder->getField();
            $collection->addOrder(
                $field,
                ($sortOrder->getDirection() == SortOrder::SORT_ASC) ? 'ASC' : 'DESC'
            );
        }
        $collection->setCurPage($searchCriteria->getCurrentPage());
        $collection->setPageSize($searchCriteria->getPageSize());
        $collection->load();

        $searchResult = $this->_searchResultsFactory->create();
        $searchResult->setSearchCriteria($searchCriteria);
        $searchResult->setItems($collection->getItems());
        $searchResult->setTotalCount($collection->getSize());
        return $searchResult;
    }

    /**
     * Check if webhook item already exists
     *
     * @param int $storeId
     * @param string $topic
     * @param string $url
     * @return Boolean
     */
    protected function _webhookExists($storeId, $topic, $url)
    {
        $collection = $this->_webhookFactory->create()->getCollection();
        $select = $collection->getSelect();
        $select->where('store_id = ?', $storeId)
                ->where('topic = ?', $topic)
                ->where('url = ?', $url);
        $collection->load();

        $firstItem = $collection->getFirstItem();
        if (!$firstItem->getId()) {
            return false;
        }
        return true;
    }
}
