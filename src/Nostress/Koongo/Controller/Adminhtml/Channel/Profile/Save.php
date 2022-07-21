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

//use Magento\Framework\App\ObjectManager;
//use Magento\Framework\Serialize\Serializer\FormData; //Cannot use FormData available from Magento 2.2.7

class Save extends SaveAbstract
{
    /**
     * @var \Nostress\Koongo\Model\Rule
     */
    protected $rule;

    /**
     * Export profile cron execution
     * \Nostress\Koongo\Model\Cron $cron
     */
    protected $cron;

    /**
     * @var FormData|null
     */
    //private $formDataSerializer; //Cannot use FormData available from Magento 2.2.7

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param FormData|null $formDataSerializer
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Nostress\Koongo\Helper\Version $helper,
        \Nostress\Koongo\Model\Channel\Profile\Manager $manager,
        \Nostress\Koongo\Model\Channel\ProfileFactory $profileFactory,
        \Nostress\Koongo\Model\Translation $translation,
        \Nostress\Koongo\Model\Rule $rule,
        \Nostress\Koongo\Model\Cron $cron
        //FormData $formDataSerializer = null //Cannot use FormData available from Magento 2.2.7
    ) {
        $this->rule = $rule;
        $this->cron = $cron;
        //$this->formDataSerializer = $formDataSerializer ?: ObjectManager::getInstance()->get(FormData::class);
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
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        try {
            $data = $this->getRequest()->getPostValue();
            $data = $this->preprocessData($data);
        } catch (\Exception $e) {
            $message = __("Data preprocessing failed.") . " " . $e->getMessage();
            $this->messageManager->addException($e, __('Something went wrong while saving the post.') . " " . $message);
            return $resultRedirect->setPath('*/*/', ['entity_id' => $this->getRequest()->getParam('entity_id'), '_current' => true], ['error' => true]);
        }

        $paramId = $this->getRequest()->getParam('entity_id');
        $profileId = $this->getRequest()->getParam('koongo_profile_id');
        if (!empty($profileId)) {
            $paramId = $profileId;
            $data['entity_id'] = $profileId;
        }

        if ($data) {
            /** @var \Ashsmith\Blog\Model\Post $model */
            $model = $this->profileFactory->create();

            if ($paramId) {
                $model->load($paramId);
            }

            $id = $model->getId();
            if (empty($id)) {
                $this->messageManager->addError(__('Profile with id #%1 doesn\'t exist.', $paramId));
                return $resultRedirect->setPath('*/*/');
            }
            $model->updateData($data);
            $this->_eventManager->dispatch(
                'koongo_channel_profile_prepare_save',
                ['post' => $model, 'request' => $this->getRequest()]
            );

            //update cron schedule settings
            if (isset($data['cron']['rules'])) {
                $this->cron->applyRules($id, $data['cron']['rules']);
            }

            try {
                $model->save();
                $this->messageManager->addSuccess(__('Profile #%1 has been successfully saved.', $paramId));
                $this->_session->setFormData(false);

                $backParam = $this->getRequest()->getParam('back');
                if ($backParam) {
                    $pageCode = '';
                    $pagePath = '*/*/';
                    switch ($backParam) {
                        case 'edit':
                            $pageCode = 'editgeneral';
                            break;

                        case 'upload':
                            $pagePath = '*/channel_profile_ftp/';
                            $pageCode = 'upload';
                            break;

                        case 'execute':
                            $pageCode = 'execute';
                            break;
                        case 'filter':
                            $pageCode = 'editfilter';
                            break;
                        case 'cron':
                            $pageCode = 'editcron';
                            break;
                        case 'duplicate':
                            $newProfile = $this->manager->duplicateProfile($model->getId());
                            $this->cron->applyRules($newProfile->getId(), $newProfile->getConfigItem('cron', true, 'rules'));
                            $this->messageManager->addSuccess(__('Profile #%1 has been successfully duplicated.', $paramId));
                            $this->messageManager->addSuccess(__('Profile #%1 has been successfully created.', $newProfile->getId()));
                            break;
                    }
                    return $resultRedirect->setPath($pagePath . $pageCode, ['entity_id' => $model->getId(), '_current' => true]);
                }
                return $resultRedirect->setPath('*/*/');
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('Something went wrong while saving the post.'));
            }

            $this->_session->setFormData(false);
            return $resultRedirect->setPath('*/*/editgeneral', ['entity_id' => $paramId]);
        }
        return $resultRedirect->setPath('*/*/');
    }

    /**
     * Preprocess posted data
     * @param unknown_type $data
     * @return unknown
     */
    protected function preprocessData($data)
    {
        unset($data['koongo_profile_id']);
        $data = $this->_preproecssSerializedOptions($data);
        $data = $this->_removeUnwantedInputsFromData($data);
        $data = $this->_preprocessFilterRules($data);
        $data = $this->_preprocessMultiselectValues($data);
        return $data;
    }

    /**
     * Unserialize serialized options if they are availale (from Magento 2.2.7)
     *
     * @param array $data
     * @return array
     */
    protected function _preproecssSerializedOptions($data)
    {
        if (!isset($data['serialized_options'])) {
            return $data;
        }

        $serializedOptions = $data['serialized_options'];
        $optionData = null;
        if (isset($serializedOptions)) {
            $optionData = $this->_unserializeFormData($serializedOptions);
        }
        //$optionData = $this->formDataSerializer->unserialize($serializedOptions); //Cannot use FormData available from Magento 2.2.7

        if ($optionData) {
            $data = array_replace_recursive($data, $optionData);
        }

        unset($data['serialized_options']);
        return $data;
    }

    /**
     * Preprocess filter rules
     * @param unknown_type $data
     * @return unknown
     */
    protected function _preprocessFilterRules($data)
    {
        if (!isset($data['rule'])) {
            return $data;
        }

        $data['filter']['conditions'] = $this->rule->parseConditionsPost($data['rule']);
        unset($data['rule']);
        return $data;
    }

    /**
     * Preprocess multiselect values
     * @param unknown_type $data
     * @return unknown
     */
    protected function _preprocessMultiselectValues($data)
    {
        if (!isset($data['filter'])) {
            return $data;
        }

        if (!isset($data['filter']['types'])) {
            $data['filter']['types'] = [];
        }

        if (!isset($data['filter']['visibility_parent'])) {
            $data['filter']['visibility_parent'] = [];
        }

        if (!isset($data['filter']['visibility'])) {
            $data['filter']['visibility'] = [];
        }

        return $data;
    }

    /**
     * Remove unwanted content from posted data. The content is added by hidden inputs.
     * @param unknown_type $data
     * @return unknown
     */
    protected function _removeUnwantedInputsFromData($data)
    {
        $unwantedItemIndexes = ["form_key", "option", "dropdown_attribute_validation"];

        foreach ($unwantedItemIndexes as $index) {
            if (isset($data[$index])) {
                unset($data[$index]);
            }
        }
        return $data;
    }
}
