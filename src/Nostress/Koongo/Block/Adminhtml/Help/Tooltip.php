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
class Tooltip extends \Magento\Backend\Block\Template
{
    /**
     * @var \Nostress\Koongo\Helper\Data
     */
    protected $helper = null;

    protected $_key = null;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Nostress\Koongo\Helper\Data $helper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Nostress\Koongo\Helper\Data $helper,
        array $data = []
    ) {
        $this->helper = $helper;
        parent::__construct($context);
    }

    public function setKey($key)
    {
        $this->_key = $key;
    }

    public function _toHtml()
    {
        return "<div class='store-switcher'>" . $this->helper->renderTooltip($this->_key) . "</div>";
    }
}
