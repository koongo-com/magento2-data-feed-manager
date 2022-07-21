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

namespace Nostress\Koongo\Controller\Adminhtml\Channel\Profile;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Loadcategories extends SaveAbstract
{
    /**
     * @var \Nostress\Koongo\Model\Channel\Profile\Categories
     */
    protected $_categoriesModel = null;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Nostress\Koongo\Helper\Version $helper,
        \Nostress\Koongo\Model\Channel\Profile\Manager $manager,
        \Nostress\Koongo\Model\Channel\ProfileFactory $profileFactory,
        \Nostress\Koongo\Model\Translation $translation,
        \Nostress\Koongo\Model\Channel\Profile\Categories $categoriesModel
    ) {
        parent::__construct($context, $resultPageFactory, $helper, $manager, $profileFactory, $translation);
        $this->_categoriesModel = $categoriesModel;
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
            $this->_categoriesModel->initProfile($profile);

            $result = [
                'mapping_rules' => $this->_categoriesModel->getMappingRules($locale)
            ];
            if ($this->getRequest()->getParam('with_categories')) {
                $result['channel_categories'] = $this->_categoriesModel->getChannelCategories($locale);
            }

            $this->_sendAjaxSuccess($result);
        } else {
            $this->_sendAjaxError('wrong data format');
        }
    }
}
