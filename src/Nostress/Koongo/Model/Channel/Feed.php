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
* Class for feed layout updates
*
* @category Nostress
* @package Nostress_Koongo
*
*/

namespace Nostress\Koongo\Model\Channel;

class Feed extends \Nostress\Koongo\Model\AbstractModel
{
    const COL_LINK = 'link';
    const COL_CODE = 'code';
    const COL_FILE_TYPE = 'file_type';
    const COL_TYPE = 'type';
    const COL_COUNTRY = 'country';
    const COL_ENABLED = 'enabled';
    const COL_TAXONOMY_CODE = 'taxonomy_code';
    const COL_LAYOUT = 'layout';

    const DEF_ENABLED = '1';
    const ENABLED_YES = '1';
    const ENABLED_NO = '0';
    const DEFAULT_ROOT = "ITEM ROOT";
    const XPATH_DELIMITER = '/';
    const NOSTRESSDOC_TAG = "nscdoc";

    /**
     * @var \Nostress\Koongo\Model\ChannelFactory
     */
    protected $channelFactory;

    /**
     * \Nostress\Koongo\Model\Taxonomy\Setup
     * @var unknown_type
     */
    protected $taxonomySetup;

    protected $_defaultAttribute = [
        "code" => "",
        "label" => "",
        "magento" => "",
        "type" => "normal",
        "limit" => "",
        "postproc" => "",
        "path" => "",
        "description" => [
            "text" => "",
            "example" => "",
            "options" => "",
            "format" => "text"
        ]
    ];

    public function _construct()
    {
        $this->_init('Nostress\Koongo\Model\ResourceModel\Channel\Feed');
    }

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Nostress\Koongo\Helper\Data\Feed\Description $helper
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Nostress\Koongo\Model\Translation $translation
     * @param \Nostress\Koongo\Model\ChannelFactory $channelFactory
     * @param \Nostress\Koongo\Model\Taxonomy\Setup $taxonomySetup
     * @param \Magento\Framework\Filesystem\DriverInterface $driver,
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Nostress\Koongo\Helper\Data\Feed\Description $helper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Nostress\Koongo\Model\Translation $translation,
        \Nostress\Koongo\Model\ChannelFactory $channelFactory,
        \Nostress\Koongo\Model\Taxonomy\Setup $taxonomySetup,
        \Magento\Framework\Filesystem\DriverInterface $driver,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->channelFactory = $channelFactory;
        $this->taxonomySetup = $taxonomySetup;

        parent::__construct($context, $registry, $helper, $storeManager, $translation, $driver, $resource, $resourceCollection, $data);
    }

    public function getFeedByCode($code = null)
    {
        if (isset($code)) {
            $filter = [self::COL_CODE => $code];
        } else {
            $filter = [];
        }
        $collection = $this->getFeedCollection($filter);
        foreach ($collection as $item) {
            return $item;
        }
        return null;
    }

    public function toOptionArray($enabled = null, $addFileType = null, $isMultiselect = true)
    {
        $options = $this->getCollection()->loadData()->toOptionArray($enabled, $addFileType);
        $options = $this->helper->array_unique_tree($options);

        if (!$isMultiselect) {
            array_unshift($options, ['value'=>'', 'label'=> __('--Please Select--')]);
        }
        return $options;
    }

    public function getFeedCollection($filter = null, $select = null, $sortColumn = null, $sortOrder = "ASC")
    {
        $collection = $this->getCollection();
        if (isset($filter) && !empty($filter)) {
            $collection->addFieldsToFilter($filter);
        }

        if (isset($select) && !empty($select)) {
            $collection->addFieldsToSelect($select);
        }

        if (!empty($sortColumn)) {
            $collection->getSelect()->order($sortColumn, $sortOrder);
        }

        return $collection->load();
    }

    public function feedsLoaded()
    {
        if (!$this->hasData('feeds_loaded')) {
            $collection = $this->getCollection();
            $collection->getSelect()->limit(1);

            $items = $collection->load()->getItems();
            $this->setData('feeds_loaded', count($items) > 0);
        }
        return $this->getData('feeds_loaded');
    }

    public function getFeeds()
    {
        if (!$this->getData('feeds')) {
            $this->setData('feeds', $this->getCollection()->load());
        }
        return $this->getData('feeds');
    }

    public function getLayout()
    {
        $layout = $this->getData(self::COL_LAYOUT);
        $layout = $this->helper->dS($layout);
        return $layout;
    }

    public function getTaxonomy()
    {
        $code = $this->getTaxonomyCode();
        if (empty($code)) {
            return null;
        }

        $taxonomy =  $this->taxonomySetup->getTaxonomyByCode($code);
        return $taxonomy;
    }

    public function hasTaxonomy()
    {
        $code = $this->getTaxonomyCode();
        if (empty($code)) {
            return false;
        }

        return true;
    }

    public function getChannel()
    {
        if ($this->getData('channel') === null) {
            $channel = $this->channelFactory->create();
            $channel->setChannelCode($this->getChannelCode());
            $this->setData('channel', $channel);
        }
        return $this->getData('channel');
    }

    public function getTrnasformationXslt($layout = null)
    {
        if (!isset($layout)) {
            $layout = $this->getLayout();
        }
        $xlst = $this->extractTrnasformationXslt($layout);
        return $xlst;
    }

    public function containMediaGallery()
    {
        return $this->containAttribute(self::PLATFORM_ATTRIBUTE_MEDIA);
    }

    public function containMulipleCategories()
    {
        return $this->containAttribute(self::PLATFORM_ATTRIBUTE_CATEGORIES);
    }

    public function containTierPrices()
    {
        return $this->containAttribute(self::PLATFORM_ATTRIBUTE_TIER_PRICES);
    }

    public function containReviews()
    {
        return $this->containAttribute(self::PLATFORM_ATTRIBUTE_REVIEWS);
    }

    protected function containAttribute($attributeCode)
    {
        $setup = $this->getAttributesSetup();
        if (empty($setup[self::ATTRIBUTES][self::ATTRIBUTE])) {
            return false;
        }
        foreach ($setup[self::ATTRIBUTES][self::ATTRIBUTE] as $attribute) {
            if (!empty($attribute[self::PLATFORM_ATTRIBUTE_ALIAS]) && $attribute[self::PLATFORM_ATTRIBUTE_ALIAS] == $attributeCode) {
                return true;
            }
        }
        return false;
    }

    public function getAttributesSetup($asArray = true, $layout = null)
    {
        if (!isset($layout)) {
            $layout = $this->getLayout();
        }
        $setup = $this->extractAttributesSetupFromLayout($layout, $asArray);
        if (!$setup) {
            $this->logAndException('Missing layout and attributes setup in feed with code %1' . $this->getCode());
        }
        $setup = $this->fillAttributesSetup($setup);
        return $setup;
    }

    public function getFeedAttributes()
    {
        $setupArray = $this->getAttributesSetup();

        if (!empty($setupArray[self::ATTRIBUTES][self::ATTRIBUTE]) && is_array($setupArray[self::ATTRIBUTES][self::ATTRIBUTE])) {
            return $setupArray[self::ATTRIBUTES][self::ATTRIBUTE];
        } else {
            return [];
        }
    }

    public function getSubmissionParams($section = self::FTP)
    {
        $setupArray = $this->getAttributesSetup();
        if (!empty($setupArray[self::SUBMISSION][ $section]) && is_array($setupArray[self::SUBMISSION][ $section])) {
            $params = $setupArray[ self::SUBMISSION][ $section];
            return $params;
        } else {
            return [];
        }
    }

    public function getSubmissionDefaults($section = self::FTP)
    {
        $params = $this->getSubmissionParams($section);
        $defaults = [];
        foreach ($params as $name => $pData) {
            if (isset($pData['default_value'])) {
                $defaults[ $name] = $pData['default_value'];
            }
        }
        return $defaults;
    }

    public function getCustomParamsAsCodeIndexedArray()
    {
        $setupArray = $this->getAttributesSetup();
        $customParamsSetup = [];
        if (isset($setupArray[self::COMMON][self::CUSTOM_PARAMS][self::PARAM])) {
            $customParamsSetup = $setupArray[self::COMMON][self::CUSTOM_PARAMS][self::PARAM];
        }

        $resultArray = [];

        foreach ($customParamsSetup as $param) {
            $resultArray[$param[self::CODE]] = $param;
        }
        return $resultArray;
    }

    protected function fillAttributesSetup($setup)
    {
        if (!isset($setup["attributes"])) {
            $this->logAndException('Missing attributes setup in feed with code %1' . $this->getCode());
        }

        if (isset($setup[self::COMMON][self::COLUMN_DELIMITER])) {
            $setup[self::COMMON][self::COLUMN_DELIMITER] = $this->helper->decodeSpaceCharacters($setup[self::COMMON][self::COLUMN_DELIMITER]);
        }

        if (isset($setup[self::COMMON][self::NEWLINE])) {
            $setup[self::COMMON][self::NEWLINE] = $this->helper->decodeSpaceCharacters($setup[self::COMMON][self::NEWLINE]);
        }

        if (isset($setup[self::COMMON][self::CUSTOM_PARAMS][self::PARAM][self::CODE])) {
            $setup[self::COMMON][self::CUSTOM_PARAMS][self::PARAM] = [$setup[self::COMMON][self::CUSTOM_PARAMS][self::PARAM]];
        }

        if (is_array($setup["attributes"]) && array_key_exists("attribute", $setup["attributes"])) {
            $attributes = $setup["attributes"]["attribute"];
        } else {
            $setup["attributes"] = [];
            return $setup;
        }

        if (!is_array($attributes)) {
            $attributes = [];
        }

        $attributes = $setup["attributes"]["attribute"];
        if (isset($attributes["code"])) {
            $attributes = [$attributes];
        }

        foreach ($attributes as $index => $attribute) {
            $defaultAttribute = $this->_defaultAttribute;
            if (array_key_exists(self::PLATFORM_ATTRIBUTE_ALIAS, $attribute) && !empty($attribute[self::PLATFORM_ATTRIBUTE_ALIAS])) {
                $defaultAttribute =  $this->helper->updateArray($this->helper->getAttributeDescription($attribute[self::PLATFORM_ATTRIBUTE_ALIAS]), $defaultAttribute, false);

                //switch original attribute for available magento attributes
                switch ($attribute[self::PLATFORM_ATTRIBUTE_ALIAS]) {
                    case "shipping_method_price":
                        $attribute[self::PLATFORM_ATTRIBUTE_ALIAS] = "shipping_cost";
                        break;
                    default:
                        break;
                }
            }

            $attribute = $this->helper->updateArray($attribute, $defaultAttribute, false);

            if (isset($attribute[self::PATH])) {
                if (!empty($attribute[self::PATH])) {
                    $attribute[self::PATH] = self::XPATH_DELIMITER . $attribute[self::PATH];
                }
                $attribute[self::PATH] = self::DEFAULT_ROOT . $attribute[self::PATH];
            }

            if (empty($attribute[self::LABEL])) {
                $attribute[self::LABEL] = $attribute[self::CODE];
            } elseif (empty($attribute[self::CODE])) {
                $attribute[self::CODE] = $this->helper->createCode($attribute[self::LABEL]);
            }

            $attributes[$index] = $attribute;
        }
        $setup["attributes"]["attribute"] = $attributes;
        return $setup;
    }

    ////////////////////////////FEED LAYOUT PROCESSING FUNCTIONS ///////////////////////////

    protected function extractAttributesSetupFromLayout($layout, $asArray = true)
    {
        $pattern = $this->getDocPattern();
        $numberOfMatches = preg_match($pattern, $layout, $setup);
        if (!$numberOfMatches) {
            return false;
        } else {
            $setup = $setup[0];
        }
        if (!$asArray) {
            return $setup;
        }

        //transform to array
        $xml = $this->helper->stringToXml($setup);
        $setup = $this->helper->XMLnodeToArray($xml);

        return $setup;
    }

    public function extractTrnasformationXslt($layout)
    {
        $pattern = $this->getDocPattern();
        $xlst = preg_replace($pattern, "", $layout);
        return $xlst;
    }

    protected function getDocPattern()
    {
        return "#\<" . self::NOSTRESSDOC_TAG . "\>(.+?)\<\/" . self::NOSTRESSDOC_TAG . "\>#s";
    }
}
