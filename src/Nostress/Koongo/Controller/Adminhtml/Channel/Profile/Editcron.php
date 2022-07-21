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
 * Edit channel profile filter settings action
 *
 * @category Nostress
 * @package Nostress_Koongo
 *
 */

namespace Nostress\Koongo\Controller\Adminhtml\Channel\Profile;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Editcron extends SaveAbstract
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * \Magento\Backend\Model\Session
     * @var unknown_type
     */
    protected $_session;

    /**
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Nostress\Koongo\Helper\Version $helper,
        \Nostress\Koongo\Model\Channel\Profile\Manager $manager,
        \Nostress\Koongo\Model\Channel\ProfileFactory $profileFactory,
        \Nostress\Koongo\Model\Translation $translation,
        \Magento\Framework\Registry $registry
    ) {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $resultPageFactory, $helper, $manager, $profileFactory, $translation);
    }

    /**
     * Init actions
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    protected function _initAction()
    {
        $resultPage = $this->resultPageFactory->create();

        $resultPage->setActiveMenu('Nostress_Koongo::koongo');
        $resultPage->addBreadcrumb(__('Koongo'), __('Koongo'));
        $resultPage->addBreadcrumb(__('Manage Profile Schedule Execution'), __('Manage Profile Schedule Execution'));

        return $resultPage;
    }

    /**
     * Edit FIlter setting
     *
     * @return \Magento\Backend\Model\View\Result\Page|\Magento\Backend\Model\View\Result\Redirect
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('entity_id');

        if ($id) {
            $model =  $this->profileFactory->create()->load($id);

            if (!$model->getId()) {
                $this->messageManager->addError(__('This profile no longer exists.'));
                /** \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('*/*/');
            }
        }

        $data = $this->_session->getFormData(true);
        if (!empty($data)) {
            $model->setData($data);
        }

        $this->_coreRegistry->register('koongo_channel_profile', $model);

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->_initAction();
        $resultPage->addBreadcrumb(__("Edit Schedule Execution - Profile #%1", $model->getId()), __("Edit Schedule Execution - Profile #%1", $model->getId()));
        $title = __("Edit Schedule Execution - Profile #%1", $model->getId());
        $resultPage->getConfig()->getTitle()->prepend($title);

        return $resultPage;
    }
}
