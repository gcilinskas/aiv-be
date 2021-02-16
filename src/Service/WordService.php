<?php

namespace App\Service;

use App\Entity\Word;
use App\Repository\WordRepository;

/**
 * Class WordService
 */
class WordService extends BaseService
{
    /**
     * @var WordRepository
     */
    protected $repository;

    /**
     * @return string
     */
    public function getEntityClass(): string
    {
        return Word::class;
    }
}
