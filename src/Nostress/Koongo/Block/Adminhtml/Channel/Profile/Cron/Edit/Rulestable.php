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
 * Block for cron schedule rules setting
 *
 * @category Nostress
 * @package Nostress_Koongo
 *
 */
namespace Nostress\Koongo\Block\Adminhtml\Channel\Profile\Cron\Edit;

use Nostress\Koongo\Model\Channel\Profile;

class Rulestable extends \Magento\Backend\Block\Template
{
    protected $_tooltip = 'cron_schedule_rules';

    /**
     * @var string
     */
    protected $_template = 'Nostress_Koongo::koongo/channel/profile/cron/rules_table.phtml';

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_registry;

    /**
     *  @var $model \Nostress\Koongo\Model\Channel\Profile
     **/
    protected $profile;

    /**
     * @var \Nostress\Koongo\Model\Config\Source\Scheduledays
     */
    protected $scheduledays;

    /**
     * @var \Nostress\Koongo\Model\Config\Source\Scheduletimes
     */
    protected $scheduletimes;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Nostress\Koongo\Model\Channel\Profile\Categories $categoriesModel
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Nostress\Koongo\Model\Channel\Profile\Categories $categoriesModel,
        \Nostress\Koongo\Model\Config\Source\Scheduledays $scheduledays,
        \Nostress\Koongo\Model\Config\Source\Scheduletimes $scheduletimes,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_registry = $registry;
        $this->profile = $this->_registry->registry('koongo_channel_profile');
        $this->scheduledays = $scheduledays;
        $this->scheduletimes = $scheduletimes;
    }

    /**
     * {@inheritdoc}
     */
    public function getComponentName()
    {
        if (null === $this->getData('component_name')) {
            $this->setData('component_name', $this->getNameInLayout());
        }
        return $this->getData('component_name');
    }

    public function getProfile()
    {
        return $this->profile;
    }

    public function getCronRules()
    {
        $rules = $this->profile->getConfigItem(Profile::CONFIG_CRON, false, Profile::CONFIG_CRON_RULES);
        if (empty($rules)) {
            $rules = [];
        }

        return $rules;
    }

    public function getChannelLabel()
    {
        return $this->profile->getFeed()->getChannel()->getLabel();
    }

    /**
     * Get attribute name for knockout
     * @param unknown_type $name
     * @return string
     */
//     public function getRuleInputNameKO($name)
//     {
//     	$prefix = 'rules[';
//     	$suffix = "][{$name}]";
//     	return $value = "'{$prefix}'+ ".'$index()'." +'{$suffix}'";
//     }

    public function getFieldsetName()
    {
        return "cron_schedule_rules_fieldset";
    }

    public function getDaysSelectHtml()
    {
        $options = $this->scheduledays->toIndexedArray();
        $nameIndex = "days_interval";
        return $this->getSelectHtml($options, $nameIndex);
    }

    public function getTimesSelectHtml()
    {
        $options = $this->scheduletimes->toIndexedArray();
        $nameIndex = "times_interval";
        return $this->getSelectHtml($options, $nameIndex);
    }

    public function getHoursSelectHtml()
    {
        $options = [];
        for ($i = 0;$i < 24;$i++) {
            $value = $i;
            if (strlen($value) <= 1) {
                $value = "0" . $value;
            }
            $options[$i] = $value;
        }

        $nameIndex = "time_hours";
        return $this->getSelectHtml($options, $nameIndex);
    }

    public function getMinutesSelectHtml()
    {
        $options = [];
        for ($i = 0;$i < 60;$i= $i+5) {
            $value = $i;
            if (strlen($value) <= 1) {
                $value = "0" . $value;
            }
            $options[$i] = $value;
        }

        $nameIndex = "time_minutes";
        return $this->getSelectHtml($options, $nameIndex);
    }

    /**
     * Select field HTML.
     *
     * @param Attribute $attribute
     * @param mixed $value
     * @return \Magento\Framework\Phrase|string
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    protected function getSelectHtml($options, $nameIndex)
    {
        if ($size = count($options)) {
            //array_unshift($options, ['value' => '', 'label' => __("Please select attribute.")]);

            $arguments = [
                'id' => $nameIndex,
                'class' => 'admin__control-select select select-attributes-magento',
                'extra_params' => "data-bind=\"value: {$nameIndex}, attr: { name: {$this->getInputNameKO($nameIndex, false)} }\""
                ];
            /** @var $selectBlock \Magento\Framework\View\Element\Html\Select */
            $selectBlock = $this->_layout->createBlock(
                'Magento\Framework\View\Element\Html\Select',
                '',
                ['data' => $arguments]
            );
            return $selectBlock->setOptions($options)->getHtml();
        } else {
            return __('Attribute load failed.');
        }
    }

    /**
     * Get attribute name for knockout
     * @param unknown_type $name
     * @return string
     */
    public function getInputNameKO($name, $parent = false)
    {
        $parentString = '';
        if ($parent) {
            $parentString = '$parentContext.';
        }

        $prefix = $this->getRulesInputName() . '[';
        $suffix = "][{$name}]";
        $value = "'{$prefix}'+ " . $parentString . '$index()' . " +'{$suffix}'";
        return $value;
    }

    public function getRulesInputName()
    {
        return  Profile::CONFIG_CRON . '[' . Profile::CONFIG_CRON_RULES . ']';
    }

    public function getHelp($key)
    {
        return $this->profile->helper->getHelp($key);
    }

    public function getTooltip($key = null)
    {
        if ($key === null) {
            $key = $this->_tooltip;
        }
        return $this->profile->helper->renderTooltip($key);
    }
}
