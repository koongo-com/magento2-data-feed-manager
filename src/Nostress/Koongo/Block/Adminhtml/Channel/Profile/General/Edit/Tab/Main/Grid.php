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
 * Channel profile feed settings edit form main tab - attribute mapping table
 *
 * @category Nostress
 * @package Nostress_Koongo
 *
 */

namespace Nostress\Koongo\Block\Adminhtml\Channel\Profile\General\Edit\Tab\Main;

use Nostress\Koongo\Model\Channel\Profile;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    const ATTRIBUTE_DESCRIPTION_PREVIEW_LENGHT = 20;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry|null
     */
    protected $_coreRegistry = null;

    /**
     *  @var $model \Nostress\Koongo\Model\Channel\Profile
     **/
    protected $profile;

    /**
     * @var \Magento\Newsletter\Model\ResourceModel\Queue\CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * @var \Magento\Framework\Data\CollectionFactory
     */
    protected $collectionFactoryCustom;

    /*
     * @var \Nostress\Koongo\Model\Config\Source\Attributes
    */
    protected $attributeSource;

    /*
     * @var Array
    */
    protected $_magentoAttributeOptions;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Newsletter\Model\ResourceModel\Queue\CollectionFactory $collectionFactory
     * @param \Magento\Framework\Data\CollectionFactory $collectionFactoryCustom
     * @param \Nostress\Koongo\Model\Config\Source\Attributes $attributeSource
     * @param \Magento\Framework\Registry $coreRegistry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Newsletter\Model\ResourceModel\Queue\CollectionFactory $collectionFactory,
        \Magento\Framework\Data\CollectionFactory $collectionFactoryCustom,
        \Nostress\Koongo\Model\Config\Source\Attributes $attributeSource,
        \Magento\Framework\Registry $coreRegistry,
        array $data = []
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->_collectionFactory = $collectionFactory;
        parent::__construct($context, $backendHelper, $data);

        $this->attributeSource = $attributeSource;
        $this->collectionFactoryCustom = $collectionFactoryCustom;
        /* @var $model \Nostress\Koongo\Model\Channel\Profile */
        $this->profile = $this->_coreRegistry->registry('koongo_channel_profile');
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('attributesGrid');
        $this->setDefaultSort('start_at');
        $this->setDefaultDir('desc');
        $this->setPagerVisibility(false);
        $this->setFilterVisibility(false);
        $this->setRowClickCallback(false);//'openSettingsDialog');
        $this->setDefaultLimit(null);

        $this->setUseAjax(true);

        $this->setEmptyText(__('No Attributes Found'));
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return false;
    }

    /**
     * Get row edit URL.
     *
     * @param Attribute $row
     * @return string|false
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getRowUrl($row)
    {
        return false;
    }

    public function getCollection()
    {
        $collection = parent::getCollection();

        if (empty($collection)) {
            $collection =  $this->collectionFactoryCustom->create();

            $attributesSetup = $this->profile->getAttributesWithDescription();

            $index = 0;
            $position = 1;
            $multiAttributesArray = $this->attributeSource->getProductMultiAttributeCodes(true);
            foreach ($attributesSetup as $attribute) {
                $item = $collection->getNewEmptyItem();
                $item->setData($attribute);
                $item->setIndex($index);
                $item->setPosition($position);
                $item->setGridIndex($position-1);

                //Convert post-proc actions into array
                $ppactions = $item->getPostproc();
                if (!empty($ppactions)) {
                    $ppactions = preg_replace('/\s+/', '', $ppactions);
                    $ppactions = explode(",", $ppactions);
                    $item->setPostproc($ppactions);
                }

                //Add option array to description
                $description = $item->getDescription();
                if (!empty($description['options'])) {
                    $description['option_array'] = $this->prepareOptionArray($description['options']);
                    $item->setDescription($description);
                }

                //Check eppav and assign 0 if value is empty
                $eppav = $item->getEppav();
                if (empty($eppav)) {
                    $item->setEppav('0');
                }

                $index++;
                $magentoAttribute = $item->getMagento();

                if (in_array($magentoAttribute, $multiAttributesArray)) {
                    continue;
                }

                $position++;
                $collection->addItem($item);
            }
            $this->setCollection($collection);
        }

        return $collection;
    }

    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
        $collection = $this->getCollection();
        return parent::_prepareCollection();
    }

    protected function prepareOptionArray($optionString)
    {
        if (empty($optionString)) {
            return [];
        }
        $options = explode(";", $optionString);

        $containsValueLabel = false;
        if (strpos($optionString, ":") !== false) {
            $containsValueLabel = true;
        }
        $resultArray = [];
        foreach ($options as $option) {
            if ($containsValueLabel) {
                if (strpos($option, ":") !== false) {
                    $items = explode(":", $option);
                    $resultItem = [];
                    if (!empty($items[0])) {
                        $resultItem["value"] = trim($items[0]);
                        $resultItem["value_label"] = $resultItem["value"];
                    }
                    if (!empty($items[1])) {
                        $resultItem["label"] = trim($items[1]);
                        $resultItem["value_label"] .= " = " . $resultItem["label"];
                    }
                    $resultArray[] = $resultItem;
                } else {
                    $resultArray[] = ["value"=> $option, "value_label" => $option];
                }
            } else {
                $resultArray[] = ["value"=> $option, "value_label" => $option];
            }
        }

        return $resultArray;
    }

    /**
     * @return $this
     */
    protected function _prepareColumns()
    {
        $channelLabel = $this->profile->getChannel()->getLabel();

        $this->addColumn(
            'position',
            ['header' => __('#'), 'align' => 'left', 'index' => 'position', 'width' => 10, 'sortable' => false]
        );

        $this->addColumn(
            'label',
            [
                'header' => $channelLabel . " " . __('Attribute'),
                'align' => 'left',
                'index' => 'label',
                'sortable' => false,
                'frame_callback' => [$this, 'decorateAttributeLabel']]
        );

        $this->addColumn(
            'required',
            [
                'header' => __('Required'),
                'align' => 'left',
                'renderer' => 'Nostress\Koongo\Block\Adminhtml\Channel\Profile\General\Edit\Tab\Main\Grid\Renderer\Required',
                'sortable' => false,
            ]
        );

        $this->addColumn(
            'magento_attribute',
            [
                'header' => __('Magento Attribute'),
                'sortable' => false,
                'filter' => false,
                'frame_callback' => [$this, 'decorateMagentoAttribute']
            ]
        );

        $this->addColumn(
            'constant',
            [
                'header' => __('Default Value'),
                'sortable' => false,
                'filter' => false,
                'frame_callback' => [$this, 'decorateConstant']
            ]
        );

        $this->addColumn(
            'settings',
            [
                'header' => __('Settings'),
                'sortable' => false,
                'filter' => false,
                'frame_callback' => [$this, 'decorateSettings']
            ]
        );

        return parent::_prepareColumns();
    }

    /**
     * Create magento attribute select field for 'Magento Attribute' column.
     *
     * @param mixed $value
     * @param \Magento\Framework\DataObject $row
     * @param \Magento\Framework\DataObject $column
     */
    public function decorateMagentoAttribute($value, \Magento\Framework\DataObject $row, \Magento\Framework\DataObject $column)
    {
        $value = $row->getMagento();
        return $this->_getSelectHtmlWithValue($row, $value);
    }

    /**
     * Create default value field for 'Default Value' column.
     *
     * @param mixed $value
     * @param \Magento\Framework\DataObject $row
     * @param \Magento\Framework\DataObject $column
     */
    public function decorateConstant($value, \Magento\Framework\DataObject $row, \Magento\Framework\DataObject $column)
    {
        $value = $row->getConstant();
        return $this->_getInputHtmlWithValue($row, $value);
    }

    /**
     * Create button for 'Stettings' column.
     *
     * @param mixed $value
     * @param \Magento\Framework\DataObject $row
     * @param \Magento\Framework\DataObject $column
     */
    public function decorateSettings($value, \Magento\Framework\DataObject $row, \Magento\Framework\DataObject $column)
    {
        $value = $row->getId();
        $html = $this->_getSettingsButtonHtmlWithValue($row, $value);

        $html .="<input type='hidden' name='{$this->getAttributeInputName($row->getIndex(), 'limit')}' data-bind='value: attributes()[{$row->getGridIndex()}].limit'/>";
        $html .="<input type='hidden' name='{$this->getAttributeInputName($row->getIndex(), 'eppav')}' data-bind='value: attributes()[{$row->getGridIndex()}].eppav'/>";
        $html .="<input type='hidden' name='{$this->getAttributeInputName($row->getIndex(), 'postproc')}' data-bind='value: attributes()[{$row->getGridIndex()}].postproc'/>";

        //Add empty value for convert if no row is defined
        $html .= "<span id=\"convert_container_values\" data-bind=\"visible: attributes()[{$row->getGridIndex()}].convert().length <= 0\">
    			      <input type=\"hidden\" value=\"\" name='{$this->getAttributeInputName($row->getIndex(), 'convert')}' class=\" input-text admin__control-text\">
		          </span>";
        //Render caonvert value inputs
        $html .=
        "<span id=\"convert_container_values\" data-bind=\"foreach: attributes()[{$row->getGridIndex()}].convert\">
		            <input type=\"hidden\"
		            	id=\"convert_row_from\"
		            	data-bind=\"value: from,
		            			  attr: { name: '{$this->getAttributeInputName($row->getIndex(), 'convert')}[' + \$index() + '][from]',
    							  id: 'convert_row_from_{$row->getIndex()}_' + \$index()}\"
		            	 class=\" input-text admin__control-text\">
		            <input type=\"hidden\"
		            	data-bind=\"value: to,
		            			  attr: { name: '{$this->getAttributeInputName($row->getIndex(), 'convert')}[' + \$index() + '][to]',
    										id: 'convert_row_to_{$row->getIndex()}_' + \$index()}\"
		            	 class=\" input-text admin__control-text\">
				</span>";

        $html .="<input type='hidden' name='{$this->getAttributeInputName($row->getIndex(), 'composed_value')}' data-bind='value: attributes()[{$row->getGridIndex()}].composed_value'/>";
        return $html;
    }

    protected function getAttributeInputName($rowIndex, $name)
    {
        return Profile::CONFIG_FEED . '[' . Profile::CONFIG_ATTRIBUTES . ']' . "[{$rowIndex}][{$name}]";
    }

    /**
     * Create label with info for 'Label' column.
     *
     * @param mixed $value
     * @param \Magento\Framework\DataObject $row
     * @param \Magento\Framework\DataObject $column
     */
    public function decorateAttributeLabel($value, \Magento\Framework\DataObject $row, \Magento\Framework\DataObject $column)
    {
        return $this->_getLabelHtmlWithValue($row, $value);
    }

    /**
     * Select field magento attribute HTML with selected value.
     *
     * @param Attribute $attribute
     * @param mixed $value
     * @return \Magento\Framework\Phrase|string
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    protected function _getSelectHtmlWithValue(\Magento\Framework\DataObject $row, $value)
    {
        if (!isset($this->_magentoAttributeOptions)) {
            $this->_magentoAttributeOptions = $this->attributeSource->toIndexedArray($this->profile->getStoreId(), $this->profile->getTaxonomyLabel());
        }

        $options = $this->_magentoAttributeOptions;

        if ($size = count($options)) {
            array_unshift($options, ['value' => '', 'label' => __("Please select attribute.")]);

            $arguments = [
                'name' => Profile::CONFIG_FEED . '[' . Profile::CONFIG_ATTRIBUTES . ']' . "[{$row->getIndex()}][magento]",
                'id' => "magento_attribute_" . $row->getCode(),
                'class' => 'admin__control-select select select-attributes-magento',
                'extra_params' => "data-bind='value: attributes()[{$row->getGridIndex()}].magento'"
                ];
            /** @var $selectBlock \Magento\Framework\View\Element\Html\Select */
            $selectBlock = $this->_layout->createBlock(
                'Magento\Framework\View\Element\Html\Select',
                '',
                ['data' => $arguments]
            );
            return $selectBlock->setOptions($options)->setValue($value)->getHtml();
        } else {
            return __('Attribute load failed.');
        }
    }

    /**
     * Input text constant HTML with value
     *
     * @param Attribute $attribute
     * @param mixed $value
     * @return string
     */
    protected function _getInputHtmlWithValue(\Magento\Framework\DataObject $row, $value)
    {
        $html = "<input type='text' name='" . Profile::CONFIG_FEED . '[' . Profile::CONFIG_ATTRIBUTES . ']' . "[{$row->getIndex()}][constant]'"
                . ' class="admin__control-text input-text input-text-attributes-constant"'
                . " data-bind='value: attributes()[{$row->getGridIndex()}].constant'";
        if ($value) {
            $html .= ' value="' . $this->escapeHtml($value) . '"';
        }
        return $html . ' />';
    }

    /**
     * Settings Button HTML with value
     *
     * @param Attribute $attribute
     * @param mixed $value
     * @return string
     */
    protected function _getSettingsButtonHtmlWithValue(\Magento\Framework\DataObject $row, $value)
    {
        $buttonLabel = __("Settings");

        $html = "<button id='attribute_settings' data-bind='click: function(){ openSettings({$row->getGridIndex()}); }' data-action='open-attribute-settings' class='primary add'>{$buttonLabel}</button>";
        return $html;
    }

    /**
     * Settings Button HTML with value
     *
     * @param Attribute $attribute
     * @param mixed $value
     * @return string
     */
    protected function _getLabelHtmlWithValue(\Magento\Framework\DataObject $row, $value)
    {
        $label = "<strong>{$value}</strong>";
        $html = $label . "<br>";
        $desc = $row->getDescription();
        if (isset($desc) && isset($desc['text'])) {
            $text = "";
            if (strlen($desc['text']) > self::ATTRIBUTE_DESCRIPTION_PREVIEW_LENGHT) {
                $text = substr($desc['text'], 0, self::ATTRIBUTE_DESCRIPTION_PREVIEW_LENGHT) . "...";
            } elseif (!empty($desc['text'])) {
                $text = $desc['text'];
            }

            if (!empty($text)) {
                $html .= $text . " ";
                $html .= "<a id='attribute_info' target='_blank' href='#' data-bind='click: function(){ openInfo({$row->getGridIndex()}); }' data-action='open-attribute-info' >Read More</a>";
            }
        }

        return $html;
    }

    public function getProfile()
    {
        return $this->profile;
    }
}
