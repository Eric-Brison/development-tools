<?php
/**
 * Created by PhpStorm.
 * User: charles
 * Date: 11/09/14
 * Time: 17:33
 */

namespace Dcp\DevTools\Webinst;


class Webinst {

    protected $inputPath;
    protected $conf;

    public function __construct($inputPath) {
        if (!is_dir($inputPath)) {
            throw new Exception("The input path doesn't exist ($inputPath)");
        }
        $this->inputPath = $inputPath;
        if (!is_file($inputPath . DIRECTORY_SEPARATOR . 'build.json')) {
            throw new Exception("The build.json doesn't exist ($inputPath)");
        }
        $this->conf = json_decode(file_get_contents($inputPath . DIRECTORY_SEPARATOR . 'build.json'), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("The build.json is not a valid JSON file ($inputPath)");
        }
        if (!isset($this->conf["moduleName"])) {
            throw new Exception("The build.json doesn't not contain the module name ($inputPath)");
        }
        if (!isset($this->conf["version"])) {
            throw new Exception("The build.json doesn't not contain the version ($inputPath)");
        }
        if (!isset($this->conf["version"])) {
            throw new Exception("The build.json doesn't not contain the release ($inputPath)");
        }
    }

    public function makeWebinst() {
        $allowedDirectory = array();
        if (isset($this->conf["application"]) && is_array($this->conf["application"])) {
            $allowedDirectory = array_merge($allowedDirectory, $this->conf["application"]);
        }
        if (isset($this->conf["includedPath"]) && is_array($this->conf["includedPath"])) {
            $allowedDirectory = array_merge($allowedDirectory, $this->conf["includedPath"]);
        }
        $contentTar = $this->inputPath.DIRECTORY_SEPARATOR."temp_tar";
        $pharTar = new \PharData($contentTar.".tar");
        $firstLevelIterator = new \DirectoryIterator($this->inputPath);
        foreach ($firstLevelIterator as $fileInfo) {
            /* @var \SplFileInfo $fileInfo */
            if (in_array($fileInfo->getFilename(), $allowedDirectory)) {
                $recursiveDirectoryIterator = new \RecursiveDirectoryIterator(
                    $this->inputPath . DIRECTORY_SEPARATOR . $fileInfo->getFilename(), \FilesystemIterator::SKIP_DOTS);
                $pharTar->buildFromIterator(new \RecursiveIteratorIterator($recursiveDirectoryIterator), $this->inputPath);
            }
        }
        $pharTar->compress(\Phar::GZ);
        unlink($contentTar.".tar");
        $template = new \Mustache_Engine();
        $infoXML = $template->render('{{=@ @=}}'.file_get_contents($this->inputPath.DIRECTORY_SEPARATOR."info.xml"), $this->conf);
        $webinstName = $template->render("{{moduleName}}-{{version}}-{{release}}", $this->conf);
        $pharTar = new \PharData($this->inputPath . DIRECTORY_SEPARATOR . $webinstName.".tar");
        $pharTar->addFromString("info.xml", $infoXML);
        $pharTar->addFile($contentTar.".tar.gz", "content.tar.gz");
        rename($this->inputPath . DIRECTORY_SEPARATOR . $webinstName.".tar", $this->inputPath . DIRECTORY_SEPARATOR . $webinstName . ".webinst");
        unlink($contentTar . ".tar.gz");
    }


} 