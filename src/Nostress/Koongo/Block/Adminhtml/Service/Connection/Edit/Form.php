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
namespace Nostress\Koongo\Block\Adminhtml\Service\Connection\Edit;

class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
    const DATA_CONSUMER_PASSWORD = 'current_password';

    /**
     * @var \Nostress\Koongo\Helper\Data\Service
     */
    protected $_helper = null;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Nostress\Koongo\Helper\Data $helper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Nostress\Koongo\Helper\Data\Service $helper,
        array $data = []
    ) {
        $this->_helper = $helper;
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
        //Load step number
        $numStep = $this->_coreRegistry->registry(\Nostress\Koongo\Helper\Data::REGISTRY_KEY_KOONGO_SERVIVE_CONNECTION_STEP_NUMBER);

        $postUrl = $this->getUrl('*/*/step' . $numStep);

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(
            ['data' => ['id' => 'edit_form', 'action' => $postUrl, 'method' => 'post']]
        );

        switch ($numStep) {
            case 2:
                $this->_formContentStep2($form);
                break;
            case 3:
                $this->_formContentStep3($form);
                break;
            default:
                $this->_formContentStep1($form);
                break;
        }

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Create content for step one
     *
     * @param [type] $form
     * @return void
     */
    protected function _formContentStep1($form)
    {
        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('New REST API Integration')]);

        $fieldset->addField('step_number', 'hidden', [
            'name' => 'step_number',
            'value' => "1"
        ]);

        $messageArray = [];
        //$messageArray[] = __("Magetno 2 REST API is used for Koongo integration.");
        $messageArray[] = __("A new system integration called Koongo will be created.");
        $messageArray[] = __("Please insert your admin password and click NEXT.");

        // $messageArray[] = __("New system integration with name Koongo will be created.");
        // $messageArray[] = __("Permissions toThe system integration with name Koongo will be created.");

        foreach ($messageArray as $key => $message) {
            $fieldset->addField('message_' . $key, 'label', [
                'label' => "",
                "value" => $message
            ]);
        }

        $fieldset->addField(
            self::DATA_CONSUMER_PASSWORD,
            'password',
            [
                'name' => self::DATA_CONSUMER_PASSWORD,
                'label' => __('Your Password'),
                'id' => self::DATA_CONSUMER_PASSWORD,
                'title' => __('Your Password'),
                'class' => 'input-text validate-current-password required-entry',
                'style' => "width: 50%;padding: 4px 10px;",
                'required' => true
            ]
        );

        $fieldset->addField('submit', 'submit', [
            'value' => __('Next'),
            'class' => 'action-primary abs-action-l',
            'name' => 'submit',
            'label' => ""
        ]);
    }

    /**
     * Create content for step two
     *
     * @param [type] $form
     * @return void
     */
    protected function _formContentStep2($form)
    {
        //Load integration
        $integrationData = $this->_coreRegistry->registry(\Magento\Integration\Controller\Adminhtml\Integration::REGISTRY_KEY_CURRENT_INTEGRATION);

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('REST API Integration - Approve Permissions')]);

        $fieldset->addField('step_number', 'hidden', [
            'name' => 'step_number',
            'value' => "2"
        ]);

        $integrationId = "";
        if (isset($integrationData['integration_id']) && $integrationData['integration_id']) {
            $integrationId = $integrationData['integration_id'];
        }

        $fieldset->addField('integration_id', 'hidden', [
            'name' => 'integration_id',
            'value' => $integrationId
        ]);

        $messageArray = [];
        //$messageArray[] = __("The Koongo system integration asks you to approve access.");
        $messageArray[] = __("Please check the permissions below and click ALLOW INTEGRATION.");

        foreach ($messageArray as $key => $message) {
            $fieldset->addField('message_' . $key, 'label', [
                'label' => "",
                "value" => $message
            ]);
        }

        $fieldset->addField('submit', 'submit', [
            'value' => __('ALLOW INTEGRATION'),
            'class' => 'action-primary abs-action-l',
            'name' => 'submit',
            'label' => ""
        ]);
    }

    /**
    * Create content for step three
    *
    * @param [type] $form
    * @return void
    */
    protected function _formContentStep3($form)
    {
        //Load integration
        $integrationData = $this->_coreRegistry->registry(\Magento\Integration\Controller\Adminhtml\Integration::REGISTRY_KEY_CURRENT_INTEGRATION);
        $redirectLink = $this->_helper->getServiceRedirectUrl($integrationData);

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('REST API - Authorization')]);

        $fieldset->addField('step_number', 'hidden', [
            'name' => 'step_number',
            'value' => "3"
        ]);

        $messageArray = [];
        $messageArray[] = __("Please click button below to connect your Koongo account with Magento 2 store.");

        foreach ($messageArray as $key => $message) {
            $fieldset->addField('message_' . $key, 'label', [
                'label' => "",
                "value" => $message
            ]);
        }

        $fieldset->addField('connect_service', 'button', [
            'value' => __('AUTHORIZE'),
            'class' => 'action-primary abs-action-l',
            'name' => 'connect_service',
            'label' => "",
            'onclick' => "window.open('{$redirectLink}','_blank')"
        ]);
    }
}
