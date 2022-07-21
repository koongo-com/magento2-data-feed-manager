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
 * Rule Product Condition data model
 *
 * @category Nostress
 * @package Nostress_Koongo
 *
 */

namespace Nostress\Koongo\Model\Rule\Condition;

/**
 * Class Product
 */
class Product extends \Magento\CatalogRule\Model\Rule\Condition\Product
{
    /**
     * Default table alias for sql condition generation
     * @var unknown_type
     */
    const DEFAULT_TABLE_ALIAS = 'default_table_alias';
    /*
     * @var \Nostress\Koongo\Model\Config\Source\Attributes
    */
    protected $attributeSource;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /*
     *  \Nostress\Koongo\Model\Config\Source\Datetimeformat
    */
    protected $_datetimeformat;

    /**
     * @param \Magento\Rule\Model\Condition\Context $context
     * @param \Magento\Backend\Helper\Data $backendData
     * @param \Magento\Eav\Model\Config $config
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Catalog\Model\ResourceModel\Product $productResource
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\Collection $attrSetCollection
     * @param \Magento\Framework\Locale\FormatInterface $localeFormat
     * @param \Nostress\Koongo\Model\Config\Source\Attributes $attributeSource
     * @param  \Magento\Framework\Registry $registry
     * @param \Nostress\Koongo\Model\Config\Source\Datetimeformat $datetimeformat
     * @param array $data
     */
    public function __construct(
        \Magento\Rule\Model\Condition\Context $context,
        \Magento\Backend\Helper\Data $backendData,
        \Magento\Eav\Model\Config $config,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Catalog\Model\ResourceModel\Product $productResource,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\Collection $attrSetCollection,
        \Magento\Framework\Locale\FormatInterface $localeFormat,
        \Nostress\Koongo\Model\Config\Source\Attributes $attributeSource,
        \Magento\Framework\Registry $registry,
        \Nostress\Koongo\Model\Config\Source\Datetimeformat $datetimeformat,
        array $data = []
    ) {
        $this->attributeSource = $attributeSource;
        $this->_coreRegistry = $registry;
        $this->_datetimeformat = $datetimeformat;
        parent::__construct($context, $backendData, $config, $productFactory, $productRepository, $productResource, $attrSetCollection, $localeFormat, $data);
    }

    /**
     * Load attribute options
     *
     * @return $this
     */
    public function loadAttributeOptions()
    {
        $productAttributes = $this->_productResource->loadAllAttributes()->getAttributesByCode();

        /* @var $model \Nostress\Koongo\Model\Channel\Profile */
        $profile = $this->_coreRegistry->registry('koongo_channel_profile');
        //$currentStoreId = $this->_coreRegistry->registry('koongo_current_store_id');

        $taxonomyLabel = "";
        $storeId = \Magento\Store\Model\Store::DEFAULT_STORE_ID;
        if (!empty($profile)) {
            $storeId = $profile->getStoreId();
            $taxonomyLabel = $profile->getTaxonomyLabel();
        }

        $moduleProductAttributes =  $this->attributeSource->toIndexedArray($storeId, $taxonomyLabel, true);

        $attributes = [];
        foreach ($productAttributes as $attribute) {
            /* @var $attribute \Magento\Catalog\Model\ResourceModel\Eav\Attribute */

            $attributeCode = $attribute->getAttributeCode();

            if (!isset($moduleProductAttributes[$attributeCode])) {
                continue;
            }

            $attributes[$attributeCode] = $moduleProductAttributes[$attributeCode];
            unset($moduleProductAttributes[$attributeCode]);
        }

        //
        $attributes = array_merge($attributes, $moduleProductAttributes);
        $this->_addSpecialAttributes($attributes);

        asort($attributes);
        $this->setAttributeOption($attributes);

        return $this;
    }

    /**
     * Add special attributes
     *
     * @param array $attributes
     * @return void
     */
    protected function _addSpecialAttributes(array &$attributes)
    {
        parent::_addSpecialAttributes($attributes);
//      $attributes['price_final_include_tax'] = __('Price Final Include Tax');
    }

    protected function _getAttributeAliases($attribute, $columns)
    {
        //$columns['default_table_alias']
        foreach ($columns as $tableAlias => $tableColumns) {
            if ($tableAlias == self::DEFAULT_TABLE_ALIAS) {
                continue;
            }
            foreach ($tableColumns as $columnAlias => $columnValue) {
                if ($attribute == $columnAlias) {
                    //If column value contains composed value - it means it contains table alias inside
                    if (strpos($columnValue, $tableAlias) !== false) {
                        return $columnValue;
                    } else {
                        return $tableAlias . "." . $columnValue;
                    }
                }
            }
        }
        return $columns[self::DEFAULT_TABLE_ALIAS] . "." . $attribute;
    }

    /**
     * @param Zend_Db_Select
     * @return bool|mixed|string
     */
    public function asSqlWhere($columns)
    {
        $attributeCode = $where = $this->getAttribute();
        $operator = $this->getOperator();
        $value = $this->getValue();
        if (is_array($value)) {
            $ve = addslashes(join(',', $value));
        } else {
            $ve = addslashes($value);
        }

        $attributeAlias = $this->_getAttributeAliases($attributeCode, $columns);

        $attribute = $this->_config->getAttribute('catalog_product', $attributeCode);

        // whether attribute is multivalue
        $isMultiselect = $attribute->getId() && ($attribute->getFrontendInput() == 'multiselect');

        //category_ids exception, IS and IS NOT must be evaluates in the same way as IS ONE OF and IS NOT ONE OF
        if ($attributeCode == 'category_ids') {
            $isMultiselect = true;
            $operator = $this->transformCategoriesOperator($operator);
        }

        switch ($operator) {
            case 'is_today':
                $whereTemplate = "DATE({{ta}}) = '" . $this->_datetimeformat->getDate(null, true) . "'";
                 break;
            case '==':
                $whereTemplate = '{{ta}}' . '=' . "'{$ve}'";
                break;
            case '!=':
                if (!empty($ve)) {
                    $whereTemplate = '{{ta}}' . '<>' . "'{$ve}' OR {{ta}} IS NULL";
                } else {
                    $whereTemplate = '{{ta}}' . '<>' . "'' AND {{ta}} IS NOT NULL";
                }
                break;

            case '>=':
            case '<=':
            case '>':
            case '<':
                        $whereTemplate = "{{ta}}{$operator}'{$ve}'";
                break;

            case '{}':
                $whereTemplate = "{{ta}} LIKE '%{$ve}%'";
                break;
            case '!{}':
                $whereTemplate = "{{ta}} NOT LIKE '%{$ve}%' OR {{ta}} IS NULL";
                break;

            case '()':
                $valueArray = preg_split('|\s*,\s*|', $ve);
                if (!$isMultiselect) {
                    $whereTemplate = "{{ta}} IN ('" . join("','", $valueArray) . "')";
                } else {
                    $whereItems = [];
                    foreach ($valueArray as $valueItem) {
                        $whereItems[] = "find_in_set('" . addslashes($valueItem) . "', {{ta}})";
                    }
                    $whereTemplate = '(' . join(') OR (', $whereItems) . ')';
                }
                break;
            case '!()':
                $valueArray = preg_split('|\s*,\s*|', $ve);
                if (!$isMultiselect) {
                    $whereTemplate = "{{ta}} NOT IN ('" . join("','", $valueArray) . "')";
                } else {
                    $whereItems = [];
                    foreach ($valueArray as $valueItem) {
                        $whereItems[] = "!find_in_set('" . addslashes($valueItem) . "', {{ta}})";
                    }
                    $whereTemplate = '(' . join(') AND (', $whereItems) . ')';
                }
                $whereTemplate = "({$whereTemplate}) OR {{ta}} IS NULL";

                break;

            default:
                return false;
        }

        $where = str_replace('{{ta}}', $attributeAlias, $whereTemplate);
        return $where;
    }

    protected function transformCategoriesOperator($operator)
    {
        if ($operator == "==") {
            $operator = "()";
        }
        if ($operator == "!=") {
            $operator = "!()";
        }
        return $operator;
    }

    /**
     * Koongo modification to be able to filter by todays date
     * Default operator input by type map getter
     *
     * @return array
     */
    public function getDefaultOperatorInputByType()
    {
        if (null === $this->_defaultOperatorInputByType) {
            parent::getDefaultOperatorInputByType();
            array_unshift($this->_defaultOperatorInputByType['date'], 'is_today');
        }
        return $this->_defaultOperatorInputByType;
    }

    /**
     * Koongo modification to be able to filter by todays date
     * Default operator options getter
     * Provides all possible operator options
     *
     * @return array
     */
    public function getDefaultOperatorOptions()
    {
        if (null === $this->_defaultOperatorOptions) {
            parent::getDefaultOperatorOptions();
            $this->_defaultOperatorOptions['is_today'] = __('is Today (attribute value is compared with current date)');
        }
        return $this->_defaultOperatorOptions;
    }
}
