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
 * Block for category filter settings
 *
 * @category Nostress
 * @package Nostress_Koongo
 *
 */
namespace Nostress\Koongo\Block\Adminhtml\Channel\Profile\Filter\Edit\Tab\Main;

use Nostress\Koongo\Model\Channel\Profile;

class Categories extends \Magento\Backend\Block\Template
{
    protected $_tooltip = 'filter_categories';

    /**
     * @var string
     */
    protected $_template = 'Nostress_Koongo::koongo/channel/profile/filter/main/categories.phtml';

    /**
     *  @var $model \Nostress\Koongo\Model\Channel\Profile
     **/
    protected $profile;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_registry = $registry;
        $this->profile = $this->_registry->registry('koongo_channel_profile');
    }

    public function getCategoryTreeHtml()
    {
        $treeBlock = $this->getChildBlock("category_checkboxes_tree");
        $treeBlock->setStore($this->profile->getStoreId());
        $treeBlock->setJsFormObject($this->getFieldsetName());
        $treeBlock->setCategoriesFilterInputId($this->getCategoriesFilterInputId());

        $categoryIds = $this->getCategoryIdsString();
        if (empty($categoryIds)) {
            $categoryIds = [];
        } else {
            $categoryIds = explode(",", $categoryIds);
        }
        $treeBlock->setCategoryIds($categoryIds);

        return $treeBlock->toHtml();
    }

    public function getCategoryIdsString()
    {
        $categoryIds = $this->profile->getConfigItem(Profile::CONFIG_FILTER, false, Profile::CONFIG_FILTER_CATEGORIES);
        if (!isset($categoryIds)) {
            $categoryIds = "";
        }
        return $categoryIds;
    }

    public function getFieldsetName()
    {
        return "categories_fieldset";
    }

    public function getCategoriesFilterInputName()
    {
        return Profile::CONFIG_FILTER . '[categories]';
    }

    public function getCategoriesFilterInputId()
    {
        return 'nsc_koongo_filter_categories';
    }

    public function getTooltip()
    {
        return $this->profile->helper->renderTooltip($this->_tooltip);
    }
}
