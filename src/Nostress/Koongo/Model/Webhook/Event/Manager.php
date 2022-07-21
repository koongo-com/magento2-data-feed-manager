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
 * Webhook event manager model for Koongo api
 *
 * @category Nostress
 * @package Nostress_Koongo
 *
 */

namespace Nostress\Koongo\Model\Webhook\Event;

class Manager extends \Nostress\Koongo\Model\AbstractModel
{
    /**
     * Webhook event factory
     *
     * @var \Nostress\Koongo\Model\Webhook\EventFactory
     */
    protected $_eventFactory;

    /**
     * Webhook factory
     *
     * @var \Nostress\Koongo\Model\WebhookFactory
     */
    protected $_webhookFactory;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Nostress\Koongo\Helper\Data $helper
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Nostress\Koongo\Model\Translation $translation
     * @param \Nostress\Koongo\Model\Webhook\EventFactory $eventFactory
     * @param \Nostress\Koongo\Model\WebhookFactory $webhookFactory
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Nostress\Koongo\Helper\Data $helper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Nostress\Koongo\Model\Translation $translation,
        \Nostress\Koongo\Model\Webhook\EventFactory $eventFactory,
        \Nostress\Koongo\Model\WebhookFactory $webhookFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->_eventFactory = $eventFactory;
        $this->_webhookFactory = $webhookFactory;
        parent::__construct($context, $registry, $helper, $storeManager, $translation, $resource, $resourceCollection, $data);
    }

    /**
     * Add cancel order webhook events
     *
     * @param Mage_Sales_Model_Order $order
     * @param String $cancelReason
     * @param boolean $invoiceExists
     * @return Array $eventIds
     */
    public function addCancelOrderEvents($order, $cancelReason = "")
    {
        $topic = \Nostress\Koongo\Model\Webhook::WEBHOOK_TOPIC_CANCEL_ORDER;
        return $this->addWebhookEvents($topic, null, $order->getId(), $order->getStoreId(), [\Nostress\Koongo\Model\Webhook\Event::PARAM_NOTE => $cancelReason]);
    }

    /**
     * Add shipment webhook events
     *
     * @param \Magento\Sales\Model\Order\Shipment $shipment
     * @param \Magento\Sales\Model\Order\Shipment\Track $track
     * @return Array $eventIds
     */
    public function addShipmentEvents($shipment, $track)
    {
        $topic = \Nostress\Koongo\Model\Webhook::WEBHOOK_TOPIC_ADD_SHIPMENT;
        $params = [\Nostress\Koongo\Model\Webhook\Event::PARAM_STORE_SHIPMENT_ID => $shipment->getId(),
                        \Nostress\Koongo\Model\Webhook\Event::PARAM_STORE_TRACK_ID => $track->getId()];
        return $this->addWebhookEvents($topic, null, $shipment->getOrderId(), $shipment->getStoreId(), $params);
    }

    /**
     * Add creditmemo webhook events
     *
     * @param \Magento\Sales\Model\Order\Creditmemo $creditmemo
     * @return Array $eventIds
     */
    public function addCreditmemoEvents($creditmemo)
    {
        $topic = \Nostress\Koongo\Model\Webhook::WEBHOOK_TOPIC_ADD_CREDITMEMO;
        $params = [\Nostress\Koongo\Model\Webhook\Event::PARAM_STORE_CREDITMEMO_ID => $creditmemo->getId()
                        ];
        return $this->addWebhookEvents($topic, null, $creditmemo->getOrderId(), $creditmemo->getStoreId(), $params);
    }

    /**
     * Add new product webhook events
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return Array $eventIds
     */
    public function addNewProductEvents($product)
    {
        $topic = \Nostress\Koongo\Model\Webhook::WEBHOOK_TOPIC_PRODUCTS_CREATE;
        $params = [\Nostress\Koongo\Model\Webhook\Event::PARAM_STORE_PRODUCT_ID => $product->getId(),
                        \Nostress\Koongo\Model\Webhook\Event::PARAM_STORE_PRODUCT_SKU => $product->getSku()];
        return $this->addWebhookEvents($topic, $product->getId(), null, null, $params);
    }

    /**
     * Add update product webhook events
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return Array $eventIds
     */
    public function addUpdateProductEvents($product)
    {
        $topic = \Nostress\Koongo\Model\Webhook::WEBHOOK_TOPIC_PRODUCTS_UPDATE;
        $params = [\Nostress\Koongo\Model\Webhook\Event::PARAM_STORE_PRODUCT_ID => $product->getId(),
                        \Nostress\Koongo\Model\Webhook\Event::PARAM_STORE_PRODUCT_SKU => $product->getSku()];
        return $this->addWebhookEvents($topic, $product->getId(), null, null, $params);
    }

    /**
     * Add delete product webhook events
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return Array $eventIds
     */
    public function addDeteleProductEvents($product)
    {
        $topic = \Nostress\Koongo\Model\Webhook::WEBHOOK_TOPIC_PRODUCTS_DELETE;
        $params = [\Nostress\Koongo\Model\Webhook\Event::PARAM_STORE_PRODUCT_ID => $product->getId()];
        return $this->addWebhookEvents($topic, $product->getId(), null, null, $params);
    }

    /**
     * Add batch product import event
     *
     * @param array  $productSkusToUpdate Array of product skus for update
     * @return Array $eventIds
     */
    public function addBatchProductEvents($productSkusToUpdate)
    {
        if (empty($productSkusToUpdate)) {
            $productSkusToUpdate = [];
        }

        $topic = \Nostress\Koongo\Model\Webhook::WEBHOOK_TOPIC_PRODUCTS_BATCH;
        $eventIdsResult = [];
        $offset = 0;
        $productsCountLimit = \Nostress\Koongo\Model\Webhook::WEBHOOK_PRODUCTS_BATCH_LIMIT;
        $skusForEvent = array_slice($productSkusToUpdate, $offset, $productsCountLimit);

        while (!empty($skusForEvent)) {
            $params = [\Nostress\Koongo\Model\Webhook\Event::PARAM_STORE_PRODUCT_SKUS => $skusForEvent];
            $eventIds = $this->addWebhookEvents($topic, null, null, null, $params);
            $eventIdsResult = array_merge($eventIdsResult, $eventIds);

            $offset += $productsCountLimit;
            $skusForEvent = array_slice($productSkusToUpdate, $offset, $productsCountLimit);
        }

        return $eventIdsResult;
    }

    /**
     * Add batch product delete event
     *
     * @param array $idsToDelete Array of product ids to delete
     * @return Array $eventIds
     */
    public function addBatchDeleteProductEvents($idsToDelete)
    {
        if (empty($idsToDelete)) {
            $idsToDelete = [];
        }

        $topic = \Nostress\Koongo\Model\Webhook::WEBHOOK_TOPIC_PRODUCTS_BATCH_DELETE;
        $eventIdsResult = [];

        $offset = 0;
        $productsCountLimit = \Nostress\Koongo\Model\Webhook::WEBHOOK_PRODUCTS_BATCH_LIMIT;
        $idsForEvent = array_slice($idsToDelete, $offset, $productsCountLimit);

        while (!empty($idsForEvent)) {
            $params = [\Nostress\Koongo\Model\Webhook\Event::PARAM_STORE_PRODUCT_IDS => $idsForEvent];
            $eventIds = $this->addWebhookEvents($topic, null, null, null, $params);
            $eventIdsResult = array_merge($eventIdsResult, $eventIds);

            $offset += $productsCountLimit;
            $idsForEvent = array_slice($idsToDelete, $offset, $productsCountLimit);
        }

        return $eventIdsResult;
    }

    /**
     * Add webhook events to table
     *
     * @param String $topic Webhook topic
     * @param Int $productId
     * @param Int $orderId
     * @param Int $storeId
     * @param Array $params
     * @return Array $eventIds
     */
    public function addWebhookEvents($topic, $productId, $orderId, $storeId, $params)
    {
        $webhooks = $this->_getWebhooks($storeId, $topic);

        $eventIds = [];
        foreach ($webhooks as $webhook) {
            $event = $this->_createWebhookEvent($webhook, $productId, $orderId, $params);
            $event->save();
            $eventIds[] = $event->getId();
        }
        return $eventIds;
    }

    /**
     * Duplicate event and adjsut parent event id and duplicity counter
     * Returns new event
     *
     * @param \Nostress\Koongo\Model\Webhook\Event $event
     * @return \Nostress\Koongo\Model\Webhook\Event
     */
    public function duplicateWebhookEvent($event)
    {
        $eventData = $event->getData();

        $newEvent = $this->_eventFactory->create();
        $newEvent->setData($eventData);
        $newEvent->setId(null);

        $parentEventId = $event->getParentEventId();
        if (empty($parentEventId)) {
            $parentEventId = $event->getId();
        }

        $newEvent->setParentEventId($parentEventId);
        $counter = $event->getDuplicityCounter();
        $counter++;
        $newEvent->setDuplicityCounter($counter);
        $newEvent->setStatus(\Nostress\Koongo\Model\Webhook\Event::STATUS_PENDING);
        $newEvent->setMessage("");
        $now = $this->helper->getNow();
        $newEvent->setCreatedAt($now);
        $newEvent->setupdatedAt($now);
        return $newEvent;
    }

    /**
     * Get webhooks by storeId and topic
     *
     * @param Int $storeId
     * @param String $topic
     * @return void
     */
    protected function _getWebhooks($storeId, $topic)
    {
        $webhooksCollection = $this->_webhookFactory->create()->getCollection();
        $select = $webhooksCollection->getSelect();
        if (isset($storeId)) {
            $select->where("store_id = ?", $storeId);
        }
        $select->where("topic = ?", $topic);

        return $webhooksCollection->load();
    }

    /**
     * Create event for webhook
     *
     * @param \Nostress\Koongo\Model\Webhook $webhook
     * @param Int $productId
     * @param Int $orderId
     * @param Array $params
     * @return void
     */
    protected function _createWebhookEvent($webhook, $productId, $orderId, $params)
    {
        $event = $this->_eventFactory->create();
        $event->setTopic($webhook->getTopic());
        $event->setWebhookId($webhook->getId());
        $event->setProductId($productId);
        $event->setOrderId($orderId);
        $event->setStatus(\Nostress\Koongo\Model\Webhook\Event::STATUS_PENDING);
        $event->setParams(json_encode($params));
        $now = $this->helper->getNow();
        $event->setCreatedAt($now);
        $event->setupdatedAt($now);
        return $event;
    }
}
