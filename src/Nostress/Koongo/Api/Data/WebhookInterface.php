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
* Interface for webhook
*
* @category Nostress
* @package Nostress_Koongo
*
*/

namespace Nostress\Koongo\Api\Data;

interface WebhookInterface
{
    /**
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const ENTITY_ID = 'entity_id';
    const TOPIC = 'topic';
    const URL = 'url';
    const STORE_ID = 'store_id';
    const SECRET = 'secret';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    /**
     * Gets the ID for the webhook.
     *
     * @return int|null Webhook ID.
     */
    public function getEntityId();

    /**
     * Gets the Topic for the webhook.
     *
     * @return string|null Webhook topic.
     */
    public function getTopic();

    /**
     * Gets the Url for the webhook.
     *
     * @return string|null Webhook url.
     */
    public function getUrl();

    /**
     * Gets the Store id for the webhook.
     *
     * @return int|null Store id.
     */
    public function getStoreId();

    /**
     * Gets the secret key for the webhook.
     *
     * @return string|null Secret.
     */
    public function getSecret();

    /**
     * Gets the created at datetime for the webhook.
     *
     * @return string|null Date created at.
     */
    public function getCreatedAt();

    /**
     * Gets the updated at datetime for the webhook.
     *
     * @return string|null Date updated at.
     */
    public function getUpdatedAt();

    /**
     * Sets entity ID.
     *
     * @param int $entityId
     * @return $this
     */
    public function setEntityId($entityId);

    /**
     * Sets the Topic for the webhook.
     *
     * @return $this
     */
    public function setTopic($topic);

    /**
     * Sets the Url for the webhook.
     *
     * @return $this
     */
    public function setUrl($url);

    /**
     * Sets the Store id for the webhook.
     *
     * @return $this
     */
    public function setStoreId($storeId);

    /**
     * Sets the secret key for the webhook.
     *
     * @return $this
     */
    public function setSecret($secret);

    /**
     * Sets the created at datetime for the webhook.
     *
     * @return $this
     */
    public function setCreatedAt($createdAt);

    /**
     * Sets the updated at datetime for the webhook.
     * @return $this
     */
    public function setUpdatedAt($updatedAt);
}
