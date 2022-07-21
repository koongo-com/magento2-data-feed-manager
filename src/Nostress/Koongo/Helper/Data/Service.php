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

namespace Nostress\Koongo\Helper\Data;

use Magento\Authorization\Model\UserContextInterface;

/**
 * Koongo connector service Helper.
 *
 * @category Nostress
 * @package Nostress_Koongo
 *
 */
class Service extends \Nostress\Koongo\Helper\Data
{
    /**
     * @var \Magento\Backend\Helper\Data
     */
    protected $_backendHelper;

    /**
     * Url builder
     *
     * @var \Magento\Backend\Model\Url
     */
    protected $_urlBuilder;

    /**
     * Integration factory
     *
     * @var \Magento\Integration\Model\IntegrationFactory
     */
    protected $_integrationFactory;

    /**
     * OAuth service
     *
     * @var \Magento\Integration\Model\OauthService
     */
    protected $_oauthService;

    /**
     * AuthorizationService
     *
     * @var \Magento\Integration\Model\AuthorizationService
     */
    protected $_authorizationService;

    /** @var \Magento\Framework\Acl\AclResource\ProviderInterface */
    protected $_resourceProvider;

    /**
     * \Magento\Integration\Model\Oauth\TokenFactory
     *
     * @var \Magento\Integration\Model\Oauth\TokenFactory
     */
    protected $_tokenFactory;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Catalog\Model\Product\Type $productType
     * @param \Nostress\Koongo\Model\Logger $logger,
     * @param \Magento\Directory\Model\CurrencyFactory $currencyFactory
     * @param \Magento\Config\Model\ResourceModel\Config $resourceConfig
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\Config\ValueFactory $configDataFactory
     * @param \Magento\Framework\Filesystem
     * @param \Magento\Framework\App\Config\ReinitableConfigInterface
     * @param \Magento\Framework\App\Cache\TypeListInterface
     * @param \Nostress\Koongo\Model\Config\Source\Datetimeformat $datetimeformat,
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Framework\App\CacheInterface $cache
     * @param \Magento\Framework\Module\ModuleList $moduleList
     * @param \Magento\Framework\App\ProductMetadataInterface $productMetadataInterface
     * @param \Magento\Framework\App\State $appState
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Backend\Model\Url $urlBuilder,
     * @param \Magento\Integration\Model\IntegrationFactory $integrationFactory
     * @param \Magento\Integration\Model\OauthService $oauthService
     * @param \Magento\Integration\Model\AuthorizationService $authorizationService
     * @param \Magento\Framework\Acl\AclResource\ProviderInterface $resourceProvider,
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Catalog\Model\Product\Type $productType,
        \Nostress\Koongo\Model\Logger $logger,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        \Magento\Config\Model\ResourceModel\Config $resourceConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ValueFactory $configDataFactory,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\App\Config\ReinitableConfigInterface $coreConfig,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Nostress\Koongo\Model\Config\Source\Datetimeformat $datetimeformat,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Framework\App\CacheInterface $cache,
        \Magento\Framework\Module\ModuleList $moduleList,
        \Magento\Framework\App\ProductMetadataInterface $productMetadataInterface,
        \Magento\Framework\App\State $appState,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Backend\Model\Url $urlBuilder,
        \Magento\Integration\Model\IntegrationFactory $integrationFactory,
        \Magento\Integration\Model\OauthService $oauthService,
        \Magento\Integration\Model\AuthorizationService $authorizationService,
        \Magento\Framework\Acl\AclResource\ProviderInterface $resourceProvider,
        \Magento\Integration\Model\Oauth\TokenFactory $tokenFactory
    ) {
        $this->_backendHelper = $backendHelper;
        $this->_urlBuilder = $urlBuilder;
        $this->_integrationFactory = $integrationFactory;
        $this->_oauthService = $oauthService;
        $this->_authorizationService = $authorizationService;
        $this->_resourceProvider = $resourceProvider;
        $this->_tokenFactory = $tokenFactory;
        parent::__construct(
            $context,
            $productType,
            $logger,
            $currencyFactory,
            $resourceConfig,
            $storeManager,
            $configDataFactory,
            $filesystem,
            $coreConfig,
            $cacheTypeList,
            $datetimeformat,
            $eavConfig,
            $cache,
            $moduleList,
            $productMetadataInterface,
            $appState
        );
    }

    /**
     * Get url for redirect to Koongo service
     *
     * @param [type] $integration
     * @return void
     */
    public function getServiceRedirectUrl($integration)
    {
        $isMagentoVersion244OrNewer = $this->isMagentoVersion244OrNewer();
        $redirectUrl = $this->getKoongoServiceConnectionUrl();
        $backendUrl = $this->_urlBuilder->getBaseUrl(\Magento\Backend\Model\Url::URL_TYPE_WEB);
        $backendUrlEncoded = base64_encode($backendUrl);
        $areaFrontName = $this->_backendHelper->getAreaFrontName();

        if (!isset($integration['consumer_id']) || $integration['consumer_id'] == "") {
            throw new \Exception("Consumer ID is missing in integration.");
        }

        $consumer = $this->_oauthService->loadConsumer($integration['consumer_id']);
        $consumerKey = $consumer->getKey();
        $resultRequestUrl = $redirectUrl . "?";
        $resultRequestUrl .= "domain=" . $backendUrlEncoded;
        $resultRequestUrl .= "&adminpath=" . $areaFrontName;
        $resultRequestUrl .= "&consumer_key=" . $consumerKey;
        if (!$isMagentoVersion244OrNewer) {
            $consumerSecret = $consumer->getSecret();

            $token = $this->_tokenFactory->create()->loadByConsumerIdAndUserType($integration['consumer_id'], UserContextInterface::USER_TYPE_INTEGRATION);
            $accessToken = $token->getToken();
            $accessTokenSecret = $token->getSecret();

            $resultRequestUrl .= "&consumer_secret=" . $consumerSecret;
            $resultRequestUrl .= "&access_token=" . $accessToken;
            $resultRequestUrl .= "&access_token_secret=" . $accessTokenSecret;
        } else {
            $resultRequestUrl.="&handshake=1";
        }
        return $resultRequestUrl;
    }

    /**
     * Prepare system integration for Koongo connection
     *
     * @return void
     */
    public function prepareIntegration()
    {
        //Set your Data
        $integrationName = $this->getKoongoIntegrationName();

        $data = [
            'name' => $integrationName,
            'email' => '',
            'endpoint' => $this->getKoongoServiceEndpointUrl(),
            'identity_link_url' => $this->getKoongoServiceIdentityLinkUrl(),
            'status' => '0',
            'setup_type' => '0'
        ];

        $integration = $this->_integrationFactory->create()->load($integrationName, 'name');
        if ($integration->getId()) {
            throw new \Exception(__("Integration already exists with id %1", $integration->getId()));
        }

        //New integration creation
        $integration = $this->_integrationFactory->create();
        $integration->setData($data);
        $integration->save();

        //New consumer creation
        $consumerName = 'KoongoIntegration' . $integration->getId();
        $consumer = $this->_oauthService->createConsumer(['name' => $consumerName]);
        $integration->setConsumerId($consumer->getId());
        $integration->save();

        //Add permissions
        $scopes = explode(",", \Nostress\Koongo\Helper\Data::KOONGO_SYSTEM_INTEGRATION_PERMISSON_SCOPES);
        $scopes = $this->_getAllSubResourceIds($scopes);
        $this->_authorizationService->grantPermissions($integration->getId(), $scopes);

        //Activate only magento version 2.4.3 and older
        if (!$this->isMagentoVersion244OrNewer()) {
            $token = $this->_tokenFactory->create()->createVerifierToken($consumer->getId());
            $token->setType('access');
            $token->save();
        }
    }

    protected function _getAllSubResourceIds($allowedResourceArray)
    {
        $resources = $this->_resourceProvider->getAclResources();

        $allResourcesIds = [];

        $mainResource = [];
        foreach ($resources as $resourceItem) {
            if ($resourceItem["id"] == "Magento_Backend::admin") {
                $mainResource = $resourceItem['children'];
            }
        }

        foreach ($mainResource as $resourceItem) {
            if (in_array($resourceItem["id"], $allowedResourceArray)) {
                $allResourcesIds[] = $resourceItem["id"];
                $subResourceIds = $this->_getAllResourceIds($resourceItem['children']);
                $allResourcesIds = array_merge($allResourcesIds, $subResourceIds);
            }
        }
        return $allResourcesIds;
    }

    /**
     * Return an array of all resource Ids.
     *
     * @param array $resources
     * @return string[]
     */
    protected function _getAllResourceIds(array $resources)
    {
        $resourceIds = [];
        foreach ($resources as $resource) {
            $resourceIds[] = $resource['id'];
            if (isset($resource['children'])) {
                $resourceIds = array_merge($resourceIds, $this->_getAllResourceIds($resource['children']));
            }
        }
        return $resourceIds;
    }
}
