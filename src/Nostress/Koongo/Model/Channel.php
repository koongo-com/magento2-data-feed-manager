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
* Class for channel
*
* @category Nostress
* @package Nostress_Koongo
*
*/

namespace Nostress\Koongo\Model;

class Channel extends \Nostress\Koongo\Model\AbstractModel
{
    const CHANNEL_CACHE_DIR = "ChannelCacheM2";
    const CHANNEL_LOGO_FILE = "logo.png";
    const CHANNEL_MANUAL_FILE = "manual.html";
    const CHANNEL_DESCRIPTION_FILE = "short_description.html";
    protected ?string $_channelCacheDirUrl = null;

    public function getLogoUrl()
    {
        return $this->getCacheUrl() . self::CHANNEL_LOGO_FILE;
    }

    public function getManualUrl()
    {
        return $this->getCacheUrl() . self::CHANNEL_MANUAL_FILE;
    }

    public function getDescriptionUrl()
    {
        return $this->getCacheUrl() . self::CHANNEL_DESCRIPTION_FILE;
    }

    public function getPageUrl()
    {
        $urlCode = $this->getUrlCode();
        return $this->getPresentationWebsiteUrl() . $urlCode . ".html";
    }

    public function getLabel()
    {
        $code = $this->getChannelCode();
        $label = ucfirst($code);
        $label = str_replace("_", " ", $label);
        return $label;
    }

    protected function getCacheUrl()
    {
        if (empty($this->_channelCacheDirUrl)) {
            $this->_channelCacheDirUrl = $this->getPresentationResourcesUrl() . self::CHANNEL_CACHE_DIR . "/";
        }

        return $this->_channelCacheDirUrl . $this->getChannelCode() . "/";
    }

    public function getUrlCode()
    {
        $channelCode = $this->getChannelCode();
        return str_replace("_", "-", $channelCode);
    }

    protected function getPresentationWebsiteUrl()
    {
        return $this->helper->getKoongoWebsiteUrl();
    }

    protected function getPresentationResourcesUrl()
    {
        return $this->helper->getKoongoResourcesUrl();
    }
}
