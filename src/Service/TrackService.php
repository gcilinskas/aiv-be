<?php

namespace App\Service;

use App\Entity\File;
use App\Entity\Track;
use App\Factory\FileFactory;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\File\File as HttpFile;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Class TrackService
 */
class TrackService extends BaseService
{
    /**
     * @var FileService
     */
    private $fileService;

    /**
     * @var FileFactory
     */
    private $fileFactory;

    /**
     * @var GoogleCloudService
     */
    private $googleCloudService;

    /**
     * @var string
     */
    private $googleCloudStorageUrl;

    /**
     * @var S3Service
     */
    private $s3Service;

    /**
     * TrackService constructor.
     *
     * @param EntityManagerInterface   $em
     * @param EventDispatcherInterface $dispatcher
     * @param FileService              $fileService
     * @param FileFactory              $fileFactory
     * @param GoogleCloudService       $googleCloudService
     * @param string                   $googleCloudStorageUrl
     * @param S3Service                $s3Service
     */
    public function __construct(
        EntityManagerInterface $em,
        EventDispatcherInterface $dispatcher,
        FileService $fileService,
        FileFactory $fileFactory,
        GoogleCloudService $googleCloudService,
        string $googleCloudStorageUrl,
        S3Service $s3Service
    ) {
        parent::__construct($em, $dispatcher);
        $this->fileService = $fileService;
        $this->fileFactory = $fileFactory;
        $this->googleCloudService = $googleCloudService;
        $this->googleCloudStorageUrl = $googleCloudStorageUrl;
        $this->s3Service = $s3Service;
    }

    /**
     * @return string
     */
    public function getEntityClass(): string
    {
        return Track::class;
    }

    /**
     * @param Track        $track
     * @param UploadedFile $audioFile
     *
     * @return Track
     * @throws Exception
     */
    public function createWithFile(Track $track, UploadedFile $audioFile): Track
    {
        $tmpPath = $this->fileService->saveTmpUploadedFile($audioFile);
        $audioFile = $this->fileService->createAndUpload($tmpPath, File::TYPE_AUDIO);

        return $this->update($track->addFile($audioFile));
    }

    /**
     * @param Track $entity
     * @param bool  $flush
     *
     * @return Track
     * @throws Exception
     */
    public function update($entity, bool $flush = true): Track
    {
        $entity->setUpdatedAt(new DateTime());

        return parent::update($entity, $flush);
    }

    public function formatWords(Track $track): array
    {
        return $track->getFileByType(File::TYPE_VOCABULARY)
            ? $this->matchWordsWithLyrics($track)
            : $this->formatWordsFromAws($track);
    }


    public function matchWordsWithLyrics(Track $track)
    {
        $words = [];

        $exactLyrics = [];

        $vocabularyUrl = $track->getFileByType(File::TYPE_VOCABULARY)->getUrl();
        $userLyrics = explode(' ', file_get_contents($vocabularyUrl));

        $awsTranscriptionUrl = $track->getFileByType(File::TYPE_TRANSCRIPTION)->getUrl();

        $transcriptionJson = json_decode(file_get_contents($awsTranscriptionUrl), true);
        $awsLyrics = $transcriptionJson['results']['items'];

        //get time sequence
        // fit lyrics in time

        $indexedAwsResult = [];
        $times = [];

        $awsTimes = [];
        foreach ($awsLyrics as $awsItem) {
            $indexedAwsResult[] = $awsItem;
            $times[] = [
                'startTime' => $awsItem['start_time'],
                'endTime' => $awsItem['end_time'],
            ];
        }

        $wordDurations = [];
        $timesToNext = [];
        foreach ($indexedAwsResult as $index => $item) {
            $timeToNext = isset($indexedAwsResult[$index + 1])
                ? $indexedAwsResult[$index + 1]['start_time'] - $item['end_time']
                : null;


            $awsTimes[] = [
                'startTime' => $item['start_time'],
                'endTime' => $item['end_time'],
                'duration' => $item['end_time'] - $item['start_time'],
                'timeToNext' => $timeToNext
            ];

            $duration = $item['end_time'] - $item['start_time'];

            if ($duration < 10) {
                $wordDurations[] = $duration;
            }

            if ($timeToNext < 10) {
                $timesToNext[] = $timeToNext;
            }
        }

        $blockManual = [];
        $lyricIndex = 0;
        $end = false;

        // reikia dar 183 elementus sudeti
        foreach ($awsTimes as $index => $time) {
            if (!isset($awsTimes[$index+1])) {
                break;
            }

            if ($awsTimes[$index+1]['timeToNext'] > 3)  {
                //add three elements
                for ($i = 0; $i < 3; $i++) {
                    if (isset($userLyrics[$lyricIndex])) {
                        $blockManual[$index][] = $userLyrics[$lyricIndex];
                        $lyricIndex++;
                    } else {
                        $end = true;
                    }
                }

                if ($end) {
                    break;
                }

            } else if ($awsTimes[$index+1]['timeToNext'] > 0.5)  {
                //add three elements
                for ($i = 0; $i < 2; $i++) {
                    if (isset($userLyrics[$lyricIndex])) {
                        $blockManual[$index][] = $userLyrics[$lyricIndex];
                        $lyricIndex++;
                    } else {
                        $end = true;
                    }
                }

                if ($end) {
                    break;
                }

            } elseif ($awsTimes[$index+1]['timeToNext'] > 0.1) {
                //add two elements
                for ($i = 0; $i < 1; $i++) {
                    if (isset($userLyrics[$lyricIndex])) {
                        $blockManual[$index][] = $userLyrics[$lyricIndex];
                        $lyricIndex++;
                    } else {
                        $end = true;
                    }
                }

                if ($end) {
                    break;
                }
            } else {
                //add one elements
                for ($i = 0; $i < 1; $i++) {
                    if (isset($userLyrics[$lyricIndex])) {
                        $blockManual[$index][] = $userLyrics[$lyricIndex];
                        $lyricIndex++;
                    } else {
                        $end = true;
                    }
                }

                if ($end) {
                    break;
                }
            }
        }

        $blockWithTimes = [];
        foreach ($blockManual as $index => $block) {
            $wordLine = '';
            foreach ($block as $word) {
                $wordLine .= $word . " ";
            }

            $blockWithTimes[] = [
                'startTime' => $indexedAwsResult[$index]['start_time'],
                'endTime' => $indexedAwsResult[$index]['start_time'],
                'word' => $wordLine
            ];
        }


        return $blockWithTimes;

        var_dump($blockManual);
        die();


        die();
        $chunk = count($indexedAwsResult);

        // Reiketu chunkinti PAGAL elemento TRUKME
        $result = $this->partition($userLyrics, $chunk);

        var_dump($result);

        die();

        $differenceInAmount = ceil(count($userLyrics) / count($awsLyrics));

        $wordsByTime = [];

        $ceilBlocks = array_chunk($userLyrics, $differenceInAmount);

        //split three blocks into two until it reaches block amount of awsresult size
        //calcluate difference on how many blocks to push;

        // TO MATCH AWS RESULT COUNT I HAVE TO ADD THIS AMOUNT OF BLOCKS
        $blocksToPush = count($indexedAwsResult) - count($ceilBlocks);

        $matchedAmountBlock = [];

        for ($index = 0; $index === $blocksToPush - 1; $index++) {
            $forward = $ceilBlocks[$index][2];
            unset($ceilBlocks[$index][2]);

            $matchedAmountBlock[] = $ceilBlocks[$index];


        }

        $counter = 0;
        while (count($minBlocks) === count($indexedAwsResult)) {
            $counter++;

            foreach ($minBlocks as $blockIndex => $block) {
                foreach ($block as $blockItemIndex => $blockItem) {
                    if ($blockItemIndex + 1 === count($block)) {
                        $pushedForward = $minBlocks[$blockIndex][$blockItemIndex];
                        unset($minBlocks[$blockIndex][$blockItemIndex]);


                    }
                }
            }

        }
        $minBlocks = ceil($differenceInAmount);
        $chunked_array = array_chunk($userLyrics, $minBlocks, true);
        $chunked_array = array_chunk($userLyrics, $differenceInAmount, true);


        $currentIndex = 0;
        // get all user lyrics
        foreach ($userLyrics as $userLyricsIndex => $userLyric) {
            if ($currentIndex === count($indexedAwsResult)) {
                break;
            }

            //loop through aws to get time
            foreach ($indexedAwsResult as $awsIndex => $awsItem) {
                foreach ($awsItem['alternatives'] as $alternativeWord) {

                    if (trim(strtolower($alternativeWord['content'])) == trim(strtolower($userLyric))) {
                        // FOUND MATCH!

                        // Gali buti, kad cia yra matchu daugiau, jie sedi per keleta indexu kitur, ir jie yra teisingi
                        // tokiu atveju reikia tikrinti 10 indexu i prieki ir 10 indexu atgal, ir paimti artimiausia nuo dabar ir kad nesipjautu su kitais

                        // if index is the next element from aws result(must to keep to the sequence to get correct times)
                        if ($awsIndex === $currentIndex) {
                            // EXACT MATCH
                            $exactLyrics[$currentIndex] = [
                                'word' => $alternativeWord['content'],
                                'startTime' => number_format(floatval($awsItem['start_time']), 2),
                                'endTime' => number_format(floatval($awsItem['end_time']), 2),
                            ];
                        } else {
                            // MOST LIKELY MATCH, A FEW INDEXES OFF ORIGINAL
                            $indexFinder = -40;
//temp
                            $exactLyrics[$currentIndex] = [
                                'word' => $alternativeWord['content'],
                                'startTime' => number_format(floatval($awsItem['start_time']), 2),
                                'endTime' => number_format(floatval($awsItem['end_time']), 2),
                            ];

//                            while ($indexFinder < 40) {
//                                $indexFinder++;
//                                $currentIndexFinder = $currentIndex + $indexFinder;
//                                if (isset($indexedAwsResult[$currentIndexFinder])) {
////                                    var_dump('MOST LIKELY');
//                                    foreach ($indexedAwsResult[$currentIndexFinder]['alternatives'] as $alternativeFinder) {
//                                        if (trim(strtolower($alternativeFinder['content'])) == trim(strtolower($userLyric))) {
//                                            // WE FOUND MATCH IN NEAR INDEX!
//                                            $exactLyrics[$currentIndex] = [
//                                                'word' => $alternativeFinder['content'],
//                                                'startTime' => number_format(floatval($indexedAwsResult[$currentIndexFinder]['start_time']), 2),
//                                                'endTime' => number_format(floatval($indexedAwsResult[$currentIndexFinder]['end_time']), 2),
//                                            ];
//                                        }
//                                    }
//                                }
//                            }

                            // Word was not found in closest indexes, add without time :(
//                            $exactLyrics[$currentIndex] = [
//                                'word' => $alternativeWord['content']
//                            ];
                        }
                    }

                    // if word alternative doesnt match, count time average to split time and fit

                    // MATCH NOT FOUND AT ALL :(
                    if (!isset($exactLyrics[$currentIndex]['word'])) {
                        // did not find any matching words, then just add original word instead and without time
                        $exactLyrics[$currentIndex]['word'] = $userLyric;
                    }
                }


            }
            $currentIndex++;
        }

//        $indexesWithoutTimes = [];
//        $correctMatches = [];
//        foreach ($exactLyrics as $index => $exactLyric) {
//            if (!isset($exactLyric['startTime'])) {
//                // there was no time match
//                // get average difference between words and set it
//
//                $indexesWithoutTimes[] = $index;
//            } else {
//                $correctMatches[] = $index;
//            }
//        }

        return $words;
    }

    /**
     * @param array $list
     * @param int $p
     * @link http://www.php.net/manual/en/function.array-chunk.php#75022
     */
    public function partition(array $list, $p)
    {
        $listlen = count($list);
        $partlen = floor($listlen / $p);
        $partrem = $listlen % $p;
        $partition = array();
        $mark = 0;
        for($px = 0; $px < $p; $px ++) {
            $incr = ($px < $partrem) ? $partlen + 1 : $partlen;
            $partition[$px] = array_slice($list, $mark, $incr);
            $mark += $incr;
        }

        return $partition;
    }
}
