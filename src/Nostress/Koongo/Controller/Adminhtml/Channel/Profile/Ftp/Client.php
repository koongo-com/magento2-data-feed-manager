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
 * Save channel profile attribte settings action
 *
 * @category Nostress
 * @package Nostress_Koongo
 *
 */

namespace Nostress\Koongo\Controller\Adminhtml\Channel\Profile\Ftp;

use Nostress\Koongo\Model\Channel\Profile;

class Client extends \Nostress\Koongo\Controller\Adminhtml\Channel\Profile\SaveAbstract
{
    public function execute()
    {
        $path = $this->getRequest()->getParam('path');

        if (($postData = $this->getRequest()->getParams()) !== false && isset($postData[Profile::CONFIG_FEED][Profile::CONFIG_FTP])) {
            $config = $postData[Profile::CONFIG_FEED][Profile::CONFIG_FTP];
        } elseif (($id = $this->_request->getParam('entity_id')) > 0) {
            $profile =  $this->profileFactory->create()->load($id);
            $config = $profile->getFtpParams();
        } else {
            $message = __("Wrong data format!");
            return $this->_sendAjaxError($message);
        }

        $ftp = $this->manager->getFtp();

        if ($this->getRequest()->getParam('test')) {
            $message = $ftp->checkFtpConnection($config);
            if ($message['error']) {
                return $this->_sendAjaxResponse($message);
            }
        } else {
            $message = [];
        }

        if (!$path) {
            $path = $config['path'];
        }

        try {
            $ftpClient = $ftp->getClient($config);
        } catch (\Exception $e) {
            return $this->_sendAjaxError($e->getMessage());
        }

        $file = $this->getRequest()->getParam('file');
        if ($file) {
            $content = $ftpClient->read($file);
            $ftpClient->close();

            $this->getResponse()
                ->setHttpResponseCode(200)
                ->setHeader('Pragma', 'public', true)
                ->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true)
                ->setHeader('Content-type', 'application/octet-stream', true)
                ->setHeader('Content-Length', strlen($content))
                ->setHeader('Content-Disposition', 'attachment; filename="' . basename($file) . '"')
                ->setHeader('Last-Modified', date('r'))

                ->setBody($content);
            $this->getResponse()->sendResponse();
            return;
        } else {
            if ($path) {
                $ftpClient->cd($path);
            }
            $list = $ftp->getFilesSorted($ftpClient);
            $path = $ftpClient->pwd();
            $ftpClient->close();
            $message['list'] = $list;
            $message['path'] = $path;

            return $this->_sendAjaxResponse($message);
        }
    }
}
