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
 */

namespace Nostress\Koongo\Model\Webhook\Event;

use Nostress\Koongo\Model\Webhook;
use Nostress\Koongo\Model\Webhook\Event;

final class Processor extends AbstractProcessor
{
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

    protected function _processEvents(array $eventIds = null, bool $pendingOnly = true)
    {
        $eventCollection = $this->_eventFactory->create()->getCollection();
        $select = $eventCollection->getSelect();

        if ($pendingOnly) {
            $select->where('status = ?', Event::STATUS_PENDING);
        }

        if (isset($eventIds) && !empty($eventIds)) {
            $select->where('entity_id IN (?)', $eventIds);
        } else {
            $select->limit(self::EVENT_PROCESSING_MAX_AMOUNT);
        }

        $select->where('topic IN (?)', [
            Webhook::WEBHOOK_TOPIC_ADD_CREDITMEMO,
            Webhook::WEBHOOK_TOPIC_ADD_SHIPMENT,
            Webhook::WEBHOOK_TOPIC_CANCEL_ORDER,
            Webhook::WEBHOOK_TOPIC_INVENTORY_UPDATE,
            Webhook::WEBHOOK_TOPIC_PRODUCTS_CREATE,
            Webhook::WEBHOOK_TOPIC_PRODUCTS_DELETE,
            Webhook::WEBHOOK_TOPIC_PRODUCTS_UPDATE,
            Webhook::WEBHOOK_TOPIC_PRODUCTS_BATCH_DELETE,
        ]);

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
                    $event->updateStatus(Event::STATUS_ERROR, $message);
                    continue;
                }
            } else {
                $webhook = $webhooks[$webhookId];
            }

            $this->_runEvent($event, $webhook);
        }
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
}
