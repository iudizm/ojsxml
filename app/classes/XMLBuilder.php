<?php

namespace OJSXml;

use XMLWriter;

abstract class XMLBuilder
{
    /** @var XMLWriter $xmlWriter */
    private $xmlWriter;
    /** @var DBManager $dbManager */
    private $dbManager;
    /** @var string $locale */
    private $locale;

    /**
     * IssuesXmlBuilder constructor.
     *
     * @param $filePath
     */
    public function __construct($filePath, &$dbManager = null)
    {
        $this->xmlWriter = new XmlWriter();
        $this->xmlWriter->openUri($filePath);
        $this->xmlWriter->startDocument();
        $this->xmlWriter->setIndent(true);

        if ($dbManager != null) {
            $this->dbManager = $dbManager;
        }

        $this->locale = Config::get("locale");
    }

    /**
     * Builds and closed xml file
     */
    abstract public function buildXml();

    /**
     * @return XMLWriter
     */
    protected function getXmlWriter()
    {
        return $this->xmlWriter;
    }

    /**
     * @return DBManager
     */
    protected function getDBManager()
    {
        return $this->dbManager;
    }

    protected function addLocaleAttribute()
    {
        $this->xmlWriter->writeAttribute("locale", $this->locale);
    }
}
