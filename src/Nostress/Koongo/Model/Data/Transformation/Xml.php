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
 * Xml data transformation for export process
* @category Nostress
* @package Nostress_Koongo
*
*/
namespace Nostress\Koongo\Model\Data\Transformation;

class Xml extends \Nostress\Koongo\Model\Data\Transformation
{
    const GROUP_ID = 'group_id';
    const IS_CHILD = 'is_child';

    const MAIN_TAG = 'items';
    const ITEM_TAG = 'item';
    const BASIC_ATTRIBUTES = 'attributes';
    const MULTI_ATTRIBUTES = 'multi_attributes';
    const CUSTOM_ATTRIBUTES = 'custom_attributes';
    const CONVERSIONS = 'convert';

    const ATTRIBUTE_TYPE_CUSTOM = 'custom';

    const EMPTY_VALUE = "";

    const DEF_TEXT_SEPARATOR = '"';
    const DEF_SETUP_SEPARATOR = ',';
    const DEF_DECIMAL_DELIMITER = '.';
    const DEF_PATH_IDS_DELIMITER = '/';
    const DEF_MATH_OPS_PREFIX = "[[";
    const DEF_MATH_OPS_SUFFIX = "]]";

//     const SUPER_ATTRIBUTES = 'super_attributes';
    const MEDIA_GALLERY = 'media_gallery';
    const CATEGORIES = 'categories';
    const TIER_PRICES = 'tier_prices';
    const REVIEWS = 'reviews';

    const PP_CDATA = 'cdata';
    const PP_ENCODE_SPECIAL = 'encode_special_chars';
    const PP_DECODE_SPECIAL = 'decode_special_chars';
    const PP_ENCODE_HTML_ENTITY = 'encode_html_entity';
    const PP_DECODE_HTML_ENTITY = 'decode_html_entity';
    const PP_REMOVE_EOL = 'remove_eol';
    const PP_STRIP_TAGS = 'strip_tags';
    const PP_DELETE_SPACES = 'delete_spaces';

    const URL_SUFFIX = "#";

    protected $_store;
    protected $_mediaUrl;
    protected $_parent;
    protected $_groupId;
    protected $_row;
    protected $_multiAttributes;
    protected $_regExpIllegalChars;

    /**
     * Array of options for particular multiselect attributes. Indexed by attrribute code.
     * @var unknown_type
     */
    protected $_multiselectAttributesOptionArray;
    protected $_itemData = "";
    protected $_categoryTree;
    protected $_multiAttributesMap;
    protected $_customAttributesMap;
    protected $_skippedProductsCounter;
    protected $changeDecimalDelimiter = false;
    protected $_simpleProductCounter = 0;
    protected $_shippingExportEnabled = false;
    protected $_postProcessFunctions = [	self::PP_CDATA => "Character data",
                                                self::PP_ENCODE_SPECIAL=>"Encode html special chars",
                                                self::PP_DECODE_SPECIAL=>"Decode html special chars",
                                                self::PP_ENCODE_HTML_ENTITY=>"Encode html entities",
                                                self::PP_DECODE_HTML_ENTITY=>"Decode html entities",
                                                self::PP_REMOVE_EOL=>"Remove end of lines",
                                                self::PP_STRIP_TAGS=>"Strip HTML tags",
                                                self::PP_DELETE_SPACES=>"Delete spaces"
                                                ];

    /**
     * Main inicialization funciton
     * @param array $params
     */
    public function init($params)
    {
        parent::init($params);
        $this->resetVariables();
        $this->preprocessAttributes();
        $this->initAttributeMaps();
        $this->initDecimalDelimiter();
        $this->initShippingIntervals();
        $this->initMultiselectAttributesOptionArray();
    }

    protected function resetVariables()
    {
        $this->_itemData = "";
        $this->_categoryTree = null;
        $this->_multiAttributesMap = null;
        $this->_customAttributesMap = null;
        $this->_skippedProductsCounter = 0;
        $this->changeDecimalDelimiter = false;
        $this->resetSimpleProductsCounter();
        $this->_shippingExportEnabled = false;
        $this->_store = null;
        $this->_mediaUrl = null;
    }

    /**
     * Load attributes from configuration
     */
    protected function preprocessAttributes()
    {
        $attributes = $this->getAttributes();
        $customAttributes = $this->getCustomAttributes();

        if (empty($attributes) && empty($customAttributes) && $this->getCategoryTree() == 0) {
            $this->throwException("3");
        }
    }

    protected function getMultiAttributes()
    {
        if (!$this->_multiAttributes) {
            $this->_multiAttributes = [];
            $this->_multiAttributes[$this->addModuleAttributePrefix(self::MEDIA_GALLERY)] = [self::ORIGINAL_CODE => self::MEDIA_GALLERY, self::XML_ITEM_LABEL => "image"];
            $this->_multiAttributes[$this->addModuleAttributePrefix(self::CATEGORIES)] =  [self::ORIGINAL_CODE => self::CATEGORIES, self::XML_ITEM_LABEL => "category"];
            $this->_multiAttributes[$this->addModuleAttributePrefix(self::TIER_PRICES)] =  [self::ORIGINAL_CODE => self::TIER_PRICES, self::XML_ITEM_LABEL => "tier_price"];
            $this->_multiAttributes[$this->addModuleAttributePrefix(self::REVIEWS)] =  [self::ORIGINAL_CODE => self::REVIEWS, self::XML_ITEM_LABEL => "review"];
        }
        return $this->_multiAttributes;
    }

    public function addModuleAttributePrefix($attributeCode)
    {
        return self::MODULE_PATTRIBUTE_PREFIX . $attributeCode;
    }

    protected function initMultiselectAttributesOptionArray()
    {
        $this->_multiselectAttributesOptionArray = $this->getMultiselectAttributesOptionArray();
    }

    /**
     * Inits decimal delimiter value
     */
    protected function initDecimalDelimiter()
    {
        $delimiter = $this->getDecimalDelimiter();
        if ($delimiter != self::DEF_DECIMAL_DELIMITER) {
            $this->changeDecimalDelimiter = true;
        }
    }

    /**
     * Init attribute mapping setup
     */
    protected function initAttributeMaps()
    {
        $this->_multiAttributesMap = [];

        $attributes = $this->getAttributes();
        $attributes = $this->processAttributeArray($attributes);
        $this->setAttributes($attributes);

        $customAttributes = $this->getCustomAttributes();
        if (empty($customAttributes)) {
            $customAttributes=[];
        }
        $customAttributes = $this->processAttributeArray($customAttributes);
        $this->_customAttributesMap = $customAttributes;
    }

    protected function processAttributeArray($attributes)
    {
        //Format price
        $attributes = $this->preparePriceFields($attributes);

        foreach ($attributes as $key => $attribute) {
            //add shipping cost into feed
            $this->initShippingExport($attribute);

            //add static attributes
            $attribute = $this->initStaticAttributes($attribute);

            //prepare postprocess functions
            $attribute = $this->initPostprocessActions($attribute);

            //check limit
            $attribute = $this->initLimit($attribute);

            //prepare conversions/revrite
            $attribute = $this->initTranslation($attribute);

            //Init composed value attributes
            $attribute = $this->initComposedValueAttributes($attribute);

            //Remove multi attributes from attribute map
            if ($attribute) {
                $attribute = $this->initMultiAttributes($attribute);
            }
            if ($attribute === false) {
                unset($attributes[$key]);
                continue;
            }
            $attributes[$key] = $attribute;
        }
        return $attributes;
    }

    public function insertCategories($categories)
    {
        $this->_categoryTree = [];
        foreach ($categories as $category) {
            $this->insertCategoryInTree($category);
        }

        $categoriesAttribute = $this->getMultiAttributes()[self::MODULE_PATTRIBUTE_PREFIX . self::CATEGORIES];

        $categoriesXmlString = $this->treeToXml($this->_categoryTree, $categoriesAttribute[self::ORIGINAL_CODE], $categoriesAttribute[self::XML_ITEM_LABEL]);
        $categoriesXmlString = $this->getElement($categoriesAttribute[self::ORIGINAL_CODE], $categoriesXmlString);
        $this->appendResult($categoriesXmlString);
    }

    /**
     * Main transformation function
     * Creates target XML file
     * @param $data
     */
    public function transform($data)
    {
        parent::transform($data);
        $saveItemData = false;

        $isChildAttributeCode =  $this->addModuleAttributePrefix(self::IS_CHILD);

        foreach ($data as $row) {
            $this->setRow($row);
            $isChild = $this->getValue($isChildAttributeCode);
            if (!$isChild || $this->getChildsOnly()) {
                $saveItemData = true;
            }

            if ($this->getChildsOnly() && !$isChild && !$this->isSimpleProduct()) {
                $this->_skippedProductsCounter++;
                continue;
            }

            if ($saveItemData) {
                $saveItemData = false;
                $this->saveItemData();
            }

            $label = $this->getIsChildLabel($isChild);
            $this->addItemData($this->getTag($label));

            $this->processBasicAttributes($isChild);
            $this->processCustomAttributes($isChild);
            $this->processMultiAttributes($isChild);
            $this->addItemData($this->getTag($label, true));
        }
    }

    /**
     * Set row with product data for transformation
     * @param unknown_type $row
     */
    protected function setRow($row)
    {
        $row = $this->preProcessRow($row);

        $groupIdAttributeCode =  $this->addModuleAttributePrefix(self::GROUP_ID);
        if (array_key_exists($groupIdAttributeCode, $row) && $this->setGroupId($row[$groupIdAttributeCode])) {
            $this->setParent($row);
            $this->resetSimpleProductsCounter();
        } else {
            $this->incrementSimpleProductsCounter();
        }
        $this->_row = $row;
    }

    /**
     * Returns parent-child label
     * @param unknown_type $isChild
     */
    protected function getIsChildLabel($isChild)
    {
        $label = self::PARENT;
        if ($this->getChildsOnly()) {
            return $label;
        }
        if ($isChild) {
            $label = self::CHILD;
        }
        return $label;
    }

    /**
     * Process basic attributes
     * @param bool $isChild Is product child.
     */
    protected function processBasicAttributes($isChild)
    {
        $map = $this->getAttributes();
        $this->addItemData($this->getTag(self::BASIC_ATTRIBUTES));
        foreach ($map as &$attributeInfo) {
            $value = $this->getAttributeValue($attributeInfo, $isChild);
            if (!$this->isValueEmpty($value)) {
                $this->addItemData($this->getElement($attributeInfo[self::CODE], $value));
            }
        }
        $this->addItemData($this->getTag(self::BASIC_ATTRIBUTES, true));
    }

    /**
     * Process custom attributes
     * @param bool $isChild Is product child.
     */
    protected function processCustomAttributes($isChild)
    {
        if (!isset($this->_customAttributesMap) || empty($this->_customAttributesMap)) {
            return;
        }

        $fileType = $this->getFileType();
        $this->addItemData($this->getTag(self::CUSTOM_ATTRIBUTES));
        foreach ($this->_customAttributesMap as &$attributeInfo) {
            $value = $this->getAttributeValue($attributeInfo, $isChild);
            if ($this->isValueEmpty($value) && $fileType == self::XML) {
                continue;
            }

            $this->addItemData($this->getTag(self::ATTRIBUTE));
            $this->addItemData($this->getElement(self::VALUE, $value));
            $this->addItemData($this->getElement(self::TAG, $attributeInfo[self::TAG]));
            $this->addItemData($this->getElement(self::LABEL, $attributeInfo[self::LABEL]));
            $this->addItemData($this->getTag(self::ATTRIBUTE, true));
        }
        $this->addItemData($this->getTag(self::CUSTOM_ATTRIBUTES, true));
    }

    /**
     * Process multi attributes
     * @param bool $isChild Is product child.
     */
    protected function processMultiAttributes($isChild)
    {
        $multiAttributes = $this->getMultiAttributes();
        if (empty($this->_multiAttributesMap)) {
            return;
        }
        $this->addItemData($this->getTag(self::MULTI_ATTRIBUTES));

        foreach ($this->_multiAttributesMap as $attributeCode => $attritueItem) {
            $originalAttributeCode = $multiAttributes[$attributeCode][self::ORIGINAL_CODE];
            $loadParentValue = false;
            if (isset($attritueItem[self::PARENT_ATTRIBUTE_VALUE])) {
                $loadParentValue  = $this->evaluateParentAttributeCondition($isChild, $attritueItem[self::PARENT_ATTRIBUTE_VALUE]);
            }

            $multiAttribValue = $this->getValue($attributeCode, $loadParentValue);

            if (!isset($multiAttribValue)) {
                continue;
            }

            if (!is_array($multiAttribValue)) {
                $columns = $this->getMultiAttributeColumns($attributeCode);
                if (!isset($columns)) {
                    return;
                }
                $multiAttribValue = $this->parseAttribute($multiAttribValue, $columns);

                if ($originalAttributeCode == self::MEDIA_GALLERY) {
                    $multiAttribValue = $this->addMediaUrl($multiAttribValue);
                }

                $this->setParentAttribute($attributeCode, $multiAttribValue);
            }

            $string = $this->arrayToXml($multiAttribValue, $originalAttributeCode, $multiAttributes[$attributeCode][self::XML_ITEM_LABEL]);
            $this->addItemData($string);
        }

        $this->addItemData($this->getTag(self::MULTI_ATTRIBUTES, true));
    }

    protected function addMediaUrl($mediaGallery)
    {
        $mediaUrl = $this->getMediaUrl();
        foreach ($mediaGallery as $key => $item) {
            $mediaGallery[$key][self::VALUE] = $mediaUrl . $item[self::VALUE];
        }
        return $mediaGallery;
    }

    /**
     * Method loads and prepares attribute value.
     * Attribute value is concatenated with prefix and suffix.
     * @param array $setup Attribute setup.
     * @param bool $isChild Is product child.
     */
    protected function getAttributeValue(&$setup, $isChild)
    {
        $magentoAttribute = $setup[self::PLATFORM_ATTRIBUTE];

        $eppav = "0";
        if (isset($setup[self::PARENT_ATTRIBUTE_VALUE])) {
            $eppav = $setup[self::PARENT_ATTRIBUTE_VALUE];
        }
        $parentCondition = $this->evaluateParentAttributeCondition($isChild, $eppav);

        if ($this->_shippingExportEnabled && $magentoAttribute == self::MODULE_PATTRIBUTE_PREFIX . self::SHIPPING_COST) {
            $value = $this->getShippingCostValue($parentCondition);
        } else {
            $value = $this->getValue($magentoAttribute, $parentCondition);
        }

        if ($this->isValueEmpty($value) && isset($setup[self::CONSTANT])) {
            $value = $setup[self::CONSTANT];
        }

        $composedValue = "";
        if (!empty($setup[self::COMPOSED_VALUE])) {
            $composedValue = $setup[self::COMPOSED_VALUE];
            if (isset($setup[self::COMPOSED_VALUE_VARS])) {
                $composedValue = $this->replaceVarsWithValues($composedValue, $setup[self::COMPOSED_VALUE_VARS], $parentCondition);
            }
            $composedValue = $this->evaluateMathExpressions($composedValue);
        }

        if (!$this->isValueEmpty($composedValue)) {
            $value = $composedValue;
        }

        if (!empty($setup[self::CONVERSIONS])) {
            //all conversions are evaluated as regular expressions
            foreach ($setup[self::CONVERSIONS] as $option) {
                $from = $option['from'];
                $to = $option['to'];
                $valueWithoutSpaces = trim($value);

                if ($from == "") {
                    if ($valueWithoutSpaces == "") {
                        $value = $to;
                    }
                    continue;
                }

                //ignore invalid reg. exp
                if (preg_match($from, $value) === false) {
                    // the regex failed and is likely invalid
                    continue;
                }
                $value = preg_replace($from, $to, $value);
            }
        }

        //prepocess value
        $postProcessFunctions = isset($setup[self::POST_PROCESS]) ? $setup[self::POST_PROCESS] : [];
        $limit = isset($setup[self::LIMIT]) ? $setup[self::LIMIT] : null;
        $value = $this->postProcess($value, $postProcessFunctions, $limit);

        return $value;
    }

    protected function replaceVarsWithValues($string, $vars, $parent)
    {
        foreach ($vars as $key => &$data) {
            if ($this->_shippingExportEnabled && $data == self::MODULE_PATTRIBUTE_PREFIX . self::SHIPPING_COST) {
                $value = $this->getShippingCostValue($parent);
            } else {
                $value = $this->getValue($data, $parent);
            }

            $data = str_replace('"', '\"', $value);
        }
        return str_replace(array_keys($vars), array_values($vars), $string);
    }

    protected function evaluateMathExpressions($string)
    {
        $matches = [];
        $numMatches =  preg_match_all('/\[\[[^\]\]]*([^\]]\][^\]])*[^\]\]]*\]\]/', $string, $matches);
        if ($numMatches == 0) {
            return $string;
        } else {
            if ($this->changeDecimalDelimiter) {
                $delimiter = $this->getDecimalDelimiter();
                if (!empty($delimiter)) {
                    $patternDelimiter = '/([0-9])' . $delimiter . '([0-9])/i';
                    $replacementDelimiter = '$1' . self::DEF_DECIMAL_DELIMITER . '$2';

                    $patternDefDelimiter = '/([0-9])' . "\\" . self::DEF_DECIMAL_DELIMITER . '([0-9])/i';
                    $replacementDefDelimiter = '$1' . $delimiter . '$2';
                }
            }

            ob_start();
            foreach ($matches[0] as $match) {
                $result = "";
                $expression = str_replace(self::DEF_MATH_OPS_PREFIX, "", $match);
                $expression = str_replace(self::DEF_MATH_OPS_SUFFIX, "", $expression);

                if ($this->changeDecimalDelimiter && !empty($delimiter)) {
                    $expression = preg_replace($patternDelimiter, $replacementDelimiter, $expression);
                }

                if (!empty($expression)) {
                    eval('$result = ' . $expression . ';');
                }

                if (!empty($result) && $this->changeDecimalDelimiter && !empty($delimiter)) {
                    //if(is_numeric($result))
                    //	$result = round($result,2);
                    $result = preg_replace($patternDefDelimiter, $replacementDefDelimiter, $result);
                }

                $string = str_replace($match, $result, $string);
            }
            ob_end_clean();
            return $string;
        }
    }

    protected function getOptionText($valueIds, $options)
    {
        if (!isset($valueIds) || $valueIds === "") {
            return "";
        }

        $text = "";
        if (isset($options[$valueIds])) {
            $text = $options[$valueIds];
        }

        $valueIdsArray = explode(',', $valueIds);

        $values = [];
        foreach ($valueIdsArray as $id) {
            if (isset($options[$id])) {
                $values[] = $options[$id];
            }
        }

        $values = implode(",", $values);
        return $values;
    }

    protected function getValue($index, $parent = '0', $prepareValue = true)
    {
        switch ($parent) {
            case '0':
                $value = $this->getArrayValue($index, $this->_row);
                break;
            case '1':
                $value = $this->getParentValue($index);
                break;
            default:
                $value = $this->getArrayValue($index, $this->_row);
                if (empty($value)) {
                    $value = $this->getParentValue($index);
                }
                break;
        }

        //Replace multiselect attribute values
        if (isset($this->_multiselectAttributesOptionArray[$index])) {
            $value = $this->getMultiSelectValue($index, $value);
        }

        if ($prepareValue) {
            $value = $this->prepareValue($value, $index, $parent);
        }
        return $value;
    }

    protected function getMultiSelectValue($attributeCode, $value)
    {
        if (isset($this->_multiselectAttributesOptionArray[$attributeCode][$value])) {
            return $this->_multiselectAttributesOptionArray[$attributeCode][$value];
        }

        $optionText = $this->getOptionText($value, $this->_multiselectAttributesOptionArray[$attributeCode]);
        $this->_multiselectAttributesOptionArray[$attributeCode][$value] = $optionText;
        return $optionText;
    }

    /**
     * Returns parent product value
     * @param unknown_type $index
     */
    protected function getParentValue($index)
    {
        return $this->getArrayValue($index, $this->_parent);
    }

    /**
     * Prepares attribute value
     * @param string $value Attribute value.
     * @param string $index Attribute index.
     * @param bool $parent Is parent value.
     */
    protected function prepareValue($value, $index, $parent)
    {
        if ($this->changeDecimalDelimiter && is_numeric($value)) {
            $value = str_replace(self::DEF_DECIMAL_DELIMITER, $this->getDecimalDelimiter(), $value);
        }

        if ($parent == '1' && $index == self::MODULE_PATTRIBUTE_PREFIX . self::URL) {
            $value .= self::URL_SUFFIX . $this->getSimpleProductsCounterValue();
        }

        return $value;
    }

    /**
     * Post processing actions with attribute value
     */
    protected function postProcess($value, $setup = null, $limit  = null)
    {
        if ($this->isValueEmpty($value)) {
            return $value;
        }

        if (empty($setup) || !is_array($setup)) {
            $setup = [];
        }

        foreach ($setup as $item) {
            switch ($item) {
                case self::PP_ENCODE_SPECIAL:
                    $value = $this->ppEncodeSpecial($value);
                    break;
                case self::PP_DECODE_SPECIAL:
                    $value = $this->ppDecodeSpecial($value);
                    break;
                case self::PP_ENCODE_HTML_ENTITY:
                     $value = $this->ppEncodeHtml($value);
                       break;
                case self::PP_DECODE_HTML_ENTITY:
                      $value = $this->ppDecodeHtml($value);
                       break;
                case self::PP_STRIP_TAGS:
                    $value = $this->ppStripTags($value);
                    break;
                case self::PP_DELETE_SPACES:
                    $value = $this->ppDeleteSpaces($value);
                    break;
                case self::PP_REMOVE_EOL:
                    $value = $this->ppRemoveEol($value);
                    break;
            }
        }
        $value = $this->ppFile($value);
        $value = $this->ppDefault($value, $limit);

        return $value;
    }

    /**
     * Returns multi attribute colum names
     * @param string $attributeName Multi attribute name.
     */
    protected function getMultiAttributeColumns($attributeName)
    {
        $cols = parent::getMultiAttributeColumns();
        if (!empty($cols[$attributeName])) {
            return array_keys($cols[$attributeName]);
        } else {
            return null;
        }
    }

    /**
     * Parse multi attribute.
     * @param string $attributeValue
     * @param array $columns Column names as array.
     * @return Separate attributes.
     */
    protected function parseAttribute($attributeValue, $columns)
    {
        $itemSeparator = self::GROUP_ROW_ITEM_SEPARATOR;
        $rowSeparator = self::GROUP_ROW_SEPARATOR;

        $rows = explode($rowSeparator, $attributeValue);
        $result = [];
        foreach ($rows as $key => $row) {
            $values = explode($itemSeparator, $row);
            if (count($columns) == count($values)) {
                $result[$key] = array_combine($columns, $values);
            }
        }
        return $result;
    }

    protected function setParentAttribute($index, $value)
    {
        $isChildAttributeCode =  $this->addModuleAttributePrefix(self::IS_CHILD);
        if (!$this->getValue($isChildAttributeCode) && isset($this->_parent)) {
            $this->_parent[$index] = $value;
        }
    }

    protected function getShippingConfig($index, $default = "")
    {
        $shipping = $this->getShipping();
        if (!empty($shipping[$index])) {
            return $shipping[$index];
        } else {
            return $default;
        }
    }

    protected function getShippingCostValue($parentCondition)
    {
        $dependentAttribute = $this->getShippingConfig(self::DEPENDENT_ATTRIBUTE);
        $intervals = $this->getShippingConfig(self::COST_SETUP);
        $dependentAttributeValue = $this->getValue($dependentAttribute, $parentCondition, false);
        if (empty($dependentAttribute) || empty($intervals) || empty($dependentAttributeValue)) {
            return "";
        }

        foreach ($intervals as $interval) {
            if ($dependentAttributeValue >= $interval[self::PRICE_FROM] && $dependentAttributeValue < $interval[self::PRICE_TO]) {
                return $interval[self::COST];
            }
        }
        return "";
    }

    /*********************************************** INIT ATTRIBUTE MAPs -- FUNCTIONS -- START *********************************************/
    protected function initShippingExport($attribute)
    {
        if (!empty($attribute[self::PLATFORM_ATTRIBUTE]) && $attribute[self::PLATFORM_ATTRIBUTE] == self::MODULE_PATTRIBUTE_PREFIX . self::SHIPPING_COST) {
            $this->_shippingExportEnabled = true;
        }
    }

    protected function initShippingIntervals()
    {
        $intervals = $this->getShippingConfig(self::COST_SETUP);
        if (empty($intervals)) {
            return;
        }
        foreach ($intervals as $index => $interval) {
            if (empty($interval[self::PRICE_FROM]) && empty($interval[self::PRICE_TO])) {
                unset($intervals[$index]);
                continue;
            } elseif (empty($interval[self::PRICE_FROM])) {
                $interval[self::PRICE_FROM] = self::SHIPPING_INTERVAL_MIN;
            } elseif (empty($interval[self::PRICE_TO])) {
                $interval[self::PRICE_TO] = self::SHIPPING_INTERVAL_MAX;
            }

            $intervals[$index][self::PRICE_TO] = str_replace(",", self::DEF_DECIMAL_DELIMITER, $interval[self::PRICE_TO]);
            $intervals[$index][self::PRICE_FROM] = str_replace(",", self::DEF_DECIMAL_DELIMITER, $interval[self::PRICE_FROM]);
            $intervals[$index][self::COST] = str_replace(self::DEF_DECIMAL_DELIMITER, $this->getDecimalDelimiter(), $interval[self::COST]);
        }

        $shipping = $this->getShipping();
        $shipping[self::COST_SETUP] = $intervals;
        $this->setShipping($shipping);
    }

    protected function initStaticAttributes($attribute)
    {
        $resetMagentoAttribute = true;
        if (!isset($attribute[self::CONSTANT])) {
            $attribute[self::CONSTANT] = "";
        }
        switch ($attribute[self::PLATFORM_ATTRIBUTE]) {
            //Add currency into feed
            case self::CURRENCY:
                $attribute[self::CONSTANT] .= $this->getCurrency();
                break;
            case self::COUNTRY_CODE:
                $attribute[self::CONSTANT] .= $this->getStoreCountry();
                break;
            case self::LOCALE:
                $attribute[self::CONSTANT] .= $this->getStoreLocale();
                break;
            case self::LANGUAGE:
                $attribute[self::CONSTANT] .= $this->getStoreLanguage();
                break;
            case $this->addModuleAttributePrefix(self::SHIPPING_METHOD_NAME):
                $attribute[self::CONSTANT] .= $this->getShippingConfig(self::METHOD_NAME);
                $resetMagentoAttribute = false;
                break;
            default:
                $resetMagentoAttribute = false;
                break;
        }
        if ($resetMagentoAttribute) {
            $attribute[self::PLATFORM_ATTRIBUTE] = "";
        }
        return $attribute;
    }

    protected function initPostprocessActions($attribute)
    {
        if (isset($attribute[self::POST_PROCESS])) {
            $postprocessFuncitons = $attribute[self::POST_PROCESS];
            if (empty($postprocessFuncitons)) {
                $postprocessFuncitons = [];
            } else {
                if (strpos($postprocessFuncitons, self::POSTPROC_DELIMITER) === false) {
                    $postprocessFuncitons = [$postprocessFuncitons];
                } else {
                    $postprocessFuncitons = explode(self::POSTPROC_DELIMITER, $postprocessFuncitons);
                }
            }
            $attribute[self::POST_PROCESS] = $postprocessFuncitons;
        }
        return $attribute;
    }

    protected function initLimit($attribute)
    {
        if (isset($attribute[self::LIMIT])) {
            $limit = $attribute[self::LIMIT];
            if (empty($limit) || !is_numeric($limit) || $limit < 0) {
                unset($attribute[self::LIMIT]);
            }
        }
        return $attribute;
    }

    protected function initTranslation($attribute)
    {
        if (!empty($attribute[self::CONVERSIONS])) {
            $array = [];
            foreach ($attribute[self::CONVERSIONS] as $key => $option) {
                $from = $option["from"];
                //it is not regular expression, transform it to reg exp. and add slashes
                if ($from != "" &&  !preg_match("/^\/.+\/[a-zA-Z]*$/i", $from)) {
                    $from = "/" . preg_quote($from, '/') . "/";
                    $attribute[self::CONVERSIONS][$key]["from"] = $from;
                }
            }
        }

        return $attribute;
    }

    protected function initComposedValueAttributes($attribute)
    {
        return $this->initContextAttributes($attribute, self::COMPOSED_VALUE, self::COMPOSED_VALUE_VARS);
    }

    protected function initContextAttributes($attribute, $type, $varType)
    {
        if (!isset($attribute[$type])) {
            return $attribute;
        }
        $vars = $this->grebVariables($attribute[$type], true, true);
        if (!empty($vars)) {
            $attribute[$varType] = $vars;
        }
        return $attribute;
    }

    protected function initMultiAttributes($attribute)
    {
        $multiAttributes = $this->getMultiAttributes();
        if (isset($multiAttributes[$attribute[self::PLATFORM_ATTRIBUTE]])) {
            $this->_multiAttributesMap[$attribute[self::PLATFORM_ATTRIBUTE]] = $attribute;
            return false;
        }
        return $attribute;
    }

    protected function preparePriceFields($map)
    {
        $priceFormat = $this->getPriceFormat();

        $currency = $this->getCurrency();
        $symbol = $this->getCurrencySymbol();

        foreach ($map as $key => $attributesInfo) {
            if (!isset($attributesInfo[self::COMPOSED_VALUE])) {
                $attributesInfo[self::COMPOSED_VALUE] = "";
            }

            if (empty($attributesInfo[self::COMPOSED_VALUE]) &&
                    (strpos($attributesInfo[self::PLATFORM_ATTRIBUTE], "price") !== false
                            || $attributesInfo[self::PLATFORM_ATTRIBUTE] == self::MODULE_PATTRIBUTE_PREFIX . self::SHIPPING_COST)) {
                switch ($priceFormat) {
                    case self::PRICE_FORMAT_CURRENCY_SUFFIX:
                        $attributesInfo[self::COMPOSED_VALUE] = self::BRACE_DOUBLE_OPEN . $attributesInfo[self::PLATFORM_ATTRIBUTE] . self::BRACE_DOUBLE_CLOSE . " " . $currency;
                        break;
                    case self::PRICE_FORMAT_CURRENCY_PREFIX:
                        $attributesInfo[self::COMPOSED_VALUE] = $currency . " " . self::BRACE_DOUBLE_OPEN . $attributesInfo[self::PLATFORM_ATTRIBUTE] . self::BRACE_DOUBLE_CLOSE;
                        break;
                    case self::PRICE_FORMAT_SYMBOL_SUFFIX:
                        $attributesInfo[self::COMPOSED_VALUE] = self::BRACE_DOUBLE_OPEN . $attributesInfo[self::PLATFORM_ATTRIBUTE] . self::BRACE_DOUBLE_CLOSE . " " . $symbol;
                        break;
                    case self::PRICE_FORMAT_SYMBOL_PREFIX:
                        $attributesInfo[self::COMPOSED_VALUE] = $symbol . " " . self::BRACE_DOUBLE_OPEN . $attributesInfo[self::PLATFORM_ATTRIBUTE] . self::BRACE_DOUBLE_CLOSE;
                        break;
                    default:
                        break;
                }
                $map[$key] = $attributesInfo;
            }
        }
        return $map;
    }

    /*********************************************** INIT ATTRIBUTE MAPS FUNCTIONS -- END *********************************************/

    //******************************** POST PROCESS ACTIONS - START******************************///
    protected function ppEncodeSpecial($value)
    {
        return htmlspecialchars($value);
    }

    protected function ppDecodeSpecial($value)
    {
        return htmlspecialchars_decode($value);
    }

    protected function ppEncodeHtml($value)
    {
        return htmlentities($value, ENT_COMPAT, 'UTF-8');
    }

    protected function ppDecodeHtml($value)
    {
        return html_entity_decode($value, ENT_COMPAT, 'UTF-8');
    }

    protected function ppStripTags($value)
    {
        return strip_tags($value);
    }

    protected function ppDeleteSpaces($string)
    {
        return preg_replace("/\s+/", '', $string);
    }

    protected function ppRemoveEol($string)
    {
        return str_replace(["\r\n", "\r", "\n"], ' ', $string);
    }

    protected function ppFile($value)
    {
        switch ($this->getFileType()) {
            case self::CSV:
            case self::TXT:
                $value = $this->ppCsv($value);
                break;
            //case self::XML:
            default:
                break;
        }
        return $value;
    }

    protected function ppCsv($value)
    {
        $textEnclosure = $this->getTextEnclosure();
        $stringToRemove = "";
        if ($textEnclosure != "") {
            //if text enclosure is not empty string -> remove all text enclosures from value
            $stringToRemove = $textEnclosure;
        } else {
            //if text enclosure is empty string -> remove all column delimiters from value
            $stringToRemove = $this->getColumnDelimiter();
        }

        //Remove end of line characters
        $value =  str_replace(["\r\n", "\r", "\n",$stringToRemove], ' ', $value);

        return $value;
    }

    protected function ppDefault($value, $limit)
    {
        $value = $this->removeIllegalChars($value);
        $value = $this->removeCdataChars($value);
        $value = $this->changeEncoding($this->getEncoding(), $value);
        $value = $this->ppLimit($value, $limit, $this->getEncoding());
        $value = $this->getCdataString($value);
        return $value;
    }

    protected function ppLimit($value, $limit, $encoding)
    {
        if (isset($limit)) {
            $value =  mb_substr($value, 0, $limit, $encoding);
        }
        return $value;
    }
    //******************************** POST PROCESS ACTIONS - END******************************///

    //***************************** PARENT METHODS OVERWRITE  **************//
    public function getResult($allData = false)
    {
        if ($allData) {
            $this->saveItemData();
        }
        $result = parent::getResult();
        if (!empty($result)) {
            $result = $this->getHeader() . $result . $this->getTail();
        }
        return $result;
    }

    protected function check($data)
    {
        if (!parent::checkSrc($data) || !is_array($data)) {
            $message = $this->throwException("3");
        }
        return true;
    }

    protected function addItemData($string)
    {
        $this->_itemData .= $string;
    }

    protected function saveItemData()
    {
        if (empty($this->_itemData)) {
            return;
        }
        $element = $this->getElement(self::ITEM_TAG, $this->_itemData);
        $this->appendResult($element);
        $this->_itemData = "";
    }

    //***************************** PARENT METHODS OVERWRITE -- END **************//

    ///////////////////////////////////COMMON FUNCTIONS/////////////////////////////////

    /**
     * Remove illegal/non-ascii characters from inpit string.
     *
     **/
    protected function removeIllegalChars($string)
    {
        if (!isset($this->_regExpIllegalChars)) {
            $this->_regExpIllegalChars = $this->getRemoveIllegalCharsRegExpression();
        }
        return preg_replace($this->_regExpIllegalChars, '', $string);
    }

    public function getSkippedProductsCounter()
    {
        return $this->_skippedProductsCounter;
    }

    protected function resetSimpleProductsCounter()
    {
        $this->_simpleProductCounter = 0;
    }

    protected function incrementSimpleProductsCounter()
    {
        $this->_simpleProductCounter++;
    }

    protected function getSimpleProductsCounterValue()
    {
        return $this->_simpleProductCounter;
    }

    protected function setGroupId($groupId)
    {
        if ($groupId == $this->_groupId) {
            return false;
        } else {
            $this->_groupId = $groupId;
            return true;
        }
    }

    protected function setParent($row)
    {
        $this->_parent = $row;
    }

    protected function getHeader()
    {
        return "<?xml version=\"1.0\" encoding=\"{$this->getEncoding()}\"?><" . self::MAIN_TAG . ">";
    }

    protected function getTail()
    {
        return "</" . self::MAIN_TAG . ">";
    }

    protected function getElement($name, $value)
    {
        return "<{$name}>{$value}</{$name}>";
    }

    protected function getTag($name, $end = false)
    {
        if ($end) {
            return "</{$name}>";
        } else {
            return "<{$name}>";
        }
    }

    protected function preProcessRow($row)
    {
        $stockStatusAttributeCode = $this->addModuleAttributePrefix("stock_status");

        if (array_key_exists($stockStatusAttributeCode, $row)) {
            $stockStatus = $row[$stockStatusAttributeCode];
            $attribute = '';
            $stockStatus = $this->getStockStatusValue($stockStatus, $attribute);
            if (!empty($attribute)) {
                $stockStatus = $row[$attribute];
            }
            $row[$stockStatusAttributeCode] = $stockStatus;
        }
        return $row;
    }

    protected function getStockStatusValue($status, &$attribute)
    {
        $stock = $this->getStock();
        if ($status) {
            $status = $stock["yes"];
        } else {
            if (empty($stock["availability"])) {
                $status = $stock["no"];
            } else {
                $attribute =  $stock["availability"];
            }
        }
        return $status;
    }

    protected function getArrayValue($index, $array)
    {
        if (array_key_exists($index, $array)) {
            return $array[$index];
        } else {
            return self::EMPTY_VALUE;
        }
    }

    protected function isValueEmpty($value)
    {
        if (empty($value) && $value != "0") {
            return true;
        } else {
            return false;
        }
    }

    protected function isSimpleProduct()
    {
        return $this->getValue($this->addModuleAttributePrefix(self::PRODUCT_TYPE)) == "simple";
    }

    public function getChildsOnly()
    {
        $parentChilds = $this->getParentsChilds();
        if ($parentChilds == self::PCH_CHILDS_ONLY) {
            return true;
        }
        return false;
    }

    public function getPostProcessFunctions()
    {
        return $this->_postProcessFunctions;
    }

    protected function arrayToXml($input, $xmlSectionTag, $xmlItemTag)
    {
        $result = "";
        foreach ($input as $row) {
            $rowText = "";
            foreach ($row as $index => $value) {
                if ($this->isValueEmpty($value)) {
                    continue;
                }
                $value = $this->postProcess($value);
                $rowText .= $this->getElement($index, $value);
            }

            if (!empty($rowText)) {
                $result .= $this->getElement($xmlItemTag, $rowText);
            }
        }
        if (!empty($result)) {
            $result = $this->getElement($xmlSectionTag, $result);
        }
        return $result;
    }

    protected function treeToXml($input, $xmlSectionTag, $xmlItemTag)
    {
        $result = "";
        foreach ($input as $row) {
            $rowText = "";
            foreach ($row as $index => $value) {
                if ($this->isValueEmpty($value)) {
                    continue;
                }
                if ($index == self::CHILDREN) {
                    $value = $this->treeToXml($value, $xmlSectionTag, $xmlItemTag);
                } else {
                    $value = $this->postProcess($value);
                }

                $rowText .= $this->getElement($index, $value);
            }

            if (!empty($rowText)) {
                $result .= $this->getElement($xmlItemTag, $rowText);
            }
        }
        return $result;
    }

    /////////////////////CATEGORY PROCESS FUNCTIONS//////////////////////////////////

    protected function insertCategoryInTree($category)
    {
        $tmpTree = &$this->_categoryTree;
        $level = $this->getArrayField(self::LEVEL, $category, '0');
        $categoryId = $this->getArrayField(self::ID, $category, '-1');
        if ($level <= 0) {
            $tmpTree[$categoryId] = $category;
            return;
        }
        $pathIds = $this->getCategoryPathIds($category, $level);

        $canInsert = false;
        $categoryInserted = false;
        foreach ($pathIds as $id) {
            if (isset($tmpTree[$id])) {
                if (!isset($tmpTree[$id][self::CHILDREN])) {
                    $tmpTree[$id][self::CHILDREN] = [];
                }
                $tmpTree = &$tmpTree[$id][self::CHILDREN];
                $canInsert = true;
            } elseif ($canInsert) {
                $tmpTree[$categoryId] = $category;
                $categoryInserted = true;
            }
        }

        if (!$categoryInserted) {
            $tmpTree = &$this->_categoryTree;
            $tmpTree[$categoryId] = $category;
        }

        return;
    }

    protected function getCategoryPathIds($category, $level)
    {
        $idsPath = $this->getArrayField(self::PATH_IDS, $category, null);
        if (!isset($idsPath)) {
            $tmpTree = $category;
        }
        if (isset($idsPath)) {
            if (strpos($idsPath, self::DEF_PATH_IDS_DELIMITER) !== false) {
                $idsPath = explode(self::DEF_PATH_IDS_DELIMITER, $idsPath);
            } else {
                $idsPath = [$idsPath];
            }
        }
        return $idsPath;
    }

    protected function evaluateParentAttributeCondition($isChild, $parentAttributeCondition)
    {
        $result =  $parentAttributeCondition;
        if (!$isChild) {
            $result = '0';
        }
        return $result;
    }
}
