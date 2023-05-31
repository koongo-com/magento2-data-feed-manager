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

use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\Mail\Message;
use Magento\Framework\Mail\Transport;
use Nostress\Koongo\Helper\Version;

class Help extends \Nostress\Koongo\Controller\Adminhtml\License
{
    protected Message $message;
    protected Version $version;

    public function __construct(Context $context, Version $helper, Message $message)
    {
        $this->version = $helper;
        $this->message = $message;

        parent::__construct($context);
    }

    /**
     * Save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        if ($data) {
            if (!empty($data['form_key'])) {
                unset($data['form_key']);
            }

            try {
                $toEmail = $this->version->getModuleConfig(\Nostress\Koongo\Helper\Data::PARAM_SUPPORT_EMAIL);
                $this->message
                    ->addTo($toEmail)
                    ->addCc([ 'jiri.z@koongo.com', 'tomas.f@koongo.com'])
                    ->setBody($data['message'])
                    ->setSubject($data['subject'])
                    ->setFrom($data['email']);
                $transport =  new Transport($this->message);
                $transport->sendMessage();

                $this->messageManager->addSuccess(__('Your inquiry has been submitted and we should respond within 24 hours. Thank you for contacting Koongo Support.'));
            } catch (Exception $e) {
                $this->messageManager->addError(__('Unable to submit your request. Please, contact Koongo support desk via email address %1', $toEmail));
                $this->messageManager->addError(__('Error: ') . $e->getMessage());
            }
        }

        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        $redirectUrlKey = $this->getRequest()->getParam('redirect_url_key');
        if (empty($redirectUrlKey)) {
            $redirectUrlKey = '*/channel_profile/';
        }
        return $resultRedirect->setPath($redirectUrlKey);
    }
}
