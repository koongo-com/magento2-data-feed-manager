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
 * Model for Koongo API stock repository
 *
 * @category Nostress
 * @package Nostress_Koongo
 *
 */

namespace Nostress\Koongo\Model\Cache;

use Magento\Framework\Api\SortOrder;
use Nostress\Koongo\Api\Cache\StockRepositoryInterface;
use Nostress\Koongo\Model\ResourceModel\Cache\Stock\Collection;
use Magento\InventoryApi\Api\GetSourceItemsBySkuInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\App\Bootstrap;

class StockRepository implements StockRepositoryInterface
{
    /**
     * Stock factory
     *
     * @var \Nostress\Koongo\Model\Cache\StockFactory
     */
    protected $_stockFactory;

    /**
     * Stock resource model
     *
     * @var \Nostress\Koongo\Model\ResourceModel\Cache\Stock
     */
    protected $_resourceModel;

    /**
     * @var \Nostress\Koongo\Api\Cache\Data\StockSearchResultsInterfaceFactory
     */
    protected $_searchResultsFactory;

    /**
     *
     * @var \Nostress\Koongo\Helper
     */
    public $helper;

    /**
     * @param \Nostress\Koongo\Model\Cache\StockFactory $stockFactory
     * @param \Nostress\Koongo\Model\ResourceModel\Cache\Stock $resourceModel,
     */
    public function __construct(
        \Nostress\Koongo\Model\Cache\StockFactory $stockFactory,
        \Nostress\Koongo\Model\ResourceModel\Cache\Stock $resourceModel,
        \Nostress\Koongo\Api\Cache\Data\StockSearchResultsInterfaceFactory $searchResultsFactory,
        \Nostress\Koongo\Helper\Data $helper
    ) {
        $this->_stockFactory = $stockFactory;
        $this->_resourceModel = $resourceModel;
        $this->_searchResultsFactory = $searchResultsFactory;
        $this->helper = $helper;
    }

    /**
     * Get stock record list
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Nostress\Koongo\Api\Cache\Data\StockSearchResultsInterface
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria)
    {
        /** @var \Nostress\Koongo\Model\ResourceModel\Cache\Stock\Collection $collection */
        $collection = $this->_stockFactory->create()->getCollection();

        //Add filters from root filter group to the collection
        foreach ($searchCriteria->getFilterGroups() as $group) {
            $this->addFilterGroupToCollection($group, $collection);
        }

        /** @var SortOrder $sortOrder */
        foreach ((array)$searchCriteria->getSortOrders() as $sortOrder) {
            $field = $sortOrder->getField();
            $collection->addOrder(
                $field,
                ($sortOrder->getDirection() == SortOrder::SORT_ASC) ? 'ASC' : 'DESC'
            );
        }
        $collection->setCurPage($searchCriteria->getCurrentPage());
        $collection->setPageSize($searchCriteria->getPageSize());
        $collection->load();

        $searchResult = $this->_searchResultsFactory->create();
        $searchResult->setSearchCriteria($searchCriteria);
        $searchResult->setItems($collection->getItems());
        $searchResult->setTotalCount($collection->getSize());
        return $searchResult;
    }

    /**
     * Helper function that adds a FilterGroup to the collection.
     *
     * @param \Magento\Framework\Api\Search\FilterGroup $filterGroup
     * @param Collection $collection
     * @return void
     */
    protected function addFilterGroupToCollection(
        \Magento\Framework\Api\Search\FilterGroup $filterGroup,
        Collection $collection
    ) {
        foreach ($filterGroup->getFilters() as $filter) {
            $conditionType = $filter->getConditionType() ? $filter->getConditionType() : 'eq';
            $collection->addFieldToFilter($filter->getField(), [$conditionType => $filter->getValue()]);
        }
    }

    /**
     * Reload stock data for given store
     *
     * @param int $storeId
     * @return string Json config.
     */
    public function getReload($storeId)
    {
        $status = "success";
        $message = "";
        try {
            $this->_stockFactory->create()->reload($storeId);
        } catch (\Exception $e) {
            $status = "failure";
            $message = $e->getMessage();
            $this->helper->log("Koongo stock cache reload failure for store_id " . $storeId);
        }

        $result = ['status' => $status, 'message' => $message];
        return [$result];
    }

    /**
     * Reload stock data for given store and product and return the item data
     *
     * @param int $storeId
     * @param int $productId
     * @return string Json config.
     */
    public function getProductStock($storeId, $productId)
    {
        $status = "success";
        $message = "";
        $itemData = null;

        try {
            $this->_stockFactory->create()->reloadItem($storeId, $productId);
        } catch (\Exception $e) {
            $status = "failure";
            $message = $e->getMessage();
            $this->helper->log("Koongo stock cache reload failure for store_id " . $storeId . " and product_id" . $productId);
        }

        if ($status == "success") {
            /** @var \Nostress\Koongo\Model\ResourceModel\Cache\Stock\Collection $collection */
            $collection = $this->_stockFactory->create()->getCollection();
            $collection->addFieldToFilter("store_id", $storeId);
            $collection->addFieldToFilter("product_id", $productId);
            $collection->load();
            $itemData = $collection->getFirstItem()->getData();
            if (!isset($itemData["product_id"])) {
                $status = "failure";
                $message = "Record not found for product " . $productId;
            }
        }

        $result = ['status' => $status, 'message' => $message, 'item' => $itemData];
        return [$result];
    }

    /**
     * Get product stock id by sku
     * @param string $sku
     * @return string|null
     */
    public function getStockId($sku)
    {
        $bootstrap = Bootstrap::create(BP, $_SERVER);
        $objectManager = $bootstrap->getObjectManager();
        $resource = $objectManager->get(ResourceConnection::class);

        $sourceItemsBySku = $objectManager->get(GetSourceItemsBySkuInterface::class);
        $sourceItems = $sourceItemsBySku->execute($sku);

        $result = null;
        foreach ($sourceItems as $sourceItem) {
            $connection = $resource->getConnection();
            $tableName = $resource->getTableName('inventory_source_stock_link');

            $select = $connection->select()
                ->from($tableName, ['stock_id'])
                ->where('source_code = :source_code');

            $sourceCode = $sourceItem->getSourceCode();
            $bind = ['source_code' => $sourceCode];

            $result = $connection->fetchOne($select, $bind);
        }

        return $result;
    }
}
