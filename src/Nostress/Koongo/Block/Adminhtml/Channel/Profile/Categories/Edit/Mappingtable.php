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
 * BLock for products to channel category mapping  form tab
 *
 * @category Nostress
 * @package Nostress_Koongo
 *
 */
namespace Nostress\Koongo\Block\Adminhtml\Channel\Profile\Categories\Edit;

class Mappingtable extends \Magento\Backend\Block\Template
{

    /**
     * @var string
     */
    protected $_template = 'Nostress_Koongo::koongo/channel/profile/categories/mapping_table.phtml';

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_registry;

    /**
     *  @var $model \Nostress\Koongo\Model\Channel\Profile
     **/
    protected $profile;

    /**
     * @var \Nostress\Koongo\Model\Channel\Profile\Categories
     */
    protected $categoriesModel;

    protected $categoriesInfoJson = null;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Nostress\Koongo\Model\Channel\Profile\Categories $categoriesModel
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Nostress\Koongo\Model\Channel\Profile\Categories $categoriesModel,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_registry = $registry;
        $this->profile = $this->_registry->registry('koongo_channel_profile');

        $this->categoriesModel = $categoriesModel;
        $this->categoriesModel->initProfile($this->profile);
    }

    /**
     * {@inheritdoc}
     */
    public function getComponentName()
    {
        if (null === $this->getData('component_name')) {
            $this->setData('component_name', $this->getNameInLayout());
        }
        return $this->getData('component_name');
    }

    public function getProfile()
    {
        return $this->profile;
    }

    public function getChannelLabel()
    {
        return $this->profile->getFeed()->getChannel()->getLabel();
    }

    /**
     * Get attribute name for knockout
     * @param unknown_type $name
     * @return string
     */
    public function getRuleInputNameKO($name)
    {
        $prefix = 'rules[';
        $suffix = "][{$name}]";
        return $value = "'{$prefix}'+ " . '$index()' . " +'{$suffix}'";
    }

    public function getCategoryTreeHtml()
    {
        $treeBlock = $this->getChildBlock("magento_category_checkboxes_tree");
        $treeBlock->setStore($this->profile->getStoreId());
        $treeBlock->setJsFormObject($this->getFieldsetName());
        $treeBlock->setCategoriesFilterInputId($this->getCategoriesFilterInputId());
        $treeBlock->setChannelCategoriesSaerchInputId($this->getChannelCategoriesSaerchInputId());

        $categoryIds = $this->getCategoryIdsString();
        if (empty($categoryIds)) {
            $categoryIds = [];
        } else {
            $categoryIds = explode(",", $categoryIds);
        }
        $treeBlock->setCategoryIds($categoryIds);
        $this->categoriesInfoJson = $treeBlock->getCategoriesInfoJson();

        return $treeBlock->toHtml();
    }

    public function getCategoriesInfoJson()
    {
        return $this->categoriesInfoJson;
    }

    public function getCategoryIdsString()
    {
        $categoryIds = "";
        return $categoryIds;
    }

    public function getFieldsetName()
    {
        return "magento_categories_fieldset";
    }

    public function getCategoriesFilterInputName()
    {
        return 'nsc_koongo_magento_categories_tree';
    }

    public function getCategoriesFilterInputId()
    {
        return 'nsc_koongo_magento_categories_tree';
    }

    public function getChannelCategoriesSaerchInputId()
    {
        return 'nsc_koongo_channel_categories_search';
    }

    public function getHelp($key)
    {
        return $this->profile->helper->getHelp($key);
    }

    public function getTooltip($key)
    {
        return $this->profile->helper->renderTooltip($key);
    }
}
