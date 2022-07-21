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
 * ResourceModel for Koongo Connector tax cache table
 *
 * @category Nostress
 * @package Nostress_Koongo
 *
 */

namespace Nostress\Koongo\Model\ResourceModel\Cache;

class Tax extends \Nostress\Koongo\Model\ResourceModel\Cache
{
    protected $_cacheName = 'Tax';
    /** @var $taxClassModel \Magento\Tax\Model\ClassModel */
    protected $taxClassModel;
    /** @var $taxCalculation \Magento\Tax\Model\Calculation */
    protected $taxCalculation;
    /** @var $_rateRequest \Magento\Framework\DataObject */
    protected $_rateRequest;

    /**
     * Construct
     *
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Nostress\Koongo\Helper\Data\Loader $helper
     * @param \Magento\Tax\Model\ClassModel
     * @param string|null $resourcePrefix
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Nostress\Koongo\Model\Config\Source\Datetimeformat $datetimeformat,
        \Nostress\Koongo\Helper\Data\Loader $helper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Tax\Model\Config $taxConfig,
        \Magento\Weee\Helper\Data $weeeData,
        \Magento\Tax\Model\ClassModel $taxClassModel,
        \Magento\Tax\Model\Calculation $taxCalculation,
        $resourcePrefix = null
    ) {
        $this->taxClassModel = $taxClassModel;
        $this->taxCalculation = $taxCalculation;
        parent::__construct($context, $datetimeformat, $helper, $storeManager, $taxConfig, $weeeData, $resourcePrefix);
    }

    public function _construct()
    {
        $this->_init('nostress_koongo_cache_tax', 'tax_class_id');
    }

    protected function reloadTable()
    {
        $this->cleanMainTable();
        $this->updateRates();
    }

    /**
     * Updates tax rates for particular tax classes and current store
     */
    protected function updateRates()
    {
        $storeId = $this->getStoreId();
        $productTaxClassCollection = $this->taxClassModel->getCollection()->setClassTypeFilter(
            \Magento\Tax\Model\ClassModel::TAX_CLASS_TYPE_PRODUCT
        )->load();

        foreach ($productTaxClassCollection as $productTaxClassItem) {
            $request = $this->getRateRequest($productTaxClassItem->getId());
            $rate = $this->taxCalculation->getRate($request);
            $rate /= 100;
            $this->insertRate($productTaxClassItem->getId(), $storeId, $rate);
        }
    }

    protected function getRateRequest($productTaxClassId)
    {
        if (!isset($this->_rateRequest)) {
            $storeId = $this->getStoreId();
            $store = $this->getStore();

            $countryId = $this->helper->getStoreConfig($storeId, \Magento\Tax\Model\Config::CONFIG_XML_PATH_DEFAULT_COUNTRY);
            $regionId = $this->helper->getStoreConfig($storeId, \Magento\Tax\Model\Config::CONFIG_XML_PATH_DEFAULT_REGION);
            $postcode = $this->helper->getStoreConfig($storeId, \Magento\Tax\Model\Config::CONFIG_XML_PATH_DEFAULT_POSTCODE);

            $request = new \Magento\Framework\DataObject();

            $request->setCountryId($countryId)
            ->setRegionId($regionId)
            ->setPostcode($postcode)
            ->setStore($store)
            ->setCustomerClassId($this->taxCalculation->getDefaultCustomerTaxClass($store));

            $this->_rateRequest = $request;
        }
        $this->_rateRequest->setProductClassId($productTaxClassId);
        return $this->_rateRequest;
    }

    protected function insertRate($productTaxClassId, $storeId, $rate)
    {
        $this->getConnection()->beginTransaction();
        $this->helper->log(__("Insert row to nostress_koongo_cache_tax tax_class_id: %1 store_id: %2 tax_percent: %3", $productTaxClassId, $this->getStoreId(), $rate));
        try {
            $this->getConnection()->insert(
                $this->getMainTable(),
                ['tax_class_id' => $productTaxClassId, 'store_id' => $storeId, "tax_percent" => $rate]
            );
            $this->getConnection()->commit();
        } catch (\Exception $e) {
            $this->getConnection()->rollBack();
            throw $e;
        }
        return $this;
    }

    protected function cleanMainTable()
    {
        $this->helper->log(__("Clean nostress_koongo_cache_tax records for store #%1", $this->getStoreId()));
        $this->getConnection()->delete($this->getMainTable(), ['store_id = ?' => $this->getStoreId()]);
    }
}
