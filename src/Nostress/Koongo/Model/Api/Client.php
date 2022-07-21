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
 * Model for Koongo connector cominication with kaas - client.
 *
 * @category Nostress
 * @package Nostress_Koongo
 *
 */

namespace Nostress\Koongo\Model\Api;

class Client extends \Nostress\Koongo\Model\Api\Client\Simple
{
    const LINK_BATCH_SIZE = 10;

    /*
     * @var \Nostress\Koongo\Model\Channel\Feed\Manager
     */
    protected $feedManager;

    /*
     * @var \Nostress\Koongo\Model\Channel\Profile\Manager
     */
    protected $profileManager;

    /*
     * @var \Nostress\Koongo\Model\Taxonomy\Setup\Manager
    */
    protected $taxonomySetupManager;

    /**
     * @var \Nostress\Koongo\Model\Taxonomy\Category\Manager
     */
    protected $taxonomyCategoryManager;

    /**
     * @param \Nostress\Koongo\Helper\Version $versionHelper
     * @param Magento\Framework\App\CacheInterface $cache
     * @param \Nostress\Koongo\Model\Data\Reader
     * @param \Nostress\Koongo\Model\Channel\Feed\Manager
     * @param \Nostress\Koongo\Model\Channel\Profile\Manager
     * @param \Nostress\Koongo\Model\Taxonomy\Category\Manager
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Nostress\Koongo\Helper\Version $versionHelper,
        \Magento\Framework\App\CacheInterface $cache,
        \Nostress\Koongo\Model\Data\Reader $reader,
        \Nostress\Koongo\Model\Channel\Feed\Manager $feedManager,
        \Nostress\Koongo\Model\Channel\Profile\Manager $profileManager,
        \Nostress\Koongo\Model\Taxonomy\Setup\Manager $taxonomySetupManager,
        \Nostress\Koongo\Model\Taxonomy\Category\Manager $taxonomyCategoryManager
    ) {
        parent::__construct($versionHelper, $cache, $reader);

        $this->feedManager = $feedManager;
        $this->profileManager = $profileManager;
        $this->taxonomySetupManager = $taxonomySetupManager;
        $this->taxonomyCategoryManager = $taxonomyCategoryManager;
    }

    public function createLicenseKey($params)
    {
        // module name is important for validation of licensekey
        $params[self::PARAM_MODULE_NAME] = $this->helper->getModuleName();
        $params[self::PARAM_SERVER_ID] = $this->getServerId();

        $params['trial'] = !empty($params['trial']);

        if ($params['trial']) {
            $response = $this->postApiRequest('createLicense', $params);
        } else {
            $response = $this->postApiRequest('createLicenseLive', $params);
        }

        $response = $this->processResponse($response);
        if (empty($response[self::RESPONSE_KEY])) {
            throw new \Exception(__("Server response is missing the license key."));
        }

        $this->helper->saveLicenseKey($response[self::RESPONSE_KEY]);
        return $response;
    }

    public function updateFeeds($messageManager = null)
    {
        $this->checkLicense();

        $response = $this->sendServerRequest('updateFeedsAndTaxonomies');
        $response = $this->processResponse($response);

        //3.check response data
        if (!isset($response[self::RESPONSE_FEED]) || !isset($response[self::RESPONSE_TAXONOMY])) {
            throw new \Exception(__("Missing feeds and taxonomy data in response"));
        }

        //4.update tables
        $links = $this->updateConfig($response[self::RESPONSE_FEED], $response[self::RESPONSE_TAXONOMY]);

        if ($messageManager) {
            $this->_addLinksToMessages($messageManager, $links);
        }
        return $links;
    }

    protected function _addLinksToMessages($manager, $links)
    {
        $manager->addSuccess(__('Following feeds have been updated:'));

        sort($links);
        $linkBatch = [];
        foreach ($links as $link) {
            $linkBatch[] = $link;
            if (count($linkBatch) >= self::LINK_BATCH_SIZE) {
                $this->_addLinksToSessionSuccess($manager, $linkBatch);
                $linkBatch = [];
            }
        }

        if (!empty($linkBatch)) {
            $this->_addLinksToSessionSuccess($manager, $linkBatch);
        }
    }

    protected function _addLinksToSessionSuccess($manager, $linkBatch)
    {
        $manager->addSuccess("᛫" . implode("&nbsp;&nbsp;&nbsp;᛫", $linkBatch));
    }

    public function getFeedsInfo()
    {
        $response = $this->sendServerRequest(self::TYPE_FEEDS_INFO);
        $response = $this->processResponse($response);

        if (isset($response[self::RESPONSE_INFO])) {
            $info = $response[self::RESPONSE_INFO];
        } else {
            $info = [];
        }

        if (isset($info[self::RESPONSE_FEEDS_UPDATE])) {
            $info[self::RESPONSE_FEEDS_UPDATE] = str_replace("||", ", ", $info[self::RESPONSE_FEEDS_UPDATE]);
        }
        if (isset($info[self::RESPONSE_FEEDS_NEW])) {
            $info[self::RESPONSE_FEEDS_NEW] = str_replace("||", ", ", $info[self::RESPONSE_FEEDS_NEW]);
        }

        return $info;
    }

    public function updateLicense()
    {
        if ($this->helper->isLicenseKeyT()) {
            $isValid = true;
        } else {
            $response = $this->sendServerRequest(self::TYPE_LICENSE);
            $response = $this->processResponse($response);

            if (isset($response[self::RESPONSE_LICENSE])) {
                $this->updateLicenseData($response[self::RESPONSE_LICENSE]);
            }

            $isValid = $this->helper->isDateValid();
        }

        return [
            'license_status' => $this->helper->getLicenseKeyStatusHtml(false),
            'valid' => $isValid
        ];
    }

    public function reloadTaxonomyCategories($code, $locale)
    {
        return $this->taxonomyCategoryManager->reloadTaxonomyCategories($code, $locale);
    }

    protected function updateLicenseData($lincenseData)
    {
        if (empty($lincenseData)) {
            return false;
        } else {
            return $this->helper->processLicenseData($lincenseData);
        }
    }
}
