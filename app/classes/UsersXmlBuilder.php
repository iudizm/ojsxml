<?php

namespace OJSXml;

class UsersXmlBuilder extends XMLBuilder
{
    private array $csvData;
    private bool $isTest;

    public function __construct(bool $isTest, $filePath, &$dbManager = null)
    {
        $this->isTest = $isTest;
        parent::__construct($filePath, $dbManager);
    }

    /**
     * Set data to object used for creating xml
     *
     * @param array $csvData
     */
    public function setData($csvData)
    {
        $this->csvData = $csvData;
    }

    /**
     * Converts single csv file of users to import xml
     */
    public function buildXml()
    {
        $xml = $this->getXmlWriter();
        $xml->startElement("PKPUsers");
        $xml->writeAttribute("xmlns", "http://pkp.sfu.ca");
        $xml->writeAttribute("xmlns:xsi", "http://www.w3.org/2001/XMLSchema-instance");
        $xml->writeAttribute("xsi:schemaLocation", "http://pkp.sfu.ca pkp-users.xsd");
        $xml->startElement("users");

        $userBuilder = new UserBuilder($this->isTest);
        foreach ($this->csvData as $userData) {
            $this->writeUser($userBuilder->buildUserData($userData));
        }

        $xml->endElement();
        $xml->endElement();

        $xml->endDocument();
        $xml->flush();
    }

    /**
     * @param array $userData
     */
    public function writeUser($userData)
    {
        $xml = $this->getXmlWriter();
        $xml->startElement("user");

        $xml->startElement("givenname");
        $this->addLocaleAttribute();
        $xml->writeRaw($userData["firstname"]);
        $xml->endElement();

        if (!empty($userData["lastname"])) {
            $xml->startElement("familyname");
            $this->addLocaleAttribute();
            $xml->writeRaw($userData["lastname"]);
            $xml->endElement();
        }

        if (!empty($userData["affiliation"])) {
            $xml->startElement("affiliation");
            $this->addLocaleAttribute();
            $xml->writeRaw($userData["affiliation"]);
            $xml->endElement();
        }

        if (!empty($userData["country"])) {
            $xml->startElement("country");
            $xml->writeRaw($userData["country"]);
            $xml->endElement();
        }

        $xml->startElement("email");
        $xml->writeRaw($userData["email"]);
        $xml->endElement();

        $xml->startElement("username");
        $xml->writeRaw($userData["username"]);
        $xml->endElement();

        $xml->startElement("password");
        $xml->writeAttribute("must_change", "true");
        $xml->writeAttribute("encryption", "plaintext");
        $xml->startElement("value");
        $xml->writeRaw('');
        $xml->endElement();

        $xml->endElement();

        $xml->startElement("date_registered");
        $xml->writeRaw(date("Y-m-d H:i:s"));
        $xml->endElement();

        $xml->startElement("date_last_login");
        $xml->writeRaw(date("Y-m-d H:i:s"));
        $xml->endElement();

        $xml->startElement("inline_help");
        $xml->writeRaw("true");
        $xml->endElement();

        for ($i = 1; $i < 6; $i++) {
            if (isset($userData["role" . $i])) {
                $xml->startElement("user_group_ref");
                $xml->writeRaw($userData["role" . $i]);
                $xml->endElement();
            }
        }

        if (!empty($userData["reviewInterests"])) {
            $xml->startElement("review_interests");
            $xml->writeRaw($userData["reviewInterests"]);
            $xml->endElement();
        }

        $xml->endElement();
    }
}
