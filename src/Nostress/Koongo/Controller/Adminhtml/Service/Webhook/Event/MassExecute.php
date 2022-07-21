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
 * Webhhok event grid mass delete controller
 *
 * @category Nostress
 * @package Nostress_Koongo
 *
 */

namespace Nostress\Koongo\Controller\Adminhtml\Service\Webhook\Event;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Ui\Component\MassAction\Filter;
use Nostress\Koongo\Model\ResourceModel\Webhook\Event\CollectionFactory;
use Nostress\Koongo\Model\Webhook\Event\Processor;

/**
 * Class Mass Execute
 */
class MassExecute extends \Magento\Backend\App\Action
{
    /**
     * @var Filter
     */
    protected $filter;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
    * @var EventProcessor
    */
    protected $eventProcessor;

    /**
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param Processor $eventProcessor
     */
    public function __construct(Context $context, Filter $filter, CollectionFactory $collectionFactory, Processor $eventProcessor)
    {
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->eventProcessor = $eventProcessor;
        parent::__construct($context);
    }

    /**
     * Execute action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @throws \Magento\Framework\Exception\LocalizedException|\Exception
     */
    public function execute()
    {
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $ids = $collection->getAllIds();
        $collectionSize = $collection->getSize();

        if ($collectionSize > 0) {
            $this->eventProcessor->processWebhookEventsByIds($ids, false);
        }

        $this->messageManager->addSuccess(__('A total of %1 webhook event(s) have been executed.', $collectionSize));

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('*/*/');
    }
}
