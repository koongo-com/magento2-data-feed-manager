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
 * Config management for Koongo Service
 *
 * @category Nostress
 * @package Nostress_Koongo
 *
 */

namespace Nostress\Koongo\Model;

class ConfigManagement extends AbstractModel
{
    protected $_configResourceGroups = ['tax/classes',
                                        'tax/calculation',
                                        'tax/defaults',
                                        'shipping/origin',
                                        'general/country',
                                        'general/region',
                                        'general/locale',
                                        'general/single_store_mode',
                                        'currency/options',
                                        'catalog/fields_masks',
                                        // 'catalog/frontend',
                                        // 'catalog/review',
                                        'catalog/productalert',
                                        'catalog/productalert_cron',
                                        'catalog/placeholder',
                                        // 'catalog/recently_products',
                                        // 'catalog/product_video',
                                        'catalog/price',
                                        'catalog/layered_navigation',
                                        'catalog/search',
                                        'catalog/seo',
                                        'catalog/navigation',
                                        'catalog/downloadable',
                                        'catalog/custom_options',
                                        'cataloginventory/options',
                                        'cataloginventory/item_options',
                                        // 'cataloginventory/bulk_operations',
                                        // 'cataloginventory/source_selection_distance_based',
                                        // 'cataloginventory/source_selection_distance_based_google',
                                        'sales/general',
                                        'sales/reorder',
                                        'sales/allow_zero_grandtotal',
                                        'sales/minimum_order',
                                        'koongo_license/general'
                                    ];

    /**
     * Shipping config
     *
     * @var \Magento\Shipping\Model\Config
     */
    protected $_shippingConfig;

    /**
     * Payment helper
     *
     * @var \Magento\Payment\Helper\Data
     */
    protected $_paymentHelper;

    /**
     * License helper
     * @var \Nostress\Koongo\Helper\Version
     */
    protected $_licenseHelper;

    /**
     * @var ResourceConnection
     */
    protected $_resourceConnection;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Nostress\Koongo\Helper\Data $helper
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Nostress\Koongo\Model\Translation $translation
     * @param \Magento\Shipping\Model\Config $shippingConfig
     * @param \Magento\Payment\Helper\Data $paymentHelper
     * @param \Nostress\Koongo\Helper\Version $licenseHelper
     * @param \Magento\Framework\App\ResourceConnection $resourceConnection
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
        \Magento\Shipping\Model\Config $shippingConfig,
        \Magento\Payment\Helper\Data $paymentHelper,
        \Nostress\Koongo\Helper\Version $licenseHelper,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->_shippingConfig = $shippingConfig;
        $this->_paymentHelper = $paymentHelper;
        $this->_licenseHelper  = $licenseHelper;
        $this->_resourceConnection = $resourceConnection;
        parent::__construct($context, $registry, $helper, $storeManager, $translation, $resource, $resourceCollection, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig($storeId)
    {
        $settings = [];
        foreach ($this->_configResourceGroups as $group) {
            $groupConfig = $this->helper->getStoreConfig($storeId, $group);
            if (!empty($groupConfig)) {
                foreach (array_keys($groupConfig) as $nodeCode) {
                    $nodeIndex = $group . '/' . $nodeCode;
                    $settings[$nodeIndex] = $this->helper->getStoreConfig($storeId, $nodeIndex);
                }
            } else {
                $this->log("Configuration group " . $group . "is EMPTY");
            }
        }
        $settings["koongo_license/general/module_version"] = $this->_licenseHelper->getModuleVersion();
        $settings["cataloginventory/options/reservations_enabled"] = (int)$this->inventoryReservationsTableExists();

        $result = ['settings' => $settings];
        $result['carriers'] = $this->_getShippingCarriersAndMethods($storeId);
        $result['payment_methods'] = $this->_getPaymentMethods($storeId);
        return [$result];
    }

    protected function _getShippingCarriersAndMethods($storeId)
    {
        $store = $this->helper->getStore($storeId);
        $carriersArray = [];

        $carriers = $this->_shippingConfig->getAllCarriers($store);
        foreach ($carriers as $carrierCode => $carrier) {
            $carrierItem = [];
            $carrierItem['title'] = $this->helper->getStoreConfig($storeId, 'carriers/' . $carrierCode . '/title');
            $carrierItem['active'] = $this->helper->getStoreConfig($storeId, 'carriers/' . $carrierCode . '/active');
            $carrierItem['code'] = $carrierCode;

            $shippingMethods = $carrier->getAllowedMethods();
            $shippingMethodsArray = [];

            foreach ($shippingMethods as $methodCode => $methodTitle) {
                $shippingMethodsArray[] = ['code' => $methodCode, 'title' => $methodTitle];
            }

            $carrierItem['shipping_methods'] = $shippingMethodsArray;
            $carriersArray[] = $carrierItem;
        }
        return $carriersArray;
    }

    protected function _getPaymentMethods($storeId)
    {
        $paymentMethods = $this->_paymentHelper->getStoreMethods($storeId);
        $paymentMethodsArray = [];
        foreach ($paymentMethods as $method) {
            $paymentMethodItem = [];
            $paymentMethodItem['code'] = $method->getCode();
            $paymentMethodItem['title'] = $method->getTitle();
            $paymentMethodsArray[] = $paymentMethodItem;
        }
        return $paymentMethodsArray;
    }

    /**
     * Check if reservations table exists
     *
     * @return boolean
     */
    protected function inventoryReservationsTableExists(): bool
    {
        $connection  = $this->_resourceConnection->getConnection();
        $tableName = $connection->getTableName('inventory_reservation');
        return $connection->isTableExists($tableName);
    }
}
