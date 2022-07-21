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
 * Model for API client for Koongo service connection
 *
 * @category Nostress
 * @package Nostress_Koongo
 */

namespace Nostress\Koongo\Model\Api\Restclient;

class Kaas extends \Nostress\Koongo\Model\Api\Restclient
{
    /**
     * Performs a post request
     *
     * @param Resource or url $resource
     * @param Array or Json of parameters $payload
     * @param boolean $toJson Transform payload to json
     * @param Array of query $query
     * @param string $returnBodyConversion Conversion of returned body.
     * @return mixed
     */
    public function sendRequestPost($resource, $payload, $topic, $webhookSecret)
    {
        $payload = json_encode($payload);
        $this->_adjustHeader($topic, $payload, $webhookSecret);
        return $this->_sendRequest("POST", $resource, null, $payload, false, self::REQUEST_RETURN_CONVERSION_NO_CHANGE);
    }

    /**
     * Set headers
     *
     * @param string $topic
     * @param string $payload Json encoded payload.
     * @param string $webhookSecret
     * @return void
     */
    protected function _adjustHeader($topic, $payload, $webhookSecret)
    {
        $signature = $this->_calculateSignature($payload, $webhookSecret);
        $this->_cleanHeader();
        $this->_addHeader("X-Webhook-Topic", $topic);
        $this->_addHeader("X-Webhook-Signature", $signature);
    }

    /**
     * Calculate signature for Kaas communication
     *
     * @param string $payload Json encoded payload
     * @param string $webhookSecret
     * @return string
     */
    protected function _calculateSignature($payload, $webhookSecret)
    {
        return base64_encode(hash_hmac('sha256', $payload, $webhookSecret, true));
    }
}
