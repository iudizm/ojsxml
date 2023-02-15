<?php

namespace OJSXml;

require("./app/bootstrap.php");

class csvToXmlConverter
{
    public $command;
    public $user;
    public $sourceDir;
    public $destinationDir;

    /**
     * csvToXmlConverter constructor.
     *
     * @param array $argv Command line arguments
     */
    public function __construct($argv = array())
    {
        array_shift($argv);

        if (sizeof($argv) != 4) {
            $this->usage();
        }

        $this->command = array_shift($argv);
        $this->user = array_shift($argv);
        $this->sourceDir = array_shift($argv);
        $this->destinationDir = array_shift($argv);

        $validCommands = [
            "issues",
            "users",
            "users:test",
            "help"
        ];
        if (!in_array($this->command, $validCommands)) {
            echo '[Error]: Valid commands are "issues" or "users"' . PHP_EOL;
            exit();
        }

        if (!is_dir($this->sourceDir)) {
            echo "[Error]: <source_directory> must be a valid directory";
            exit();
        } elseif ($this->command == "issues" && !is_dir($this->sourceDir . "/issue_cover_images")) {
            echo '[Error]: "The subdirectory "<source_directory>/issue_cover_images" must exist when converting issues' . PHP_EOL;
            exit();
        } elseif ($this->command == "issues" && !is_dir($this->sourceDir . "/article_galleys")) {
            echo '[Error]: "The subdirectory "<source_directory>/article_galleys" must exist when converting issues' . PHP_EOL;
            exit();
        }
        if (!is_dir($this->destinationDir)) {
            echo "[Error]: <destination_directory> must be a valid directory" . PHP_EOL;
            exit();
        }
    }

    /**
     * Prints CLI usage instructions to console
     */
    public function usage()
    {
        echo "Script to convert issue or user CSV data to OJS XML" . PHP_EOL
            . "Usage: issues|users|users:test <ojs_username> <source_directory> <destination_directory>" . PHP_EOL . PHP_EOL
            . 'NB: `issues` source directory must include "issue_cover_images" and "article_galleys" directory' . PHP_EOL
            . 'user:test appends "test" to user email addresses' . PHP_EOL;
        exit();
    }

    /**
     * Executes tasks associated with given command
     */
    public function execute()
    {
        switch ($this->command) {
            case "issues":
                $this->generateIssuesXml($this->sourceDir, $this->destinationDir);
                break;
            case "users":
                $this->generateUsersXml($this->sourceDir, $this->destinationDir);
                break;
            case "users:test":
                $this->generateUsersXml($this->sourceDir, $this->destinationDir, true);
                break;
            case "help":
                $this->usage();
                break;
        }

        Logger::writeOut($this->command, $this->user);
    }

    /**
     * Converts issue CSV data to OJS Native XML files
     *
     * @param string $sourceDir Location of CSV files
     * @param string $destinationDir Target directory for XML files
     */
    private function generateIssuesXml($sourceDir, $destinationDir)
    {
        $dbManager = new DBManager();
        $dbManager->importIssueCsvData($sourceDir . "/*");

        $issueCoversDir = $sourceDir . "/issue_cover_images/";
        $issueCount = $dbManager->getIssueCount();

        $articleGalleysDir = $sourceDir . "/article_galleys/";

        Logger::print("Running issue CSV-to-XML conversion...");
        Logger::print("----------------------------------------");

        for ($i = 0; $i < ceil($issueCount / Config::get("issues_per_file")); $i++) {
            $fileName = "issues_" . formatOutputFileNumber($issueCount, $i) . ".xml";
            Logger::print("===== " . $fileName . " =====");

            $xmlBuilder = new IssuesXmlBuilder(
                $destinationDir . "/" . $fileName,
                $dbManager,
                $issueCoversDir,
                $articleGalleysDir,
                $this->user
            );
            $xmlBuilder->setIteration($i);
            $xmlBuilder->buildXml();
        }

        Logger::print("----------------------------------------");
        Logger::print("Successfully converted {$issueCount} issue(s).");
    }

    /**
     * Converts user CSV data to OJS User XML files
     *
     * @param string $sourceDir Location of CSV files
     * @param string $destinationDir Target directory for XML files
     */
    private function generateUsersXml($sourceDir, $destinationDir, $isTest = false)
    {
        $files = glob($sourceDir . "/*");
        $filesCount = 0;

        Logger::print("Running user CSV-to-XML conversion...");

        foreach ($files as $file) {
            $filesCount += 1;
            $data = csv_to_array($file, ",");

            if (empty($data)) {
                continue;
            }
            $xmlBuilder = new UsersXmlBuilder($isTest, $destinationDir . "/users_{$filesCount}.xml");
            $xmlBuilder->setData($data);
            $xmlBuilder->buildXml();
        }

        Logger::print("Successfully converted {$filesCount} user file(s).");
    }
}

$tool = new csvToXmlConverter(isset($argv) ? $argv : array());
$tool->execute();
