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

namespace Nostress\Koongo\Model\Channel\Feed;

use Nostress\Koongo\Model\Channel\Feed;

class Manager extends \Magento\Framework\Model\AbstractModel
{
    /**
     * @var \Nostress\Koongo\Model\Channel\FeedFactory
     */
    protected $feedFactory;

    /**
     * Construct
     *
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Nostress\Koongo\Helper\Data\Loader $helper
     */
    public function __construct(\Nostress\Koongo\Model\Channel\FeedFactory $feedFactory)
    {
        $this->feedFactory = $feedFactory;
    }

    public function updateFeeds($data)
    {
        $data = $this->prepareData($data);
        $this->updateData($data);
    }

    protected function prepareData($data)
    {
        $modifData = [];
        foreach ($data as $key => $item) {
            if (!isset($item[Feed::COL_CODE])) {
                throw new \Exception(__("Missing feed setup attribute '" . self::COL_CODE . "'"));
            }
            $modifData[$item[Feed::COL_CODE]] = $item;
        }
        return $modifData;
    }

    protected function updateData($data)
    {
        $collection = $this->feedFactory->create()->getCollection()->load();
        foreach ($collection as $item) {
            $code = $item->getCode();
            if (isset($data[$code])) {
                $this->copyData($data[$code], $item);
                unset($data[$code]);
            } else {
                $item->delete();
            }
        }
        $this->insertData($data, $collection);
        $collection->save();
    }

    protected function insertData($data, $collection)
    {
        foreach ($data as $itemData) {
            $itemData[Feed::COL_ENABLED] = Feed::DEF_ENABLED;
            $colItem = $collection->getNewEmptyItem();
            $colItem->setData($itemData);
            $collection->addItem($colItem);
        }
    }

    protected function copyData($data, $dstItem)
    {
        foreach ($data as $key => $src) {
            $dstItem->setData($key, $src);
        }
    }
}
