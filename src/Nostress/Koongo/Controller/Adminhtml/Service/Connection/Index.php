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
 * Koongo service connection adjustment page
 *
 * @category Nostress
 * @package Nostress_Koongo
 *
 */

namespace Nostress\Koongo\Controller\Adminhtml\Service\Connection;

use Magento\Backend\App\Action;
use Magento\Framework\View\Result\PageFactory;

class Index extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     */
    const ADMIN_RESOURCE = 'Nostress_Koongo::service_api_connection';

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
     * Integration factory
     *
     * @var \Magento\Integration\Model\IntegrationFactory
     */
    protected $_integrationFactory;

    /**
     * @var \Nostress\Koongo\Helper\Data
     */
    protected $_dataHelper = null;

    /**
     * @param Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Integration\Model\IntegrationFactory $integrationFactory
     * @param \Nostress\Koongo\Helper\Data $dataHelper
     */
    public function __construct(
        Action\Context $context,
        PageFactory $resultPageFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Integration\Model\IntegrationFactory $integrationFactory,
        \Nostress\Koongo\Helper\Data $dataHelper
    ) {
        $this->_coreRegistry = $registry;
        $this->resultPageFactory = $resultPageFactory;
        $this->_integrationFactory = $integrationFactory;
        $this->_dataHelper = $dataHelper;
        parent::__construct($context);
    }

    public function execute()
    {
        $resultRedirect = $this->_checkAndUpdateServerConfig();
        if ($resultRedirect) {
            return $resultRedirect;
        }

        $integration = $this->_getIntegration();
        if (!empty($integration)) {  // Must add integration data into registry due to block \Magento\Integration\Block\Adminhtml\Integration\Activate\Permissions\Tab\Webapi
            $this->_coreRegistry->register(\Magento\Integration\Controller\Adminhtml\Integration::REGISTRY_KEY_CURRENT_INTEGRATION, $integration->getData());
        }

        $numStep = $this->_getStepNumber($integration);
        $this->_coreRegistry->register(\Nostress\Koongo\Helper\Data::REGISTRY_KEY_KOONGO_SERVIVE_CONNECTION_STEP_NUMBER, $numStep);

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Nostress_Koongo::koongo');
        $resultPage->addBreadcrumb(__('Koongo Service'), __('Koongo Service'));
        $resultPage->addBreadcrumb(__('Connection Wizard'), __('Connection Wizard'));
        $resultPage->getConfig()->getTitle()->prepend(__("Koongo Service - Connection Wizard - Step %1", $numStep));

        return $resultPage;
    }

    /**
     * Get Koongo Integration instance
     *
     * @return false|\Magento\Integration\Model\Integration
     */
    protected function _getIntegration()
    {
        //Set your Data
        $integrationName = $this->_dataHelper->getKoongoIntegrationName();
        $integration = $this->_integrationFactory->create()->load($integrationName, 'name');
        if (!$integration->getId()) {
            return false;
        }
        return $integration;
    }

    /**
     * Get step number based on Koongo integration availability and status
     *
     * @param \Magento\Integration\Model\Integration $integration
     * @return void
     */
    protected function _getStepNumber($integration)
    {
        $stepNum = 1;
        if ($integration) {
            $stepNum = 2;
            if ($integration->getStatus()) {
                $stepNum = 3;
            }
        }
        return $stepNum;
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
            $redirectPageKey = 'service_connection';
            return $resultRedirect->setPath('koongo/license/updateserverconfig', [ 'eei'=>true, 'redirect_page_key' => $redirectPageKey]);
        }
        return false;
    }
}
