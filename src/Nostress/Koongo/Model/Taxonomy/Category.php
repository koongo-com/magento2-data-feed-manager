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
* Class for taxonomy
*
* @category Nostress
* @package Nostress_Koongo
*
*/

namespace Nostress\Koongo\Model\Taxonomy;

class Category extends \Nostress\Koongo\Model\AbstractModel
{
    const ALL_LOCALES = 'all';
    const ROOT = 'taxonomy';

    // 	const SRC = 'src';
    // 	const PATH = 'path';
    // 	const FILENAME = 'filename';
    //const DOWNLOAD = 'download';

    //config tags
    //const LOCALE = 'locale';
    // 	const DELIMITER = 'delimiter';
    // 	const VARIABLE = 'variable';
    // 	//const DEFAULT_LOCALE = 'default';
    // 	const TRANSLATE = 'rewrite';
    // 	const GENERAL = 'general';
    // 	const OPTION =  'option';
    // 	const LABEL = 'label';
    // 	const VALUE = 'value';
    //const COMMON = 'common';

    //columns
    const C_CODE = 'taxonomy_code';
    const C_LOCALE = 'locale';
    const C_NAME = 'name';
    const C_ID = 'id';
    const C_PATH = 'path';
    const C_IDS_PATH = 'ids_path';
    const C_LEVEL = 'level';
    const C_PARENT_NAME = 'parent_name';
    const C_PARENT_ID = 'parent_id';
    const C_CODE1 = 'code1';
    const C_CODE2 = 'code2';

    const DEFAULT_LOCALE_DELIMITER = "_";

    protected $_enginesConfig;
    protected $_message = [true=>[],false=>[]];

    /**
     * @var \Nostress\Koongo\Model\Taxonomy\SetupFactory
     */
    protected $taxonomySetupFactory;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Nostress\Koongo\Helper\Data $helper
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Nostress\Koongo\Model\Translation $translation
     * @param \Nostress\Koongo\Model\Taxonomy\SetupFactory $taxonomySetupFactory
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Nostress\Koongo\Helper\Data $helper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Nostress\Koongo\Model\Translation $translation,
        \Nostress\Koongo\Model\Taxonomy\SetupFactory $taxonomySetupFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        \Magento\Framework\Filesystem\DriverInterface $driver,
        array $data = []
    ) {
        $this->taxonomySetupFactory = $taxonomySetupFactory;
        parent::__construct($context, $registry, $helper, $storeManager, $translation, $resource, $resourceCollection, $driver, $data);
    }

    public function _construct()
    {
        $this->_init('Nostress\Koongo\Model\ResourceModel\Taxonomy\Category');
    }

    public function getCategories($code, $locale, $defaultLocale, $select = null, $indexField = null)
    {
        $filter = "";
        if ($this->countColumns($code, $locale) > 0) {
            $filter = $this->getFilterFields($code, $locale);
        } elseif (!empty($defaultLocale) && $this->countColumns($code, $defaultLocale) > 0) { //google has no defined taxonomy for all other locales
            $filter = $this->getFilterFields($code, $defaultLocale);
        } else {
            $filter = $this->getFilterFields($code);
        }

        $items = $this->_getTaxonomyCategories($filter, $select, $indexField);
        return $items;
    }

    public function countColumns($code, $locale = self::ALL_LOCALES)
    {
        return $this->getResource()->countColumns($code, $locale);
    }

    public function _getTaxonomyCategories($filter = null, $select = null, $indexField = null)
    {
        $collection = $this->getCollection();
        $collection->addFieldsToFilter($filter);
        $collection->addFieldsToSelect($select);
        $select = $collection->getSelect();//init select don't delete
        $select->order('path');
        // 		echo $select->__toString();
        // 		exit();
        $collection->load();
        return $collection->getItems($indexField);
    }

    protected function getFilterFields($code, $locale = self::ALL_LOCALES)
    {
        $fields = [];
        $fields[self::C_CODE] = $code;
        $fields[self::C_LOCALE] = $locale;
        return $fields;
    }
}
