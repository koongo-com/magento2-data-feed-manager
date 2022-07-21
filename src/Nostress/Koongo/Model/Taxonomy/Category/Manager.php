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
* Class for taxonomy category management
*
* @category Nostress
* @package Nostress_Koongo
*
*/

namespace Nostress\Koongo\Model\Taxonomy\Category;

class Manager extends \Nostress\Koongo\Model\AbstractModel
{
    const HTTP_PREFIX = 'http';

    /**
     * Return message
     * @var Array
     */
    protected $_message = [true=>[],false=>[]];

    /**
     * @var \Nostress\Koongo\Model\Taxonomy\Setup
     */
    protected $taxonomySetupModel;

    /**
     * @var \Nostress\Koongo\Model\Data\Reader
     */
    protected $reader;

    /**
     *
     * @var \Nostress\Koongo\Model\Taxonomy\Category\Preprocessor
     */
    protected $categoryPreprocessor;

    /**
     * @var \Nostress\Koongo\Model\Taxonomy\Category
     */
    protected $taxonomyCategory;

    /**
     * @param \Nostress\Koongo\Helper\Data $helper
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Nostress\Koongo\Model\Taxonomy\CategoryFactory $taxonomyCategoryFactory
     * @param \Nostress\Koongo\Model\Taxonomy\Setup $taxonomySetupModel
     * @param \Nostress\Koongo\Model\Data\Reader
     * @param \Nostress\Koongo\Model\Taxonomy\Category\Preprocessor $categoryPreprocessor
     * @param \Nostress\Koongo\Model\Taxonomy\Category
     * @param array $data
     */
    public function __construct(
        \Nostress\Koongo\Helper\Data $helper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Nostress\Koongo\Model\Taxonomy\Setup $taxonomySetupModel,
        \Nostress\Koongo\Model\Data\Reader $reader,
        \Nostress\Koongo\Model\Taxonomy\Category\Preprocessor $categoryPreprocessor,
        \Nostress\Koongo\Model\Taxonomy\Category $taxonomyCategory
    ) {
        $this->taxonomySetupModel = $taxonomySetupModel;
        $this->reader = $reader;
        $this->categoryPreprocessor = $categoryPreprocessor;
        $this->taxonomyCategory = $taxonomyCategory;
        $this->helper = $helper;
        $this->storeManager = $storeManager;
        // 		parent::__construct($context, $registry, $helper, , $resource, $resourceCollection, $data);
    }

    public function reloadTaxonomyCategories($code, $locale)
    {
        //clear all
        $this->taxonomyCategory->getResource()->cleanTable($code, $locale);

        //load all records
        $taxonomySetupItem = $this->taxonomySetupModel->getTaxonomyByCode($code);
        $localesSourceConfig = $taxonomySetupItem->prepareLocalesSourceConfig([$locale]);
        if (empty($localesSourceConfig[$locale])) {
            $this->logAndException("%1 categories configuration don't exist.", $taxonomySetupItem->getName() . " - " . $locale);
        } else {
            $this->insertTaxonomyCategories($taxonomySetupItem->getName(), $taxonomySetupItem->getCode(), $locale, $localesSourceConfig[$locale]);
        }

        return $this->_message;
    }

    protected function insertTaxonomyCategories($name, $code, $locale, $fileSourceConfig)
    {
        $message = "";
        try {
            //load engine category records
            $records = $this->loadEngineCategoriesFromFile($fileSourceConfig);
            $this->taxonomyCategory->getResource()->insertTaxonomyCategoryRecords($code, $locale, $records);

            $message = __("Category Tree for %1 locale: %2 has been updated", $name, $locale);
            $this->_message[true][] = $message;
        } catch (\Exception $e) {
            $message = __("Categories for %1 locale: %2 haven't been updated  Error: %3", $name, $locale, $e->getMessage());
            $this->_message[false][] = $message;
        }
        $this->log($message);
    }

    protected function loadEngineCategoriesFromFile($params)
    {
        $params[self::FILE_PATH] = $this->initFilePath($params[self::FILE_PATH]);
        $records = $this->reader->getTaxonomyFileContent($params);
        $records = $this->categoryPreprocessor->processRecords($records, $params);
        return $records;
    }

    protected function initFilePath($path)
    {
        if (strpos($path, self::HTTP_PREFIX) === false) {
            $taxonomyUrl = $this->helper->getModuleConfig(\Nostress\Koongo\Model\Api\Client::PARAM_TAXONOMY_SOURCE_URL);
            $path = (string)$taxonomyUrl . $path;
        }
        return $path;
    }
}
