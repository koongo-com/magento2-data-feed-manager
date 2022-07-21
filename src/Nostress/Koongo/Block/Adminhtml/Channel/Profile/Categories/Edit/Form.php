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
 * Adminhtml koongo channel categories form block
 *
 * @category Nostress
 * @package Nostress_Koongo
 *
 */

namespace Nostress\Koongo\Block\Adminhtml\Channel\Profile\Categories\Edit;

use Nostress\Koongo\Model\Channel\Profile;

class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * @var \Magento\Backend\Block\Widget\Form\Renderer\Fieldset
     */
    protected $_rendererFieldset;

    /**
     * @var \Magento\Rule\Block\Conditions
     */
    protected $_conditions;

    /**
     * @var \Nostress\Koongo\Model\Rule
     */
    protected $rule;

    /**
     * @var \Magento\Config\Model\Config\Source\Locale
     */
    protected $localeSource;

    /**
     *
     * @var \Nostress\Koongo\Helper
     */
    protected $helper;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Rule\Block\Conditions $conditions
     * @param \Magento\Backend\Block\Widget\Form\Renderer\Fieldset $rendererFieldset
     * @param \Nostress\Koongo\Model\Rule $rule
     * @param \Magento\Config\Model\Config\Source\Locale $localeSource,
     * @param \Nostress\Koongo\Helper\Data $helper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Rule\Block\Conditions $conditions,
        \Magento\Backend\Block\Widget\Form\Renderer\Fieldset $rendererFieldset,
        \Nostress\Koongo\Model\Rule $rule,
        \Magento\Config\Model\Config\Source\Locale $localeSource,
        \Nostress\Koongo\Helper\Data $helper,
        array $data = []
    ) {
        $this->rule = $rule;
        $this->_conditions = $conditions;
        $this->_rendererFieldset = $rendererFieldset;
        $this->localeSource = $localeSource;
        $this->helper = $helper;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare form
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(
            ['data' => ['id' => 'edit_form', 'action' => $this->getData('action'), 'method' => 'post']]
        );
        $form->setUseContainer(true);

        /* @var $model \Nostress\Koongo\Model\Channel\Profile */
        $model = $this->_coreRegistry->registry('koongo_channel_profile');
        $channelLabel = $model->getChannel()->getLabel();

        /*
         * Checking if user have permissions to save information
        */
        if ($this->_isAllowedAction('Nostress_Koongo::save')) {
            $isElementDisabled = false;
        } else {
            $isElementDisabled = true;
        }

        //Prefix must be rule_ to force conditions filter work properly
        $form->setHtmlIdPrefix('channel_profile_category_');

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Locale Switcher'),'expanded'  => true]);

        if ($model->getId()) {
            $fieldset->addField('entity_id', 'hidden', ['name' => 'entity_id']);
        }

        $taxonomyModel = $model->getFeed()->getTaxonomy();
        $storeLocale = $this->helper->getStoreConfig($model->getStoreId(), \Nostress\Koongo\Helper\Data::PATH_STORE_LOCALE);
        $availabelLocales = $taxonomyModel->getAvailableLocales();

        //Load current locale - load from profile -> load from store -> set default
        $currentLocale = $model->getConfigItem(Profile::CONFIG_GENERAL, false, 'taxonomy_locale');
        if (empty($currentLocale) && in_array($storeLocale, $availabelLocales)) {
            $currentLocale = $storeLocale;
        }
        if (empty($currentLocale)) {
            $currentLocale = $taxonomyModel->getDefaultLocale();
        }

        $localeOptions= $this->getLocaleOptions($availabelLocales, $storeLocale);

        $disable = false;
        $note = __("Category mapping rules table is reloaded when selection is changed.");
        if (count($availabelLocales) <= 1) {
            $disable = true;
            $note = __("Categories in different locales not available.");
        }

        $localeCode = "locale";
        if ($disable) {
            //Add hidden input if main locale input is disabled
            $fieldset->addField('locale', 'hidden', ['name' => Profile::CONFIG_GENERAL . '[taxonomy_locale]']);
            $localeCode = "locale_disabled";
        }
        $fieldset->addField(
            $localeCode,
            'select',
            [
                'name' => Profile::CONFIG_GENERAL . '[taxonomy_locale]',
                'label' => __('%1 Category Locale', $channelLabel),
                'title' => __('%1 Category Locale', $channelLabel),
                'required' => false,
                'options' => $localeOptions,
                'disabled' => $disable,
                'note' => $note
                ]
        );

        $this->_eventManager->dispatch('adminhtml_koongo_channel_profile_categories_edit_tab_main_prepare_form', ['form' => $form]);

        $data = [];
        $data['locale'] = $currentLocale;
        $data['entity_id'] = $model->getId();

        $form->setValues($data);
        $this->setForm($form);
        return parent::_prepareForm();
    }

    protected function getLocaleOptions($availableOptions, $defaultStoreLocale)
    {
        sort($availableOptions);
        $localeOptions = $this->localeSource->toOptionArray();

        $localeOptionsIndexed = [];
        foreach ($localeOptions as $data) {
            $localeOptionsIndexed[$data['value']] = $data['label'];
        }

        $options = [];

        foreach ($availableOptions as $availableOptionItem) {
            if ($availableOptionItem == 'all') {
                $options[$availableOptionItem] = __("Default");
            } elseif (isset($localeOptionsIndexed[$availableOptionItem])) {
                $options[$availableOptionItem] = $localeOptionsIndexed[$availableOptionItem];
            }
        }

        if (isset($options[$defaultStoreLocale])) {
            $defItem = $options[$defaultStoreLocale];
            $defItem .= __(" - Recommended");
            unset($options[$defaultStoreLocale]);
            $options = array_merge([$defaultStoreLocale => $defItem], $options);
        }
        return $options;
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
