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

class Updateserverconfig extends \Nostress\Koongo\Controller\Adminhtml\License
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
        $enableErrorIncrement = $this->_request->getParam('eei', false);

        if (($response = $this->client->updateServerConfig($enableErrorIncrement)) === true) {
            $this->messageManager->addSuccess("Server config has been updated!");
        } else {
            $this->messageManager->addError($response);
        }

        $resultRedirect = $this->resultRedirectFactory->create();

        $redirectPageKey = $this->getRequest()->getParam('redirect_page_key');
        if (empty($redirectPageKey)) {
            $redirectUrlKey = '*/channel_profile/';
        } else {
            $redirectUrlKey = "*/{$redirectPageKey}/";
        }

        return $resultRedirect->setPath($redirectUrlKey);
    }
}
