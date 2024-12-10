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
* FTP Manager for export profiles
*
* @category Nostress
* @package Nostress_Koongo
*
*/

namespace Nostress\Koongo\Model\Channel\Profile;

use Nostress\Koongo\Model\Channel\Profile;

class Ftp extends \Nostress\Koongo\Model\AbstractModel
{
    const CODE_NOT_ENABLED = 101;
    const CODE_ERROR = 301;

    /**
     * @var \Magento\Framework\Filesystem\DirectoryList
     */
    protected $_dir;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Nostress\Koongo\Helper\Data $helper
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Nostress\Koongo\Model\Translation $translation
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param \Magento\Framework\Filesystem\DirectoryList $dir,
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Nostress\Koongo\Helper\Data $helper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Nostress\Koongo\Model\Translation $translation,
        \Magento\Framework\Filesystem\DirectoryList $dir,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        \Magento\Framework\Filesystem\DriverInterface $driver,
        array $data = []
    ) {
        $this->_dir = $dir;
        parent::__construct($context, $registry, $helper, $storeManager, $translation, $resource, $resourceCollection, $driver, $data);
    }

    public function uploadFeed(Profile $profile, $checkAutosubmit = false)
    {

        // remove old Upload messages
        $pMessage = preg_replace('/Upload\: .+/', '', $profile->getMessage());

        try {
            $this->_uploadFeed($profile, $checkAutosubmit);

            $profile->setMessage($pMessage . " " . __('Upload:') . " OK");
            $profile->setStatus(Profile::STATUS_FINISHED);

            return true;
        } catch (\Exception $e) {
            if ($e->getCode() != self::CODE_NOT_ENABLED) {
                $profile->setMessageStatusError(
                    $pMessage . " " . __('Upload:') . " " . $e->getMessage(),
                    Profile::STATUS_ERROR
                );
            }
            return $e->getMessage();
        }
    }

    public function isEnabled($config)
    {
        if (isset($config['feed']['ftp'])) {
            $config = $config['feed']['ftp'];
        }

        return (isset($config['enabled']) && $config['enabled']);
    }

    public function isFilled($config)
    {
        if (isset($config['feed']['ftp'])) {
            $config = $config['feed']['ftp'];
        }

        return (!empty($config['hostname']) && !empty($config['username']) && !empty($config['password']));
    }

    protected function _getFtpAdatper($config)
    {
        if (isset($config['protocol']) && $config['protocol'] == \Nostress\Koongo\Model\Config\Source\Ftpprotocol::SFTP) {
            $ftp = new \Nostress\Koongo\Model\Io\Sftp();
            $ftpConfig = [
                'host'      => $config['hostname'] . ":" . $config['port'],
                'username'  => $config['username'],
                'password'  => $config['password'],
            ];
        } else {
            $ftp = new \Nostress\Koongo\Model\Io\Ftp();
            $ftpConfig = [
                'host'      => $config['hostname'],
                'port' => $config['port'],
                'user'  => $config['username'],
                'password'  => $config['password'],
                'passive' => (bool) $config['passive_mode'],
                'ssl' => isset($config['ssl']) ? (bool) $config['ssl'] : false,

            ];
            if ($ftpConfig['ssl'] && !function_exists('ftp_ssl_connect')) {
                throw new \Exception('FTPS cannot be used! ftp_ssl_connect function is not installed on your server', self::CODE_ERROR);
            }
        }
        $ftp->open($ftpConfig);

        return $ftp;
    }

    public function checkFtpConnection($config)
    {
        $ftp = null;
        try {
            $ftp = $this->_getFtpAdatper($config);
            // test prava zapisu
            $filename = "ftp_test.xml";
            $fullfilename = $this->_dir->getPath('var') . '/' . $filename;
            $this->driver->filePutContents($fullfilename, "FTP TEST"); // vytvori novy soubor

            $fullpath = rtrim($config['path'], '/') . '/' . $filename;

            //nahraje na FTP
        if (!$ftp->write($fullpath, $this->driver->fileGetContents($fullfilename))) {
                throw new \Exception('Check write permissions!', self::CODE_ERROR);
            }
            // smaze z FTP
            if (!$ftp->rm($fullpath)) {
                throw new \Exception('Check delete permissions!', self::CODE_ERROR);
            }
            $this->driver->deleteFile($fullfilename); // smaze z disku
            $ftp->close();
            return [ 'error'=> false, 'message'=> __('Connection Successful!')];
        } catch (\Exception $e) {
            if (is_object($ftp)) {
                $ftp->close();
            }
            return [ 'error'=> true, 'message'=> $e->getMessage() . "!"];
        }
    }

    protected function _uploadFeed(Profile $profile, $checkAutosubmit = false)
    {
        $config = $profile->getFtpParams();

        if ($checkAutosubmit && !$this->isEnabled($config)) {
            throw new \Exception('Autosubmit of feed is not enabled!', self::CODE_NOT_ENABLED);
        }

        if (!$this->isFilled($config)) {
            throw new \Exception('FTP Settings is not filled in!', self::CODE_NOT_ENABLED);
        }

        $suffix = $profile->getFeed()->getFileType();
        $fullFilename = $profile->getFilename(true, true, $suffix);
        $filename = $profile->getFilename(false, true, $suffix);

        $fullpath = rtrim($config['path'], '/') . '/' . $filename;

        if (!$this->driver->isFile($fullFilename)) {
            throw new \Exception('Feed file does not exists!', self::CODE_ERROR);
        }

        $ftp = $this->_getFtpAdatper($config);
        $result = $ftp->write($fullpath, $fullFilename);

        if (!$result) {
            $ftp->close();
            throw new \Exception('Feed file can not be uploaded via FTP! Check permissions!', self::CODE_ERROR);
        }

        $ftp->close();

        return $result;
    }

    /**
     *
     * @param Profile $profile
     * @throws \Exception
     * @return \Magento\Framework\Filesystem\Io\AbstractIo
     */
    public function getClient($config)
    {
        return $this->_getFtpAdatper($config);
    }

    public function getFilesSorted($ftp)
    {
        $items = $ftp->getItems();
        if (!$items) {
            return false;
        }
        uksort($items, "strnatcasecmp");
        $dirs = [];
        $files = [];
        foreach ($items as $name => $item) {
            $item['name'] = $name;
            if ($item['type'] == \Nostress\Koongo\Model\Io\Listable::DIR) {
                $dirs[] = $item;
            } else {
                $files[] = $item;
            }
        }

        return array_merge($dirs, $files);
    }
}
