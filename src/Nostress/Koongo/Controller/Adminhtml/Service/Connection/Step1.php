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
use Laminas\Http\Client as Client;

class Step1 extends \Magento\Backend\App\Action
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
     * @var \Magento\Framework\Escaper
     */
    protected $escaper;

    /**
     * @var \Nostress\Koongo\Helper\Data\Service
     */
    protected $_serviceHelper = null;
    protected $_httpClient;
    /**
     * @param Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Registry $registry,
     * @param \Magento\Framework\Escaper $escaper,
     */
    public function __construct(
        Action\Context $context,
        PageFactory $resultPageFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Escaper $escaper,
        \Nostress\Koongo\Helper\Data\Service $serviceHelper,
        Client $httpClient
    ) {
        $this->_coreRegistry = $registry;
        $this->resultPageFactory = $resultPageFactory;
        $this->escaper = $escaper;
        $this->_serviceHelper = $serviceHelper;
        $this->_httpClient = $httpClient;

        parent::__construct($context);
    }

    public function execute()
    {
        try {
            $this->_validateUser();
            $this->_serviceHelper->prepareIntegration();
            $this->messageManager->addSuccess(__('A system integration with name %1 was sucessfully created.', $this->_serviceHelper->getKoongoIntegrationName()));
        } catch (UserLockedException $e) {
            $this->_auth->logout();
            $this->getSecurityCookie()->setLogoutReasonCookie(
                \Magento\Security\Model\AdminSessionsManager::LOGOUT_REASON_USER_LOCKED
            );
            $this->_redirect('*');
        } catch (\Exception $e) {
            $message = __("Error during integration creation.");
            $this->messageManager->addError($message . " " . $this->escaper->escapeHtml($e->getMessage()));
        }

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath('*/*/');
    }

    /**
     * Validate current user password
     *
     * @return $this
     * @throws UserLockedException
     * @throws \Magento\Framework\Exception\AuthenticationException
     */
    protected function _validateUser()
    {
        $password = $this->getRequest()->getParam(
            \Nostress\Koongo\Block\Adminhtml\Service\Connection\Edit\Form::DATA_CONSUMER_PASSWORD
        );
        $user = $this->_auth->getUser();
        $user->performIdentityCheck($password);

        return $this;
    }
}
