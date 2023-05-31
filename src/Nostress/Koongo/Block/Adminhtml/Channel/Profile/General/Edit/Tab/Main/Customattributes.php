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
 * BLock for attribute settings form options tab
 *
 * @category Nostress
 * @package Nostress_Koongo
 *
 */
namespace Nostress\Koongo\Block\Adminhtml\Channel\Profile\General\Edit\Tab\Main;

use Nostress\Koongo\Model\Channel\Profile;

class Customattributes extends \Nostress\Koongo\Block\Adminhtml\Channel\Profile\General\Edit\Tab\Main\Attributes
{
    protected $_tooltip = 'attributes_mapping_custom_attributes';

    /**
     * @var string
     */
    protected $_template = 'Nostress_Koongo::koongo/channel/profile/general/main/custom_attributes.phtml';
    private ?array $_magentoAttributeOptions = null;

    public function exportCustomAttributes()
    {
        return $this->profile->exportCustomAttributes();
    }

    public function getCustomAttributesJson()
    {
        $attributes = $this->getCustomAttributesArray();
        return json_encode($attributes);
    }

    /**
     * @return $this
     */
    public function getCustomAttributesArray()
    {
        $attributesSetup = $this->profile->getCustomAttributes();

        $index = 0;
        $position = 1;

        foreach ($attributesSetup as $key => $attribute) {
            $attribute['index'] = $index;
            $attribute['position'] = $position;
            $attribute['grid_index'] = $position-1;

            //Convert post-proc actions into array
            if (isset($attribute['postproc'])) {
                $ppactions = preg_replace('/\s+/', '', $attribute['postproc']);
                $ppactions = explode(",", $ppactions);
                $attribute['postproc'] = $ppactions;
            }

            //Check eppav if not emplty
            if (empty($attribute['eppav'])) {
                $attribute['eppav'] = '0';
            }

            $index++;
            $position++;
            $attributesSetup[$key] = $attribute;
        }

        return $attributesSetup;
    }

    /**
     * Get attribute name for knockout
     * @param unknown_type $name
     * @return string
     */
    public function getAttributeInputNameKO($name, $parent = false)
    {
        $parentString = '';
        if ($parent) {
            $parentString = '$parentContext.';
        }

        $prefix = $this->getAttributeInputName() . '[';
        $suffix = "][{$name}]";
        $value = "'{$prefix}'+ " . $parentString . '$index()' . " +'{$suffix}'";
        return $value;
    }

    public function getAttributeInputName()
    {
        return Profile::CONFIG_FEED . '[' . Profile::CONFIG_CUSTOM_ATTRIBUTES . ']';
    }

    /**
     * Select field magento attribute HTML with selected value.
     *
     * @param Attribute $attribute
     * @param mixed $value
     * @return \Magento\Framework\Phrase|string
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function getSelectHtmlWithValue()
    {
        if ($this->_magentoAttributeOptions === null) {
            $this->_magentoAttributeOptions = $this->attributeSource->toIndexedArray($this->profile->getStoreId(), $this->profile->getTaxonomyLabel());
        }

        $options = $this->_magentoAttributeOptions;

        if ($size = count($options)) {
            array_unshift($options, ['value' => '', 'label' => __("Please select attribute.")]);

            $arguments = [
            //'name' => $this->getAttributeInputName("magento"),
            'id' => "magento_attribute",
            'class' => 'admin__control-select select select-attributes-magento',
            'extra_params' => "data-bind=\"value: magento, attr: { name: {$this->getAttributeInputNameKO('magento', false)} }\""
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

    public function getHelp($key)
    {
        return $this->profile->helper->getHelp($key);
    }
}
