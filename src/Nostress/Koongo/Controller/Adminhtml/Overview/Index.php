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
 * Koongo overview page
 *
 * @category Nostress
 * @package Nostress_Koongo
 *
 */

namespace Nostress\Koongo\Controller\Adminhtml\Overview;

use Magento\Backend\App\Action;
use Magento\Framework\View\Result\PageFactory;

class Index extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     */
    const ADMIN_RESOURCE = 'Nostress_Koongo::koongo';

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Nostress\Koongo\Helper\Data
     */
    protected $_dataHelper = null;

    /**
     * @param Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Registry $registry
     * @param \Nostress\Koongo\Helper\Data $dataHelper
     */
    public function __construct(
        Action\Context $context,
        PageFactory $resultPageFactory,
        \Magento\Framework\Registry $registry,
        \Nostress\Koongo\Helper\Data $dataHelper
    ) {
        $this->_coreRegistry = $registry;
        $this->resultPageFactory = $resultPageFactory;
        $this->_dataHelper = $dataHelper;
        parent::__construct($context);
    }

    public function execute()
    {
        $resultRedirect = $this->_checkAndUpdateServerConfig();
        if ($resultRedirect) {
            return $resultRedirect;
        }

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Nostress_Koongo::koongo');
        $resultPage->addBreadcrumb(__('Koongo'), __('Koongo'));
        $resultPage->addBreadcrumb(__('Getting Started'), __('Getting Started'));
        $resultPage->getConfig()->getTitle()->prepend(__("Koongo - Getting Started"));

        return $resultPage;
    }

    /**
     * Check and update server config
     *
     * @return false|\Magento\Framework\Controller\Result\Redirect
     */
    protected function _checkAndUpdateServerConfig()
    {
        // if empty configuration - redirect to update server config
        if ($this->_dataHelper->isServerConfigUpdateNeeded()) {
            $resultRedirect = $this->resultRedirectFactory->create();
            $redirectPageKey = 'overview';
            return $resultRedirect->setPath('koongo/license/updateserverconfig', [ 'eei'=>true, 'redirect_page_key' => $redirectPageKey]);
        }
        return false;
    }
}
