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
 * Manager for Koongo connector cache models
 *
 * @category Nostress
 * @package Nostress_Koongo
 *
 */

namespace Nostress\Koongo\Model\Cache;

use Nostress\Koongo\Model\Channel\Profile;

class Manager extends \Nostress\Koongo\Model\AbstractModel
{
    /*
     * @var \Nostress\Koongo\Model\Cache\Weee
     */
    protected $cacheWeee;

    /*
     * @var \Nostress\Koongo\Model\Cache\Tax
     */
    protected $cacheTax;

    /*
     * @var \Nostress\Koongo\Model\Cache\Categorypath
    */
    protected $cacheCategorypath;

    /*
     * @var \Nostress\Koongo\Model\Cache\Product
     */
    protected $cacheProduct;

    /*
     * @var \Nostress\Koongo\Model\Cache\Price
     */
    protected $cachePrice;

    /*
     * @var \Nostress\Koongo\Model\Cache\Pricetier
     */
    protected $cachePricetier;

    /*
     * @var \Nostress\Koongo\Model\Cache\Profilecategory
     */
    protected $cacheProfileCategory;

    /*
     * @var \Nostress\Koongo\Model\Cache\Channelcategory
     */
    protected $cacheChannelCategory;

    /*
     * @var \Nostress\Koongo\Model\Cache\Mediagallery
     */
    protected $cacheMediaGallery;

    /*
     * @var \Nostress\Koongo\Model\Cache\Review
     */
    protected $cacheReview;

    /**
     * \Nostress\Koongo\Model\Taxonomy\Category\Mapping
     */
    protected $mappingModel;

    /*
     * @var \Nostress\Koongo\Model\Config\Source\Datetimeformat
    */
    protected $datetimeSource;

    /**
     * @param \Nostress\Koongo\Helper\Data\Loader $helper
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Nostress\Koongo\Model\Cache\Weee $cacheWeee
     * @param \Nostress\Koongo\Model\Cache\Tax $cacheTax
     * @param \Nostress\Koongo\Model\Cache\Categorypath $cacheCategorypath
     * @param \Nostress\Koongo\Model\Cache\Product $cacheProduct
     * @param \Nostress\Koongo\Model\Cache\Price $cachePrice
     * @param \Nostress\Koongo\Model\Cache\Pricetier $cachePricetier
     * @param \Nostress\Koongo\Model\Cache\Profilecategory $cacheProfileCategory
     * @param \Nostress\Koongo\Model\Cache\Channelcategory $cacheChannelCategory
     * @param \Nostress\Koongo\Model\Cache\Mediagallery $cacheMediaGallery
     * @param \Nostress\Koongo\Model\Cache\Review $cacheReview
     * @param \Nostress\Koongo\Model\Taxonomy\Category\Mapping $mappingModel
     * @param \Nostress\Koongo\Model\Config\Source\Datetimeformat $datetimeSource
     */
    public function __construct(
        \Nostress\Koongo\Helper\Data\Loader $helper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Nostress\Koongo\Model\Cache\Weee $cacheWeee,
        \Nostress\Koongo\Model\Cache\Tax $cacheTax,
        \Nostress\Koongo\Model\Cache\Categorypath $cacheCategorypath,
        \Nostress\Koongo\Model\Cache\Product $cacheProduct,
        \Nostress\Koongo\Model\Cache\Price $cachePrice,
        \Nostress\Koongo\Model\Cache\Pricetier $cachePricetier,
        \Nostress\Koongo\Model\Cache\Profilecategory $cacheProfileCategory,
        \Nostress\Koongo\Model\Cache\Channelcategory $cacheChannelCategory,
        \Nostress\Koongo\Model\Cache\Mediagallery $cacheMediaGallery,
        \Nostress\Koongo\Model\Cache\Review $cacheReview,
        \Nostress\Koongo\Model\Taxonomy\Category\Mapping $mappingModel,
        \Nostress\Koongo\Model\Config\Source\Datetimeformat $datetimeSource
    ) {
        $this->helper = $helper;
        $this->storeManager = $storeManager;
        $this->cacheWeee = $cacheWeee;
        $this->cacheTax = $cacheTax;
        $this->cacheCategorypath = $cacheCategorypath;
        $this->cacheProduct = $cacheProduct;
        $this->cachePrice = $cachePrice;
        $this->cachePricetier = $cachePricetier;
        $this->cacheProfileCategory = $cacheProfileCategory;
        $this->cacheChannelCategory = $cacheChannelCategory;
        $this->cacheMediaGallery =  $cacheMediaGallery;
        $this->cacheReview = $cacheReview;
        $this->mappingModel = $mappingModel;
        $this->datetimeSource = $datetimeSource;
    }

    public function reloadAllCache($storeIds, $websiteIds, $priceReloadList, $priceTierReloadList)
    {
        foreach ($storeIds as $storeId) {
            $categoryLowestLevel = $this->helper->getModuleConfig(\Nostress\Koongo\Helper\Data::PARAM_CATEGORY_LOWEST_LEVEL, false, false, $storeId);

            //Reload category path
            $this->cacheCategorypath->setLowestLevel($categoryLowestLevel);
            $this->cacheCategorypath->reload($storeId);

            //Load excluded images export status from module configuration(store dependent)
            $excludedImagesExportEnabled = $this->helper->getModuleConfig(\Nostress\Koongo\Helper\Data::PARAM_ALLOW_EXCLUDED_IMAGES_EXPORT, false, false, $storeId);
            //Load source for image attributes
            $imageAttributeSource = $this->helper->getModuleConfig(\Nostress\Koongo\Helper\Data::PARAM_IMAGE_ATTRIBUTE_SOURCE, false, false, $storeId);

            //Reload media gallery
            $this->cacheMediaGallery->setExcludedImagesExportEnabled($excludedImagesExportEnabled);
            $this->cacheMediaGallery->setImageAttributeSource($imageAttributeSource);
            $this->cacheMediaGallery->reload($storeId);

            //Load lowest level from module configuration(store dependent)
            $this->cacheProduct->setLowestLevel($categoryLowestLevel);

            //Load inactive categories export status from module configuration(store dependent)
            $allowInactiveCategoriesExport = $this->helper->getModuleConfig(\Nostress\Koongo\Helper\Data::PARAM_ALLOW_INACTIVE_CATEGORIES_EXPORT, false, false, $storeId);
            $this->cacheProduct->setAllowInactiveCategoriesExport($allowInactiveCategoriesExport);

            //Load bundle options required only setting from module configuration(store dependent)
            $bundleOptionsRequiredOnly = $this->helper->getModuleConfig(\Nostress\Koongo\Helper\Data::PARAM_BUNDLE_OPTIONS_REQUIRED_ONLY, false, false, $storeId);
            $this->cacheProduct->setBundleOptionsRequiredOnly($bundleOptionsRequiredOnly);

            //Load bundle options default items only from module configuration(store dependent)
            $bundleOptionsDefaultItmesOnly = $this->helper->getModuleConfig(\Nostress\Koongo\Helper\Data::PARAM_BUNDLE_OPTIONS_DEFAULT_ITEMS_ONLY, false, false, $storeId);
            $this->cacheProduct->setBundleOptionsDefaultItmesOnly($bundleOptionsDefaultItmesOnly);

            //Load website id for stock status table
            $stockWebsiteId = $this->helper->getStockWebsiteId();
            $this->cacheProduct->setStockWebsiteId($stockWebsiteId);

            $this->cacheProduct->reload($storeId);
            $this->cacheTax->reload($storeId);
        }

        //Reload prices for adjusted customer groups
        foreach ($priceReloadList as $index => $reloadItem) {
            $customerGroupId = $reloadItem["customer_group_id"];
            $storeId = $reloadItem["store_id"];
            $this->cachePrice->setCustomerGroupId($customerGroupId);
            $this->cachePrice->reload($storeId);

            if (isset($priceTierReloadList[$index])) {
                $this->cachePricetier->setCustomerGroupId($customerGroupId);
                $this->cachePricetier->reload($storeId);

                $this->cachePrice->reloadTierPrices();
            }
        }

        foreach ($websiteIds as $websiteId) {
            $this->cacheWeee->reloadWebsite($websiteId);
        }
    }

    public function reloadProfileCache($profile)
    {
        $categoryIds = $profile->getConfigItem(Profile::CONFIG_FILTER, false, Profile::CONFIG_FILTER_CATEGORIES);

        //Reload profile categories cache
        if (!empty($categoryIds)) {
            $storeId = $profile->getStoreId();
            $categoryLowestLevel = $this->helper->getModuleConfig(\Nostress\Koongo\Helper\Data::PARAM_CATEGORY_LOWEST_LEVEL, false, false, $storeId);
            $allowInactiveCategoriesExport = $this->helper->getModuleConfig(\Nostress\Koongo\Helper\Data::PARAM_ALLOW_INACTIVE_CATEGORIES_EXPORT, false, false, $storeId);
            $this->cacheProfileCategory->setParameters($profile->getId(), $categoryIds, $categoryLowestLevel, $allowInactiveCategoriesExport);
            $this->cacheProfileCategory->reload($storeId);
        }

        //Reload channel categories cache
        $this->reloadProfileChannelCategoriesCache($profile);

        $exportReviews = $profile->getFeed()->containReviews();
        if ($exportReviews) {
            $dateTimeFormat = $profile->getConfigItem(Profile::CONFIG_FEED, false, Profile::CONFIG_COMMON, Profile::CONFIG_DATETIME_FORMAT);
            if (isset($dateTimeFormat)) {
                $sqlDatetimeFormat = $this->datetimeSource->getSqlFormat($this->getDatetimeFormat(), self::DATE_TIME);
                $this->cacheReview->setTimestampFormatForSql($sqlDatetimeFormat);
            }
            $this->cacheReview->reload($this->getStoreId());
        }
    }

    public function reloadProfileChannelCategoriesCache($profile)
    {
        $feedModel = $profile->getFeed();
        $taxonomyCode = null;
        if ($feedModel) {
            $taxonomyCode = $feedModel->getTaxonomyCode();
        }
        $locale = $profile->getConfigItem(Profile::CONFIG_GENERAL, false, 'taxonomy_locale');

        //Reload channel categories cache
        $this->reloadChannelCategoriesCache($profile->getId(), $taxonomyCode, $locale, $profile->getStoreId());
    }

    protected function reloadChannelCategoriesCache($profileId, $taxonomyCode, $locale, $storeId)
    {
        //Reload channel categories cache
        if (!empty($taxonomyCode) && !empty($locale)) {
            $mappingModel = $this->mappingModel->getMapping($taxonomyCode, $locale, $storeId);
            $rules = [];
            if (isset($mappingModel)) {
                $rules = $mappingModel->getRules();
            }

            $this->cacheChannelCategory->setParameters($profileId, $taxonomyCode, $locale, $rules);
            $this->cacheChannelCategory->reload($storeId);
        }
    }
}
