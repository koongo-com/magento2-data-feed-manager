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
 * Export profiles grid controller
 *
 * @category Nostress
 * @package Nostress_Koongo
 *
 */

namespace Nostress\Koongo\Controller\Adminhtml\Channel\Profile;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Nostress\Koongo\Helper\Version;

class Index extends \Nostress\Koongo\Controller\Adminhtml\Channel\Profile
{
    const BLOG_NEWS_LIMIT = 3;

    /**
     * @var \Nostress\Koongo\Model\Channel\Feed
     */
    protected $feedSingelton;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Nostress\Koongo\Helper\Version $helper,
        \Nostress\Koongo\Model\Channel\Profile\Manager $manager,
        \Nostress\Koongo\Model\Channel\ProfileFactory $profileFactory,
        \Nostress\Koongo\Model\Translation $translation,
        \Nostress\Koongo\Model\Channel\Feed $feedSingelton
    ) {
        $this->feedSingelton = $feedSingelton;
        parent::__construct($context, $resultPageFactory, $helper, $manager, $profileFactory, $translation);
    }

    /**
     * Index action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $resultRedirect = $this->_checkAndUpdateServerConfig();
        if ($resultRedirect) {
            return $resultRedirect;
        }

        $this->_checkLicense();
        $this->checkFlatEnabled();
        $this->checkDebugMode();

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Nostress_Koongo::koongo');
        $resultPage->addBreadcrumb(__('Koongo'), __('Koongo'));
        $resultPage->addBreadcrumb(__('Manage Export Profiles'), __('Manage Export Profiles'));
        $resultPage->getConfig()->getTitle()->prepend(__('Koongo Connector - Export Profiles'));

        return $resultPage;
    }

    /**
     * Check and update server config
     *
     * @return false|\Magento\Framework\Controller\Result\Redirect
     */
    protected function _checkAndUpdateServerConfig()
    {
        // if empty configuration - redirect to update server config
        if ($this->version->isServerConfigUpdateNeeded()) {
            $resultRedirect = $this->resultRedirectFactory->create();
            $redirectPageKey = 'channel_profile';
            return $resultRedirect->setPath('koongo/license/updateserverconfig', [ 'eei'=>true, 'redirect_page_key' => $redirectPageKey]);
        }
        return false;
    }

    protected function _checkLicense()
    {
        //License empty
        if ($this->version->isLicenseEmpty()) {
            $this->messageManager->addNotice($this->version->getActivationMessage());
        } elseif (!$this->version->isLicenseKeyValid()) { //License not valid
            $this->messageManager->addNotice($this->version->getTrialLicenseKeyInvalidMessage());
        } else { //License valid
            $feedsLoaded = $this->feedSingelton->feedsLoaded();
            if (!$feedsLoaded) { //Feeds not loaded
                $updateFeedsLink = $this->_url->getUrl("*/*/updatefeeds");
                $helpUrl = $helpUrl = $this->version->getModuleConfig(Version::HELP_FEED_COLLECTIONS);
                $this->messageManager->addNotice(
                    __('Feeds & Taxonomies have not been downloaded yet. Click <a href="%1">Get Feeds Templates</a> button.', $updateFeedsLink) .
                    " " . __('More information you may find in <a href="%1" target="blank" >documentation</a>.', $helpUrl)
                );
            }

            if ($this->version->isLicenseKeyT()) { //License is trial
                $newLicenseUrl = $this->version->getNewLicenseUrl();

                $this->messageManager->addNotice(__('You are using the 30 days Trial version of Koongo Connector'));
                if ($this->version->isDateValid()) { //Trial period ok
                    $this->messageManager->addNotice(__('Your Trial period expires on %1 and we encourage you to buy <a href="%2" target="_blank" >Full version</a>.', $this->version->gLD(), $newLicenseUrl));
                } else { //Trial period expired
                    $this->messageManager->addNotice($this->version->getTrialLicenseInvalidMessage());
                }
            } else { //License is live
                if (!$this->version->isDateValid()) { //and is expired
                    $feedsinfoUrl = $this->_url->getUrl("*/*/feedsinfo");

                    $message = $this->version->getLicenseInvalidMessage();
                    $message .= " " . __("<a href='%1'>Which Feeds I get if I extend my Support & Updates period?</a>", $feedsinfoUrl);
                    $this->messageManager->addError($message);
                }
            }

            $showBlogList = $this->version->getModuleConfig(Version::PARAM_SHOW_BLOG_NEWS);
            if ($showBlogList) {
                $this->renderBlogList();
            }
        }
    }

    protected function checkFlatEnabled()
    {
        $productFlatEnabled = $this->version->getStoreConfig(null, \Magento\Config\Model\Config\Backend\Admin\Custom::XML_PATH_CATALOG_FRONTEND_FLAT_CATALOG_PRODUCT);
        $categoryFlatEnabled = $this->version->getStoreConfig(null, \Magento\Config\Model\Config\Backend\Admin\Custom::XML_PATH_CATALOG_FRONTEND_FLAT_CATALOG_CATEGORY);

        if ($productFlatEnabled == 0 || $categoryFlatEnabled == 0) {
            $actionLink = $this->_url->getUrl("*/*/enableflat");
            $helpLink = $this->version->getModuleConfig(\Nostress\Koongo\Helper\Data::HELP_FLAT_CATALOG);
            $this->messageManager->addNotice(__("Flat Catalog Category and Flat Catalog Product usage is required. <a href=\"%1\">Click here to enable Flat Catalog</a>.", $actionLink));
            $this->messageManager->addNotice(__("More information you may find in <a target=\"_blank\" href=\"%1\">Koongo Docs</a>.", $helpLink));
        }
    }

    protected function checkDebugMode()
    {
        //Mage::helper("adminhtml")->getUrl('koongo/check') pridat koongo check
        $debugEnabled = $this->version->isDebugMode();

        if ($debugEnabled == 1) {
            $actionLink = $this->_url->getUrl("*/*/disabledebug");
            $this->messageManager->addNotice(__("Debug mode is On. <a href=\"%1\">Click here to disable Debug Mode</a>.", $actionLink));
        }
    }

    protected function renderBlogList()
    {
        $feed = $this->version->getBlogNews();
        if (!empty($feed)) {
            $this->messageManager->addNotice("<strong>" . __("Latest news from Koongo") . "</strong>");
            $counter = 0;
            foreach ($feed as $item) {
                if ($counter >= self::BLOG_NEWS_LIMIT) {
                    break;
                }
                $date = date('F d, Y', strtotime($item["date"]));

                $this->messageManager->addNotice(" * <a href='" . $item["link"] . "' target='_blank' title='" . __("Read more.") . "'>" . $item["title"] . "</a> (" . $date . ") - " . $item["desc"]);
                $counter++;
            }
            $hideBlogUrl  = $this->_url->getUrl("*/*/updateconfig", [ 'label'=>base64_encode(Version::PARAM_SHOW_BLOG_NEWS), 'value'=>0]);
            $this->messageManager->addNotice(__('<a href="%1" title="Hide blog news.">Hide blog news block.</a>', $hideBlogUrl));
        }
    }
}
