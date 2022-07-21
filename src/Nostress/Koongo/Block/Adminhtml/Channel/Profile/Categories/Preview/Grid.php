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

namespace Nostress\Koongo\Block\Adminhtml\Channel\Profile\Categories\Preview;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    const TABLE_CATALOG_PRODUCT_FLAT_ALIAS = 'cpf';
    const TABLE_CATALOG_PRODUCT_ENTITY = 'cpe';
    const TABLE_NOSTRESS_KOONGO_TAXONOMY_CATEGORY = 'nktc';

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry|null
     */
    protected $_coreRegistry = null;

    /**
     * @var \Nostress\Koongo\Model\Channel\Profile\Categories
     */
    protected $_categoriesModel = null;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $_productFactory;

    /**
     *  @var $model \Nostress\Koongo\Model\Channel\Profile
     **/
    protected $profile;

    /**
     *
     * @var \Nostress\Koongo\Helper
     */
    protected $helper;

    /**
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Nostress\Koongo\Model\Cache\Channelcategory $channelcategory
     * @param \Nostress\Koongo\Model\Channel\Profile\Categories $categoriesModel
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Nostress\Koongo\Helper\Data $helper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Framework\Registry $coreRegistry,
        \Nostress\Koongo\Model\Cache\Channelcategory $channelcategory,
        \Nostress\Koongo\Model\Channel\Profile\Categories $categoriesModel,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Nostress\Koongo\Helper\Data $helper,
        array $data = []
    ) {
        $this->_coreRegistry = $coreRegistry;

        parent::__construct($context, $backendHelper, $data);

        /* @var $model \Nostress\Koongo\Model\Channel\Profile */
        $this->profile = $this->_coreRegistry->registry('koongo_channel_profile');

        $this->_productFactory = $productFactory;
        $this->_categoriesModel = $categoriesModel;
        $this->_categoriesModel->initProfile($this->profile);
        $this->helper = $helper;

        $collection = $channelcategory->getCollection();

        $this->setCollection($collection);
    }

    protected function _prepareCollection()
    {
        $locale = $this->getRequest()->getParam('taxonomy_locale');
        if (!$locale) {
            $locale = $this->_categoriesModel->getCurrentLocale();
        }
        $taxonomyCode = $this->profile->getFeed()->getTaxonomyCode();

        $channelCategoriesTableAlias = self::TABLE_NOSTRESS_KOONGO_TAXONOMY_CATEGORY;
        $channelCategoriesTable =  $this->_collection->getResource()->getTable('nostress_koongo_taxonomy_category');

        $select = $this->_collection->getSelect();

        //Join channel category table
        $condition =  "{$channelCategoriesTableAlias}.hash = main_table.hash " .
                "AND {$channelCategoriesTableAlias}.taxonomy_code = '{$taxonomyCode}' " .
                "AND {$channelCategoriesTableAlias}.locale = '{$locale}' ";
        $select->joinLeft(
            [$channelCategoriesTableAlias => $channelCategoriesTable],
            $condition,
            ['name','path','id']
        );

        //Join catalog product entity
        $catalogProductEntityTableAlias = self::TABLE_CATALOG_PRODUCT_ENTITY;
        $catalogProductEntityTable =  $this->_collection->getResource()->getTable('catalog_product_entity');

        $condition =  "{$catalogProductEntityTableAlias}.entity_id = main_table.product_id ";
        $select->joinLeft(
            [$catalogProductEntityTableAlias => $catalogProductEntityTable],
            $condition,
            ['sku']
        );

        //Join catalog product flat
        $catalogProductFlatTableAlias = self::TABLE_CATALOG_PRODUCT_FLAT_ALIAS;
        $catalogProductFlatTable =  $this->getFlatTableName($this->profile->getStoreId());

        //Check if flat is available
        if ($this->checkTableExists($catalogProductFlatTable)) {
            $condition =  "{$catalogProductFlatTableAlias}.entity_id = main_table.product_id ";
            $select->joinLeft(
                [$catalogProductFlatTableAlias => $catalogProductFlatTable],
                $condition,
                ['product_name' => 'name']
            );
        }

        $select->where('profile_id = ?', $this->profile->getId());
        return parent::_prepareCollection();
    }

    protected function getFlatTableName($storeId)
    {
        return $this->_collection->getResource()->getTable("catalog_product_flat_" . $storeId);
    }

    protected function checkTableExists($tableName)
    {
        return $this->_collection->getResource()->getConnection()->isTableExists($tableName);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('previewGrid');
        $this->setRowClickCallback(false);
        $this->setUseAjax(true);

        $this->setEmptyText(__('No Products Found'));
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

    /**
     * @return $this
     */
    protected function _prepareColumns()
    {
        $channelLabel = $this->profile->getChannel()->getLabel();

        $this->addColumn(
            'product_id',
            [
                'header' => __('Product Id'),
                'sortable' => true,
                'index' => 'product_id',
                'type' => 'number',
            ]
        );

        $this->addColumn(
            'sku',
            [
                'header' => __('Product Sku'),
                'sortable' => true,
                'index' => 'sku',
                'filter_index' => self::TABLE_CATALOG_PRODUCT_ENTITY . '.sku',
            ]
        );

        $catalogProductFlatTable =  $this->getFlatTableName($this->profile->getStoreId());

        if ($this->checkTableExists($catalogProductFlatTable)) {
            $this->addColumn(
                'product_name',
                [
                    'header' => __('Product Name'),
                    'sortable' => true,
                    'index' => 'product_name',
                    'filter_index' => self::TABLE_CATALOG_PRODUCT_FLAT_ALIAS . '.name',
                ]
            );
        }

        $this->addColumn(
            'edit',
            [
                'header' => __('Action'),
                'type' => 'action',
                'getter' => 'getId',
                'actions' => [
                    [
                        'caption' => __('View Product'),
                        'url' => [
                            'base' => 'catalog/product/edit',
                            'params' => ['store' => $this->profile->getStoreId()]
                        ],
                        'target' => '_blank',
                        'field' => 'id'
                    ]
                ],
                'filter' => false,
                'sortable' => false,
                'index' => 'stores',
                'header_css_class' => 'col-action',
                'column_css_class' => 'col-action'
            ]
        );

        $this->addColumn(
            'id',
            [
                'header' => __('%1 Category Id', $channelLabel),
                'sortable' => true,
                'index' => "id",
                'filter_index' => self::TABLE_NOSTRESS_KOONGO_TAXONOMY_CATEGORY . '.id',
                'type' => 'number',
            ]
        );

        $this->addColumn(
            'path',
            [
                'header' => __('%1 Category Path', $channelLabel),
                'sortable' => true,
                'index' => 'path',
                'filter_index' => self::TABLE_NOSTRESS_KOONGO_TAXONOMY_CATEGORY . '.path',
            ]
        );

        return parent::_prepareColumns();
    }
}
