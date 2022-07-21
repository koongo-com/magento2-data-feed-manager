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
 * Channel profile feed settings edit form shipping cost tab
 *
 * @category Nostress
 * @package Nostress_Koongo
 *
 */

namespace Nostress\Koongo\Block\Adminhtml\Channel\Profile\General\Edit\Tab;

use Nostress\Koongo\Model\Channel\Profile;

class Shippingcost extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /*
     * @var \Nostress\Koongo\Model\Config\Source\Attributes
    */
    protected $attributeSource;

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
     * @param \Nostress\Koongo\Model\Config\Source\Attributes $attributeSource
     * @param \Nostress\Koongo\Block\Widget\Form\Renderer\Fieldset $rendererFieldset
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Nostress\Koongo\Model\Config\Source\Attributes $attributeSource,
        \Nostress\Koongo\Block\Widget\Form\Renderer\Fieldset $rendererFieldset,
        array $data = []
    ) {
        $this->attributeSource = $attributeSource;
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

        $fieldset = $form->addFieldset('shipping_cost_fieldset', [
            'legend' => __('Shipping Cost') . $model->helper->renderTooltip('shipping_cost'),
        ])->setRenderer($this->_rendererFieldset);

        $fieldset->addField(
            Profile::CONFIG_SHIPPING_METHOD_NAME,
            'text',
            [
                'name' => Profile::CONFIG_FEED . '[' . Profile::CONFIG_COMMON . ']' . '[' . Profile::CONFIG_SHIPPING . '][' . Profile::CONFIG_SHIPPING_METHOD_NAME . ']',
                'label' => __('Shipping Method Name'),
                'title' => __('Shipping Method Name'),
                'required' => false,
                'disabled' => $isElementDisabled
            ]
        );

        $fieldset->addField(
            Profile::CONFIG_SHIPPING_DEPENDENT_ATTRIBUTE,
            'select',
            [
                'name' => Profile::CONFIG_FEED . '[' . Profile::CONFIG_COMMON . ']' . '[' . Profile::CONFIG_SHIPPING . '][' . Profile::CONFIG_SHIPPING_DEPENDENT_ATTRIBUTE . ']',
                'label' => __('Shipping Costs Dependent on'),
                'title' => __('Shipping Costs Dependent on'),
                'required' => false,
                'onchange' => 'updateShippingTable(this)',
                'options' => $this->attributeSource->toIndexedArray($model->getStoreId(), $model->getTaxonomyLabel()),
                'disabled' => $isElementDisabled,
                'note' => __("Usually Price or Weight.")
            ]
        );

        $this->_eventManager->dispatch('adminhtml_koongo_channel_profile_general_edit_tab_shippingcost_prepare_form', ['form' => $form]);

        $data = [];
        if (isset($config[Profile::CONFIG_SHIPPING])) {
            $data = $config[Profile::CONFIG_SHIPPING];
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
        return __('Shipping cost');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Shipping cost');
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
}
