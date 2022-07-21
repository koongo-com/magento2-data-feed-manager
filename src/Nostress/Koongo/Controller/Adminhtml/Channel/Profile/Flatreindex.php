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
 * Reindex category and product flat catalog
 *
 * @category Nostress
 * @package Nostress_Koongo
 *
 */

namespace Nostress\Koongo\Controller\Adminhtml\Channel\Profile;

use Magento\Framework\Controller\ResultFactory;

class Flatreindex extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Backend\Model\View\Result\Forward
     */
    protected $resultForwardFactory;

    /**
     * @var Manager
     */
    protected $manager;

    /**
     *
     * @var \Magento\Catalog\Model\Indexer\Product\Flat $indexerProductFlat
     */
    protected $indexerProductFlat;

    /**
     * @var \Magento\Catalog\Model\Indexer\Category\Flat $indexerCategoryFlat
     */
    protected $indexerCategoryFlat;

    /**
     * @var \Magento\Framework\App\Cache\Frontend\Pool
     */
    protected $_cacheFrontendPool;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory
     * @param \Nostress\Koongo\Model\Channel\Profile\Manager
     * @param \Magento\Catalog\Model\Indexer\Product\Flat $indexerProductFlat
     * @param \Magento\Catalog\Model\Indexer\Category\Flat $indexerCategoryFlat
     * @param \Magento\Framework\App\Cache\Frontend\Pool $cacheFrontendPool
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory,
        \Nostress\Koongo\Model\Channel\Profile\Manager $manager,
        \Magento\Catalog\Model\Indexer\Product\Flat $indexerProductFlat,
        \Magento\Catalog\Model\Indexer\Category\Flat $indexerCategoryFlat,
        \Magento\Framework\App\Cache\Frontend\Pool $cacheFrontendPool
    ) {
        $this->resultForwardFactory = $resultForwardFactory;
        $this->manager = $manager;
        $this->indexerProductFlat = $indexerProductFlat;
        $this->indexerCategoryFlat = $indexerCategoryFlat;
        $this->_cacheFrontendPool = $cacheFrontendPool;
        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Nostress_Koongo::execute');
    }

    /**
     * Forward to edit
     *
     * @return \Magento\Backend\Model\View\Result\Forward
     */
    public function execute()
    {
        try {
            //Reindex flat
            $this->indexerCategoryFlat->executeFull();
            $this->indexerProductFlat->executeFull();
            $this->messageManager->addSuccess(__("The product flat index has been reindexed."));
            //Refresh cache
            $this->_eventManager->dispatch('adminhtml_cache_flush_all');
            /** @var $cacheFrontend \Magento\Framework\Cache\FrontendInterface */
            foreach ($this->_cacheFrontendPool as $cacheFrontend) {
                $cacheFrontend->getBackend()->clean();
            }
            $this->messageManager->addSuccess(__("The cache storage has been flushed."));
            $this->manager->runProfilesWithErrorStatus();
            $this->messageManager->addSuccess(__("Profiles with error status were executed."));
        } catch (\Exception $e) {
            //throw new \Magento\Framework\Exception\LocalizedException(__($e->getMessage()), $e);
            $this->messageManager->addError(__("Following error occurred during category and product flat reindex: %1", $e->getMessage()));
        }

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('*/*/');
    }
}
