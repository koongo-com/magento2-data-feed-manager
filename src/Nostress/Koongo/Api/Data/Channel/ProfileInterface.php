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
* Interface for profile main class
*
* @category Nostress
* @package Nostress_Koongo
*
*/

namespace Nostress\Koongo\Api\Data\Channel;

interface ProfileInterface
{
    /**
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const PROFILE_ID = 'entity_id';
    const STORE_ID = 'store_id';
    const NAME = 'name';
    const FILENAME = 'filename';
    const URL = 'url';
    const FEED_CODE = 'feed_code';
    const CONFIG = 'config';
    const STATUS = 'status';
    const MESSAGE = 'message';
    const CREATION_TIME = 'creation_time';
    const UPDATE_TIME   = 'update_time';

    /**
     * Get Profile ID
     *
     * @return int|null
     */
    public function getProfileId();

    /**
     * Get Store ID
     *
     * @return int|null
     */
    public function getStoreId();

    /**
     * Get profile name
     *
     * @return string
     */
    public function getName();

    /**
     * Get feed url
     *
     * @return string
     */
    public function getUrl();

    /**
     * Get feed code
     *
     * @return string
     */
    public function getFeedCode();

    /**
     * Get profile config
     *
     * @return string
     */
    public function getConfig();

    /**
     * Get profile status
     *
     * @return string|enum
     */
    public function getStatus();

    /**
     * Get message
     *
     * @return string
     */
    public function getMessage();

    /**
     * Get profile creation time
     *
     * @return string
     */
    public function getCreationTime();

    /**
     * Get feed update time
     *
     * @return string
     */
    public function getUpdateTime();
}
