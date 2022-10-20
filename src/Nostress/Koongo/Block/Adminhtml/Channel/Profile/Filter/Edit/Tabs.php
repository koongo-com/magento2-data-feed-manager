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
 * Admin page left menu
 *
 * @category Nostress
 * @package Nostress_Koongo
 *
 */

namespace Nostress\Koongo\Block\Adminhtml\Channel\Profile\Filter\Edit;

class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    const BASIC_TAB_GROUP_CODE = 'basic';
    const ADVANCED_TAB_GROUP_CODE = 'advanced';

    /**
     * @var string - uncomment to add BASIC and ADVANCED section
     */
    //protected $_template = 'Magento_Catalog::product/edit/tabs.phtml';

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('page_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Filter Settings'));
    }

    /**
     * Check whether active tab belong to advanced group
     *
     * @return bool
     */
    public function isAdvancedTabGroupActive()
    {
        return $this->_tabs[$this->_activeTab]->getGroupCode() == self::ADVANCED_TAB_GROUP_CODE;
    }

    /**
     * @return $this
     */
    protected function _beforeToHtml()
    {
        $this->addTab(
            'main',
            [
                'label' => __('Stock & Conditions'),
                'title' => __('Stock & Conditions'),
                'active' => true,
                'group_code' => self::BASIC_TAB_GROUP_CODE,
                'content' => $this->getChildHtml('main'),
                'class' => ''
            ]
        );

        $this->addTab(
            'types_variants',
            [
                'label' => __('Variants & Types'),
                'title' => __('Variants & Types'),
                'group_code' => self::ADVANCED_TAB_GROUP_CODE,
                'content' => $this->getChildHtml('types'),
                'class' => ''
            ]
        );

        $this->addTab(
            'visibility',
            [
                'label' => __('Visibility'),
                'title' => __('Visibility'),
                'group_code' => self::BASIC_TAB_GROUP_CODE,
                'content' => $this->getChildHtml('visibility'),
                'class' => ''
            ]
        );

        return parent::_beforeToHtml();
    }
}
