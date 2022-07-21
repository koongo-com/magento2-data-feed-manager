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
namespace Nostress\Koongo\Block\Adminhtml\License\Activate\Trial;

class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * @var \Nostress\Koongo\Model\Api\Client
     */
    protected $_client;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig
     * @param \Magento\Store\Model\System\Store $systemStore
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Nostress\Koongo\Model\Api\Client $client,
        array $data = []
    ) {
        $this->_client = $client;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Init form
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        // must be edit_form because of relation with form container
        $this->setId('edit_form');
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

        $fieldset = $form->addFieldset('base_fieldset', ['expanded'  => true]);

        $fieldset->addField('trial', 'hidden', [
            'name' => 'trial',
            'value' => true
        ]);

        $code = $this->_client->getHelper()->getModuleConfig(\Nostress\Koongo\Helper\Version::TRIAL_CODE);

        $fieldset->addField('code', 'hidden', [
            'name' => 'code',
            'value' => $code
        ]);

        $fieldset->addField('name', 'text', [
            'label' => __('Name:'),
            'class' => 'required-entry',
            'required' => true,
            'name' => 'name'
        ]);

        $fieldset->addField('email', 'text', [
                'label' => __('Email:'),
                'class' => 'required-entry validate-email',
                'required' => true,
                'name' => 'email'
        ]);

        $fieldset->addField('telephone', 'text', [
                'label' => __('Phone:'),
                'class' => 'validate-phone',
                'required' => false,
                'name' => 'telephone'
        ]);

        $helpUrl = $this->_client->getHelper()->getModuleConfig(\Nostress\Koongo\Helper\Version::HELP_FEED_COLLECTIONS);

        $field = $fieldset->addField('collection', 'select', [
                'label' => __('Feed Collection:'),
                'class' => 'required-entry',
                'required' => true,
                'name' => 'collection[]',
                'onchange' => 'changeCollection(this.value)',
                'values' => $this->_client->getAvailableCollectionsAsOptionArray(),
                'note' => __('What is') . ' <a target="_blank" href="' . $helpUrl . '">'
                . __('Feed Collection') . '</a>?'
        ]);
        $field->setAfterElementHtml("<script>
                
            require([
                'jquery',
            ], function ( $)
            {
                var feedsByCountry = " . $this->_client->getAvailableFeedsJson() . ";
                    
                changeCollection = function(selectItem) {
    
                    if( selectItem == '') {
                        $('#feed_link').html( '').attr('disabled', 'disabled');
                    } else {
                        var feeds = feedsByCountry[selectItem];
        
                        var html = '';
                        for (var i = 0; i < feeds.length; i++) {
                            html += '<option value=\"'+feeds[i]['value']+'\">'+feeds[i]['label']+'</option>';
                        }
                        $('#feed_link').html( html).removeAttr('disabled');
                    }
               }
            });
        </script>");

        $feedField = $fieldset->addField('feed_link', 'select', [
            'label' => __('Feed:'),
            'class' => 'required-entry',
            'required' => true,
            'name' => 'feed_link',
            'values' => [],
            'disabled' => 'disabled'
        ]);

        $licenseConditionsLink = $this->_client->getHelper()->getModuleConfig(\Nostress\Koongo\Helper\Version::HELP_LICENSE_CONDITIONS);
        $privacyLink = $this->_client->getHelper()->getModuleConfig(\Nostress\Koongo\Helper\Version::HELP_PRIVACY_POLICY);
        $termsLink = $this->_client->getHelper()->getModuleConfig(\Nostress\Koongo\Helper\Version::HELP_TERMS);
        $note = __(' I agree to the ') . ' <a href="' . $termsLink . '" target="_blank">' . __('Terms') . '</a>';
        $note .= ", " . '<a href="' . $licenseConditionsLink . '" target="_blank">' . __('License Condtions') . '</a>';
        $note .= " and " . __("I understand the") . " " . '<a href="' . $privacyLink . '" target="_blank">' . __('Privacy Policy') . '</a>';

        $fieldset->addField('accept_license_conditions', 'checkbox', [
                'label' => "",
                'note' =>  $note,
                'class' => 'required-entry',
                'name' => 'accept_license_conditions',
                'value' => 0,
                'onclick' => 'this.value = this.checked ? 1 : 0;',
                'required' => true,
        ]);

        $fieldset->addField('submit', 'submit', [
            'value' => __('Activate Trial'),
            'class' => 'action-primary abs-action-l',
            'name' => 'submit',
            'label' => ""
        ]);

        // data after error in save
        $data = $this->_coreRegistry->registry('edit_form');
        if ($data) {
            if (!empty($data[ 'collection'])) {
                $feedField->setValues($this->_client->getAvailableFeedsAsOptionArray($data[ 'collection'][0]));
                $feedField->setData('disabled', '');
            }

            $form->setValues($data);
        }

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
