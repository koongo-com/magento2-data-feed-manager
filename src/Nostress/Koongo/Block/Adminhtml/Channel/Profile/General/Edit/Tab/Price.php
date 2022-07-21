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
 * Channel profile feed settings edit form price and date format tab
 *
 * @category Nostress
 * @package Nostress_Koongo
 *
 */

namespace Nostress\Koongo\Block\Adminhtml\Channel\Profile\General\Edit\Tab;

use Nostress\Koongo\Model\Channel\Profile;

class Price extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /*
     * @var \Nostress\Koongo\Model\Config\Source\Datetimeformat
    */
    protected $datetimeSource;

    /*
     * @var \Nostress\Koongo\Model\Config\Source\Priceformat
     */
    protected $priceformatSource;

    /*
     * @var \Nostress\Koongo\Model\Config\Source\Decimaldelimiter
    */
    protected $decimaldelimiterSource;

    /**
     * Customer Group
     *
     * @var \Magento\Customer\Model\ResourceModel\Group\Collection
     */
    protected $_customerGroup;

    /**
     *
     * @var \Nostress\Koongo\Helper
     */
    protected $helper;

    /**
     * @var \Nostress\Koongo\Block\Widget\Form\Renderer\Fieldset
     */
    protected $_rendererFieldset;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Store\Model\System\Store $systemStore
     * @param array $data
     * @param \Nostress\Koongo\Model\Config\Source\Datetimeformat $datetimeSource
     * @param\Nostress\Koongo\Model\Config\Source\Priceformat $priceformatSource
     * @param \Nostress\Koongo\Model\Config\Source\Decimaldelimiter $decimaldelimiterSource
     * @param \Magento\Customer\Model\ResourceModel\Group\Collection $customerGroup
     * @param \Nostress\Koongo\Helper\Data $helper
     * @param \Nostress\Koongo\Block\Widget\Form\Renderer\Fieldset $rendererFieldset
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Nostress\Koongo\Model\Config\Source\Datetimeformat $datetimeSource,
        \Nostress\Koongo\Model\Config\Source\Priceformat $priceformatSource,
        \Nostress\Koongo\Model\Config\Source\Decimaldelimiter $decimaldelimiterSource,
        \Magento\Customer\Model\ResourceModel\Group\Collection $customerGroup,
        \Nostress\Koongo\Helper\Data $helper,
        \Nostress\Koongo\Block\Widget\Form\Renderer\Fieldset $rendererFieldset,
        array $data = []
    ) {
        $this->datetimeSource = $datetimeSource;
        $this->priceformatSource = $priceformatSource;
        $this->decimaldelimiterSource = $decimaldelimiterSource;
        $this->_customerGroup = $customerGroup;
        $this->helper = $helper;
        $this->_rendererFieldset = $rendererFieldset;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare form
     *
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareForm()
    {
        /* @var $model \Nostress\Koongo\Model\Channel\Profile */
        $model = $this->_coreRegistry->registry('koongo_channel_profile');
        $config = $model->getConfigItem(Profile::CONFIG_FEED, true, Profile::CONFIG_COMMON);
        $channelLabel = $model->getChannel()->getLabel();

        /*
         * Checking if user have permissions to save information
         */
        if ($this->_isAllowedAction('Nostress_Koongo::save')) {
            $isElementDisabled = false;
        } else {
            $isElementDisabled = true;
        }

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('channel_profile_');

        $fieldset = $form->addFieldset('price_fieldset', [
            'legend' => __('Price and Date Format') . $model->helper->renderTooltip('advanced_price_and_date'),
        ])->setRenderer($this->_rendererFieldset);

        $fieldset->addField(
            Profile::CONFIG_PRICE_CUSTOMER_GROUP_ID,
            'select',
            [
                'name' => Profile::CONFIG_FEED . '[' . Profile::CONFIG_COMMON . '][' . Profile::CONFIG_PRICE_CUSTOMER_GROUP_ID . ']',
                'label' => __('Customer Group'),
                'title' => __('Customer Group'),
                'required' => false,
                'options' => $this->_getCustomerGroupsAsIndexedArray(),
                'disabled' => $isElementDisabled,
                'note' => __('Customer group for product price export.')
            ]
        );

        $fieldset->addField(
            Profile::CONFIG_CURRENCY,
            'select',
            [
                'name' => Profile::CONFIG_FEED . '[' . Profile::CONFIG_COMMON . ']' . '[' . Profile::CONFIG_CURRENCY . ']',
                'label' => __('Currency'),
                'title' => __('Currency'),
                'required' => false,
                'options' => $this->helper->getStoreCurrenciesOptionArray($model->getStoreId()),
                'disabled' => $isElementDisabled,
                'note' => __("Choose from currencies allowed for current store.")
            ]
        );

        $fieldset->addField(
            Profile::CONFIG_DECIMAL_DELIMITER,
            'select',
            [
                'name' => Profile::CONFIG_FEED . '[' . Profile::CONFIG_COMMON . '][' . Profile::CONFIG_DECIMAL_DELIMITER . ']',
                'label' => __('Decimal Delimiter'),
                'title' => __('Decimal Delimiter'),
                'required' => false,
                'options' => $this->decimaldelimiterSource->toIndexedArray(),
                'disabled' => $isElementDisabled,
                'note' => __('Delitimiter of decimal numbers, in most cases use default value "dot".')
            ]
        );

        $fieldset->addField(
            Profile::CONFIG_PRICE_FORMAT,
            'select',
            [
                'name' => Profile::CONFIG_FEED . '[' . Profile::CONFIG_COMMON . '][' . Profile::CONFIG_PRICE_FORMAT . ']',
                'label' => __('Price Format'),
                'title' => __('Price Format'),
                'required' => false,
                'options' => $this->priceformatSource->toIndexedArray(),
                'disabled' => $isElementDisabled,
                'note' => __("Format of the exported price attribute values.")
               ]
        );

        $fieldset->addField(
            Profile::CONFIG_DATETIME_FORMAT,
            'select',
            [
                'name' => Profile::CONFIG_FEED . '[' . Profile::CONFIG_COMMON . '][' . Profile::CONFIG_DATETIME_FORMAT . ']',
                'label' => __('Datetime Format '),
                'title' => __('Datetime Format '),
                'required' => false,
                'options' => $this->datetimeSource->toIndexedArray(),
                'disabled' => $isElementDisabled,
                'note' => __("Format of the date and time used in the feed.")
            ]
        );

        $this->_eventManager->dispatch('adminhtml_koongo_channel_profile_general_edit_tab_price_prepare_form', ['form' => $form]);

        $data = [];
        $fields = [Profile::CONFIG_CURRENCY,
                Profile::CONFIG_DECIMAL_DELIMITER,
                Profile::CONFIG_PRICE_FORMAT,
                Profile::CONFIG_PRICE_CUSTOMER_GROUP_ID,
                Profile::CONFIG_DATETIME_FORMAT];

        foreach ($fields as $field) {
            if (isset($config[$field])) {
                $data[$field] = $config[$field];
            }
        }

        $form->setValues($data);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Prepare label for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('Price and Date Format');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Price and Date Format');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Check permission for passed action
     *
     * @param string $resourceId
     * @return bool
     */
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }

    /**
     * Get customer groups as indexed array
     *
     * @return array
     */
    protected function _getCustomerGroupsAsIndexedArray()
    {
        $customerGroups = $this->_customerGroup->load();
        $indexedArray = [];
        foreach ($customerGroups as $key => $group) {
            $customerGroupId = $group->getCustomerGroupId();
            if (isset($customerGroupId) && $customerGroupId != "") {
                $indexedArray[$customerGroupId] = $group->getCustomerGroupCode();
            }
        }
        return $indexedArray;
    }
}
