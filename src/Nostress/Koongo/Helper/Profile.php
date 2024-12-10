<?php

namespace Nostress\Koongo\Helper;

class Profile extends \Nostress\Koongo\Helper\Data
{
    const PARAM_HIGHLIGHTJS_STYLE = "general/highlightjs_style";

    public function readXmlPreview($url, $encoding = 'utf-8', $max = 30000)
    {

        //if( !$this->driver->isFile( $url)) return false;

        // read file by lines
        $code = '';
        $braked = false;

        $resource = $this->driver->fileOpen($url, 'r');
        if (!$resource) {
            throw new \Exception(__("Can't open file $url for reading."));
        }
        try {
            while (!$this->driver->endOfFile($resource)) {
                $result = $this->driver->fileRead($resource, 10000);
                $code .= $result;
                // if max is reached, we must end xml with correct element
                if (strlen($code) > $max) {
                    $braked = true;
                    break;
                }
            }
        } finally {
            $this->driver->fileClose($resource);
        }

        //XML file is already formated see "Format as pretty XML" in Nostress\Koongo\Model\Data\Transformation\Xslt
        //$code = $this->formatXmlString($code);

        // prepare for print
        $code = $this->_formatHtmlEntities($code, $encoding);

        if ($braked) {
            $code .= "\n...";
        }

        $stylesheet = $this->getModuleConfig(self::PARAM_HIGHLIGHTJS_STYLE);

        // dark - darkula
        // light -
        $html = "
<link rel='stylesheet' href='$stylesheet'>
<pre class='noformat'><code class='html'>$code</code></pre>";

        return $html;
    }

    protected function _formatHtmlEntities($string, $encoding = 'utf-8')
    {
        if ($encoding != 'utf-8') {
            $string = mb_convert_encoding($string, 'UTF-8', $encoding);
        }
        return $string = htmlentities($string, ENT_SUBSTITUTE, 'UTF-8');
    }

    public function formatXmlString($xml)
    {
        $xml = preg_replace('/(>)(<)(\/*)/', "$1\n$2$3", $xml);
        $token      = strtok($xml, "\n");
        $result     = '';
        $pad        = 0;

        $matches    = [];
        while ($token !== false) :

            // text + koncovy element (nebo zacatek, text a konec)
            if (preg_match('/.+<\/\w[^>]*>$/', $token, $matches)) :
                $indent=0;
        // samostatny konec
        elseif (preg_match('/^<\/\w/', $token, $matches)) :
                $pad-=4;
        $indent = 0;
        // zacatek, muze byt s textem ale nemusi
        elseif (preg_match('/^<\w[^>]*[^\/]>$/', $token, $matches)) :
                $indent=4;
        // bez elementu
        else :
                $indent = 0;
        endif;

        $line    = str_pad($token, strlen($token)+$pad, ' ', STR_PAD_LEFT);

        $result .= $line . "\n";

        $token   = strtok("\n");
        $pad    += $indent;
        endwhile;

        return $result;
    }

    public function readCsvPreview($url, $columnSeparator = ';', $enclosure = '"', $encoding = 'utf-8', $max = 500)
    {

        //if( !$this->driver->isFile( $url)) return false;

        if (empty($enclosure)) {
            $enclosure = '"';
        }

        $row = 1;
        $header = [];
        $array = [];
        if ($handle = $this->driver->fileOpen($url, 'r') !== false) {
            try {
                while (!$this->driver->endOfFile($handle)) {
                    $data = $this->driver->fileGetCsv($handle, 10000, $columnSeparator, $enclosure);
                    if ($data === false) {
                        break;
                    }

                    if ($row > $max) {
                        break;
                    }
                if ($row== 1) {
                        // overjump first row with comment if exist
                        if (count($data) == 1) {
                            continue;
                        }

                        $header = $data;
                    } else {
                        $array[] = $data;
                    }
                    $row++;
                }
            } finally {
                $this->driver->fileClose($handle);
            }
        }

        $html = '<table class="data-grid feed-preview-table"><thead><tr>';
        foreach ($header as $col) {
            $html .= "<th class='data-grid-th'><span class='data-grid-cell-content'>$col</span></th>";
        }
        $html .= '</tr></thead><tbody>';
        $c = 0;
        foreach ($array as $row) {
            $trClass = (++$c%2 == 0) ? "_odd-row" : "";
            $html .= "<tr class='$trClass'>";
            foreach ($row as $value) {
                $html .= "<td>" . $this->_formatHtmlEntities($value, $encoding) . "</td>";
            }
            $html .= "</tr>";
        }
        $html .= "</tbody></table>";

        return $html;
    }

    public function formatStatus($profile)
    {

        /*
         * label-info - blue
         * label-warning - yellow
         * label-success, - green
         * label-primary - dark blue
         * label-default - gray,
         * label-danger - red
         */

        $suffix = null;
        $label = $profile->getStatus();

        switch ($label) {
            case Nostress_Kaas_Model_Profile::STATUS_NEW:
                $labelClass = "label-default";
                break;
            case Nostress_Kaas_Model_Profile::STATUS_GENERATED:
            case Nostress_Kaas_Model_Profile::STATUS_SUBMITED:
                $labelClass = "label-success";
                $label = "feed updated";
                $date = $this->formatDate($profile->getData('last_run_at'), $profile->getProject()->getTimezone());
                $suffix = $date;
                break;
            case Nostress_Kaas_Model_Profile::STATUS_PROCESSING:
                $labelClass = "label-warning";
                $label = "update processing";
                $suffix = $this->__("Feed is being updated");
                break;
            case Nostress_Kaas_Model_Profile::STATUS_PENDING:
                $labelClass = "label-warning";

                if (!$profile->getProject()->isDataImported() && $profile->getProject()->isDataProcessing()) {
                    $label = "update waiting";
                    $suffix = $this->__("for end of products import");
                } else {
                    $label = "update pending";
                    $date = $this->formatDate($profile->getData('last_run_at'), $profile->getProject()->getTimezone());
                    $suffix = $date;
                }

                break;
            case Nostress_Kaas_Model_Profile::STATUS_ERROR:
                $suffix = $profile->getMessage('error');
                // no break
            default:
                $labelClass = "label-danger";
                break;
        }
        $label = strtoupper($label);
        if (!empty($suffix)) {
            $label .= "<div class='suffix'>" . $suffix . "</div>";
        }

        return "<div class='label $labelClass'>" . $label . "</div>";
    }

    public function formatSubmitStatus($profile)
    {

        // not submited yet
        $submitStatus = strtoupper("not submitted yet");
        $submitClass = "label-default";

        // submitted
        $submitDate = $profile->last_submit_at;
        if (!empty($submitDate)) {
            // compare submit date with last_run date
            if (strtotime($submitDate) >= strtotime($profile->last_run_at)) {
                $submitClass = "label-success";
            // if submit date is smaller than last run date, process is not complete!
            } else {
                $submitClass = "label-warning";
            }

            $submitType = $profile->getData('submit_type');
            // downloaded by channel
            if ($submitType == Nostress_Kaas_Model_Profile::SUBMIT_TYPE_MANUAL) {
                $submitStatus = $this->__("Downloaded") . "<br>" . $this->__("by") . " " . $profile->getChannelLabel();
            // submitted by FTP or API
            } else {
                $submitStatus = "submitted via " . $profile->getExportApi($submitType)->getName();
            }
            $submitStatus = strtoupper($submitStatus);
            $submitStatus .= "<div class='suffix'>" . $this->formatDate($submitDate, $profile->getProject()->getTimezone()) . "</div>";
        }

        return "<div class='label-submit label $submitClass'>$submitStatus</div>";
    }
}
