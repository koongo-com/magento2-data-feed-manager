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
 * Export profiles grid controller
 *
 * @category Nostress
 * @package Nostress_Koongo
 *
 */

namespace Nostress\Koongo\Controller\Adminhtml\License;

class Check extends \Nostress\Koongo\Controller\Adminhtml\License
{

    /**
     * Api client
     *
     * @var \Nostress\Koongo\Model\Api\Client
     */
    protected $client = null;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Nostress\Koongo\Model\Api\Client $apiClient
    ) {
        $this->client = $apiClient;
        parent::__construct($context);
    }

    /**
     * Index action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $isAjax = (bool) $this->getRequest()->getParam('ajax', false);

        try {
            $response = $this->client->updateLicense();

            if (!$isAjax) {
                if ($response['valid']) {
                    $this->messageManager->addSuccess($response['license_status']);
                } else {
                    $this->messageManager->addError($response['license_status']);
                }
            }
        } catch (\Exception  $e) {
            $message = __("License status check failed: ") . $e->getMessage();
            if (!$isAjax) {
                $this->messageManager->addError($message);
            } else {
                $response = [ 'error'=>$message];
            }
        }

        if ($isAjax) {
            $this->_sendAjaxSuccess($response);
        } else {
            $resultRedirect = $this->resultRedirectFactory->create();
            return $resultRedirect->setPath('*/channel_profile');
        }
    }

    protected function _sendAjaxSuccess($message)
    {
        $this->getResponse()->representJson(
            $this->_objectManager->get(
                'Magento\Framework\Json\Helper\Data'
            )->jsonEncode($message)
        );
    }
}
