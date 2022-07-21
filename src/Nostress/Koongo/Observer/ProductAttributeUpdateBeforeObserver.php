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
 * Observer for Catalog Product Attribute Update Before Observer for Kaas webhooks
 *
 * @category Nostress
 * @package Nostress_Koongo
 */

namespace Nostress\Koongo\Observer;

class ProductAttributeUpdateBeforeObserver extends \Nostress\Koongo\Observer\BaseObserver
{
    /**
     * Product factory
     *
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $_productFactory;

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
        \Nostress\Koongo\Helper\Data\Order $helper,
        \Magento\Catalog\Model\ProductFactory $productFactory
    ) {
        $this->_productFactory = $productFactory;
        parent::__construct($eventManager, $eventProcessor, $helper);
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $productIds = $observer->getEvent()->getProductIds();

        $collection = $this->_productFactory->create()->getCollection();
        $collection->addIdFilter($productIds);
        $collection->addAttributeToSelect("sku");
        $collection->load();

        $productSkusToUpdate = [];
        foreach ($collection as $product) {
            $productSkusToUpdate[] = $product->getSku();
        }

        //Product batch add/update webhook
        $this->_addBatchProductEvent($productSkusToUpdate);
    }
}
