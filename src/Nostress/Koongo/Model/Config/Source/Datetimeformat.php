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
* Config source model - date time format
*
* @category Nostress
* @package Nostress_Koongo
*
*/
namespace Nostress\Koongo\Model\Config\Source;

class Datetimeformat extends \Nostress\Koongo\Model\Config\Source
{
    const SQL = "sql";
    const PHP = "php";
    const DATE_TIME = "date_time";
    const DATE = "date";
    const TIME = "time";

    const STANDARD = "standard";
    const ISO8601 = "iso8601";
    const ATOM = "atom";
    const SLASH = "slash";
    const COOKIE = "cookie";
    const RFC822 = "rfc822";
    const RSS = "rss";
    const AT = "at";
    const TIMESTAMP = "unix_timestamp";

    const STANDARD_DATETIME = "Y-m-d H:i:s";
    const STANDARD_DATE = "Y-m-d";
    const STANDARD_TIME = "H:i:s";

    const STANDARD_DATETIME_SQL = "%Y-%m-%d %H:%i:%s";
    const STANDARD_DATE_SQL = "%Y-%m-%d";
    const STANDARD_TIME_SQL = "%H:%i:%s";

    protected $_formats = [];

    /**
     * @var \Magento\Cron\Model\ConfigInterface
     */
    protected $_config;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $_localeDate;

    /**
     * Date model
     *
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_date;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param ConfigInterface $config
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Cron\Model\ConfigInterface $config,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Framework\Stdlib\DateTime\DateTime $date
    ) {
        $this->_config = $config;
        $this->_scopeConfig = $scopeConfig;
        $this->_localeDate = $localeDate;
        $this->_date = $date;
    }

    public function toOptionArray()
    {
        return [
            ['value'=> self::STANDARD, 'label'=> __('Standard (Y-m-d H:i:s)')],
            ['value'=>self::ISO8601, 'label'=> __('ISO  8601 (Y-m-dTH:i:sO)')],
            ['value'=>self::SLASH, 'label'=> __('Slash delimiter (Y/m/d H:M)')],
            ['value'=>self::ATOM, 'label'=> __('ATOM,W3C (Y-m-d\TH:i:sP)')],
            ['value'=>self::COOKIE, 'label'=> __('COOKIE (l, d-M-y H:i:s T)')],
            ['value'=>self::RFC822, 'label'=> __('RFC822 (D, d M Y H:i:s O)')],
            ['value'=>self::RSS, 'label'=> __('RSS (D, d M Y H:i:s O)')],
            ['value'=>self::AT, 'label'=> __('@ (d.m.Y @ H:i:s)')],
            ['value'=>self::TIMESTAMP, 'label'=> __('Timestamp')]
        ];
    }

    public function formatDatetime($timestamp, $format=self::STANDARD)
    {
        $phpFormat = $this->getPhpFormat($format, self::DATE_TIME);
        return $this->convertTimestamp($timestamp, $phpFormat);
    }

    public function formatDate($timestamp, $format=self::STANDARD)
    {
        $phpFormat = $this->getPhpFormat($format, self::DATE);
        return $this->convertTimestamp($timestamp, $phpFormat);
    }

    public function formatTime($timestamp, $format=self::STANDARD)
    {
        $phpFormat = $this->getPhpFormat($format, self::TIME);
        return $this->convertTimestamp($timestamp, $phpFormat);
    }

    public function getTime($timeString = null, $format = false)
    {
        $time = $this->_getDateTime($timeString);
        if ($format) {
            $time = $this->formatTime($time, $format);
        }

        return $time;
    }

    public function getSqlFormat($format, $type)
    {
        return $this->getFormat($format, $type, self::SQL);
    }

    public function getDate($timeString = null, $format = false)
    {
        $time = $this->_getDateTime($timeString);
        if ($format) {
            $time = $this->formatDate($time, $format);
        }

        return $time;
    }

    public function getDateTime($timeString = null, $format = false)
    {
        $dateTime = $this->_getDateTime($timeString);

        if ($format) {
            $dateTime = $this->formatDatetime($dateTime, $format);
        }

        return $dateTime;
    }

    public function getDayOfWeek($timeString = "now")
    {
        $time = strtotime($timeString);
        $dayOfWeek = date('N', $time);
        return $dayOfWeek;
    }

    public function getMonthOfYear($timeString = "now")
    {
        $time = strtotime($timeString);
        $month = date('m', $time);
        return $month;
    }

    public function getYear($timeString = "now")
    {
        $time = strtotime($timeString);
        $year = date('Y', $time);
        return $year;
    }

    protected function _getDateTime($timeString = null)
    {
        $time = null;
        if (!isset($timeString)) {
            $time = $this->_localeDate->scopeTimeStamp();
        } else {
            $time = $this->_localeDate->scopeDate(null, $timeString, true)->getTimestamp();
        }
        //strtotime($timeString);

        return $time;
        //get time zone time
//     	return $this->timezone->timestamp($time);
//     	return $this->_localeDate->scopeDate(null,$time,true);
    }

    protected function prepareFormats()
    {
        $this->_formats = [
            self::STANDARD	=> [
                self::PHP => [self::DATE_TIME => self::STANDARD_DATETIME,self::DATE => self::STANDARD_DATE, self::TIME => self::STANDARD_TIME],
                self::SQL => [self::DATE_TIME => self::STANDARD_DATETIME_SQL,self::DATE => self::STANDARD_DATE_SQL, self::TIME => self::STANDARD_TIME_SQL],
            ],
            self::ISO8601	=> [
                self::PHP => [self::DATE_TIME => \DateTime::ISO8601,self::DATE => self::STANDARD_DATE, self::TIME => "H:i:sO"],
                self::SQL => [self::DATE_TIME => "%Y-%m-%dT%T" . $this->getTimeShift(),self::DATE => self::STANDARD_DATE_SQL, self::TIME => self::STANDARD_TIME_SQL . $this->getTimeShift()],
            ],
            self::SLASH	=> [
                self::PHP => [self::DATE_TIME => "Y/m/d H:i",self::DATE => "Y/m/d", self::TIME => "H:i"],
                self::SQL => [self::DATE_TIME => "%Y/%m/%d %H:%i",self::DATE => "%Y/%m/%d", self::TIME => "%H:%i"],
            ],
            self::ATOM	=> [
                self::PHP => [self::DATE_TIME => \DateTime::ATOM,self::DATE => self::STANDARD_DATE, self::TIME => "H:i:sP"],
                self::SQL => [self::DATE_TIME => "%Y-%m-%dT%T" . $this->getTimeShift("P"),self::DATE => self::STANDARD_DATE_SQL, self::TIME => self::STANDARD_TIME_SQL . $this->getTimeShift("P")],
            ],
            self::COOKIE	=> [
                self::PHP => [self::DATE_TIME => \DateTime::COOKIE,self::DATE => "l, d-M-y", self::TIME => "H:i:s T"],
                self::SQL => [self::DATE_TIME => "%W, %d-%b-%y %T " . $this->getTimeShift("T"),self::DATE => "%W, %d-%M-%y", self::TIME => self::STANDARD_TIME_SQL . " " . $this->getTimeShift("T")],
            ],
            self::RFC822	=> [
                self::PHP => [self::DATE_TIME => \DateTime::RFC822,self::DATE => "D, d M y", self::TIME => "H:i:s O"],
                self::SQL => [self::DATE_TIME => "%a, %d %b %y %T " . $this->getTimeShift(),self::DATE => "%a, %d %M %y", self::TIME => self::STANDARD_TIME_SQL . " " . $this->getTimeShift()],
            ],
            self::RSS	=> [
                self::PHP => [self::DATE_TIME => \DateTime::RSS,self::DATE => "D, d M Y", self::TIME => "H:i:s O"],
                self::SQL => [self::DATE_TIME => "%a, %d %b %Y %T " . $this->getTimeShift(),self::DATE => "%a, %d %M %Y", self::TIME => self::STANDARD_TIME_SQL . " " . $this->getTimeShift()],
            ],
            self::AT	=> [
                self::PHP => [self::DATE_TIME => "d.m.Y @ H:i:s",self::DATE => "d.m.Y", self::TIME => self::STANDARD_TIME],
                self::SQL => [self::DATE_TIME => "%d.%m.%Y @ %H:%i:%s",self::DATE => "%d.%m.%Y", self::TIME => self::STANDARD_TIME_SQL],
            ],
            self::TIMESTAMP	=> [
                self::PHP => [self::DATE_TIME => "U",self::DATE => "U", self::TIME => "U"],
                self::SQL => [self::DATE_TIME => self::TIMESTAMP ,self::DATE => self::TIMESTAMP, self::TIME => self::TIMESTAMP],
            ]
        ];
    }

    protected function getTimeShift($format = "O")
    {
        return $this->convertTimestamp($this->_localeDate->scopeTimeStamp(), $format);
    }

    protected function getPhpFormat($format, $type)
    {
        return $this->getFormat($format, $type);
    }

    protected function getFormat($format, $type, $platform = self::PHP)
    {
        if (empty($this->_formats)) {
            $this->prepareFormats();
        }

        $formatBase = $this->_formats[self::STANDARD][$platform];
        if (isset($this->_formats[$format][$platform])) {
            $formatBase = $this->_formats[$format][$platform];
        }

        $result = $formatBase[self::DATE_TIME];
        if (isset($formatBase[$type])) {
            $result = $formatBase[$type];
        }
        return $result;
    }

    protected function convertTimestamp($timestamp, $format)
    {
        $time = date(self::STANDARD_DATETIME, $timestamp);
        if ($format == self::STANDARD_DATETIME) {
            return $time;
        }

        $timezone = $this->getTimezone();
        $datetime = new \DateTime($time, $timezone);
        return $datetime->format($format);
    }

    protected function getTimezone($scopeType = null, $scopeCode = null)
    {
        $timezone = $this->_localeDate->getConfigTimezone($scopeType, $scopeCode);
        try {
            $tz = new \DateTimeZone($timezone);
        } catch (\Exception $e) {
            $tz = new \DateTimeZone('Europe/Prague');
        }
        return $tz;
    }
}
