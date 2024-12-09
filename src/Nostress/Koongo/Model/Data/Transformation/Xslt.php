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
* Xslt data transformation for export process
* @category Nostress
* @package Nostress_Koongo
*
*/
namespace Nostress\Koongo\Model\Data\Transformation;

use Magento\Framework\Filesystem\DriverInterface;

class Xslt extends \Nostress\Koongo\Model\Data\Transformation
{
    const ENCODING_TAG = '{{encoding}}';
    const CDATA_SECTION_TAG = '{{cdata_section_elements}}';
    const CUSTOM_COLUMNS_HEADER_TAG = "{{custom_columns_header}}";
    const COLUMNS_HEADER_TAG = "{{columns_header}}";
    const CSV_CUSTOM_ATTRIBUTES_TAG = "{{csv_custom_attributes}}";
    const CSV_CUSTOM_ATTRIBUTES_TEMPLATE = '<xsl:call-template name="column_param"><xsl:with-param name="value" select="attribute[{{i}}]/value"/></xsl:call-template>';
    const CSV_CUSTOM_ATTRIBUTES_TEMPLATE_WITHOUT_DELIMITER = '<xsl:call-template name="column_param_without_delimiter"><xsl:with-param name="value" select="attribute[{{i}}]/value"/></xsl:call-template>';
    const INDEX_TAG = "{{i}}";

    const INPUT_FILE = 'input.xml';
    const XSLT_FILE = 'trans.xsl';
    const DEF_ELEMENTS_DELIMITER = " ";
    const DEF_COLUMNS_DELIMITER = "|";

    const DATA_CDATA_SECTION_ELEMENTS = 'cdata_section_elements';
    const DATA_COLUMNS_HEADER = 'columns_header';
    const DATA_BASIC_ATTRIBUTES_COLUMN_HEADER = 'basic_attributes_columns_header';
    const DATA_CUSTOM_COLUMNS_HEADER = 'custom_columns_header';

    protected DriverInterface $driver;

    public function __construct(DriverInterface $driver)
    {
        $this->driver = $driver;
    }

    public function transform($data)
    {
        parent::transform($data);

        $xp = $this->initProcessor();
        $data = $this->initData($data);
        $this->_transform($xp, $data);
    }

    protected function _transform($xp, $data)
    {
        $result = "";
        $result = $xp->transformToXML($data);

        if (!$result) {
            $errMessage = "";
            $e = libxml_get_last_error();
            if ($e) {
                $errMessage = $e->message;
            }
            $this->throwException("9 " . $errMessage);
        }

        $fileType = $this->getFileType();
        if ($fileType == self::XML) {
            //Format as pretty XML
            $xmlDocument = new \DOMDocument('1.0');
            $xmlDocument->preserveWhiteSpace = false;
            $xmlDocument->formatOutput = true;
            $xmlDocument->loadXML($result);
            $result = $xmlDocument->saveXML();
        }

        $this->appendResult($result);
    }

    protected function initProcessor()
    {
        $xp = $this->getXsltProcessor();
        // create a DOM document and load the XSL stylesheet
        $xsl = $this->getDomDocument();
        $xsl->loadXML($this->getXslt());

        if ($this->getIsDebugMode()) {
            $dir = $this->getDefaultDirectoryName();
            $dir = rtrim($dir, "/");
            $this->driver->filePutContents($dir . "/" . self::XSLT_FILE, $this->getXslt());
        }

        // import the XSL styelsheet into the XSLT process
        $xp->importStylesheet($xsl);
        $xp = $this->setProcessorParameters($xp);
        return $xp;
    }

    protected function saveInputData($data)
    {
        $dir = $this->getDefaultDirectoryName();
        $dir = rtrim($dir, "/");
        $this->driver->filePutContents($dir . "/" . self::INPUT_FILE, $data);
    }

    protected function initData($data)
    {
        // create a DOM document and load the XML datat
        $xml_doc = $this->getDomDocument();
        if ($this->getIsDebugMode()) {
            $this->saveInputData($data);
        }

        if (!$xml_doc->loadXML($data, LIBXML_PARSEHUGE)) {
            if (!$xml_doc->loadXML($data)) {
                $this->saveInputData($data);
                $this->throwException("4");
            }
        }

        return $xml_doc;
    }

    protected function check($data)
    {
        if (!parent::checkSrc($data)) {
            $this->throwException("5");
        }
        return true;
    }

    protected function getXslt()
    {
        $xslt = $this->getData(self::XSLT);
        $xslt = str_replace(self::ENCODING_TAG, $this->getEncoding(), $xslt);

        switch ($this->getFileType()) {
            case self::XML:
                $xslt = str_replace(self::CDATA_SECTION_TAG, $this->getCdataSectionElements(), $xslt);
                break;
            case self::CSV:
            case self::TXT:
                $xslt = str_replace(self::CSV_CUSTOM_ATTRIBUTES_TAG, $this->getCustomAttributesXslt(), $xslt);
                break;
        }

        return $xslt;
    }

    protected function setProcessorParameters($xp)
    {
        $params = $this->getCustomParameters();
        $params = array_merge($this->getCommonParams(), $params);
        $params = array_merge($this->getFileTypeParams(), $params);

        foreach ($params as $code => $value) {
            $xp->setParameter('', $code, $value);
        }
        return $xp;
    }

    protected function getCustomParameters()
    {
        $result = [];
        $params = $this->getCustomParams();
        if (!empty($params)) {
            $result = $params;
        }
        return $result;
    }

    protected function getFileTypeParams()
    {
        $result = [];
        if ($this->getFileType() == self::CSV || $this->getFileType() == self::TXT) {
            $result[self::TEXT_ENCLOSURE] =  $this->getTextEnclosure();
            $result[self::COLUMN_DELIMITER] = $this->getColumnDelimiter();
            $result[self::NEWLINE] = $this->getNewLine();
            $result[self::COLUMNS_HEADER] = $this->getColumnsHeader();
        }
        return $result;
    }

    protected function getCommonParams()
    {
        $result = [];
        $result[self::LOCALE] =  $this->getStoreLocale();
        $result[self::LANGUAGE] = $this->getStoreLanguage();
        $result[self::COUNTRY] = $this->getStoreCountry();
        $result[self::DATE] = $this->getCurrentDate();
        $result[self::DATE_TIME] = $this->getCurrentDateTime();
        $result[self::TIME] = $this->getCurrentTime();

        $result[self::CURRENCY] = $this->getCurrency();
        $result[self::FILE_URL] = $this->getFileUrl();

        return $result;
    }

    protected function getCdataSectionElements()
    {
        $elements = $this->getData(self::DATA_CDATA_SECTION_ELEMENTS);
        $result = "";
        if (is_array($elements) && !empty($elements)) {
            $result = implode(self::DEF_ELEMENTS_DELIMITER, $elements);
        }
        return $result;
    }

    protected function getStrippedColumnsHeader()
    {
        $headerTemplate = $this->getData(self::DATA_COLUMNS_HEADER);
        $headerTemplate = str_replace(self::COLUMNS_HEADER_TAG, "", $headerTemplate);
        $headerTemplate = str_replace(self::CUSTOM_COLUMNS_HEADER_TAG, "", $headerTemplate);
        return $headerTemplate;
    }

    protected function _getColumnsHeader()
    {
        $headerTemplate = $this->getStrippedColumnsHeader();

        if (!empty($headerTemplate)) {
            if (strpos($headerTemplate, self::DEF_COLUMNS_DELIMITER) !== false) {
                $headerTemplate = explode(self::DEF_COLUMNS_DELIMITER, $headerTemplate);
            } else {
                $headerTemplate = [$headerTemplate];
            }

            $headerTemplate = $this->prepareCsvRow($headerTemplate);
        } else {
            $headerTemplate = "";
        }

        return $headerTemplate;
    }
    protected function getColumnsHeader()
    {
        $headerTemplate = $this->getData(self::DATA_COLUMNS_HEADER);
        $staticColumns = $this->_getColumnsHeader();
        $attributeColumns = $this->getBasicAttributesColumnsHeader();
        $customColumns = $this->getCustomColumnsHeader();

        $headerTemplateStripped = $this->getStrippedColumnsHeader();

        $headerTemplate = str_replace($headerTemplateStripped, $staticColumns, $headerTemplate);
        $headerTemplate = str_replace(self::COLUMNS_HEADER_TAG, $attributeColumns, $headerTemplate);
        $headerTemplate = str_replace(self::CUSTOM_COLUMNS_HEADER_TAG, $customColumns, $headerTemplate);

        $delimiter = $this->getColumnDelimiter();
        $enclosure = $this->getTextEnclosure();
        $headerTemplate = str_replace($enclosure . $enclosure, "", $headerTemplate);
        $headerTemplate = str_replace($delimiter . $delimiter, $delimiter, $headerTemplate);
        $headerTemplate = str_replace($delimiter . $delimiter, $delimiter, $headerTemplate);

        $headerTemplate = trim($headerTemplate, $delimiter);
        return $headerTemplate;
    }

    protected function getBasicAttributesColumnsHeader()
    {
        $columns = $this->getData(self::DATA_BASIC_ATTRIBUTES_COLUMN_HEADER);
        $result = $this->prepareCsvRow($columns);
        return $result;
    }

    protected function getCustomColumnsHeader()
    {
        $columns = $this->getData(self::DATA_CUSTOM_COLUMNS_HEADER);
        $result = $this->prepareCsvRow($columns);
        return $result;
    }

    protected function getCustomColumnsCount()
    {
        $columns = $this->getData(self::DATA_CUSTOM_COLUMNS_HEADER);
        if (is_array($columns)) {
            return count($columns);
        } else {
            return 0;
        }
    }

    protected function prepareCsvRow($columns)
    {
        $result = "";
        if (!is_array($columns) || empty($columns)) {
            return $result;
        }
        $enclosure = $this->getTextEnclosure();
        $delimiter = $this->getColumnDelimiter();
        $result = implode($enclosure . $delimiter . $enclosure, $columns);
        $result = $delimiter . $enclosure . $result . $enclosure . $delimiter;
        return $result;
    }

    protected function getCustomAttributesXslt()
    {
        $customAttributesCount = $this->getCustomColumnsCount();

        $result = "";
        for ($i = 1;$i <= $customAttributesCount;$i++) {
            if ($i != $customAttributesCount) {
                $result .= str_replace(self::INDEX_TAG, $i, self::CSV_CUSTOM_ATTRIBUTES_TEMPLATE);
            } else {
                $result .= str_replace(self::INDEX_TAG, $i, self::CSV_CUSTOM_ATTRIBUTES_TEMPLATE_WITHOUT_DELIMITER);
            }
        }
        return $result;
    }
}
