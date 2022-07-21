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
 * Update feed templates controller
 *
 * @category Nostress
 * @package Nostress_Koongo
 *
 */

namespace Nostress\Koongo\Controller\Adminhtml\Channel\Profile;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;

class Feedsinfo extends \Magento\Backend\App\Action
{
    /**
     * Client for comunication with Koongo server
     * @var \Nostress\Koongo\Model\Api\Client
     */
    protected $client;

    /**
     *
     * @var \Nostress\Koongo\Helper\Version
     */
    protected $version;

    public function __construct(
        Context $context,
        \Nostress\Koongo\Model\Api\Client $client,
        \Nostress\Koongo\Helper\Version $version
    ) {
        $this->client = $client;
        $this->version = $version;
        parent::__construct($context);
    }

    /**
     * Update feeds action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $info = $this->client->getFeedsInfo();

        $this->_renderFeedsList($info['feeds_new'], __('<strong>New feeds and channels</strong>'));
        $this->_renderFeedsList($info['feeds_update'], __('<strong>Updated feeds and channels</strong>'));

        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath('*/*/');
    }

    protected function _renderFeedsList($list, $label)
    {
        if (!empty($list)) {
            $this->messageManager->addNotice(__($label));
            foreach ($list as $item) {
                $channelLink = $this->version->getKoongoWebsiteUrl() . str_replace("_", "-", $item['channel_code']) . ".html";
                $this->messageManager->addNotice(" - " . $item['link'] . " " . __("(<a href='%1' target='_blank'>more information</a>)", $channelLink));
            }
        }
    }
}
