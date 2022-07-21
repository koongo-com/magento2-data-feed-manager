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
 * Model for Koongo API webhook event
 *
 * @category Nostress
 * @package Nostress_Koongo
 *
 */

namespace Nostress\Koongo\Model\Webhook;

class Event extends \Nostress\Koongo\Model\AbstractModel
{
    const STATUS_PENDING = "pending";
    const STATUS_PROCESSING = "processing";
    const STATUS_FINISHED = "finished";
    const STATUS_ERROR = "error";
    const STATUS_DUPLICATED = "duplicated";

    const PARAM_STORE_ORDER_ID = "store_order_id";
    const PARAM_STORE_SHIPMENT_ID = "store_shipment_id";
    const PARAM_STORE_TRACK_ID = "store_track_id";
    const PARAM_STORE_CREDITMEMO_ID = "store_creditmemo_id";
    const PARAM_STORE_PRODUCT_ID = "store_product_id";
    const PARAM_STORE_PRODUCT_SKU = "store_product_sku";
    const PARAM_STORE_PRODUCT_IDS = "store_product_ids";
    const PARAM_STORE_PRODUCT_SKUS = "store_product_skus";
    const PARAM_WEBHOOK_EVENT_ID = "webhook_event_id";
    const PARAM_NOTE = "note";

    public function _construct()
    {
        $this->_init('Nostress\Koongo\Model\ResourceModel\Webhook\Event');
    }

    /**
     * Update webhook event status
     *
     * @param string $status
     * @param string $message
     * @return void
     */
    public function updateStatus($status, $message = null)
    {
        $this->setStatus($status);
        if (isset($message)) {
            $this->setMessage($message);
        }
        $now = $this->helper->getNow();
        $this->setupdatedAt($now);
        $this->save();
    }

    /**
     * Get available statuses
     *
     * @return void
     */
    public function getStatusesAsIndexedArray()
    {
        return [
            self::STATUS_PENDING, self::STATUS_PENDING,
            self::STATUS_PROCESSING => self::STATUS_PROCESSING,
            self::STATUS_FINISHED => self::STATUS_FINISHED,
            self::STATUS_DUPLICATED => self::STATUS_DUPLICATED,
            self::STATUS_ERROR => self::STATUS_ERROR
        ];
    }
}
