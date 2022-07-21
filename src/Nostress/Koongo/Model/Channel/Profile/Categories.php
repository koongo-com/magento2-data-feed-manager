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
* Class for profile processing.
*
* @category Nostress
* @package Nostress_Koongo
*
*/

namespace Nostress\Koongo\Model\Channel\Profile;

use Nostress\Koongo\Model\Channel\Profile;

class Categories extends \Nostress\Koongo\Model\AbstractModel
{
    /**
     *  @var \Nostress\Koongo\Model\Channel\Profile
     **/
    protected $profile;

    /**
     * Channel category source
     * @var unknown_type
     */
    protected $categorySource;

    /**
     *
     * @var \Nostress\Koongo\Model\Api\Client
     */
    protected $client;
    /**
     *
     * @var \Nostress\Koongo\Helper\Data
     */
    public $helper;

    /**
     *
     * @var \Nostress\Koongo\Model\Taxonomy\Setup
     */
    protected $taxonomyModel;

    /**
     * @var \Nostress\Koongo\Model\Taxonomy\Category\Mapping
     */
    protected $mappingModel;

    /**
     * @param \Nostress\Koongo\Model\Taxonomy\Category $categorySource
     * @param \Nostress\Koongo\Helper\Data $helper
     * @param \Nostress\Koongo\Model\Api\Client $client
     * @param \Nostress\Koongo\Model\Taxonomy\Category\Mapping $mappingModel
     * @param array $data
     */
    public function __construct(
        \Nostress\Koongo\Model\Taxonomy\Category $categorySource,
        \Nostress\Koongo\Helper\Data $helper,
        \Nostress\Koongo\Model\Api\Client $client,
        \Nostress\Koongo\Model\Taxonomy\Category\Mapping $mappingModel
    ) {
        $this->categorySource = $categorySource;
        $this->client = $client;
        $this->helper = $helper;
        $this->mappingModel = $mappingModel;
    }

    public function initProfile($profile)
    {
        $this->profile = $profile;
        $this->taxonomyModel = $this->profile->getFeed()->getTaxonomy();
    }

    /**
     * after 'Update Channel Category Tree' button is clicked
     * @param string $locale
     */
    public function reloadTaxonomyCategories($locale = null)
    {
        if ($locale === null) {
            $locale = $this->getCurrentLocale();
        }
        $taxonomyCode = $this->taxonomyModel->getCode();
        return $this->client->reloadTaxonomyCategories($taxonomyCode, $locale);
    }

    public function getMappingRules($locale = null)
    {
        if ($locale === null) {
            $locale = $this->getCurrentLocale();
        }

        $mappingItem = $this->mappingModel->getMapping($this->taxonomyModel->getCode(), $locale, $this->profile->getStoreId());
        if (isset($mappingItem)) {
            return $mappingItem->getRules();
        } else {
            return [];
        }
    }

    public function getChannelCategories($locale = null)
    {
        if ($locale === null) {
            $locale = $this->getCurrentLocale();
        }
        $taxonomyCode = $this->taxonomyModel->getCode();

        $columnsCount = $this->categorySource->countColumns($taxonomyCode, $locale);
        if ($columnsCount <= 0) {
            $this->client->reloadTaxonomyCategories($taxonomyCode, $locale);
        }

        $categories = $this->categorySource->getCategories($taxonomyCode, $locale, $this->taxonomyModel->getDefaultLocale(), ['name','path','hash','id','parent_id'], 'hash');

        // format categories
        $catPathDelimiter = $this->taxonomyModel->getCategoryPathDelimiter();
        foreach ($categories as $key => &$item) {
            $item['name_folded'] = strtolower($this->helper->removeAccent($item['name']));

            if (isset($item['path'])) {
                $pathArray = explode($catPathDelimiter, $item['path']);
                $item['pathitems'] = $pathArray;
                $item['path_folded'] = strtolower($this->helper->removeAccent($item['path']));
            }
        }
        unset($item);

        return $categories;
    }

    public function getCurrentLocale()
    {
        $storeLocale = $this->helper->getStoreConfig($this->profile->getStoreId(), \Nostress\Koongo\Helper\Data::PATH_STORE_LOCALE);
        $availabelLocales = $this->taxonomyModel->getAvailableLocales();

        //Load current locale - load from profile -> load from store -> set default
        $currentLocale = $this->profile->getConfigItem(Profile::CONFIG_GENERAL, false, 'taxonomy_locale');
        if (empty($currentLocale) && in_array($storeLocale, $availabelLocales)) {
            $currentLocale = $storeLocale;
        }
        if (empty($currentLocale)) {
            $currentLocale = $this->taxonomyModel->getDefaultLocale();
        }

        return $currentLocale;
    }
}
