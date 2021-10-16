<?php

declare(strict_types=1);

namespace Eskimi\CommissionTask\Service;

class DataProcessor
{
    private $arguments;
    private $fileName;
    public $errors = [];
    public $data = [];

    const ACCEPTED_ARGUMENT_COUNT = 2;
    const FILENAME_POSITION = 1;
    const TOO_MANY_ARGUMENTS_MSG = 'Too Many Arguments.';
    const INVALID_FILE_EXTENSION_MSG = 'Invalid File Extension.';

    /**
     * @param $arguments
     */
    public function __construct($arguments)
    {
        $this->arguments = $arguments;
    }

    public function checkArguments()
    {
        $argumentCount = count($this->arguments);

        if ($argumentCount > self::ACCEPTED_ARGUMENT_COUNT) {
            throw new \Exception(self::TOO_MANY_ARGUMENTS_MSG);
        }

        self::setFileName($this->arguments[$argumentCount - self::FILENAME_POSITION]);

        if (!self::checkFileExtension(self::getFileName())) {
            throw new \Exception(self::INVALID_FILE_EXTENSION_MSG);
        }

        return $this;
    }

    public function setFileName($fileName)
    {
        $this->fileName = $fileName;
    }

    public function getFileName(): string
    {
        return $this->fileName;
    }

    private function checkFileExtension(string $fileName): bool
    {
        $fileNameArray = explode('.', $fileName);
        $lastIndexOfFileNameArray = count($fileNameArray) - 1;

        return in_array($fileNameArray[$lastIndexOfFileNameArray], ['csv', 'CSV']);
    }

    public function loadDataFromFile()
    {
        $this->data = file(self::getFileName(), FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($this->data as $data) {
            $explodedData = explode(',', $data);

            if (count($explodedData) !== 6 && !in_array('', $explodedData)) {
                throw new \Exception('Malformed Data.');
            }
        }
    }
}