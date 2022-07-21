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
 * Execute profile action
 *
 * @category Nostress
 * @package Nostress_Koongo
 *
 */

namespace Nostress\Koongo\Controller\Adminhtml\Channel\Profile\Ftp;

use Magento\Framework\Controller\ResultFactory;

class Upload extends \Nostress\Koongo\Controller\Adminhtml\Channel\Profile\SaveAbstract
{
    protected $_auth_label = 'Nostress_Koongo::execute';

    /**
     * Forward to edit
     *
     * @return \Magento\Backend\Model\View\Result\Forward
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('entity_id');
        if (!empty($id) && is_numeric($id)) {
            $profile = $this->profileFactory->create();
            $profile->load($id);

            $result = $this->manager->uploadFeed($profile);
            if ($result === true) {
                $this->messageManager->addSuccess(__('Feed was successfuly uploaded via FTP!'));
            } else {
                $this->messageManager->addError($result);
            }
        }

        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('*/channel_profile_ftp/edit', [ 'entity_id'=>$id]);
    }
}
