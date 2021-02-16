<?php
namespace App\Response;

/**
 * Interface ResponseData
 * @package App\Response
 */
interface ResponseData
{
    /**
     * creating json api array
     * for response
     *
     * @return array
     */
    public function getResponseArray(): array;
}