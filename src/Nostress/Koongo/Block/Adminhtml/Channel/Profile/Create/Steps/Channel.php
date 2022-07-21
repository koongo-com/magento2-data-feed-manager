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
 * Channel profile wizard - Store and channel seletcion step
 *
 * @category Nostress
 * @package Nostress_Koongo
 *
 */

namespace Nostress\Koongo\Block\Adminhtml\Channel\Profile\Create\Steps;

class Channel extends \Magento\Ui\Block\Component\StepsWizard\StepAbstract
{
    /**
     * \Magento\Store\Model\System\Store
     */
    protected $_systemStore;

    /**
     * \Nostress\Koongo\Model\Channel\Feed
     */
    protected $_feedSource;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Store\Model\System\Store $storeModel
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Store\Model\System\Store $systemStore,
        \Nostress\Koongo\Model\Channel\Feed $feedSource
    ) {
        parent::__construct($context);
        $this->_systemStore = $systemStore;
        $this->_feedSource = $feedSource;
    }

    /**
     * {@inheritdoc}
     */
    public function getCaption()
    {
        return $this->isSingleStore() ? __('Channel') : __('Store & Channel');
    }

    protected function _getStores()
    {
        if ($this->getData('stores') === null) {
            $stores = $this->_systemStore->getStoreCollection();
            $this->setData('stores', $stores);
        }
        return $this->getData('stores');
    }

    public function isSingleStore()
    {
        return (count($this->_getStores()) == 1);
    }

    public function getStoreNamesWithWebsite()
    {
        $stores = $this->_getStores();
        $storeOptions = [];
        foreach ($stores as $store) {
            $id = $store->getId();
            $name = $this->_systemStore->getStoreNameWithWebsite($id);
            $name = str_replace("/", " - ", $name);
            $storeOptions[$id] = $name;
        }

        return $storeOptions;
    }

    public function getStoresEncoded()
    {
        $stores = $this->getStoreNamesWithWebsite();
        $enc = [];
        foreach ($stores as $id => $name) {
            $enc[] = [ 'value'=>$id, 'label'=>$name];
        }

        return json_encode($enc);
    }

    public function getChannelLinkOptions()
    {
        return $this->_feedSource->toOptionArray();
    }

    public function getChannelsList()
    {
        $map = [];
        $collection = $this->_feedSource->getFeeds();
        foreach ($collection as $feed) {
            $channel = $feed->getChannel();
            $map[ $feed->getLink()] = [
                'description' => $channel->getDescriptionUrl(),
                'logo' => $channel->getLogoUrl(),
                'label' => $channel->getLabel()
            ];
        }

        return $map;
    }
}
