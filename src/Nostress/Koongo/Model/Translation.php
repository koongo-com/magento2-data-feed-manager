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
 * Translation of error codes and messages.
 *
 * @category Nostress
 * @package Nostress_Koongo
 *
 */
namespace Nostress\Koongo\Model;

class Translation extends \Magento\Framework\Model\AbstractModel
{
    //help links
    const HELP_XSLT = "help/xslt_library";
    // 	const HELP_TROUBLE = "troubleshooting";
    const HELP_SUPPORT = "help/support";
    // 	const HELP_FEED_COLLECTIONS = 'feed_collections';
    // 	const HELP_LICENSE_CONDITIONS = "license_conditions";
    // 	const HELP_FLAT_CATALOG = "flat_catalog";
    // 	const HELP_TAXONOMY_SETUP = "taxonomy_setup";
    // 	const HELP_GETTING_FEEDS_AND_TAXONOMIES = "getting_feeds_and_taxonomies";

    /**
     * List of error code tranlations
     * @var unknown_type
     */
    protected $_errorList;

    /**
     * List of message code tranlations
     * @var unknown_type
     */
    protected $_messageList;

    /**
     * List of action links tranlations
     * @var unknown_type
     */
    protected $_actionLinksList;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /** @var UrlInterface */
    protected $urlBuilder;

    /*
     * @param \Magento\Framework\App\Config\ScopeConfigInterface
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\UrlInterface $urlBuilder
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->urlBuilder = $urlBuilder;
    }

    //extract error code from message
    public function processException($exception)
    {
        if (is_string($exception)) {
            $error = $this->processErrorMessage($exception);
        } else {
            //extract error code from message
            $error = $this->processErrorMessage($exception->getMessage());
        }

        $message = $error['message'];

        if (!empty($error['action_message'])) {
            $tmpMes = '<br>' . str_replace("{{action_link}}", $error['action_link'], $error['action_message']);
            if (!empty($error['params'])) {
                $tmpMes = str_replace("{{params}}", $error['params'], $tmpMes);
            }
            $message .= $tmpMes;
            //$message = $this->helper()->updateActionLinkInMessageContent($message,$profile->getId());
        }

        if (!empty($error['link'])) {
            $origMessage = $this->getMessageByCode(1);
            $message .= "<br><br>" . __($origMessage, $error['link']);
        }

        return $message;
    }

    protected function processErrorMessage($message)
    {
        $result = [];
        $errorCode = "";
        $link = "";
        $actionMessage = "";
        $actionLink = "";
        $errorParams = "";

        if (strlen($message) > 0 && is_numeric($message[0])) {
            $index = strpos($message, " ");
            if ($index === false) {
                $errorCode = $message;
                $message = "";
            } else {
                $errorCode = substr($message, 0, $index);
                $message =  substr($message, $index);
            }

            $errorItem = $this->getErrorByCode($errorCode);

            if ($errorItem != false) {
                $errorParams = $message;
                $message = $errorItem["message"] . "<strong>" . $message . "</strong>";
                $link = $errorItem["link"];
                if (!empty($errorItem["action_message"])) {
                    $actionMessage = $errorItem["action_message"];
                }
                if (!empty($errorItem["action_link"])) {
                    $actionLink = $errorItem["action_link"];
                }
            }
        }
        $result["code"] = $errorCode;
        $result["message"] = $message;
        $result["params"] = $errorParams;
        $result["link"] = $link;
        $result["action_message"] = $actionMessage;
        $result["action_link"] = $actionLink;

        return $result;
    }

    public function getErrorByCode($code)
    {
        if (empty($code) || !is_numeric($code)) {
            return false;
        }

        $list = $this->getErrorList();
        if (isset($list[$code])) {
            return $list[$code];
        } else {
            return false;
        }
    }

    public function getMessageByCode($code, $addLink = false)
    {
        if (empty($code) || !is_numeric($code)) {
            return false;
        }

        $list = $this->getMessageList();
        if (isset($list[$code])) {
            if (!$addLink) {
                return $list[$code]["message"];
            }

            return $list[$code];
        } else {
            return false;
        }
    }

    public function replaceActionLinks($string)
    {
        $list = $this->getActionLinksTranslationList();
        foreach ($list as $key => $link) {
            $link = $this->urlBuilder->getUrl($link);
            if (!empty($string)) {
                $string = str_replace($key, $link, $string);
            }
        }
        return $string;
    }

    protected function getHelpLink($configPath)
    {
        $configPath .= \Nostress\Koongo\Helper\Data::PATH_MODULE_CONFIG . $configPath;
        return $this->scopeConfig->getValue($configPath);
    }

    protected function getErrorList()
    {
        if (empty($this->_errorList)) {
            $defaultLink = $this->getHelpLink(self::HELP_SUPPORT);
            $errorList = [];
            $errorList[1] = ["message" => __("Can't load XSLT Processor class. Please install the XSLT transformation library to your server."),
                                        "link" => $this->getHelpLink(self::HELP_XSLT)];
            $errorList[2] = ["message" => "Can't load profile configuration field %1.",
                                        "link" => ""];
            $errorList[3] = ["message" => "Missing profile attributes configuration. Please add custom attributes to Custom Attributes Table.",
                                        "link" => ""];
            $errorList[5] = ["message" => "Empty product and/or category data source.",
                                        "link" => ""];
            $errorList[7] = ["message" => __("Following attributes are missing in Product Flat Catalog: "),
                                        "link" => __("https://koongo.atlassian.net/wiki/spaces/koongo/pages/787612/Following+attributes+are+missing+in+Product+Flat+catalog+-+M2"),
                                        "action_message" => __('<a onclick="return confirm(\'Following attributes are missing in Product Flat Catalog: \n {{params}} \n\nAdd the attribute(s) to Product Flat Catalog? \n\n Following actions will be performed: \n (1) Set the attribute\\\'s property Used in Product Listing to Yes \n (2) Product Flat Reindex \n (3) Flush Cache Storage\')" href="{{action_link}}">Click here to add the attribute(s) to Product Flat Catalog</a>.<br> Action includes:<br>&nbsp;&nbsp;(1) Set the attribute\'s property Used in Product Listing<br>&nbsp;&nbsp;(2) Product Flat Reindex<br>&nbsp;&nbsp;(3) Flush Cache Storage'),
                                        "action_link" => "{{add_attributes_to_product_flat_link}}"];
            $errorList[10] = ["message" => __("Zero products selected for export. Please choose products in Product filter in Export profile detail."),
                                        "link" =>""];
            $errorList[11] = ["message" => __("Product and category data reindex required."),
                                        "link" => ""];
            $errorList[12] = ["message" => __("Category and Product Flat tables are missing. It is necessary to reindex Product Flat Data and Category Flat Data indexes."),
                                        "link" => "",
                                        "action_message" => __('<a href="{{action_link}}">Click here to reindex Product Flat Data and Category Flat Data indexes</a>'),
                                        "action_link" => "{{reindex_category_and_product_flat_link}}"];
            $this->_errorList = $errorList;
        }
        return $this->_errorList;
    }

    protected function getMessageList()
    {
        if (empty($this->_messageList)) {
            $messageList = [];
            $messageList[1] = ["message" => 'The solution for this problem is described in <a href="%1" target="_blank">Koongo Docs</a>.',
                                "link" => ""];
            $messageList[2] = ["message" => __('Unable to generate an export'),
                                "link" => ""];
            $messageList[3] = ["message" => __('Missing feed layout.'),
                                "link" => ""];
            $messageList[4] = ["message" => 'Profile # %1 is disabled.',
                                "link" => ""];

            $this->_messageList = $messageList;
        }
        return $this->_messageList;
    }

    protected function getActionLinksTranslationList()
    {
        if (empty($this->_actionLinksList)) {
            $actionsList = [];
            $actionsList["{{add_attributes_to_product_flat_link}}"] = "koongo/channel_profile/flatprocess";
            $actionsList["{{reindex_category_and_product_flat_link}}"] = "koongo/channel_profile/flatreindex";
            $this->_actionLinksList = $actionsList;
        }
        return $this->_actionLinksList;
    }
}
