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
 * Add attributes to product flat catalog, refresh cache and reindex action
 *
 * @category Nostress
 * @package Nostress_Koongo
 *
 */

namespace Nostress\Koongo\Controller\Adminhtml\Channel\Profile;

use Magento\Framework\Controller\ResultFactory;

class Flatprocess extends \Magento\Backend\App\Action
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
     * @var \Magento\Framework\App\Cache\Frontend\Pool
     */
    protected $_cacheFrontendPool;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory
     * @param \Nostress\Koongo\Model\Channel\Profile\Manager
     * @param \Magento\Catalog\Model\Indexer\Product\Flat $indexerProductFlat
     * @param \Magento\Framework\App\Cache\Frontend\Pool $cacheFrontendPool
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory,
        \Nostress\Koongo\Model\Channel\Profile\Manager $manager,
        \Magento\Catalog\Model\Indexer\Product\Flat $indexerProductFlat,
        \Magento\Framework\App\Cache\Frontend\Pool $cacheFrontendPool
    ) {
        $this->resultForwardFactory = $resultForwardFactory;
        $this->manager = $manager;
        $this->indexerProductFlat = $indexerProductFlat;
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
        $profileIds = [];
        try {
            $result = $this->manager->addProfilesAttributesToFlat();

            if (!empty($result["profile_ids"])) {
                $profileIds = $result["profile_ids"];
            }

            if (!empty($result["attributes"])) {
                $attributes = $result["attributes"];
                $this->messageManager->addSuccess(__('Following attributes have been added to Product Flat Catalog: %1', implode(", ", $attributes)));

                //Reindex flat
                $this->indexerProductFlat->executeFull();
                $this->messageManager->addSuccess(__("The product flat index has been reindexed."));
                //Refresh cache
                $this->_eventManager->dispatch('adminhtml_cache_flush_all');
                /** @var $cacheFrontend \Magento\Framework\Cache\FrontendInterface */
                foreach ($this->_cacheFrontendPool as $cacheFrontend) {
                    $cacheFrontend->getBackend()->clean();
                }
                $this->messageManager->addSuccess(__("The cache storage has been flushed."));
            }
        } catch (\Exception $e) {
            //throw new \Magento\Framework\Exception\LocalizedException(__($e->getMessage()), $e);
            $this->messageManager->addError(__("Following error occurred during processing of product attributes: %1", $e->getMessage()));
        }

        //Execute profile or write down notice, if more profiles request execution.
        if (!empty($profileIds)) {
            if (count($profileIds) == 1) {
                /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
                $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
                return $resultRedirect->setPath('*/*/execute', ['entity_id' => $profileIds[0], '_current' => true]);
            } else {
                $this->messageManager->addNotice(__("Please execute profiles with following ids: %1", implode(", ", $profileIds)));
            }
        }

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('*/*/');
    }
}
