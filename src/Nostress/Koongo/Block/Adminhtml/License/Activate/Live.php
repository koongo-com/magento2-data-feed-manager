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
namespace Nostress\Koongo\Block\Adminhtml\License\Activate;

/**
 * CMS block edit form container
 */
class Live extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Nostress\Koongo\Helper\Version
     */
    protected $_helper = null;

    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Nostress\Koongo\Helper\Version $helper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        \Nostress\Koongo\Helper\Version $helper,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        $this->_helper = $helper;
        parent::__construct($context, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_blockGroup = 'Nostress_Koongo';
        $this->_controller = 'adminhtml_license_activate';
        $this->_mode = 'live';

        parent::_construct();

        $this->addButton('help', $this->_getHelpButtonData());

        $licenseUrl = $this->_helper->getNewLicenseUrl();

        $this->addButton(
            'buy',
            [
                    'label' => __('Buy Live License'),
                    'onclick' => 'window.open(\'' . $licenseUrl . '\')',
                    "formtarget"=>"_blank",
                    'class' => 'primary'
            ],
            -1
        );

        $this->buttonList->remove('save');

        $this->buttonList->remove('delete');
    }

    protected function _getHelpButtonData()
    {
        $helpButtonProps = [
                'id' => 'help_dialog',
                'label' => __('Get Support'),
                'class' => 'primary',
        ];
        return $helpButtonProps;
    }

    /**
     * Get edit form container header text
     *
     * @return \Magento\Framework\Phrase
     */
    public function getHeaderText()
    {
        return __('Koongo - Activate Live');
    }

    /**
     * Get URL for back (reset) button
     *
     * @return string
     */
    public function getBackUrl()
    {
        return $this->getUrl('adminhtml/system_config/edit/section/koongo_license/');
    }

    /**
     * Get form save URL
     *
     * @see getFormActionUrl()
     * @return string
     */
    public function getSaveUrl()
    {
        return $this->getUrl('*/*/liveactivate');
    }
}
