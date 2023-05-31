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

namespace Nostress\Koongo\Model\Api\Client;

use Magento\Framework\App\CacheInterface;
use Nostress\Koongo\Helper\Version;
use Nostress\Koongo\Model\AbstractModel;
use Nostress\Koongo\Model\Channel\Feed\Manager as FeedManager;
use Nostress\Koongo\Model\Channel\Profile\Manager as ProfileManager;
use Nostress\Koongo\Model\Data\Reader;
use Nostress\Koongo\Model\Taxonomy\Setup\Manager as SetupManager;

class Simple extends AbstractModel
{
    const RESPONSE_FEED = 'feed';
    const RESPONSE_TAXONOMY = 'taxonomy';
    const RESPONSE_ERROR = 'error';
    const RESPONSE_ERRORS = 'errors';
    const RESPONSE_INFO = 'info';
    const RESPONSE_MODULE = 'module';
    const RESPONSE_LICENSE = 'license';
    const RESPONSE_VALIDITY = 'validity';
    const RESPONSE_KEY = 'key';
    const RESPONSE_COLLECTION = 'collection';
    const RESPONSE_FEEDS_NEW = 'feeds_new';
    const RESPONSE_FEEDS_UPDATE = 'feeds_update';

    const PARAM_LICENSE = 'license';
    const PARAM_SERVER = 'server';
    const PARAM_SERVER_ID = 'server_id';
    const PARAM_SIGN = 'sign';
    const PARAM_LINK = 'link';
    const PARAM_FILE_TYPE = 'file_type';
    const PARAM_PLUGINS = 'plugins';
    const PARAM_REQUEST_TYPE = 'request_type';
    const PARAM_MODULE_NAME = 'module_name';

    const TYPE_LICENSE = "license";
    const TYPE_FEEDS_INFO = "feedsInfo";

    const PARAM_API_URL_SECURE = 'https://connectorapi.koongo.com/v2/';
    const PARAM_API_URL_UNSECURE = 'https://connectorapi.koongo.com/v2/';

    // base server config url
    const PARAM_SERVER_CONFIG_JSON_URL = 'https://resources.koongo.com/ChannelCache/ServerConfig.json';

    //default service connection url
    const DEFAULT_KOONGO_SERVICE_CONNECTION_URL = 'https://my.koongo.com/kaas/index/login/platform/magento_2';
    const DEFAULT_KOONGO_SERVICE_ENDPOINT_URL = 'https://my.koongo.com/kaas/index/handshake/platform/magento_2';
    const DEFAULT_KOONGO_SERVICE_IDENTITY_LINK_URL = 'https://my.koongo.com/kaas/index/install/platform/magento_2';

    // server config
    const PARAM_COLLECTIONS_JSON_URL = 'api/collections_json_url';
    const PARAM_FEEDS_JSON_URL = 'api/country_feed_json_url';
    const PARAM_UNIVERSITY_JSON_URL = 'api/university_json_url';
    const PARAM_VERSION_PLUGIN_JSON_URL = 'api/version_plugins_json_url';
    const PARAM_TAXONOMY_SOURCE_URL = 'api/taxonomy_source_url';
    const PARAM_KOONGO_BLOG_FEED_URL = "api/koongo_blog_rss_feed_url";
    const PARAM_KOONGO_SERVICE_CONNECTION_URL = "api/koongo_service_connection_url";
    const PARAM_KOONGO_SERVICE_ENDPOINT_URL = "api/koongo_service_endpoint_url";
    const PARAM_KOONGO_SERVICE_IDENTITY_LINK_URL = "api/koongo_service_identity_link_url";

    const CACHE_KEY_AVAILABLE_COLLECTIONS = 'koongo_available_collections';
    const CACHE_KEY_AVAILABLE_FEEDS = 'koongo_available_feeds';
    //const CACHE_KEY_CONNECTORS_INFO = 'koongo_connectors_info';
    const CACHE_KEY_VERSION_PLUGINS_INFO = 'koongo_version_plugins_info';

    const LICENSE_NOT_VALID = "License invalid";
    protected CacheInterface $cache;
    protected Reader $reader;

    protected FeedManager $feedManager;
    protected ProfileManager $profileManager;
    protected SetupManager $taxonomySetupManager;

    public function __construct(
        Version         $helper,
        CacheInterface $cache,
        Reader $reader,
        ProfileManager  $profileManager,
        SetupManager    $taxonomySetupManager,
        FeedManager $feedManager
    )
    {
        $this->helper = $helper;
        $this->cache = $cache;
        $this->reader = $reader;
    }

    /**
     * @return Version
     */
    public function getHelper()
    {
        return $this->helper;
    }

    public function getAvailableCollections()
    {
        // cached
        return $this->getInfoData(self::PARAM_COLLECTIONS_JSON_URL, self::CACHE_KEY_AVAILABLE_COLLECTIONS);
    }

    public function getAvailableFeeds()
    {
        // cached
        return $this->getInfoData(self::PARAM_FEEDS_JSON_URL, self::CACHE_KEY_AVAILABLE_FEEDS);
    }

    public function getUniversityInfo()
    {
        // not cached
        return $this->_getInfoData(self::PARAM_UNIVERSITY_JSON_URL, true);
    }

    public function getAvailableCollectionsAsOptionArray($isMultiselect = false)
    {
        $collections = $this->getAvailableCollections();
        $result = [];

        if (empty($collections) || !is_array($collections)) {
            return $result;
        }
        foreach ($collections as $item) {
            $result[$item["address"]] = ["label" => $item["address"],"value" => $item["code"]];
        }
        sort($result);

        if (!$isMultiselect) {
            array_unshift($result, ["label" => __("-- Please Select --"), "value" => ""]);
        }
        return $result;
    }

    public function getAvailableFeedsAsOptionArray($collection = null)
    {
        $feeds = $this->getAvailableFeeds();
        $result = [];

        if (empty($feeds) || !is_array($feeds)) {
            return $result;
        }

        foreach ($feeds as $country => $cFeeds) {
            $result[ $country] = [
                ["label" => __("-- Please Select --"), "value" => ""]
            ];
            foreach ($cFeeds as $cfeed) {
                $result[ $country][] = [
                   'label' => $cfeed['link'],
                   'value' => $cfeed['link']
                ];
            }
        }

        if ($collection) {
            return $result[$collection];
        } else {
            return $result;
        }
    }

    public function getAvailableFeedsJson()
    {
        return json_encode($this->getAvailableFeedsAsOptionArray());
    }

    /** API functions */

    public function updateServerConfig($enableErrorIncrement = true)
    {
        try {
            // error increment reach max value -> debug mode is enabled
            if ($enableErrorIncrement && !$this->helper->isServerConfigUpdatable()) {
                $this->helper->incrementServerConfigError();
                return __('Server config update is not working properly! Try to update it by button above or contact support.');
            }

            $data = $this->reader->getRemoteFileContent(self::PARAM_SERVER_CONFIG_JSON_URL);
            $config = $this->decodeResponse($data);

            if (is_array($config) && count($config)) {
                $this->helper->saveModuleConfigs($config);
            } else {
                throw new \Exception('Wrong server config data!');
            }
            $this->helper->clearServerConfigError();

            return true;
        } catch (\Exception $e) {
            if ($enableErrorIncrement) {
                $this->helper->incrementServerConfigError();
            } else {
                $this->helper->clearServerConfigError();
            }

            $this->log($e->getMessage());
            return $e->getMessage();
        }
    }

    protected function sendServerRequest($resource, $licensed = true)
    {
        $server = $this->getServerName();
        $license =  $this->getLicenseKey();
        if (empty($license) && !$licensed) {
            $license = 'temp';
        }

        $sign = $this->getSign($server, $license);
        $params = [];
        $params[self::PARAM_SIGN]= $sign;
        $params[self::PARAM_LICENSE] = $license;
        $params[self::PARAM_SERVER] = $server;
        $params[self::PARAM_MODULE_NAME] = $this->helper->getModuleName();

        return $this->postApiRequest($resource, $params);
    }

    protected function postApiRequest($apiFunction, $params = [])
    {
        try {
            $response = $this->postJsonUrlRequest($this->getKoongoApiUrl() . $apiFunction, $params);
        } catch (\Exception $e) {
            $response = $this->postJsonUrlRequest($this->getKoongoApiUrl(false) . $apiFunction, $params);
        }
        return $response;
    }

    protected function processResponse($response)
    {
        $response = $this->decodeResponse($response);
        $this->checkResponseContent($response);
        return $response;
    }

    protected function updateConfig($feedConfig, $taxonomyConfig)
    {
        if (empty($feedConfig)) {
            throw new \Exception($this->__("Feeds configuration empty"));
        }

        // call model and update tables
        $this->feedManager->updateFeeds($feedConfig);
        $this->taxonomySetupManager->updateTaxonomySetup($taxonomyConfig);

        $this->profileManager->updateProfilesFeedConfig();

        return $this->getLinks($feedConfig);
    }

    protected function updateInfo($info)
    {
        if (empty($info)) {
            return;
        }

        $moduleInfo = [];
        if (isset($info[self::RESPONSE_MODULE])) {
            $moduleInfo = $info[self::RESPONSE_MODULE];
        }

        //Mage::getSingleton('nscexport/plugin')->updatePluginInfo($pluginInfo);
        $this->helper->processModuleInfo($moduleInfo);
        return;
    }

    protected function checkResponseContent($response)
    {
        $error = "";
        if (!empty($response[self::RESPONSE_ERROR])) {
            throw new \Exception($response[self::RESPONSE_ERROR]);
        } elseif (!empty($response[self::RESPONSE_ERRORS])) {
            throw new \Exception(implode(", ", $response[self::RESPONSE_ERRORS]));
        }
    }

    protected $_xdfsdfskltyllk = "du45itg6df4kguyk";

    protected function checkResponseEmpty($response, $error)
    {
        if (!isset($response) || empty($response)) {
            throw new \Exception(__("Invalid or empty server response.") . __("Curl error: ") . $error);
        }
    }

    protected function getLinks($feedConfig)
    {
        $links = [];
        foreach ($feedConfig as $config) {
            if (isset($config[self::PARAM_LINK]) && !in_array($config[self::PARAM_LINK], $links)) {
                $links[] = $config[self::PARAM_LINK];
            }
        }
        return $links;
    }

    protected $_xdfsdfskmfowlt54b4 = "kd6fg54";

    protected function decodeResponse($response)
    {
        $response = json_decode($response, true);
        return $response;
    }

    protected function getServerName()
    {
        return $this->helper->getServerName();
    }

    protected function getLicenseKey()
    {
        return $this->helper->getLicenseKey();
    }

    protected function getServerId()
    {
        return $this->helper->getServerId();
    }

    protected function checkLicense()
    {
        if (!$this->helper->isLicenseValid()) {
            throw new \Exception(__('Your License is not valid'));
        }
    }

    protected function getSign($server, $license)
    {
        return hash("md5", (sha1($this->_xdfsdfskltyllk . $server . $license . "\$this->_xdfsdfskmfowlt54b4")));
    }

    protected function getKoongoApiUrl($secured = true)
    {
        if ($secured) {
            return self::PARAM_API_URL_SECURE;
        } else {
            return self::PARAM_API_URL_UNSECURE;
        }
    }

    protected function postUrlRequest($request_url, $post_params)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $request_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_params);
        $result = curl_exec($ch);
        $this->checkResponseEmpty($result, curl_error($ch));
        curl_close($ch);

        return $result;
    }

    protected function postJsonUrlRequest($request_url, $post_params)
    {
        $post_params = json_encode($post_params);
        return $this->postUrlRequest($request_url, $post_params);
    }

    protected function getInfoData($type, $cacheKey)
    {
        $json = $this->cache->load($cacheKey);
        if (empty($json)) {
            $json = $this->_getInfoData($type);
            if (!empty($json)) {
                $this->cache->save($json, $cacheKey);
            }
        }
        $data = $this->decodeResponse($json);
        return $data;
    }

    protected function _getInfoData($type, $decode = false)
    {
        try {
            $url = $this->helper->getModuleConfig($type);
            $data = $this->reader->getRemoteFileContent($url);

            if ($decode) {
                $data = $this->decodeResponse($data);
            }
        } catch (\Exception $e) {
            $this->log($e->getMessage());
            $data = [];
        }
        return $data;
    }
}
