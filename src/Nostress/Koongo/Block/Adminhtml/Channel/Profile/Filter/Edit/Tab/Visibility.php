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

class Visibility extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /*
     * @var \Magento\Catalog\Model\Product\Visibility
    */
    protected $visibilitySource;

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
     * @param \Magento\Catalog\Model\Product\Visibility $visibilitySource
     * @param \Nostress\Koongo\Helper\Data $helper
     * @param \Nostress\Koongo\Block\Widget\Form\Renderer\Fieldset $rendererFieldset
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Catalog\Model\Product\Visibility $visibilitySource,
        \Nostress\Koongo\Helper\Data $helper,
        \Nostress\Koongo\Block\Widget\Form\Renderer\Fieldset $rendererFieldset,
        array $data = []
    ) {
        $this->visibilitySource = $visibilitySource;
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

        $fieldset = $form->addFieldset('visibility_fieldset', [
            'legend' => __('Visibility') . $model->helper->renderTooltip('filter_visibility'),
        ])->setRenderer($this->_rendererFieldset);

        $fieldset->addField(
            'visibility',
            'multiselect',
            [
                'name' => Profile::CONFIG_FILTER . '[visibility]',
                'label' => __('General Product Visiblity'),
                'title' => __('General Product Visiblity'),
                'required' => false,
                'values'   => $this->visibilitySource->toOptionArray(),
                'disabled' => $isElementDisabled,
                'note' => __(' Default setup is in the moses cases the most appropriate.')
            ]
        );

        $fieldset->addField(
            'visibility_parent',
            'multiselect',
            [
                'name' => Profile::CONFIG_FILTER . '[visibility_parent]',
                'label' => __('Parent Product Visiblity'),
                'title' => __('Parent Product Visiblity'),
                'required' => false,
                'values'   => $this->visibilitySource->toOptionArray(),
                'disabled' => $isElementDisabled,
                'note' => __(' Default setup is in the moses cases the most appropriate.')
            ]
        );

        $this->_eventManager->dispatch('adminhtml_koongo_channel_profile_filter_edit_tab_visibility_prepare_form', ['form' => $form]);

        $data = [];
        $fields = ['visibility','visibility_parent'];

        foreach ($fields as $field) {
            if (isset($config[$field])) {
                $data[$field] = $config[$field];
            }
        }

        if (empty($data['visibility'])) {
            $data['visibility'] = array_keys($this->visibilitySource->getOptionArray());
        }

        //fill all posibilities if empty
//         if(empty($data['visibility_parent']) )
//         	$data['visibility_parent'] = array_keys($this->visibilitySource->getOptionArray());

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
        return __('Visibility');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Visibility');
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
