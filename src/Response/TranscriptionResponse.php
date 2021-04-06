<?php

namespace App\Response;

use App\Entity\Track;

/**
 * Class TranscriptionResponse
 */
class TranscriptionResponse
{
    /**
     * @param $url
     *
     * @return array
     */
    public static function formatFromUrl(Track $track): array
    {
        $response = [];
        $diff = [];
        $newDiff = [];
        $json = file_get_contents($url);
        $jsonFile = json_decode($json, true);
        $items = $jsonFile['results']['items'];

        $diffSum = 0;

        foreach ($items as $index => $item) {
            $response[] = [
                'startTime' => number_format(floatval($item['start_time']), 2),
                'endTime' => number_format(floatval($item['end_time']), 2),
                'word' => $item['alternatives'][0]['content']
            ];
        }

        foreach ($response as $index => $item) {
            if ($index === 0) {
                continue;
            }

            $differnece = floatval($item['startTime']) - floatval($response[$index-1]['endTime']);
            $diff[] = $differnece;
            $diffSum += $differnece;
        }

        $count = count($diff);
        $avgDiff = $diffSum / $count;

        $word = '';

        $blockCounter = 0;

        $createBlock = [];

        foreach ($response as $index => $item) {
            if ($index === 0) {
                $startTime =  number_format(floatval($item['startTime']), 2);
                $createBlock[$blockCounter]['startTime'] =  $startTime;
                $createBlock[$blockCounter]['word'] = $item['word'];

                continue;
            }

            $newDif[$index] = floatval($item['startTime']) - floatval($response[$index-1]['endTime']);

            if ($newDif[$index] > $avgDiff) {
                //build new block
                $blockCounter++;
                $startTime =  number_format(floatval($item['startTime']), 2);
                $createBlock[$blockCounter]['startTime'] =  $startTime;
                $createBlock[$blockCounter]['word'] = $item['word'];

                //set end time for previous block
                $createBlock[$blockCounter - 1]['endTime'] = $response[$index - 1]['endTime'];

            } else {

                if (strlen($createBlock[$blockCounter]['word']) > 60) {
                    //build new block
                    $blockCounter++;
                    $startTime =  number_format(floatval($item['startTime']), 2);
                    $createBlock[$blockCounter]['startTime'] =  $startTime;
                    $createBlock[$blockCounter]['word'] = $item['word'];

                    //set end time for previous block
                    $createBlock[$blockCounter - 1]['endTime'] = $response[$index - 1]['endTime'];
                    continue;
                }

                // add up words to block
                $createBlock[$blockCounter]['word'] .= ' ' . $item['word'];
            }

            if ($index + 1 === count($items)) {
                //set end time for last block
                $createBlock[$blockCounter]['endTime'] =  $response[$blockCounter]['endTime'];
            }
        }


        return ['blocks' => $createBlock, 'transcript' => $jsonFile['results']['transcripts'][0]];
    }
}
