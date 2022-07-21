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
 * Channel manage profile block
 *
 * @category Nostress
 * @package Nostress_Koongo
 *
 */

namespace Nostress\Koongo\Block\Adminhtml\Channel;

class Profile extends \Magento\Backend\Block\Widget\Container
{
    /**
     * @var \Nostress\Koongo\Helper\Version
     */
    protected $_helper;

    /**
     * @var \Nostress\Koongo\Model\Channel\Feed
     */
    protected $feedSingelton;

    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Nostress\Koongo\Helper\Version $helper,
        \Nostress\Koongo\Model\Channel\Feed $feedSingelton,
        array $data = []
    ) {
        $this->_helper = $helper;
        $this->feedSingelton = $feedSingelton;
        parent::__construct($context, $data);
    }

    /**
     * Prepare button and grid
     *
     * @return \Magento\Catalog\Block\Adminhtml\Product
     */
    protected function _prepareLayout()
    {
        if ($this->_helper->isDebugMode()) {
            $this->addButton('update_server_config', [
                'id' => 'update_server_config',
                'label' => __('Update Server Config'),
                'class' => 'default',
                'onclick' => "location.href='" . $this->getUrl('koongo/license/updateserverconfig') . "'",
            ]);
        }

        // license empty - show only trial and live button
        if ($this->_helper->isLicenseEmpty()) {
            $this->addButton('activate_trial', $this->_getTrialButtonData());
            $this->addButton('activate_live', $this->_getLiveButtonData());

        // license not empty
        } else {

            // key not valid or trial
            if (!$this->_helper->isLicenseValid() || $this->_helper->isLicenseKeyT()) {
                $this->addButton('activate_live', $this->_getLiveButtonData());
            }

            $this->addButton('update_feeds', $this->_getUpdateFeedsButtonData());
            $this->addButton('add_new', $this->_getNewProfileButtonData());

            // trial or not valid
            if (!$this->_helper->isLicenseValid()) {
                $this->_disableButton('update_feeds');
                $this->_disableButton('add_new');

            // check button only for live with ended support
            } elseif (!$this->_helper->isLicenseValid(true)) {
                $this->_disableButton('update_feeds');
                $this->addButton('check_license', $this->_getCheckButtonData());
            }

            //Feeds not loaded
            if (!$this->feedSingelton->feedsLoaded()) {
                $this->removeButton('update_feeds');
                $this->removeButton('add_new');
                $this->addButton('get_feeds', $this->_getGetFeedsButtonData());
            }
        }

        return parent::_prepareLayout();
    }

    protected function _disableButton($name)
    {
        $this->updateButton($name, 'disabled', "disabled");
    }

    protected function _getUpdateFeedsButtonData()
    {
        $updateFeedTemplatesButtonProps = [
           'id' => 'update_feed_templates',
           'label' => __('Update Feed Templates'),
           'class' => 'update',
           'onclick' => "location.href='" . $this->getUrl('*/*/updatefeeds') . "'",
        ];
        return $updateFeedTemplatesButtonProps;
    }

    protected function _getGetFeedsButtonData()
    {
        $getFeedTemplatesButtonProps = [
            'id' => 'get_feed_templates',
            'label' => __('Get Feed Templates'),
            'class' => 'primary',
            'onclick' => "location.href='" . $this->getUrl('*/*/updatefeeds') . "'",
        ];
        return $getFeedTemplatesButtonProps;
    }

    protected function _getNewProfileButtonData()
    {
        $addButtonProps = [
            'id' => 'add_new_product',
            'label' => __('Add New Export Profile'),
            'class' => 'primary',
            'before_html' => "<div data-bind=\"scope: 'new-profile-steps-wizard'\" >",
            'after_html' => "</div>",
            'data_attribute' => [
                'action' => "open-steps-wizard",
                'bind' => "click: open"
            ],
        ];
        return $addButtonProps;
    }

    protected function _getCheckButtonData()
    {
        return [
            'id' => 'koongo_license_check_button',
            'label' => __('Check License Status'),
            'onclick' => "location.href='" . $this->getUrl('koongo/license/check') . "'"
        ];
    }

    protected function _getLiveButtonData()
    {
        return [
                'id' => 'activate_live',
                'label' => __('Activate Live'),
                'class' => 'primary',
                'onclick' => "location.href='" . $this->getUrl('koongo/license/liveform') . "'",
        ];
    }

    protected function _getTrialButtonData()
    {
        return [
                'id' => 'activate_trial',
                'label' => __('Activate Trial'),
                'class' => 'default',
                'onclick' => "location.href='" . $this->getUrl('koongo/license/trialform') . "'",
        ];
    }

    /**
     * @param array $initData
     * @return string
     */
    public function getNewProfileWizard($initData)
    {
        /** @var \Magento\Ui\Block\Component\StepsWizard $wizardBlock */
        $wizardBlock = $this->getChildBlock('new-profile-steps-wizard');
        if ($wizardBlock) {
            $wizardBlock->setInitData($initData);
            return $wizardBlock->toHtml();
        }
        return '';
    }

    public function getTooltip($key = null)
    {
        return $this->_helper->renderTooltip($key);
    }

    /**
     * Prepare html output
     *
     * @return string
     */
    protected function _toHtml()
    {
        $wizardHtml = "";

        // generate wizard only if feeds are loaded
        if ($this->feedSingelton->feedsLoaded()) {
            $newProfilesWizardTitle = __('Add New Export Profile');
            // TODO - .$this->getTooltip( 'new_profile_wizard');
            $wizardHtml .=
            "<div data-role=\"step-wizard-dialog\"
             	data-mage-init='{\"Magento_Ui/js/modal/modal\":{\"type\":\"slide\",\"title\":\"{$newProfilesWizardTitle}\",
             	\"buttons\":[]}}'
             	class=\"no-display\">
    			{$this->getNewProfileWizard([])}
    			</div>";
        }

        $wizardHtml .= "
            <script type='text/javascript'>
		        sp = " . $this->getRequest()->getParam('sp', 0) . "; // show preview id
		        sf = " . $this->getRequest()->getParam('sf', 0) . "; // show ftp submission id
		    </script>
        ";

        return $wizardHtml . parent::_toHtml();
    }
}
