<?php

namespace App\Util;

use App\Entity\File;

/**
 * Class AWS
 */
class AWS
{
    const AUDIO_DIR = 'audio/';
    const VIDEO_DIR = 'video/';
    const TRANSCRIPTIONS_DIR = 'transcriptions/';
    const VOCABULARY_DIR = 'vocabulary/';
    const URI_PREFIX = 's3://';

    /**
     * @param string $filename
     *
     * @return string
     */
    public static function tracksPath(string $filename): string
    {
        return self::AUDIO_DIR . $filename;
    }

    /**
     * @param string $filename
     *
     * @return string
     */
    public static function transcriptionPath(string $filename): string
    {
        return self::TRANSCRIPTIONS_DIR . $filename;
    }

    /**
     * @param string $filename
     *
     * @return string
     */
    public static function vocabularyPath(string $filename): string
    {
        return self::VOCABULARY_DIR . $filename;
    }

    /**
     * @param File $file
     *
     * @return string
     */
    public static function formatS3Uri(File $file): string
    {
        return self::URI_PREFIX . $_ENV['AWS_BUCKET'] . '/' . self::formatFolderWithFilename($file);
    }

    /**
     * @param File $file
     *
     * @return string
     */
    public static function formatFolderWithFilename(File $file): string
    {
        return self::getFolderByFile($file) . $file->getFilename();
    }

    /**
     * @param File $file
     *
     * @return string
     */
    public static function getFolderByFile(File $file): string
    {
        switch ($file->getType()) {
            case File::TYPE_AUDIO:
                return self::AUDIO_DIR;
            case File::TYPE_TRANSCRIPTION:
                return self::TRANSCRIPTIONS_DIR;
            case File::TYPE_VOCABULARY:
                return self::VOCABULARY_DIR;
            case File::TYPE_VIDEO:
                return self::VIDEO_DIR;
        }

        return '/';
    }

    /**
     * @param string $url
     * @param string $ext
     *
     * @return array
     */
    public static function getWordsFromAwsTranscriptionFile(string $url, string $ext): array
    {
        $words = [];
        if ($ext === 'json') {
            $jsonFile = json_decode(file_get_contents($url), true);
            $items = $jsonFile['results']['items'];
            $indexedItems = [];

            foreach ($items as $item) {
                $indexedItems[] = $item;
            }


            //TODO isspresti kaip punctuation paduoti
            foreach ($indexedItems as $index => $item) {
                $words[] = [
                    'startTime' => isset($item['start_time'])
                        ? number_format(floatval($item['start_time']), 2)
                        : number_format(floatval($indexedItems[$index - 1]['start_time']), 2),
                    'endTime' => isset($item['end_time'])
                        ? number_format(floatval($item['end_time']), 2)
                        : number_format(floatval($indexedItems[$index - 1]['end_time']), 2),
                    'word' => $item['alternatives'][0]['content']
                ];
            }
        }

        return $words;
    }
}
