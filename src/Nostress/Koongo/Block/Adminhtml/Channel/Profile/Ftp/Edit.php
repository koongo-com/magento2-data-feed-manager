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
namespace Nostress\Koongo\Block\Adminhtml\Channel\Profile\Ftp;

class Edit extends \Magento\Backend\Block\Widget\Form\Container
{
    protected $_template = 'Nostress_Koongo::koongo/channel/profile/ftp/container.phtml';

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * Initialize cms page edit block
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_objectId = 'entity_id';
        $this->_blockGroup = 'Nostress_Koongo';
        $this->_controller = 'adminhtml_channel_profile_ftp';

        parent::_construct();

        if ($this->_isAllowedAction('Nostress_Koongo::save')) {
            $this->buttonList->update('save', 'label', __('Save Settings'));
        } else {
            $this->buttonList->remove('save');
        }

        $this->buttonList->add(
            'save_execute',
            [
                'class' => 'save primary',
                'label' => __('Save & Upload'),
                'data_attribute' => [
                            'mage-init' => [
                            'button' => [
                                'event' => 'save',
                                'target' => '#edit_form',
                                'eventData' => ['action' => ['args' => ['back' => 'upload']]],
                            ],
                        ],
                    ]
                ]
        );

        $this->buttonList->remove('delete');
        $this->buttonList->remove('reset');
    }

    public function getProfile()
    {
        return $this->_coreRegistry->registry('koongo_channel_profile');
    }

    /**
     * Retrieve text for header element depending on loaded page
     *
     * @return \Magento\Framework\Phrase
     */
    public function getHeaderText()
    {
        return __("Edit Profile #%1 - FTP Submission ", $this->escapeHtml($this->_coreRegistry->registry('channel_profile')->getId()));
    }

    /**
     * Get URL for back (reset) button
     *
     * @return string
     */
    public function getBackUrl()
    {
        return $this->getUrl('*/channel_profile/');
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

    /**
     * Prepare layout
     *
     * @return \Magento\Framework\View\Element\AbstractBlock
     */
    protected function _prepareLayout()
    {
        $this->_formScripts[] = "
            
        ";
        return parent::_prepareLayout();
    }
}
