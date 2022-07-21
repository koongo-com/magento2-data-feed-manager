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

namespace Nostress\Koongo\Controller\Adminhtml\License;

use Magento\Backend\App\Action;

class Liveactivate extends \Nostress\Koongo\Controller\Adminhtml\License
{
    /**
     * Api client
     *
     * @var \Nostress\Koongo\Model\Api\Client
     */
    protected $client;

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
     * Save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        $data = $this->getRequest()->getPostValue();
        if ($data) {
            try {
                $result = $this->client->createLicenseKey($data);

                $this->_session->setFormData(false);

                $this->messageManager->addSuccess(__('Koongo Connector has been activated with license key %1 .', $result['key']));
                $this->messageManager->addSuccess(__('Feed collection %1 has been assigned to the license key.', implode(", ", $result['collection'])));

                $this->client->updateFeeds($this->messageManager);
            } catch (\Exception  $e) {
                $message = __("Module activation process failed. Error: ");
                $this->messageManager->addError($message . $e->getMessage());

                // save data in session
                $this->_session->setFormData($data);
                // redirect to edit form
                return $resultRedirect->setPath('*/*/liveform');
            }
        }
        return $resultRedirect->setPath('adminhtml/system_config/edit/section/koongo_license/');
    }
}
