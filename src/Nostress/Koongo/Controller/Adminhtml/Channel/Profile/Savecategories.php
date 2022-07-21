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
 * Save channel profile attribte settings action
 *
 * @category Nostress
 * @package Nostress_Koongo
 *
 */

namespace Nostress\Koongo\Controller\Adminhtml\Channel\Profile;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Savecategories extends SaveAbstract
{

    /**
     * @var \Nostress\Koongo\Model\Taxonomy\Category\Mapping
     */
    protected $mappingModel;

    /**
     * @var \Nostress\Koongo\Model\Channel\Profile\Categories
     */
    protected $_categoriesModel = null;

    /**
     * @var \Nostress\Koongo\Model\Cache\Manager
     */
    protected $cacheManager;

    /**
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param \Nostress\Koongo\Helper\Version $helper
     * @param \Nostress\Koongo\Model\Channel\Profile\Manager $manager
     * @param \Nostress\Koongo\Model\Channel\ProfileFactory $profileFactory
     * @param \Nostress\Koongo\Model\Translation $translation
     * @param \Nostress\Koongo\Model\Taxonomy\Category\Mapping $mappingModel
     * @param \Nostress\Koongo\Model\Channel\Profile\Categories $categoriesModel
     * @param \Nostress\Koongo\Model\Cache\Manager $cacheManager
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Nostress\Koongo\Helper\Version $helper,
        \Nostress\Koongo\Model\Channel\Profile\Manager $manager,
        \Nostress\Koongo\Model\Channel\ProfileFactory $profileFactory,
        \Nostress\Koongo\Model\Translation $translation,
        \Nostress\Koongo\Model\Taxonomy\Category\Mapping $mappingModel,
        \Nostress\Koongo\Model\Channel\Profile\Categories $categoriesModel,
        \Nostress\Koongo\Model\Cache\Manager $cacheManager
    ) {
        $this->mappingModel = $mappingModel;
        $this->_categoriesModel = $categoriesModel;
        $this->cacheManager = $cacheManager;
        parent::__construct($context, $resultPageFactory, $helper, $manager, $profileFactory, $translation);
    }

    /**
     * Save Channel Profile
     *
     * @return \Magento\Backend\Model\View\Result\Page|\Magento\Backend\Model\View\Result\Redirect
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        $data = $this->preprocessData($data);

        $message = "";
        $error = "";
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data) {
            /** @var \Ashsmith\Blog\Model\Post $model */
            $model = $this->profileFactory->create();

            //Load profile by id
            $paramId = $this->getRequest()->getParam('entity_id');
            if ($paramId) {
                $model->load($paramId);
            }
            $id = $model->getId();
            if (empty($id)) {
                $message = __('Profile with id #%1 doesn\'t exist.', $paramId);
                if ($this->_isAjax()) {
                    $this->_sendAjaxError($message);
                } else {
                    $this->messageManager->addError($message);
                    return $resultRedirect->setPath('*/*/');
                }
            }

            try {
                //Save taxonomy config
                $data = $this->saveCategoryMappingRules($data, $model);

                //Save profile
                $model->updateData($data);
                $this->_eventManager->dispatch(
                    'koongo_channel_profile_prepare_save',
                    ['post' => $model, 'request' => $this->getRequest()]
                );
                $model->save();

                $message = __('Profile #%1 categories has been successfully saved.', $paramId);
                if (!$this->_isAjax()) {
                    $this->messageManager->addSuccess($message);
                }
                $error = false;
                $this->_session->setFormData(false);

                // reload profile cache
                $this->cacheManager->reloadProfileChannelCategoriesCache($model);

                if ($this->getRequest()->getParam('update_taxonomy')) {
                    $this->_categoriesModel->initProfile($model);
                    $messages = $this->_categoriesModel->reloadTaxonomyCategories($data['general']['taxonomy_locale']);
                    foreach ($messages[true] as $message) {
                        $this->messageManager->addSuccess($message);
                    }
                    foreach ($messages[false] as $message) {
                        $this->messageManager->addError($message);
                    }
                }
            } catch (\Exception $e) {
                $message = $e->getMessage();
                $error = true;
                if (!$this->_isAjax()) {
                    $this->messageManager->addError($message);
                }
            }

            //Redirect
            $backParam = $this->getRequest()->getParam('back');
            if ($backParam) {
                $pageCode = '';
                switch ($backParam) {
                    case 'edit':
                        $pageCode = 'editcategories';
                        break;
                    case 'execute':
                        $pageCode = 'execute';
                        break;
                }

                if ($this->_isAjax()) {
                    $this->_sendAjaxResponse($message, $error);
                } else {
                    return $resultRedirect->setPath('*/*/' . $pageCode, ['entity_id' => $model->getId(), '_current' => true]);
                }
            }
        }

        if ($this->_isAjax()) {
            $this->_sendAjaxResponse($message, $error);
        } else {
            return $resultRedirect->setPath('*/*/');
        }
    }

    protected function saveCategoryMappingRules($data, $profile)
    {
        $rules = [];
        if (isset($data['rules'])) {
            $rules = $data['rules'];
        }

        unset($data['rules']);

        $locale = $data['current_channel_categories_locale'];
        unset($data['current_channel_categories_locale']);

        $storeId = $profile->getStoreId();
        $taxonomyCode = $profile->getFeed()->getTaxonomyCode();

        //Insert rules into mapping table
        $mappingItem = $this->mappingModel->getMapping($taxonomyCode, $locale, $storeId);

        if (!isset($mappingItem)) {
            $mappingItem = $this->mappingModel;
            $mappingItem->setStoreId($storeId);
            $mappingItem->setTaxonomyCode($taxonomyCode);
            $mappingItem->setLocale($locale);
        }

        $mappingItem->setConfig(json_encode(["rules" => $rules]));
        $mappingItem->save();
        return $data;
    }

    /**
     * Preprocess posted data
     * @param unknown_type $data
     * @return unknown
     */
    protected function preprocessData($data)
    {
        $data = $this->removeUnwantedInputsFromData($data);
        $data = $this->preprocessMultiselectValues($data);
        return $data;
    }

    /**
     * Preprocess multiselect values
     * @param unknown_type $data
     * @return unknown
     */
    protected function preprocessMultiselectValues($data)
    {
        return $data;
    }

    /**
     * Remove unwanted content from posted data. The content is added by hidden inputs.
     * @param unknown_type $data
     * @return unknown
     */
    protected function removeUnwantedInputsFromData($data)
    {
        $unwantedItemIndexes = ["form_key","option","dropdown_attribute_validation"];

        foreach ($unwantedItemIndexes as $index) {
            if (isset($data[$index])) {
                unset($data[$index]);
            }
        }
        return $data;
    }
}
