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
* Profile events logger - events: New profile, profile execution
*
* @category Nostress
* @package Nostress_Koongo
*
*/

namespace Nostress\Koongo\Model\Channel\Profile;

class Logger extends \Nostress\Koongo\Model\AbstractModel
{
    const EVENT_LOG_FILE = "koongo.log";
    const LC_EVENT = 'event'; // log column event
    const LC_TIMESTAMP = 'timestamp';
    const LC_FEED = 'feed';
    const LC_NOP = 'nop';
    const LC_NOC = 'noc';
    const LC_URL = 'url';
    const EVENT_PROFILE_NEW = "p_new";
    const EVENT_PROFILE_RUN = "p_run";
    const LC_DELIMITER = ";";
    protected $_eventLogTemplate = [self::LC_EVENT => "", self::LC_TIMESTAMP => "", self::LC_FEED => "", self::LC_NOP => "",self::LC_NOC => "", self::LC_URL => ""];

    //config params
    const PARAM_LOG_EVENTS = 'general/log_profile_events';
    const PARAM_LOG_LIMIT = 'profile_log/log_limit';
    const PARAM_LOG_REST = 'profile_log/log_rest';

    /**
     *
     * @var \Nostress\Koongo\Helper
     */
    public $helper;

    /*
     *	\Nostress\Koongo\Model\Config\Source\Datetimeformat
    */
    protected $_datetimeformat;

    /**
     * @param \Nostress\Koongo\Helper\Data $helper

     */
    public function __construct(
        \Nostress\Koongo\Helper\Data $helper,
        \Nostress\Koongo\Model\Config\Source\Datetimeformat $datetimeformat
    ) {
        $this->helper = $helper;
        $this->_datetimeformat = $datetimeformat;
    }

    public function logNewProfileEvent($feedCode, $url)
    {
        $template = $this->getEventLogTemplate();
        $template[self::LC_EVENT] = self::EVENT_PROFILE_NEW;
        $template[self::LC_FEED] = $feedCode;
        $template[self::LC_URL] = $url;
        $this->logEvent($template);
    }

    public function logRunProfileEvent($feedCode, $url, $nop = "", $noc="")
    {
        $template = $this->getEventLogTemplate();
        $template[self::LC_EVENT] = self::EVENT_PROFILE_RUN;
        $template[self::LC_FEED] = $feedCode;
        $template[self::LC_NOP] = $nop;
        $template[self::LC_NOC] = $noc;
        $template[self::LC_URL] = $url;
        $this->logEvent($template);
    }

    protected function getEventLogTemplate()
    {
        $template = $this->_eventLogTemplate;
        $template[self::LC_TIMESTAMP] = $this->_datetimeformat->getDateTime();
        return $template;
    }

    protected function logEvent($message)
    {
        if ($this->helper->getModuleConfig(self::PARAM_LOG_EVENTS) == "0") {
            return;
        }

        try {
            if (is_array($message)) {
                $message = implode(self::LC_DELIMITER, $message);
            }

            $file = $this->helper->getFeedStorageDirPath(self::EVENT_LOG_FILE, null);
            $dir = $this->helper->getFeedStorageDirPath("", null);
            $this->helper->createDirectory($dir);

            if (file_exists($file)) {
                $this->checkFile($file);
                $message = PHP_EOL . $message;
                file_put_contents($file, $message, FILE_APPEND);
            } else {
                $this->helper->createFile($file, $message);
            }
        } catch (\Exception $e) {
            $this->log("Event log failed." . $e->getMessage());
        }
    }

    protected function checkFile($file)
    {
        $limit = (int)$this->helper->getModuleConfig(self::PARAM_LOG_LIMIT);

        $lines = file($file);
        if (count($lines) >= $limit) {
            $recordsLeft = (int)$this->helper->getModuleConfig(self::PARAM_LOG_REST);
            $recordsToRemove = $limit-$recordsLeft;

            $content = file_get_contents($file);
            $content = preg_replace("/^(.*" . PHP_EOL . "){{$recordsToRemove}}/", "", $content);
            file_put_contents($file, $content);
        }
    }
}
