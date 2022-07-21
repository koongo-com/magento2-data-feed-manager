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
 * Update feed templates controller
 *
 * @category Nostress
 * @package Nostress_Koongo
 *
 */

namespace Nostress\Koongo\Controller\Adminhtml\Channel\Profile;

class Download extends SaveAbstract
{

    /**
     * Update feeds action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('entity_id');
        $errorMessage = false;
        if ($id) {
            $profile =  $this->profileFactory->create()->load($id);
            if (!$profile->getId()) {
                $errorMessage = __('This profile no longer exists.');
            } else {
                $path = $profile->getFilename(true); //Get full file path
                $fileName = $profile->getFilename();

                $content = file_get_contents($path);

                $this->getResponse()
                    ->setHttpResponseCode(200)
                    ->setHeader('Pragma', 'public', true)
                    ->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true)
                    ->setHeader('Content-type', 'application/octet-stream', true)
                    ->setHeader('Content-Length', strlen($content))
                    ->setHeader('Content-Disposition', 'attachment; filename="' . $fileName . '"')
                    ->setHeader('Last-Modified', date('r'))

                    ->setBody($content);
                $this->getResponse()->sendResponse();
                return;
            }
        } else {
            $errorMessage = __('Wrong format of params.');
        }

        if ($errorMessage) {
            $this->messageManager->addError($errorMessage);
        }
        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath('*/channel_profile');
    }
}
