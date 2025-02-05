<?php

namespace OJSXml;

class UserBuilder
{
    private array $csvUserData;
    private bool $isTest;

    public function __construct($isTest = false)
    {
        $this->isTest = $isTest;
    }

    public function buildUserData($csvUserData)
    {
        $this->csvUserData = $csvUserData;

        $userData = [];

        $userData["username"] = empty($this->csvUserData["username"]) ? $this->generateUsername() : $this->csvUserData["username"];
        $userData["firstname"] = $this->csvUserData["firstname"];
        $userData["lastname"] = $this->csvUserData["lastname"];

        $userData["email"] = $this->buildEmail();
        $userData["affiliation"] = $this->csvUserData["affiliation"];
        $userData["country"] = $this->csvUserData["country"];

        $userData["tempPassword"] = empty($this->csvUserData["tempPassword"]) ? $this->generateTempPassword() : $this->csvUserData["tempPassword"];
        $userData["reviewInterests"] = $this->csvUserData["reviewInterests"];

        for ($i = 1; $i < 6; $i++) {
            if (isset($this->csvUserData["role" . $i]) && $this->csvUserData["role" . $i] != "") {
                $userData["role" . $i] = userGroupRef($this->csvUserData["role" . $i]);
            }
        }

        return $userData;
    }

    private function buildEmail()
    {
        $emailData = explode(',', $this->csvUserData["email"]);
        if (sizeof($emailData) > 1) {
            Logger::print($this->csvUserData["username"] . ' email truncated to first provided.');
        }
        $email = htmlspecialchars($emailData[0]);

        return $this->isTest ? $email . "test" : $email;
    }

    private function generateUsername()
    {
        $username = $this->csvUserData["firstname"] . $this->csvUserData["lastname"];
        $username = preg_replace('/[^A-Za-z0-9]/', '', $username);
        $username = strtolower($username);
        $username = substr($username, 0, 10);
        return $username;
    }

    private function generateTempPassword()
    {
        $tempPassword = substr(md5(rand()), 0, 8);
        return $tempPassword;
    }
}
