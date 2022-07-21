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
namespace Nostress\Koongo\Block\Adminhtml\Channel\Profile\Cron;

class Edit extends \Magento\Backend\Block\Widget\Form\Container
{

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var Nostress\Koongo\Model\Channel
     */
    protected $profile;

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
        $this->profile = $this->_coreRegistry->registry('koongo_channel_profile');
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
        $this->_controller = 'adminhtml_channel_profile_cron';

        $channelLabel = $this->profile->getFeed()->getChannel()->getLabel();

        parent::_construct();

        if ($this->_isAllowedAction('Nostress_Koongo::save')) {
            $this->buttonList->update('save', 'label', __('Save'));
            $this->buttonList->add(
                'saveandcontinue',
                [
                        'class' => 'save',
                        'label' => __('Save and Continue Edit'),
                        'data_attribute' => [
                            'mage-init' => [
                                'button' => [
                                'event' => 'save',
                                'target' => '#edit_form',
                                'eventData' => ['action' => ['args' => ['back' => 'cron']]],
                                ],
                            ],
                        ]
                    ],
                -100
            );
        } else {
            $this->buttonList->remove('save');
        }

        $this->buttonList->remove('delete');
        $this->buttonList->remove('reset');
    }

    /**
     * Retrieve text for header element depending on loaded page
     *
     * @return \Magento\Framework\Phrase
     */
    public function getHeaderText()
    {
        $channelLabel = $this->profile->getChannel()->getLabel();
        return __("Edit Profile #%1 - Schedule Execution", $this->escapeHtml($this->_coreRegistry->registry('channel_profile')->getId()));
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
     * Get form action URL
     *
     * @return string
     */
//     public function getFormActionUrl()
//     {
//     	return $this->getUrl('*/*/save');
//     }

    /**
     * @return string
     */
    public function getFormHtml()
    {
        //add category mapping table into Form
        $html = parent::getFormHtml();
        $rulesTableHtml = $this->getChildHtml('cron_rules_table');
        return str_replace("</form>", $rulesTableHtml . "</form>", $html);
    }
}
