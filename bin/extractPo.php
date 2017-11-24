<?php

require_once "initializeAutoloader.php";

use Ulrichsg\Getopt\Getopt;
use Ulrichsg\Getopt\Option;

use Dcp\DevTools\ExtractPo\IncludePo;
use Dcp\DevTools\ExtractPo\ApplicationPo;
use Dcp\DevTools\ExtractPo\JavascriptPo;
use Dcp\DevTools\ExtractPo\FamilyPo;
use Dcp\DevTools\ExtractPo\ModulePo;

$getopt = new Getopt(array(
    (new Option('s', 'sourcePath', Getopt::REQUIRED_ARGUMENT))->setDescription('path of the module (needed)')->setValidation(function ($path) {
        if (!is_dir($path)) {
            print "The path of the module ($path)";
            return false;
        }
        return true;
    }),
    (new Option('o', 'outputPath', Getopt::OPTIONAL_ARGUMENT))->setDescription('path of the locale output') ,
    (new Option('h', 'help', Getopt::NO_ARGUMENT))->setDescription('show the usage message'),
));

try {
    $getopt->parse();

    $error = array();

    if (!isset($getopt['sourcePath'])) {
        $error[] = "You need to set the path of the module -s or --sourcePath";
    }

    if (!empty($error)) {
        echo join("\n", $error);
        echo "\n" . $getopt->getHelpText();
        exit(42);
    }

    $extractor = new ModulePo($getopt['sourcePath'], $getopt['outputPath']);
    $extractor->extractPo();

    $extractor = new IncludePo($getopt['sourcePath'], $getopt['outputPath']);
    $extractor->extractPo();

    $extractor = new ApplicationPo($getopt['sourcePath'], $getopt['outputPath']);
    $extractor->extractPo();

    $extractor = new JavascriptPo($getopt['sourcePath'], $getopt['outputPath']);
    $extractor->extractPo();

    $extractor = new FamilyPo($getopt['sourcePath'], $getopt['outputPath']);
    $extractor->extractPo();
} catch (UnexpectedValueException $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo $getopt->getHelpText();
    exit(1);
}
