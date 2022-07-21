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

class Processor extends \Nostress\Koongo\Model\AbstractModel
{
    const MB_SIZE = 1024;
    const TIME = 'time';
    const MEMORY = 'memory';
    const PRODUCTS = 'products';
    const CATEGORIES = 'categories';
    const TIME_DECIMAL = 2;

    protected $_startTime;
    protected $_totalTime;
    protected $_productCounter = 0;
    protected $_categoryCounter = 0;

    /*
     * @var \Nostress\Koongo\Model\Data\Loader\Product
     */
    protected $productLoader;

    /*
     * @var \Nostress\Koongo\Model\Data\Loader\Category
     */
    protected $categoryLoader;

    /*
     * @var \Nostress\Koongo\Model\Data\Transformation\Xml
    */
    protected $transXml;

    /*
     * @var \Nostress\Koongo\Model\Data\Transformation\Xslt
    */
    protected $transXslt;

    /*
     * @var \Nostress\Koongo\Model\Data\Writer
    */
    protected $writer;

    /*
     * @var \Nostress\Koongo\Model\Channel\Profile\Logger
     */
    protected $profileLogger;

    /*
    * @param \Nostress\Koongo\Model\Data\Loader\Product
    * @param \Nostress\Koongo\Model\Data\Loader\Category $categoryLoader,
    * @param \Nostress\Koongo\Model\Data\Transformation\Xml $transXml,
    * @param \Nostress\Koongo\Model\Data\Transformation\Xslt $transXslt,
    * @param \Nostress\Koongo\Model\Data\Writer $writer,
    * @param \Nostress\Koongo\Model\Channel\Profile\Logger
    * @param \Nostress\Koongo\Helper\Data $helper
    * @param \Nostress\Koongo\Model\Translation $translation
    */
    public function __construct(
        \Nostress\Koongo\Model\Data\Loader\Product $productLoader,
        \Nostress\Koongo\Model\Data\Loader\Category $categoryLoader,
        \Nostress\Koongo\Model\Data\Transformation\Xml $transXml,
        \Nostress\Koongo\Model\Data\Transformation\Xslt $transXslt,
        \Nostress\Koongo\Model\Data\Writer $writer,
        \Nostress\Koongo\Model\Channel\Profile\Logger $profileLogger,
        \Nostress\Koongo\Helper\Data $helper,
        \Nostress\Koongo\Model\Translation $translation
    ) {
        $this->productLoader = $productLoader;
        $this->categoryLoader = $categoryLoader;
        $this->transXml = $transXml;
        $this->transXslt = $transXslt;
        $this->writer = $writer;
        $this->profileLogger = $profileLogger;
        $this->helper = $helper;
        $this->translation = $translation;
    }

    /**
     * Execute profile - loeds data from database and transforms them into final feed
     * @param \Nostress\Koongo\Model\Channel\Profile $profile
     * @throws Exception
     * @return \Nostress\Koongo\Model\Channel\Profile\Processor
     */
    public function run($profile)
    {
        $this->canRun($profile);

        $this->init();
        $profile->setStatus(\Nostress\Koongo\Model\Channel\Profile::STATUS_RUNNING, true);
        //load requested parameters from transform uint -- all

        $this->writer->setData($profile->getWriterParams());

        try {
            $this->transXml->init($profile->getTransformParams());
            $this->transXslt->init($profile->getTransformParams());

            //Export categories from DB
            if ($profile->exportCategoryTree()) {
                $this->categoryLoader->init($profile->getLoaderParams());
                $this->loadAndPrepareCategories();
            }

            //Export products from DB
            if ($profile->exportProducts()) {
                $this->productLoader->init($profile->getLoaderParams());
                $this->loadAndPrepareProducts();
            }

            $xml = $this->transXml->getResult(true);

            $this->log("XSL transformation started.");
            $this->transXslt->transform($xml);
            $result = $this->transXslt->getResult();
            $this->log("XSL transformation finished.");
            $this->writer->saveData($result);
            $this->log("Feed content saved into file.");

            $this->stopTime();
        } catch (\Exception $e) {
            $message = $this->translation->processException($e);
            $profile->setMessageStatusError($message, \Nostress\Koongo\Model\Channel\Profile::STATUS_ERROR);
            return;
        }

        $profile->resetUrl();
        $profile->setMessage($this->getProcessInfo(true));
        $profile->setStatus(\Nostress\Koongo\Model\Channel\Profile::STATUS_FINISHED);
        $this->logEvent($profile->getFeedCode(), $profile->getUrl());
        return;
    }

    /**
     * Loads products from database and transforms them into XSLT input
     */
    protected function loadAndPrepareProducts()
    {
        $this->log("Product batch load started.");
        while (($productsNumber = count($batch = $this->productLoader->loadBatch())) > 0) {
            $this->incrementProductCounter($productsNumber);
            $this->transXml->transform($batch);
            $this->log("Product batch loaded.");
        }

        if ($this->getProductCounter() == 0) {
            $this->logAndException("10");
        }
        $this->incrementProductCounter(-$this->transXml->getSkippedProductsCounter());
    }

    /**
     * Loads categories from database and transforms them into XSLT input
     */
    protected function loadAndPrepareCategories()
    {
        $categories = $this->categoryLoader->loadAll();
        $this->transXml->insertCategories($categories);
        $this->incrementCategoryCounter(count($categories));
        $this->log("All categories loaded.");
    }

    protected function init()
    {
        $this->initStartTime();
        $this->resetProductCounter();
        return $this;
    }

    public function getProcessInfo($format = true)
    {
        $info = [];
        $info[self::TIME] = $this->getTotalTime($format);
        $info[self::MEMORY] = $this->getTotalMemory($format);
        $info[self::PRODUCTS] = $this->getProductCounter();
        $info[self::CATEGORIES] = $this->getCategoryCounter();

        if ($format) {
            $infoString = "";
            if ($info[self::PRODUCTS] > 0) {
                $infoString = __("Products: %1 ", $info[self::PRODUCTS]);
            }
            if ($info[self::CATEGORIES] > 0) {
                $infoString .= __("Categories: %1 ", $info[self::CATEGORIES]);
            }
            $infoString .= __("Time: %1 Memory: %2 ", $info[self::TIME], $info[self::MEMORY]);
            $info = $infoString;
        }

        return $info;
    }

    protected function canRun($profile)
    {
        $message = "";

        $status = $profile->getStatus();

        if (is_null($profile->getFeed())) {
            $message = $this->translation->getMessageByCode(3);
        } elseif ($status == \Nostress\Koongo\Model\Channel\Profile::STATUS_DISABLED) {
            $message = __($this->translation->getMessageByCode(4), $profile->getId());
        }

        if (!empty($message)) {
            $messagePrefix = $this->translation->getMessageByCode(2);
            $profile->setMessageStatusError($messagePrefix . ". " . $message, \Nostress\Koongo\Model\Channel\Profile::STATUS_ERROR);
            throw new \Exception($message);
        }
    }

    protected function initStartTime()
    {
        $this->_startTime = $this->helper->getProcessorTime();
    }

    protected function stopTime()
    {
        $endTime =  $this->helper->getProcessorTime();
        $this->_totalTime = $endTime - $this->_startTime;
    }

    protected function getTotalTime($format = true)
    {
        $time = $this->_totalTime;
        $time = round($time, self::TIME_DECIMAL);
        if ($format) {
            $time .= " " . __("s");
        }
        return $time;
    }

    protected function getTotalMemory($format = true)
    {
        //$memory = memory_get_usage(true);
        $memory = memory_get_peak_usage(1);
        $memory = round(($memory/self::MB_SIZE)/self::MB_SIZE);
        if ($format) {
            $memory .= " " . __("MB");
        }
        return $memory;
    }

    protected function incrementProductCounter($number)
    {
        $this->_productCounter += $number;
    }

    protected function incrementCategoryCounter($number)
    {
        $this->_categoryCounter += $number;
    }

    protected function resetProductCounter()
    {
        $this->_productCounter = 0;
    }

    protected function getProductCounter()
    {
        return $this->_productCounter;
    }

    protected function getCategoryCounter()
    {
        return $this->_categoryCounter;
    }

    protected function logEvent($feedCode, $url)
    {
        $info = $this->getProcessInfo(false);
        $this->profileLogger->logRunProfileEvent($feedCode, $url, $info[self::PRODUCTS], $info[self::CATEGORIES]);
    }
}
