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
 * Model for Koongo API webhooks
 *
 * @category Nostress
 * @package Nostress_Koongo
 *
 */

namespace Nostress\Koongo\Model;

use Nostress\Koongo\Api\Data\WebhookInterface;

class Webhook extends \Nostress\Koongo\Model\AbstractModel implements WebhookInterface
{
    const WEBHOOK_TOPIC_PRODUCTS_CREATE = "products.create";
    const WEBHOOK_TOPIC_PRODUCTS_UPDATE = "products.update";
    const WEBHOOK_TOPIC_PRODUCTS_DELETE = "products.delete";
    const WEBHOOK_TOPIC_PRODUCTS_BATCH = "products.batch";
    const WEBHOOK_TOPIC_PRODUCTS_BATCH_DELETE = "products.batch_delete";
    const WEBHOOK_TOPIC_INVENTORY_UPDATE = "invetory.update";
    const WEBHOOK_TOPIC_CANCEL_ORDER = "orders.canceled";
    const WEBHOOK_TOPIC_ADD_SHIPMENT = "shipments.create";
    const WEBHOOK_TOPIC_ADD_CREDITMEMO = "creditmemos.create";

    /**
     * Limit of producs that might be sent in one batch
     */
    const WEBHOOK_PRODUCTS_BATCH_LIMIT = 50;

    /**
     * Shipment model
     *
     * @var \Magento\Sales\Model\Order\Shipment
     */
    protected $_shipmentModel;

    /**
     * @var \Magento\Shipping\Model\Config
     */
    protected $_shippingConfig;

    public function _construct()
    {
        $this->_init('Nostress\Koongo\Model\ResourceModel\Webhook');
    }

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Nostress\Koongo\Helper\Data $helper
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Nostress\Koongo\Model\Translation $translation
     * @param \Magento\Sales\Model\Order\Shipment $shipmentModel
     * @param \Magento\Shipping\Model\Config $shippingConfig
     * @param \Magento\Framework\Filesystem\DriverInterface $driver ,
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
        \Magento\Sales\Model\Order\Shipment $shipmentModel,
        \Magento\Shipping\Model\Config $shippingConfig,
        \Magento\Framework\Filesystem\DriverInterface $driver,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->_shipmentModel = $shipmentModel;
        $this->_shippingConfig = $shippingConfig;
        parent::__construct($context, $registry, $helper, $storeManager, $translation, $driver, $resource, $resourceCollection, $data);
    }

    /**
     * Create payload for webhook request
     *
     * @param \Nostress\Koongoorderapi\Model\Webhook\Event $event
     * @return void
     */
    public function preparePayloadForEvent($event)
    {
        $topic = $event->getTopic();
        $payload = [];
        switch ($topic) {
            case self::WEBHOOK_TOPIC_PRODUCTS_BATCH:
            case self::WEBHOOK_TOPIC_PRODUCTS_BATCH_DELETE:
                $payload = $this->_preparePayloadDefault($event);
                $payload[\Nostress\Koongo\Model\Webhook\Event::PARAM_WEBHOOK_EVENT_ID] =  $event->getId();
                break;
            default:
                $payload = $this->_preparePayloadDefault($event);
                break;
        }
        return $payload;
    }

    /**
     * Prepare payload for event
     *
     * @param \Nostress\Koongoorderapi\Model\Webhook\Event $event
     * @return void
     */
    protected function _preparePayloadDefault($event)
    {
        $payload = json_decode($event->getParams(), true);
        if (!empty($event->getOrderId())) {
            $payload[\Nostress\Koongo\Model\Webhook\Event::PARAM_STORE_ORDER_ID] =  $event->getOrderId();
        }
        if (!empty($event->getProductId())) {
            $payload[\Nostress\Koongo\Model\Webhook\Event::PARAM_STORE_PRODUCT_ID] =  $event->getProductId();
        }
        return $payload;
    }

    /**
     * Gets the ID for the webhook.
     *
     * @return int|null Webhook ID.
     */
    public function getEntityId()
    {
        return $this->getData(WebhookInterface::ENTITY_ID);
    }

    /**
     * Gets the Topic for the webhook.
     *
     * @return string|null Webhook topic.
     */
    public function getTopic()
    {
        return $this->getData(WebhookInterface::TOPIC);
    }

    /**
     * Gets the Url for the webhook.
     *
     * @return string|null Webhook url.
     */
    public function getUrl()
    {
        return $this->getData(WebhookInterface::URL);
    }

    /**
     * Gets the Store id for the webhook.
     *
     * @return int|null Store id.
     */
    public function getStoreId()
    {
        return $this->getData(WebhookInterface::STORE_ID);
    }

    /**
     * Gets the secret key for the webhook.
     *
     * @return string|null Secret.
     */
    public function getSecret()
    {
        return $this->getData(WebhookInterface::SECRET);
    }

    /**
     * Gets the created at datetime for the webhook.
     *
     * @return string|null Date created at.
     */
    public function getCreatedAt()
    {
        return $this->getData(WebhookInterface::CREATED_AT);
    }

    /**
     * Gets the updated at datetime for the webhook.
     *
     * @return string|null Date updated at.
     */
    public function getUpdatedAt()
    {
        return $this->getData(WebhookInterface::UPDATED_AT);
    }

    /**
     * Sets entity ID.
     *
     * @param int $entityId
     * @return $this
     */
    public function setEntityId($entityId)
    {
        return $this->setData(WebhookInterface::ENTITY_ID, $entityId);
    }

    /**
     * Sets the Topic for the webhook.
     *
     * @return $this
     */
    public function setTopic($topic)
    {
        return $this->setData(WebhookInterface::TOPIC, $topic);
    }

    /**
     * Sets the Url for the webhook.
     *
     * @return $this
     */
    public function setUrl($url)
    {
        return $this->setData(WebhookInterface::URL, $url);
    }

    /**
     * Sets the Store id for the webhook.
     *
     * @return $this
     */
    public function setStoreId($storeId)
    {
        return $this->setData(WebhookInterface::STORE_ID, $storeId);
    }

    /**
     * Sets the secret key for the webhook.
     *
     * @return $this
     */
    public function setSecret($secret)
    {
        return $this->setData(WebhookInterface::SECRET, $secret);
    }

    /**
     * Sets the created at datetime for the webhook.
     *
     * @return $this
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(WebhookInterface::CREATED_AT, $createdAt);
    }

    /**
     * Sets the updated at datetime for the webhook.
     *
     * @return $this
     */
    public function setUpdatedAt($updatedAt)
    {
        return $this->setData(WebhookInterface::UPDATED_AT, $updatedAt);
    }
}
