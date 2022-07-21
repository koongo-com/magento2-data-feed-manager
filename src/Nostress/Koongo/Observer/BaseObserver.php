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
 * Abstract Observer for Kaas webhooks
 *
 * @category Nostress
 * @package Nostress_Koongo
 *
 */

namespace Nostress\Koongo\Observer;

use Magento\Framework\Event\ObserverInterface;

abstract class BaseObserver implements ObserverInterface
{
    /** @var \Nostress\Koongo\Helper\Order */
    public $helper;

    /**
     * Webhook event manager
     *
     * @var \Nostress\Koongo\Model\Webhook\Event\Manager
     */
    protected $_eventManager;

    /**
     * Webhook event processor
     *
     * @var \Nostress\Koongo\Model\Webhook\Event\Processor
     */
    protected $_eventProcessor;

    /**
     * Constructor
     *
     * @param \Nostress\Koongo\Model\Webhook\Event\Manager $eventManager
     * @param \Nostress\Koongo\Model\Webhook\Event\Processor $eventProcessor
     * @param \Nostress\Koongo\Helper\Data\Order $helper
     */
    public function __construct(
        \Nostress\Koongo\Model\Webhook\Event\Manager $eventManager,
        \Nostress\Koongo\Model\Webhook\Event\Processor $eventProcessor,
        \Nostress\Koongo\Helper\Data\Order $helper
    ) {
        $this->helper = $helper;
        $this->_eventManager = $eventManager;
        $this->_eventProcessor = $eventProcessor;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        //$myEventData = $observer->getData('myEventData');
        // Additional observer execution code...
    }

    /**
     * Prepare cancel order events
     *
     * @param Mage_Sales_Model_Order $order
     * @param String $cancelReason
     * @return void
     */
    protected function _addCancelOrderEvent($order, $cancelReason)
    {
        $eventIds = $this->_eventManager->addCancelOrderEvents($order, $cancelReason);
        // $this->_eventProcessor->processWebhookEventsByIds($eventIds); //Events will be executed by cron
    }

    /**
     * Prepare shipment events
     *
     * @param Mage_Sales_Model_Order_Shipment $shipment
     * @param \Magento\Sales\Model\Order\Shipment\Track $track
     * @return void
     */
    protected function _addShipmentEvent($shipment, $track)
    {
        $eventIds = $this->_eventManager->addShipmentEvents($shipment, $track);
        // $this->_eventProcessor->processWebhookEventsByIds($eventIds); //Events will be executed by cron
    }

    /**
     * Prepare creditmemo events
     *
     * @param \Magento\Sales\Model\Order\Creditmemo $creditmemo
     * @return void
     */
    protected function _addCreditmemoEvent($creditmemo)
    {
        $eventIds = $this->_eventManager->addCreditmemoEvents($creditmemo);
        // $this->_eventProcessor->processWebhookEventsByIds($eventIds); //Events will be executed by cron
    }

    /**
     * Prepare new product event
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return void
     */
    protected function _addNewProductEvent($product)
    {
        $eventIds = $this->_eventManager->addNewProductEvents($product);
        // $this->_eventProcessor->processWebhookEventsByIds($eventIds); //Events will be executed by cron
    }

    /**
     * Prepare update product event
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return void
     */
    protected function _addUpdateProductEvent($product)
    {
        $eventIds = $this->_eventManager->addUpdateProductEvents($product);
        // $this->_eventProcessor->processWebhookEventsByIds($eventIds); //Events will be executed by cron
    }

    /**
     * Prepare delete product event
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return void
     */
    protected function _addDeleteProductEvent($product)
    {
        $eventIds = $this->_eventManager->addDeteleProductEvents($product);
        // $this->_eventProcessor->processWebhookEventsByIds($eventIds); //Events will be executed by cron
    }

    /**
     * Prepare batch product import event
     *
     * @param array $productSkusToUpdate Array of product skus for update
     * @return void
     */
    protected function _addBatchProductEvent($productSkusToUpdate)
    {
        $eventIds = $this->_eventManager->addBatchProductEvents($productSkusToUpdate);
        // $this->_eventProcessor->processWebhookEventsByIds($eventIds); //Events will be executed by cron
    }

    /**
     * Prepare batch product delete event
     *
     * @param array $idsToDelete Array of product ids to delete
     * @return void
     */
    protected function _addBatchDeleteProductEvent($idsToDelete)
    {
        $eventIds = $this->_eventManager->addBatchDeleteProductEvents($idsToDelete);
        // $this->_eventProcessor->processWebhookEventsByIds($eventIds); //Events will be executed by cron
    }

    /**
     * Get Manager for events
     *
     * @return \Nostress\Koongo\Model\Webhook\Event\Manager
     */
    protected function _getManager()
    {
        return $this->_eventManager;
    }

    /**
     * Get Processor for events
     *
     * @return \Nostress\Koongo\Model\Webhook\Event\Processor
     */
    protected function _getProcessor()
    {
        return $this->_eventProcessor;
    }

    protected function log($message)
    {
        $this->helper->log($message);
    }

    protected function logAndException($message, $param = null)
    {
        if (is_array($message)) {
            if (isset($message['message'])) {
                $message = $message['message'];
            } else {
                $message = "Error message not specified";
            }
        }

        $translatedMessage = __($message, $param);
        $this->helper->log($translatedMessage);
        throw new \Exception($translatedMessage);
    }
}
