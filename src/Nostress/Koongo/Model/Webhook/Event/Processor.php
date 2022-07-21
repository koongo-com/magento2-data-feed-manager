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
 * Webhook event Processor model for Koongo api
 *
 * @category Nostress
 * @package Nostress_Koongo
 *
 */

namespace Nostress\Koongo\Model\Webhook\Event;

class Processor extends \Nostress\Koongo\Model\AbstractModel
{
    /** Check events in processing state in given period */
    const EVENT_PROCESSING_CHECK_PERIOD = "-1 hours";
    /** Check and remove events older than diven number of days */
    const EVENT_REMOVAL_CHECK_PERIOD = "-14 days";
    /** Maximum number of event renews */
    const EVENT_RENEWAL_COUNTER_LIMIT = 5;
    /** Maximum amount of events processed in one run.  */
    const EVENT_PROCESSING_MAX_AMOUNT = 5000;

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
     * Kaas api client
     *
     * @var \Nostress\Koongo\Model\Api\Restclient\Kaas
     */
    protected $_apiClient;

    /**
     * Webhook event manager
     *
     * @var \Nostress\Koongo\Model\Webhook\Event\Manager
     */
    protected $_webhookEventManager;

    /**
     * Sales order factory
     *
     *  @var \Magento\Sales\Model\OrderFactory
     */
    protected $_orderFactory;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Nostress\Koongo\Helper\Data $helper
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Nostress\Koongo\Model\Translation $translation
     * @param \Nostress\Koongo\Model\Webhook\EventFactory $eventFactory
     * @param \Nostress\Koongo\Model\WebhookFactory $webhookFactory
     * @param \Nostress\Koongo\Model\Api\Restclient\Kaas $apiClient
     * @param \Nostress\Koongo\Model\Webhook\Event\Manager $eventManager
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
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
        \Nostress\Koongo\Model\Api\Restclient\Kaas $apiClient,
        \Nostress\Koongo\Model\Webhook\Event\Manager $webhookEventManager,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->_eventFactory = $eventFactory;
        $this->_webhookFactory = $webhookFactory;
        $this->_apiClient = $apiClient;
        $this->_webhookEventManager = $webhookEventManager;
        $this->_orderFactory = $orderFactory;
        parent::__construct($context, $registry, $helper, $storeManager, $translation, $resource, $resourceCollection, $data);
    }

    /**
     * Proces events
     * @return void
     */
    public function processWebhookEvents()
    {
        $this->processWebhookEventsByIds(null);
    }

    /**
     * Proces events
     *
     * @param Array $eventIds Event ids that should be processed (process all events if null)
     * @param bool $pendingOnly Process pending events only
     * @return void
     */
    public function processWebhookEventsByIds($eventIds = null, $pendingOnly = true)
    {
        $this->_processEvents($eventIds, $pendingOnly);
        $this->_renewProcessingEvents();
        $this->_removeOldEvents();
        $this->_renewErrorEvents();
    }

    /**
     * Renew error events that has not reached maximum duplicity level
     *
     * @return void
     */
    protected function _renewErrorEvents()
    {
        $eventCollection = $this->_eventFactory->create()->getCollection();
        $select = $eventCollection->getSelect();
        $select->where('status = ?', \Nostress\Koongo\Model\Webhook\Event::STATUS_ERROR);
        $select->where('duplicity_counter < ?', self::EVENT_RENEWAL_COUNTER_LIMIT);
        $eventCollection->load();

        $webhookEventManager = $this->_getManager();
        foreach ($eventCollection as $event) {
            $duplicateEvent = $webhookEventManager->duplicateWebhookEvent($event);
            $duplicateEvent->save();
            $event->updateStatus(\Nostress\Koongo\Model\Webhook\Event::STATUS_DUPLICATED);
        }
    }

    /**
     * Remove events older than given number of days
     *
     * @return void
     */
    protected function _removeOldEvents()
    {
        $eventCollection = $this->_eventFactory->create()->getCollection();
        $select = $eventCollection->getSelect();
        $checkTime = $this->helper->formatDatetime(self::EVENT_REMOVAL_CHECK_PERIOD);
        $select->where('updated_at < ?', $checkTime);
        $eventCollection->load();
        foreach ($eventCollection as $event) {
            $event->delete();
        }
    }

    /**
     * Check processing events older than given preiod
     * Renew the events into Pending state if possible.
     * @return void
     */
    protected function _renewProcessingEvents()
    {
        $eventCollection = $this->_eventFactory->create()->getCollection();
        $select = $eventCollection->getSelect();
        $select->where('status = ?', \Nostress\Koongo\Model\Webhook\Event::STATUS_PROCESSING);
        $checkTime = $this->helper->formatDatetime(self::EVENT_PROCESSING_CHECK_PERIOD);
        $select->where('updated_at < ?', $checkTime);
        $eventCollection->load();

        foreach ($eventCollection as $event) {
            $counter = $event->getDuplicityCounter();
            if ($counter >= self::EVENT_RENEWAL_COUNTER_LIMIT) {
                $message = __("Event renewal limit (%s) reached.", self::EVENT_RENEWAL_COUNTER_LIMIT);
                $event->updateStatus(\Nostress\Koongo\Model\Webhook\Event::STATUS_ERROR, $message);
            } else {
                $counter++;
                $event->setDuplicityCounter($counter);
                $message = __("Event stacked at processing state. Event renewed.");
                $event->updateStatus(\Nostress\Koongo\Model\Webhook\Event::STATUS_PENDING, $message);
            }
        }
    }
    /**
     * Process pending webhook events
     * @param Array $eventIds Event ids that should be processed (process all events if null)
     * @param bool $pendingOnly Process pending events only
     * @return void
     */
    protected function _processEvents($eventIds, $pendingOnly = true)
    {
        $eventCollection = $this->_eventFactory->create()->getCollection();
        $select = $eventCollection->getSelect();

        if ($pendingOnly) {
            $select->where('status = ?', \Nostress\Koongo\Model\Webhook\Event::STATUS_PENDING);
        }

        if (isset($eventIds) && !empty($eventIds)) {
            $select->where('entity_id IN (?)', $eventIds);
        } else {
            $select->limit(self::EVENT_PROCESSING_MAX_AMOUNT);
        }
        $eventCollection->load();

        $webhooks = [];
        foreach ($eventCollection as $event) {
            $webhookId = $event->getWebhookId();
            if (!isset($webhooks[$webhookId])) {
                $webhook = $this->_webhookFactory->create()->load($webhookId);
                if ($webhook->getId()) {
                    $webhooks[$webhookId] = $webhook;
                } else {
                    $order = $this->_orderFactory->create()->load($event->getOrderId());
                    if ($order->getId()) {
                        //webhook is missing, try to add events to existing webhook
                        $this->_getManager()->addWebhookEvents($event->getTopic(), $event->getProductId(), $event->getOrderId(), $order->getStoreId(), $event->getParams());
                    }
                    $message = __("Missing webhook for event. Events for new webhooks recreated, if webhooks available");

                    $event->setDuplicityCounter(self::EVENT_RENEWAL_COUNTER_LIMIT);
                    $event->updateStatus(\Nostress\Koongo\Model\Webhook\Event::STATUS_ERROR, $message);
                    continue;
                }
            } else {
                $webhook = $webhooks[$webhookId];
            }

            $this->_runEvent($event, $webhook);
        }
    }

    /**
     * Run event webhooks
     *
     * @param \Nostress\Koongo\Model\Webhook\Event $event
     * @param \Nostress\Koongo\Model\Webhook $webhook
     * @return void
     */
    protected function _runEvent($event, $webhook)
    {
        $client = $this->_getApiClient();
        try {
            $event->updateStatus(\Nostress\Koongo\Model\Webhook\Event::STATUS_PROCESSING);
            $payload = $webhook->preparePayloadForEvent($event);
            $response = $client->sendRequestPost($webhook->getUrl(), $payload, $webhook->getTopic(), $webhook->getSecret());
            $event->updateStatus(\Nostress\Koongo\Model\Webhook\Event::STATUS_FINISHED, $response);
        } catch (\Exception $e) {
            $event->updateStatus(\Nostress\Koongo\Model\Webhook\Event::STATUS_ERROR, $e->getMessage());
        }
    }

    /**
     * Load API client
     *
     * @return \Nostress\Koongo\Model\Api\Restclient\Kaas
     */
    protected function _getApiClient()
    {
        return $this->_apiClient;
    }

    /**
     * Get Manager for events
     *
     * @return \Nostress\Koongo\Model\Webhook\Event\Manager
     */
    protected function _getManager()
    {
        return $this->_webhookEventManager;
    }
}
