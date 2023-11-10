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

use Exception;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Nostress\Koongo\Model\Webhook;
use Nostress\Koongo\Model\Webhook\Event;
use Zend_Db_Expr;
use Zend_Db_Select;
use Magento\Cron\Model\Schedule;

/**
 * Batch processor for products.batch topic events
 */
class BatchProcessor extends AbstractProcessor
{
    private const BATCH_SIZE = 500;

    /**
     * Process events with batch & bulk it into more rows
     *
     * @return void
     */
    public function process(?Schedule $schedule = null, bool $pendingOnly = true)
    {
        $batches = $this->prepareBatch($pendingOnly);

        /** @var AbstractCollection $eventCollection */
        $eventCollection = $this->_eventFactory->create()->getCollection();

        foreach ($batches as $cnt => $webhookId) {
            var_dump($webhookId);
            $webhook = $this->_webhookFactory->create()->load($webhookId);

            if (!empty($webhook->getId())) {
                $collection = clone $eventCollection;
                $select = $collection->getSelect();
                $skus = [];
                $eventIds = [];

                while ($cnt > 0) {
                    $select->where('topic = ?', Webhook::WEBHOOK_TOPIC_PRODUCTS_BATCH)
                        ->where('status = ?', Event::STATUS_PENDING)
                        ->where('webhook_id = ?', $webhookId)
                        ->limit(self::BATCH_SIZE)
                        ->order('created_at');

                    $processed = 0;
                    $collection->load();
                    /** @var Event $event */
                    foreach ($collection as $event) {
                        $params = json_decode($event->getParams(), true);
                        $eventIds[] = $event->getId();
                        $processed++;

                        if (empty($params)) {
                            continue;
                        }

                        if (array_key_exists($event::PARAM_STORE_PRODUCT_SKUS, $params)) {
                            $skus = array_merge($params[$event::PARAM_STORE_PRODUCT_SKUS], $skus);
                            $skus = array_unique($skus);
                        }

                        if (count($skus) >= self::BATCH_SIZE) {
                            $hash = md5(json_encode($eventIds));
                            // send
                            try {
                                $result = $this->send($webhook, $skus);
                                $status = Event::STATUS_FINISHED;
                            } catch (Exception $e) {
                                $result = $e->getMessage();
                                $status = Event::STATUS_ERROR;
                            }
                            $this->updateEvents($eventIds, $status, $result, $hash);
                            $skus = [];
                            $eventIds = [];
                        }
                    }

                    if (count($skus) > 0) {
                        $hash = md5(json_encode($eventIds));
                        // send
                        try {
                            $result = $this->send($webhook, $skus);
                            $status = Event::STATUS_FINISHED;
                        } catch (Exception $e) {
                            $result = $e->getMessage();
                            $status = Event::STATUS_ERROR;
                        }
                        $this->updateEvents($eventIds, $status, $result, $hash);
                        $skus = [];
                        $eventIds = [];
                    }

                    $cnt = $cnt - $processed;
                }
            }
        }
    }

    /**
     * @return string
     */
    private function send(Webhook $webhook, array $skus)
    {
        $client = $this->_getApiClient();
        $payload = [
            Event::PARAM_STORE_PRODUCT_SKUS => $skus
        ];

        return $client->sendRequestPost($webhook->getUrl(), $payload, $webhook->getTopic(), $webhook->getSecret());
    }

    /**
     * @return array<int, int> [[count => webhook_id]]
     */
    private function prepareBatch(bool $pendingOnly = true)
    {
        /** @var AbstractCollection $eventCollection */
        $eventCollection = $this->_eventFactory->create()->getCollection();
        $select = $eventCollection->getSelect();

        $select->reset(Zend_Db_Select::COLUMNS)
            ->columns([
            'cnt' => new Zend_Db_Expr('COUNT(*)'),
            'webhook_id' => 'webhook_id'
        ]);

        if ($pendingOnly) {
            $select->where('status = ?', Event::STATUS_PENDING);
        }

        $select->where('topic = ?', Webhook::WEBHOOK_TOPIC_PRODUCTS_BATCH)
            ->group('webhook_id')
            ->order('cnt');

        $eventCollection->load();

        $hooks = [];
        foreach ($eventCollection as $item) {
            $hooks[$item->getCnt()] = $item->getWebhookId();
        }

        krsort($hooks);
        return $hooks;
    }

    private function updateEvents(array $eventIds, string $status, string $result, string $hash)
    {
        /** @var AbstractCollection $eventCollection */
        $eventCollection = $this->_eventFactory->create()->getCollection();
        $eventCollection->getConnection()->update(
            $eventCollection->getMainTable(),
            [
                'status' => $status,
                'message' => sprintf(
                    '%s: %s | %s',
                    __('Batched ID'),
                    $hash,
                    $result
                )
            ],
            [
                'entity_id IN (?)' => $eventIds
            ]
        );
    }
}
