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
namespace Nostress\Koongo\Block\Adminhtml\Help;

/**
 * CMS block edit form container
 */
class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
    const HELP_MODE_TRIAL = 'trial';
    const HELP_MODE_LIVE = 'live';
    const HELP_MODE_SERVICE = 'service';
    const HELP_MODE_GENERAL = 'general';

    protected $_template = 'Nostress_Koongo::koongo/help.phtml';

    protected $_help_mode = self::HELP_MODE_TRIAL;

    /**
     * @var \Magento\Backend\Helper\Data
     */
    protected $_helper_backend = null;

    /**
     * @var \Nostress\Koongo\Helper\Version
     */
    protected $_version = null;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Backend\Helper\Data $helperBackend
     * @param \Nostress\Koongo\Helper\Version $version
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Backend\Helper\Data $helperBackend,
        \Nostress\Koongo\Helper\Version $version,
        array $data = []
    ) {
        $this->_helper_backend = $helperBackend;
        $this->_version = $version;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    public function setHelpMode($mode)
    {
        $this->_help_mode = $mode;
    }

    /**
     * Init form
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();

        $this->_help_mode = self::HELP_MODE_TRIAL;

        // must be edit_form because of relation with form container
        $this->setId('help_form');
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
            ['data' => ['id' => 'help_form', 'action' => $this->getUrl('*/license/help'), 'method' => 'post']]
        );

        $fieldset = $form->addFieldset('base_fieldset', ['expanded'  => true]);

        $fieldset->addField('redirect_url_key', 'hidden', [
            'name' => 'redirect_url_key'
        ]);

        $fieldset->addField('subject', 'text', [
            'label' => __('Subject:'),
            'class' => 'required-entry',
            'required' => true,
            'name' => 'subject'
        ]);

        $fieldset->addField('email', 'text', [
            'label' => __('Email:'),
            'class' => 'required-entry validate-email',
            'required' => true,
            'name' => 'email'
        ]);

        $fieldset->addField('message', 'textarea', [
            'label' => __('Question:'),
            'class' => 'required-entry',
            'required' => true,
            'name' => 'message',
            'style' => "height: 300px"
        ]);

        $modeLabel = ucfirst($this->_help_mode);
        $serverIdString = "";
        $redirectUrlKey = "";

        $subject = "";
        switch ($this->_help_mode) {
            case self::HELP_MODE_GENERAL:
                $subject = __('Koongo - Activation request');
                $modeLabel = "";
                $redirectUrlKey = "*/overview/";
                break;
            case self::HELP_MODE_SERVICE:
                $subject = __('Koongo Service - Activation request');
                $redirectUrlKey = "*/service_connection/";
                break;
            case self::HELP_MODE_LIVE:
                $subject = __('Koongo Connector - Live activation request');
                $modeLabel = __("Connector") . " " . $modeLabel;
                $serverIdString = 'Server Id: %3' . PHP_EOL;
                $redirectUrlKey = "*/license/liveform/";
                break;
            default:
                $subject = __('Koongo Connector - Trial activation request');
                $modeLabel = __("Connector") . " " . $modeLabel;
                $serverIdString = 'Server Id: %3' . PHP_EOL;
                $redirectUrlKey = "*/license/trialform";
                break;
        }

        $serverId = $this->_version->getServerId();
        $url = $this->_helper_backend->getUrl('dashboard');
        $message = __('Hi,' . PHP_EOL .
                'I need help with Koongo %1 activation.' . PHP_EOL . PHP_EOL .
                'My backend URL is: %2' . PHP_EOL .
                $serverIdString .
                'Backend username:' . PHP_EOL .
                'Password:' . PHP_EOL . PHP_EOL .

                'Thanks, ' . PHP_EOL, $modeLabel, $url, $serverId);

        $form->setValues([
            'subject' => $subject,
            'message' => $message,
            'redirect_url_key' => $redirectUrlKey
        ]);

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
