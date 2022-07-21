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

namespace Nostress\Koongo\Block\Adminhtml\System\Config;

class Checklicensebutton extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * Backend helper
     * @var \Magento\Backend\Helper\Data
     */
    protected $_adminHelper;

    /**
     * License helper
     * @var \Nostress\Koongo\Helper\Version
     */
    protected $_licenseHelper;

    public function __construct(
        \Magento\Backend\Helper\Data $adminHelper,
        \Nostress\Koongo\Helper\Version $licenseHelper,
        \Magento\Backend\Block\Template\Context $context,
        array $data = []
    ) {
        $this->_adminHelper = $adminHelper;
        $this->_licenseHelper  = $licenseHelper;
        parent::__construct($context, $data);
    }

    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('system/config/check_license_button.phtml');
    }

    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        return $this->_toHtml();
    }

    public function getAjaxLicenseCheckUrl()
    {
        return $this->getUrl('koongo/license/check/', [ 'ajax'=>true]);
    }

    public function getButtonsHtml()
    {
        // trial only if license key is empty
        if ($this->_licenseHelper->isLicenseEmpty()) {
            return $this->_getTrialButton() . " " . $this->_getLiveButton();
        // not valid or is trial
        } elseif (!$this->_licenseHelper->isLicenseKeyValid() || $this->_licenseHelper->isLicenseKeyT()) {
            return $this->_getLiveButton();

        // check button only for live with ended support
        } elseif (!$this->_licenseHelper->isDateValid()) {
            return $this->_getCheckButton();
        }
    }

    protected function _getCheckButton()
    {
        $buttonData = [
            'id' => 'koongo_license_check_button',
            'label' => __('Check License Status'),
            'onclick' => 'javascript:koongoCheckLicense(\'' . $this->getAjaxLicenseCheckUrl() . '\'); return false;'
        ];

        $button = $this->getLayout()->createBlock('Magento\Backend\Block\Widget\Button')->setData($buttonData);
        return $button->toHtml();
    }

    protected function _getLiveButton()
    {
        $buttonData = [
                'id' => 'activate_live',
                'label' => __('Activate Live'),
                'class' => 'primary',
                'onclick' => "location.href='" . $this->getUrl('koongo/license/liveform') . "'",
        ];

        $button = $this->getLayout()->createBlock('Magento\Backend\Block\Widget\Button')->setData($buttonData);
        return $button->toHtml();
    }

    protected function _getTrialButton()
    {
        $buttonData = [
                'id' => 'activate_trial',
                'label' => __('Activate Trial'),
                'class' => 'default',
                'onclick' => "location.href='" . $this->getUrl('koongo/license/trialform') . "'",
        ];

        $button = $this->getLayout()->createBlock('Magento\Backend\Block\Widget\Button')->setData($buttonData);
        return $button->toHtml();
    }

    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        // Remove scope label
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }
}
