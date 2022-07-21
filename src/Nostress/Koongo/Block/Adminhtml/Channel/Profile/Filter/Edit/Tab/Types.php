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
 * Channel profile filter settings edit form types and variants format tab
 *
 * @category Nostress
 * @package Nostress_Koongo
 *
 */

namespace Nostress\Koongo\Block\Adminhtml\Channel\Profile\Filter\Edit\Tab;

use Nostress\Koongo\Model\Channel\Profile;

class Types extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * Product types sort order
     * @var unknown_type
     */
    protected $_typesSortOrder = ['simple' => 0,'configurable' => 1,'bundle' => 2,'grouped' => 3];

    /*
     * @var \Nostress\Koongo\Model\Config\Source\Parentschilds
    */
    protected $parentschildsSource;

    /*
     * @var \Magento\Catalog\Model\Product\Type
     */
    protected $productTypeSource;

    /**
     * @var \Nostress\Koongo\Block\Widget\Form\Renderer\Fieldset
     */
    protected $_rendererFieldset;

    /**
     *
     * @var \Nostress\Koongo\Helper
     */
    protected $helper;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Store\Model\System\Store $systemStore
     * @param array $data
     * @param \Nostress\Koongo\Model\Config\Source\Parentschilds $parentschildsSource,
     * @param \Magento\Catalog\Model\Product\Type $productTypeSource,
     * @param \Nostress\Koongo\Helper\Data $helper
     * @param \Nostress\Koongo\Block\Widget\Form\Renderer\Fieldset $rendererFieldset
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Nostress\Koongo\Model\Config\Source\Parentschilds $parentschildsSource,
        \Magento\Catalog\Model\Product\Type $productTypeSource,
        \Nostress\Koongo\Helper\Data $helper,
        \Nostress\Koongo\Block\Widget\Form\Renderer\Fieldset $rendererFieldset,
        array $data = []
    ) {
        $this->parentschildsSource = $parentschildsSource;
        $this->productTypeSource = $productTypeSource;
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
        $config = $model->getConfigItem(Profile::CONFIG_FILTER, true);

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

        $form->setHtmlIdPrefix('channel_profile_filter_');

        $fieldset = $form->addFieldset('variants_fieldset', [
            'legend' => __('Variants & Types') . $model->helper->renderTooltip('filter_variants')
        ])->setRenderer($this->_rendererFieldset);

        $fieldset->addField(
            'parents_childs',
            'select',
            [
                'name' => Profile::CONFIG_FILTER . '[parents_childs]',
                'label' => __('Product Variants (Child Products)'),
                'title' => __('Product Variants (Child Products)'),
                'required' => false,
                'options' => $this->parentschildsSource->toIndexedArray(),
                'disabled' => $isElementDisabled,
                'note' => __('Option importatnt only for Configurable and Grouped products.')
            ]
        );

        $fieldset->addField(
            'types',
            'multiselect',
            [
                'name' => Profile::CONFIG_FILTER . '[types]',
                'label' => __('Product Types'),
                'title' => __('Product Types'),
                'required' => false,
                'values'   => $this->getSortedProductTypes(),
                'disabled' => $isElementDisabled,
                'note' => __('Choose which type of products you wish to export.')
            ]
        );

        $this->_eventManager->dispatch('adminhtml_koongo_channel_profile_filter_edit_tab_types_prepare_form', ['form' => $form]);

        $data = [];
        $fields = ['parents_childs','types'];

        foreach ($fields as $field) {
            if (isset($config[$field])) {
                $data[$field] = $config[$field];
            }
        }

        if (empty($data['types'])) {
            $data['types'] = array_keys($this->productTypeSource->getOptionArray());
        }

        $form->setValues($data);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
    * Returns sorted product type options
     */
    protected function getSortedProductTypes()
    {
        $typeOptions = $this->productTypeSource->getOptions();
        $sortedOptions = [];
        $counter = 0;
        $sortOrderSize = count($this->_typesSortOrder);

        foreach ($typeOptions as $option) {
            $counter++;
            if (!isset($option['value'])) {
                continue;
            }
            $index = $option['value'];

            if (isset($this->_typesSortOrder[$index])) {
                $sortedOptions[$this->_typesSortOrder[$index]] = $option;
            } else {
                $sortedOptions[$counter+$sortOrderSize] = $option;
            }
        }

        ksort($sortedOptions);
        return $sortedOptions;
    }

    /**
     * Prepare label for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('Variants & Types');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Variants & Types');
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
