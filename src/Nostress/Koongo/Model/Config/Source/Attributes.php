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
 * Config source model - parent child dependency
*
* @category Nostress
* @package Nostress_Koongo
*
*/
namespace Nostress\Koongo\Model\Config\Source;

class Attributes extends \Nostress\Koongo\Model\AbstractModel
{
    /**
     * Prefix for product attributes defined by this module
     * @var unknown_type
     */
    const MODULE_PATTRIBUTE_PREFIX = 'nkp_';

    /**
     * Prefix for category attributes defined by this module
     * @var unknown_type
     */
    const MODULE_CATTRIBUTE_PREFIX = 'nkc_';

    const TAXONOMY_SUBST_LABEL = 'Taxonomy';

    /*
     * @var Attributes definde by this module.
     */
    protected $_moduleProductAttributes;

    /*
     * @var Attributes definde by this module, which enable to export its multiple values.
     */
    protected $_productMultiAttributeCodes = ["categories","media_gallery","tier_prices","reviews"];

    /*
     * @var Static attributes(same for all products). Attributes are dependent on selected store.
     */
    protected $_productStaticAttributeCodes = ["currency","country_code","locale","language","shipping_method_name","shipping_cost"];

    /*
     * @var Catalog product attributes, which are ireleveant(not used or can't be loaded from flat catalog) for priduct data export.
    */
    protected $_irelevantCatalogProductAttributeCodes = ["custom_design","custom_design_from","custom_design_to","","custom_layout_update"
                                                    ,"gift_message_available","msrp_display_actual_price_type","options_container","page_layout"
                                                    ,"price_type","price_view","quantity_and_stock_status","minimal_price"];

    protected $_productFilterExludeAttributeCodes = ['visibility','nkp_stock_status','nkp_category_id',
                                                    'nkp_category_parent_id','nkp_category_parent_name',
                                                    'nkp_category_level','nkp_category_root_name',
                                                    'nkp_category_root_id','nkp_category_path_ids'];

    /**
     * Columns which are loaded from own cache tables instaed of product flat table - category_ids
     */
    protected $_flatExceptionColumns = ["category_ids"];

    /*
     * @var \Nostress\Koongo\Model\Data\Loader\Product
    */
    protected $productLoader;

    /*
     * @var \Nostress\Koongo\Model\Data\Loader\Category
    */
    protected $categoryLoader;

    /**
     * Attribute collection factory
     *
     * @var \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory
     */
    protected $_attributeCollectionFactory;

    /**
     * \Magento\Catalog\Model\ResourceModel\Product\Flat
     * @var \Magento\Catalog\Model\ResourceModel\Product\Flat
     */
    protected $_productFlatResource;

    /**
     * \Magento\Catalog\Model\Product\AttributeSet\Options
     * @var \Magento\Catalog\Model\Product\AttributeSet\Options
     */
    protected $_productAttributeSetOptions;

    /*
    * @param \Nostress\Koongo\Model\Data\Loader\Product
    * @param \Nostress\Koongo\Model\Data\Loader\Category $categoryLoader,
    * @param \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory $attributeCollectionFactory
    * @param \Magento\Catalog\Model\ResourceModel\Product\Flat $productFlatResource
    * @param \Magento\Eav\Model\Config $eavConfig
    * @param \Nostress\Koongo\Helper\Data $helper
    */
    public function __construct(
        \Nostress\Koongo\Model\Data\Loader\Product $productLoader,
        \Nostress\Koongo\Model\Data\Loader\Category $categoryLoader,
        \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory $attributeCollectionFactory,
        \Magento\Catalog\Model\ResourceModel\Product\Flat $productFlatResource,
        \Magento\Catalog\Model\Product\AttributeSet\Options $productAttributeSetOptions,
        \Nostress\Koongo\Helper\Data $helper
    ) {
        $this->productLoader = $productLoader;
        $this->categoryLoader = $categoryLoader;
        $this->_attributeCollectionFactory = $attributeCollectionFactory;
        $this->_productFlatResource = $productFlatResource;
        $this->_productAttributeSetOptions = $productAttributeSetOptions;
        $this->helper = $helper;
    }

    public function getProductAttributeCodes($storeId, $addStaticAttributes = true, $addMultiAttributes = false)
    {
        $ownAttributeCodes = $this->getModuleProductAttributes(false, $addStaticAttributes, $addMultiAttributes);
        $productCatalogAttrobuteCodes = $this->getCatalogProductAttributeCodes();
        $codes = array_merge($ownAttributeCodes, $productCatalogAttrobuteCodes);
        return $codes;
    }

    public function getCatalogProductAttribute($code)
    {
        $returnSingleItem = false;
        if (!is_array($code)) {
            $returnSingleItem = true;
            $code = [$code];
        }

        $collection = $this->getCatalogProductAttributes(false, $code);
        if ($returnSingleItem) {
            return $collection->getFirstItem();
        } else {
            return $collection;
        }
    }

    public function getCatalogProductAttributeLabel($attribute, $storeId)
    {
        $labels = $attribute->getStoreLabels();
        $label = $defaultLabel = $attribute->getFrontendLabel();

        if (array_key_exists($storeId, $labels)) {
            $label = $labels[$storeId];
        }

        if ($label != $defaultLabel) {
            $label .= " ({$defaultLabel})";
        }

        return $label;
    }

    public function removeModuleAttributePrefix($attributeCode)
    {
        return str_replace(self::MODULE_PATTRIBUTE_PREFIX, "", $attributeCode);
    }

    public function addModuleAttributePrefix($attributeCode)
    {
        return self::MODULE_PATTRIBUTE_PREFIX . $attributeCode;
    }

    public function hasModuleAttributePrefix($attributeCode)
    {
        if (strpos($attributeCode, self::MODULE_PATTRIBUTE_PREFIX) !== false) {
            return true;
        } else {
            return false;
        }
    }

    /*
     * If attribute code is one of own module attributes, than add default prefix
     */
    public function decorateAttributeCode($attributeCode)
    {
        $ownAttributeCodes = $this->getModuleProductAttributes(false, true, true);
        $newAttributeCode = $this->addModuleAttributePrefix($attributeCode);
        if (in_array($newAttributeCode, $ownAttributeCodes)) {
            return $newAttributeCode;
        } else {
            return $attributeCode;
        }
    }

    public function attributeIsMultiselect($attribute)
    {
        return $attribute->getFrontendInput() === 'multiselect';
    }

    public function getMultiSelectAttributeOptions($attributesCodes)
    {
        $optionsArray = [];
        $codes = [];
        foreach ($attributesCodes as $code) {
            if (!$this->hasModuleAttributePrefix($code)) {
                $codes[] = $code;
            }
        }
        if (empty($codes)) {
            return $optionsArray;
        }

        $attributes = $this->getCatalogProductAttributes(false, $codes, [], true);

        foreach ($attributes as $attribute) {
            $options = $attribute->getFrontend()->getSelectOptions();
            $optionsArray[$attribute->getAttributeCode()] = $this->optionsToSearchArray($options);
        }
        return $optionsArray;
    }

    public function getProductAttributeSetOptions()
    {
        $options = $this->_productAttributeSetOptions->toOptionArray();

        $optionArray = [];
        foreach ($options as $option) {
            if (empty($option["value"])) {
                continue;
            }
            $optionArray[$option["value"]] = $option["label"];
        }
        return $optionArray;
    }

    public function toIndexedArray($storeId = 0, $taxonomyLabel = "", $attributesForFilter = false)
    {
        $options = $this->toOptionArray($storeId, $taxonomyLabel, $attributesForFilter);
        $indexedArray = [];
        foreach ($options as $item) {
            $indexedArray[$item['value']] = $item['label'];
        }
        return $indexedArray;
    }

    public function toOptionArray($storeId = 0, $taxonomyLabel = "", $attributesForFilter = false)
    {
        $ownAttributeOptions = $this->getModuleProductAttributes(true, !$attributesForFilter);
        $attributeCollection =	$this->getCatalogProductAttributes(false, [], $this->_irelevantCatalogProductAttributeCodes);
        $catalogProductAttributeOptions = $this->catalogAttributesToOptionArray($attributeCollection, $storeId);

        $labels = [];
        $options = [];

        if (!empty($taxonomyLabel)) {
            $taxonomyLabel .= " " . __("Category");
        }

        foreach ($ownAttributeOptions as $option) {
            if ($attributesForFilter) {
                if (in_array($option[self::VALUE], $this->_productFilterExludeAttributeCodes)) {
                    continue;
                }
            }

            //Replace word "Taxonomy" with proper taxonomy channel label
            if (strpos($option[self::LABEL], self::TAXONOMY_SUBST_LABEL) !== false) {
                if (!empty($taxonomyLabel)) {
                    $option[self::LABEL] = str_replace(self::TAXONOMY_SUBST_LABEL, $taxonomyLabel, $option[self::LABEL]);
                } else {
                    continue;
                }
            }

            $options[] = $option;
            $labels[] = $option[self::LABEL];
            //$options[$option[self::VALUE]] = $option[self::LABEL];
        }

        foreach ($catalogProductAttributeOptions as $option) {
            if ($attributesForFilter) {
                if (in_array($option[self::VALUE], $this->_productFilterExludeAttributeCodes)) {
                    continue;
                }
            }

            //$options[] = $option;
            $labels[] = $option[self::LABEL];

            $options[] = $option;
            //$options[$option[self::VALUE]] = $option[self::LABEL];
        }

        array_multisort($labels, $options);
        return $options;
    }

    public function filterStaticAttributes($attributeCodes, $type = "product")
    {
        $staticAttributes = [];
        if ($type == "product") {
            $staticAttributes = $this->getProductStaticAttributeCodes(true);
        }

        if (!empty($staticAttributes)) {
            $attributeCodes = array_diff($attributeCodes, $staticAttributes);
        }

        return $attributeCodes;
    }

    public function filterMultiAttributes($attributeCodes, $type = "product")
    {
        $attributes = [];
        if ($type == "product") {
            $attributes = $this->getProductMultiAttributeCodes(true);
        }

        if (!empty($attributes)) {
            $attributeCodes = array_diff($attributeCodes, $attributes);
        }

        return $attributeCodes;
    }

    /**
     * Check if attributes are in product flat catalog.
     * @param array $attributeCodes
     * @param Store id or Store instance $store
     * @return Empty array if all given attributes are in flat catalog, otherwise list of attributes which are not in flat is returned.
     */
    public function checkAttributesProductFlat($attributeCodes, $store)
    {
        $ownAttributes = $this->getModuleProductAttributes(false, true, true);
        $flatCandidates = array_diff($attributeCodes, $ownAttributes);
        $flatColumns = $this->getProductFlatColumns($store);

        //Remove columns that are in flat
        $attributesMissingInFlat = array_diff($flatCandidates, $flatColumns);
        //Remove exceptional columns
        $attributesMissingInFlat = array_diff($attributesMissingInFlat, $this->_flatExceptionColumns);

        return $attributesMissingInFlat;
    }

    /**
     * Retruns list of attributes defined by module's product loader
     * @return array:string
     */
    protected function getModuleProductAttributes($asOptionArray = false, $addStaticAttributes = true, $addMultiAttributes = false)
    {
        if (!isset($this->_moduleProductAttributes)) {
            //get product loader attributes
            $attributeCodes = $this->productLoader->getAttributesKeys();
            if (!$addMultiAttributes) {
                $attributeCodes = $this->filterMultiAttributes($attributeCodes);
            }
            $attributeCodes = array_combine($attributeCodes, $attributeCodes);

            if ($addStaticAttributes) {
                $staticCodes = $this->getProductStaticAttributeCodes(true);
                $staticCodes = array_combine($staticCodes, $staticCodes);
                $attributeCodes = array_merge($attributeCodes, $staticCodes);
            }

            ksort($attributeCodes);
            $this->_moduleProductAttributes = $attributeCodes;
        }

        if (!$asOptionArray) {
            return $this->_moduleProductAttributes;
        }

        $attributes = [];
        foreach ($this->_moduleProductAttributes as $alias => $code) {
            $aliasWithoutPrefix = $this->removeModuleAttributePrefix($alias);
            $attributes[$alias] = [self::VALUE => $alias, self::LABEL => $this->helper->codeToLabel($aliasWithoutPrefix)];
        }
        return $attributes;
    }

    public function getProductMultiAttributeCodes($addModuleAttributePrefix = false)
    {
        if (!$addModuleAttributePrefix) {
            return $this->_productMultiAttributeCodes;
        }

        $codes = $this->_productMultiAttributeCodes;
        foreach ($codes as $key => $code) {
            $codes[$key] = self::MODULE_PATTRIBUTE_PREFIX . $code;
        }

        return $codes;
    }

    protected function getProductStaticAttributeCodes($addModuleAttributePrefix = false)
    {
        if (!$addModuleAttributePrefix) {
            return $this->_productStaticAttributeCodes;
        }

        $codes = $this->_productStaticAttributeCodes;
        foreach ($codes as $key => $code) {
            $codes[$key] = self::MODULE_PATTRIBUTE_PREFIX . $code;
        }

        return $codes;
    }

    protected function getCatalogProductAttributeCodes()
    {
        $collection = $this->getCatalogProductAttributes();
        $codes = [];
        foreach ($collection as $attribute) {
            $codes[] = $attribute->getAttributeCode();
        }
        return $codes;
    }

    protected function catalogAttributesToOptionArray($attributeCollection, $storeId)
    {
        foreach ($attributeCollection as $item) {
            $attribute = [];
            $attribute[self::VALUE] = $item->getAttributeCode();
            $attribute[self::LABEL] = $this->getCatalogProductAttributeLabel($item, $storeId);
            $attributes[$attribute[self::VALUE]] = $attribute;
        }
        return $attributes;
    }

    protected function getCatalogProductAttributes($asArray = false, $attributeCodes = [], $excludeAttributeCodes = [], $multiSelectFilter = false, $excludeGalleryAttributes = true, $visibleOnly = false)
    {
        $collection = $this->_attributeCollectionFactory->create();
        if ($visibleOnly) {
            $collection->addVisibleFilter();
        }
        if (!empty($attributeCodes)) {
            $collection->addFieldToFilter("attribute_code", ["in" => $attributeCodes]);
        }
        if (!empty($excludeAttributeCodes)) {
            $collection->addFieldToFilter("attribute_code", ["nin" => $excludeAttributeCodes]);
        }

        if ($multiSelectFilter) {
            $collection->addFieldToFilter("frontend_input", 'multiselect');
        }

        if ($excludeGalleryAttributes) {
            $collection->addFieldToFilter("frontend_input", ['neq' => 'gallery']);
        }

        $collection->load();
        if (!$asArray) {
            return $collection;
        } else {
            return $this->attributeCollectionToArray($collection);
        }
    }

    protected function attributeCollectionToArray($collection)
    {
        $attributes = [];
        foreach ($collection as $atrId => $attribute) {
            $atrCode = $attribute->getAttributeCode();
            $attributes[$atrCode] = $attribute;
        }
        return $attributes;
    }

    protected function optionsToSearchArray($options, $from = self::VALUE, $to = self::LABEL)
    {
        $array = [];
        foreach ($options as $option) {
            if (empty($option[$from])) {
                continue;
            }
            $array[$option[$from]] = $option[$to];
        }
        return $array;
    }

    public function getProductFlatColumns($store)
    {
        try {
            $this->_productFlatResource->setStoreId($store);
            return $this->_productFlatResource->getAllTableColumns();
        } catch (\Exception $e) {
            $this->logAndException("11");
        }
    }
}
