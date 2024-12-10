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

use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Sales\Model\OrderFactory;
use Magento\Store\Model\StoreManagerInterface;
use Nostress\Koongo\Helper\Data;
use Nostress\Koongo\Model\AbstractModel;
use Nostress\Koongo\Model\Api\Restclient\Kaas;
use Nostress\Koongo\Model\Translation;
use Nostress\Koongo\Model\Webhook\EventFactory;
use Nostress\Koongo\Model\WebhookFactory;

abstract class AbstractProcessor extends AbstractModel
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
     * @var EventFactory
     */
    protected $_eventFactory;

    /**
     * Webhook factory
     *
     * @var WebhookFactory
     */
    protected $_webhookFactory;

    /**
     * Kaas api client
     *
     * @var Kaas
     */
    protected $_apiClient;

    /**
     * Webhook event manager
     *
     * @var Manager
     */
    protected $_webhookEventManager;

    /**
     * Sales order factory
     *
     *  @var OrderFactory
     */
    protected $_orderFactory;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param Data $helper
     * @param StoreManagerInterface $storeManager
     * @param Translation $translation
     * @param EventFactory $eventFactory
     * @param WebhookFactory $webhookFactory
     * @param Kaas $apiClient
     * @param Manager $eventManager
     * @param OrderFactory $orderFactory
     * @param AbstractResource $resource
     * @param AbstractDb $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context                                                 $context,
        Registry                                                $registry,
        Data                                                    $helper,
        StoreManagerInterface                                   $storeManager,
        Translation                                             $translation,
        EventFactory                                            $eventFactory,
        WebhookFactory                                          $webhookFactory,
        Kaas                                                    $apiClient,
        Manager                                                 $webhookEventManager,
        OrderFactory                       $orderFactory,
        AbstractResource $resource = null,
        AbstractDb           $resourceCollection = null,
        \Magento\Framework\Filesystem\DriverInterface $driver,
        array                                                   $data = []
    ) {
        $this->_eventFactory = $eventFactory;
        $this->_webhookFactory = $webhookFactory;
        $this->_apiClient = $apiClient;
        $this->_webhookEventManager = $webhookEventManager;
        $this->_orderFactory = $orderFactory;
        parent::__construct($context, $registry, $helper, $storeManager, $translation, $resource, $resourceCollection, $driver, $data);
    }

    /**
     * Load API client
     *
     * @return Kaas
     */
    protected function _getApiClient()
    {
        return $this->_apiClient;
    }

    /**
     * Get Manager for events
     *
     * @return Manager
     */
    protected function _getManager()
    {
        return $this->_webhookEventManager;
    }
}
