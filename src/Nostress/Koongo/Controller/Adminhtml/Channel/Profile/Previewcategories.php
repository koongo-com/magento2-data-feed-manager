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

namespace Nostress\Koongo\Controller\Adminhtml\Channel\Profile;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\LayoutFactory;
use Magento\Framework\View\Result\PageFactory;
use Nostress\Koongo\Helper\Version;
use Nostress\Koongo\Model\Channel\Profile\Manager;
use Nostress\Koongo\Model\Channel\ProfileFactory;
use Nostress\Koongo\Model\Translation;

class Previewcategories extends SaveAbstract
{
    protected Registry $_coreRegistry;
    private LayoutFactory $resultLayoutFactory;

    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Version $helper,
        Manager $manager,
        ProfileFactory $profileFactory,
        Translation $translation,
        Registry $registry,
        LayoutFactory $resultLayoutFactory
    ) {
        $this->_coreRegistry = $registry;
        $this->resultLayoutFactory = $resultLayoutFactory;
        parent::__construct($context, $resultPageFactory, $helper, $manager, $profileFactory, $translation);
    }

    /**
     * Index action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('entity_id');
        $locale = $this->getRequest()->getParam('taxonomy_locale');
        if ($id) {
            $profile =  $this->profileFactory->create()->load($id);
            $this->_coreRegistry->register('koongo_channel_profile', $profile);
        } else {
            $this->_sendAjaxError('wrong data format');
        }

        return $this->resultLayoutFactory->create();
    }
}
