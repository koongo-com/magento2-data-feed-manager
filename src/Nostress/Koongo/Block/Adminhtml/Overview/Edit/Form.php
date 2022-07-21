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
namespace Nostress\Koongo\Block\Adminhtml\Overview\Edit;

class Form extends \Magento\Backend\Block\Widget\Form\Generic
{

    /**
     * @var \Nostress\Koongo\Helper\Data
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
        \Nostress\Koongo\Helper\Data $helper,
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
        $postUrl = $this->getUrl('*/*/');

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(
            ['data' => ['id' => 'edit_form', 'action' => $postUrl, 'method' => 'post']]
        );

        $this->_headerContent($form);
        $this->_serviceContent($form);
        $this->_connectorContent($form);

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Create content for header
     *
     * @param [type] $form
     * @return void
     */
    protected function _headerContent($form)
    {
        $fieldset = $form->addFieldset('general', ['legend' => '']);

        $messageArray = [];
        $messageArray[] = __("Koongo offers 2 possible solutions for Magento 2 users - Koongo Service and Koongo Connector.");

        foreach ($messageArray as $key => $message) {
            $fieldset->addField('message_general_' . $key, 'label', [
                'label' => "",
                "value" => $message
            ]);
        }
    }

    /**
     * Create content connector section
     *
     * @param [type] $form
     * @return void
     */
    protected function _connectorContent($form)
    {
        //$this->_helper->renderTooltip("shipping_cost")
        $fieldset = $form->addFieldset('koongo_connector', ['legend' => __('Koongo Connector')]);

        $messageArray = [];
        $messageArray[] = __("Koongo Connector is a license-based module that fully integrates product data feed management into your Magento store. It does not support any channel API integration or order management.");
        $messageArray[] = __("The Connector license can be activated for a specific Magento server ID and has lifetime validity. The license price is fixed, additional products can be purchased - feed collections, support period, etc.");

        foreach ($messageArray as $key => $message) {
            $fieldset->addField('message_connector_' . $key, 'label', [
                'label' => "",
                "value" => $message
            ]);
        }

        $fieldset->addField('activate_trial', 'button', [
            'value' => __('Activate 30-day Free Trial'),
            'class' => 'action-primary abs-action-l',
            'name' => 'activate_trial',
            'onclick' => "location.href='" . $this->getUrl('koongo/license/trialform') . "'",
            'label' => ""
        ]);

        $fieldset->addField('activate_live', 'button', [
            'value' => __('Activate Live License'),
            'class' => 'action-primary abs-action-l',
            'name' => 'activate_live',
            'onclick' => "location.href='" . $this->getUrl('koongo/license/liveform') . "'",
            'label' => ""
        ]);
    }

    /**
     * Create content service section
     *
     * @param [type] $form
     * @return void
     */
    protected function _serviceContent($form)
    {
        $fieldset = $form->addFieldset('koongo_service', ['legend' => __('Koongo Service')]);

        $message = "Koongo Service is an externally hosted product data feed management tool. ";
        $message .= "It supports API integration and order management for channels like Amazon, eBay, Bol, Beslist, Idealo or Miinto.";

        $messageArray = [];
        $messageArray[] = $message;
        $messageArray[] = __("Koongo dynamic subscription allows you to pay only for the service you use. You can scale the subscription up or down on a monthly basis.");

        foreach ($messageArray as $key => $message) {
            $fieldset->addField('message_service_' . $key, 'label', [
                'label' => "",
                "value" => $message
            ]);
        }

        $fieldset->addField('activate_service', 'button', [
            'value' => __('Activate Koongo Service'),
            'class' => 'action-primary abs-action-l',
            'name' => 'activate_service',
            'onclick' => "location.href='" . $this->getUrl('koongo/service_connection') . "'",
            'label' => "",
            'note' => __("A 30-day free trial is available for new Koongo users")
        ]);
    }
}
