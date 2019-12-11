<?php

require_once 'SiteItem.class.php';

class SpiderCgiBook108
{

    /**
     * @var SiteItem
     */
    protected $siteItem;

    /**
     * @var string
     */
    public $inmatesUrl;
    public $searchUrl;
    public $inmateUrl;
    public $startTime;

    /**
     * SpiderCgiBook108 constructor.
     * @param $params
     */
    public function __construct($params)
    {
        $this->inmatesUrl = $params['Inmates Url'];
        $this->searchUrl = $params['Search Url'];
        $this->inmateUrl = $params['Inmate Url'];
        $this->startTime = $params['Start Timestamp'];

        $this->siteItem = new SiteItem($params['Host'], $params['Inmates Url']);
        $this->siteItem->setCounty($params['County']);
        $this->siteItem->setState($params['State']);
    }

    /**
     * @param string $xml
     */
    public function parseInmate($xml)
    {
        $uniqueID = trim(strval($xml->xpath('//LINE40/L40-I1')[0]));
        //parse first block
        $countOfFields = count($xml->xpath('//LINE02'));
        //pid, arrest number, name
        for ($i = 1; $i <= 3; $i++) {
            $fieldKey = str_replace(':', '', trim(strval($xml->xpath("//LINE02[$i]/L02-DESC1")[0])));
            $fieldValue = trim(strval($xml->xpath("//LINE02[$i]/L02-ITEM1")[0]));
            if (stripos($fieldKey, "name") !== false) {
                $this->siteItem->getInmate()->setNames(normalizeName($fieldValue, ','));
            } elseif (stripos($fieldKey, "arrest") !== false) {
                $this->siteItem->getInmate()->addField('Arrest Number', $fieldValue);
            } else {
                $this->siteItem->getInmate()->addField($fieldKey, $fieldValue);
            }
        }
        //aliases
        if ($countOfFields > 3) {
            $this->siteItem->getInmate()->createTable();
            for ($i = 4; $i <= $countOfFields; $i++) {
                $this->siteItem->getInmate()->createRow();
                $this->siteItem->getInmate()->addTableField('Aliases', trim(strval($xml->xpath("//LINE02[$i]/L02-ITEM1")[0])));
            }
            $this->siteItem->getInmate()->addFieldTable(
                "Aliases",
                $this->siteItem->getInmate()->getTableHtml(),
                Inmate::PerInfo
            );

        }
        //second block
        for($i = 1; $i <= 4; $i++) {
            $countOfFieldsInRow = ($xml->LINE04[$i]->count())/2;
            for ($j = 1; $j <= $countOfFieldsInRow; $j++) {
                $fieldKey = str_replace(array('?', ':'), '', trim(strval($xml->xpath("//LINE04[$i]/L04-DESC$j")[0])));
                $fieldValue = trim(strval($xml->xpath("//LINE04[$i]/L04-ITEM$j")[0]));
                if (!empty($fieldKey)) {
                    if (stripos($fieldKey, "height") !== false) {
                        $this->siteItem->getInmate()->addFieldHeight($fieldValue);
                    } elseif (stripos($fieldKey, "dob") !== false) {
                        $this->siteItem->getInmate()->addFieldDate($fieldKey, $fieldValue, 'M/D/Y');
                    } else {
                        $this->siteItem->getInmate()->addField($fieldKey, $fieldValue);
                    }
                }
            }
        }
        //mask tattoo
        $countOfLine04 = $xml->LINE04->count();
        $isMask = trim(strval($xml->xpath("//LINE04[5]/L04-ITEM1")[0]));
        if(!empty($isMask)) {
            $this->siteItem->getInmate()->createTable();
            for($i = 5; $i < $countOfLine04; $i++) {
                $fieldValue = trim(strval($xml->xpath("//LINE04[$i]/L04-ITEM1")[0]));
                $this->siteItem->getInmate()->createRow();
                $this->siteItem->getInmate()->addTableField('Scars/Masks', $fieldValue);
            }
            $this->siteItem->getInmate()->addFieldTable(
                "Scars/Masks",
                $this->siteItem->getInmate()->getTableHtml(),
                Inmate::PerInfo
            );
        }
        $build = trim(strval($xml->xpath("//LINE04[$countOfLine04]/L04-ITEM1")[0]));
        if (!empty($build)) {
            $this->siteItem->getInmate()->addField('Build', $build);
        }
        return $uniqueID;
    }

    public function parseChargeList($xml) {
        $countOfCharges = $xml->LINE27->count();
        $this->siteItem->getInmate()->createTable();
        for ($i = 1; $i <= $countOfCharges; $i++) {
            $this->siteItem->getInmate()->createRow();
            $this->siteItem->getInmate()->addTableField('Offense', trim(strval($xml->xpath("//LINE27[$i]/L27-CHARGE")[0])));
            $this->siteItem->getInmate()->addTableField('Book Date', trim(strval($xml->xpath("//LINE27[$i]/L27-BOOKIN-DATE")[0])));
            $this->siteItem->getInmate()->addTableField('Release Date', trim(strval($xml->xpath("//LINE27[$i]/L27-RELEASE-DATE")[0])));
            $this->siteItem->getInmate()->addTableField('Bond Amount', trim(strval($xml->xpath("//LINE27[$i]/L27-BOND-AMT")[0])));
            $this->siteItem->getInmate()->addTableField('Bondsman', trim(strval($xml->xpath("//LINE27[$i]/L27-NAME")[0])));
        }
        $this->siteItem->getInmate()->addFieldTable(
            "Charges",
            $this->siteItem->getInmate()->getTableHtml(),
            Inmate::ArrInfo
        );
    }
    /**
     * @return null
     */
    public function screenshot()
    {
        $screenshot = $this->siteItem->getInmate()->getNames().'<br><img src="'.$this->siteItem->getInmate()->getImages()[0].'"><br>';
        $fieldMap = ['PID', 'Arrest Number', 'Race', 'Ethnic', 'Sex', 'Age', 'Place of Birth', 'Height', 'Weight', 'Hair', 'Eyes', 'Glasses', 'Facial Hair', 'Skin', 'Charges'];
        foreach ($fieldMap as $fieldName) {
            $screenshot = $screenshot.$fieldName.' = '.$this->siteItem->getInmate()->getField($fieldName).'<br>';
        }
        $this->siteItem->setInmateContentPage($screenshot);
    }

    public function run() {
        $dateLimit = mktime(0, 0, 0, date('m'), date('d') - 1, date('Y'));
        $lastDate = $this->siteItem->getLastSpiderDate();
        if ($lastDate < $this->startTime) {
            $this->siteItem->setLastSpiderDate($this->startTime);
            $lastDate = $this->startTime;
        }
        while ($lastDate < $dateLimit) {
            $postData['S108ASOFDT'] = date('m/d/Y', $lastDate);
            $postData['S108INMNAM'] = '';
            $dailyPage = $this->siteItem->getPage($this->searchUrl, $postData);
            $xml = simplexml_load_string($dailyPage);
            if (!empty($dailyPage)) {
                foreach ($xml->xpath('//LINE01/L01-KEY') as $key) {
                    $postDataInmate['S100KEY'] = strval($key);
                    $postDataInmate['S100LIB'] = '';
                    $postDataInmate['S100PFX'] = '';
                    $dailyPage = $this->siteItem->getPage($this->inmateUrl, $postDataInmate);
                    $xmlInmate = simplexml_load_string($dailyPage);
                    $isImage = strval($xmlInmate->xpath('//HEAD01/H01-IMAGE-LOC')[0]);
                    $this->siteItem->createInmate()->addImageUrl($isImage);
                    $uniqueID = $this->parseInmate($xmlInmate);
                    $this->parseChargeList($xmlInmate);
                    $this->screenshot();
                    if (empty($uniqueID) || skipMug($uniqueID)) {
                        echo "SKIPPING MUG with Unique ID [ $uniqueID ]\n";
                    } else {
                        $this->siteItem->saveLastSpiderDate();
                        $this->siteItem->saveInmate($uniqueID, false, false);
                    }
                }
            }
            $lastDate = $this->siteItem->getNextSpiderDate();
        }
    }
}
