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
 * Channel profile feed settings edit form main tab
 *
 * @category Nostress
 * @package Nostress_Koongo
 *
 */

namespace Nostress\Koongo\Block\Adminhtml\Channel\Profile\General\Edit\Tab;

use Nostress\Koongo\Model\Channel\Profile;

class Main extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{

    /**
     * Prefix for custom parameters inputs
     * @var String
     */
    const CUSTOM_PARAM_INPUTNAME_PREFIX = "cust_param_";

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
     * @param \Nostress\Koongo\Block\Widget\Form\Renderer\Fieldset $rendererFieldset
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Nostress\Koongo\Block\Widget\Form\Renderer\Fieldset $rendererFieldset,
        array $data = []
    ) {
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

        $fieldset = $form->addFieldset('base_fieldset', [
            'legend' => __('%1 Specific Settings', $channelLabel) . $model->helper->renderTooltip('attributes_mapping_channel_specific'),
            'expanded'  => true,
        ])->setRenderer($this->_rendererFieldset);

        if ($model->getId()) {
            $fieldset->addField('entity_id', 'hidden', ['name' => 'entity_id']);
        }

        $fieldset->addField(
            'name',
            'text',
            [
                'name' => 'name',
                'label' => __('Profile Name'),
                'title' => __('Profile Name'),
                'required' => true,
                'disabled' => $isElementDisabled
            ]
        );

        $containMediaGallery = $model->getFeed()->containMediaGallery();
        $note = __("Allows you to export all images from Media Gallery associated with given product.");
        $disabled = $isElementDisabled;
        $value = 1;
        if (!$containMediaGallery) {
            $note = __("<strong>Not available for this feed.</strong>") . " " . $note;
            $disabled = 1;
            $value = 0;
        }

        $fieldset->addField(
            'all_images',
            'select',
            [
                'name' => Profile::CONFIG_FEED . '[' . Profile::CONFIG_COMMON . ']' . '[' . Profile::CONFIG_ALL_IMAGES . ']',
                'label' => __('Export All Images'),
                'title' => __('Export All Images'),
                'required' => false,
                'disabled' => $disabled,
                'options' => [
                                1 => __('Yes'),
                                Profile::CONFIG_MULTI_ATRIBS_OPTION_EXPORT_PARENT_VALUE => __('Yes') . " - " . __('Export parent product images for variants'),
                                Profile::CONFIG_MULTI_ATRIBS_OPTION_EXPORT_PARENT_VALUE_IF_EMPTY => __('Yes') . " - " . __('Export parent product images for variants (if missing own images)'),
                                0 => __('No'),
                               ],
                'value' => $value,
                'note' => $note
            ]
        );

        $containMulipleCategories = $model->getFeed()->containMulipleCategories();
        $note = __("Allows you to export all associated categories for given product.");
        $disabled = $isElementDisabled;
        $value = 1;
        if (!$containMulipleCategories) {
            $note = __("<strong>Not available for this feed.</strong>") . " " . $note;
            $disabled = 1;
            $value = 0;
        }

        $fieldset->addField(
            'all_categories',
            'select',
            [
                'name' => Profile::CONFIG_FEED . '[' . Profile::CONFIG_COMMON . ']' . '[' . Profile::CONFIG_ALL_CATEGORIES . ']',
                'label' => __('Export All Categories'),
                'title' =>  __('Export All Categories'),
                'required' => false,
                'disabled' => $disabled,
                'options' => [
                                1 => __('Yes'),
                                Profile::CONFIG_MULTI_ATRIBS_OPTION_EXPORT_PARENT_VALUE => __('Yes') . " - " . __('Export parent product categories for variants'),
                                Profile::CONFIG_MULTI_ATRIBS_OPTION_EXPORT_PARENT_VALUE_IF_EMPTY => __('Yes') . " - " . __('Export parent product categories for variants (if missing own categories)'),
                                0 => __('No')
                                ],
                "value" => $value,
                'note' => $note
                ]
        );

        $containTierPrices = $model->getFeed()->containTierPrices();
        $note = __("Allows you to export tier prices for given product. Teir price export is dependent on selected feed template.");
        $disabled = $isElementDisabled;
        $value = 1;
        if (!$containTierPrices) {
            $note = __("<strong>Not available for this feed.</strong>") . " " . $note;
            $disabled = 1;
            $value = 0;
        }

        $fieldset->addField(
            'tier_prices',
            'select',
            [
                'name' => Profile::CONFIG_FEED . '[' . Profile::CONFIG_COMMON . ']' . '[' . Profile::CONFIG_TIER_PRICES . ']',
                'label' => __('Export Tier Prices'),
                'title' =>  __('Export Tier Prices'),
                'required' => false,
                'disabled' => $disabled,
                'options' => [
                                1 => __('Yes'),
                                Profile::CONFIG_MULTI_ATRIBS_OPTION_EXPORT_PARENT_VALUE => __('Yes') . " - " . __('Export parent product tier prices for variants'),
                                Profile::CONFIG_MULTI_ATRIBS_OPTION_EXPORT_PARENT_VALUE_IF_EMPTY => __('Yes') . " - " . __('Export parent product tier prices for variants (if missing own tier prices)'),
                                0 => __('No')
                                ],
                "value" => $value,
                'note' => $note
                ]
        );

        $customParamsData = [];
        if (!empty($config[Profile::CUSTOM_PARAMS]) && is_array($config[Profile::CUSTOM_PARAMS])) {
            $customParamsSetup = $model->getFeed()->getCustomParamsAsCodeIndexedArray();

            foreach ($config[Profile::CUSTOM_PARAMS] as $code => $value) {
                $filedName = self::CUSTOM_PARAM_INPUTNAME_PREFIX . $code;
                $customParamsData[$filedName] = $value;

                $label = $code;
                if (isset($customParamsSetup[$code]['label'])) {
                    $label = $customParamsSetup[$code]['label'];
                }

                $description = "";
                if (isset($customParamsSetup[$code]['description'])) {
                    $description = $customParamsSetup[$code]['description'];
                }

                $fieldset->addField(
                    $filedName,
                    'text',
                    [
                        'name' => Profile::CONFIG_FEED . '[' . Profile::CONFIG_COMMON . ']' . '[' . Profile::CUSTOM_PARAMS . '][' . $code . ']',
                        'label' => $label,
                        'title' => $label,
                        'required' => false,
                        'disabled' => $isElementDisabled,
                        'note' => __("%1 parameter.", $channelLabel) . " " . $description
                    ]
                );
            }
        }

//         $customParams = $this->getAttributeValue("common", "custom_params");
//         if(isset($customParams) && is_array($customParams) && array_key_exists("param",$customParams))
//         {
//         	$customParams = $customParams["param"];
//         	if (count($customParams) > 0 && is_array($customParams)) {
//         		$index = 0;
//         		foreach ($customParams as $parameter)
//         		{
//         			$label = $this->arrayField($parameter,"label");
//         			if(!isset($label))
//         				continue;
//         			$fieldset->addField('nscexport_'.$this->arrayField($parameter,"code"),$this->arrayField($parameter,"format"), array(
//         					'label' => Mage::helper('nscexport')->__($label),
//         					'name' => self::COMMON_PATH."[custom_params][param][".$index."][value]",
//         					'note' => Mage::helper('nscexport')->__($this->arrayField($parameter,"description")),
//         					'value' => $this->arrayField($parameter,"value")
//         			));
//         			$index++;
//         		}
//         	}
//         }

        $this->_eventManager->dispatch('adminhtml_koongo_channel_profile_general_edit_tab_main_prepare_form', ['form' => $form]);

        $data = $config;
        $data['entity_id'] = $model->getId();
        $data['name'] = $model->getName();
        $data = array_merge($data, $customParamsData);

        if (!$containMediaGallery) {
            $data[Profile::CONFIG_ALL_IMAGES] = 0;
        }
        if (!$containMulipleCategories) {
            $data[Profile::CONFIG_ALL_CATEGORIES] = 0;
        }
        if (!$containTierPrices) {
            $data[Profile::CONFIG_TIER_PRICES] = 0;
        }

        // var_dump($data); exit();
        $form->setValues($data);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Prepare the layout.
     *
     * @return $this
     */
//     protected function _prepareLayout()
//     {
//     	$this->setChild(
//     			'categories',//'grid',
//     			$this->getLayout()->createBlock(
//     					//'Magento\Catalog\Block\Adminhtml\Product\Helper\Form\Category',
//     					'Magento\Catalog\Block\Adminhtml\Category\Checkboxes\Tree',
//     					//'Nostress\Koongo\Block\Adminhtml\Channel\Profile\General\Edit\Tab\Main\Grid',
//     					'attributes.categories'
//     			)->setCategoryIds([1, 4, 7, 56, 2])->setJsFormObject("rule_conditions_fieldset")->setStore(1)
//     	);
//     	parent::_prepareLayout();
//     	return $this;
//     }

    /**
     * @return string
     */
//     protected function _toHtml()
//     {
//     	if ($this->canShowTab()) {
//     		$this->_prepareForm(); //initForm

//     		return $this->getChildHtml('categories').parent::_toHtml();
//     	} else {
//     		return '';
//     	}
//     }

    /**
     * Prepare label for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('Attributes mapping');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Attributes mapping - TAB');
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
