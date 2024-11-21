<?php

namespace OM\Nospam\Api;

use OM\Nospam\Api\Data\LogInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterface;

interface LogRepositoryInterface
{
    /**
     * @param \OM\Nospam\Api\Data\LogInterface $log
     * @return mixed
     */
    public function save(LogInterface $log): LogInterface;

    /**
     * @param int $id
     * @return \OM\Nospam\Api\Data\LogInterface
     */
    public function getById(int $id): LogInterface;

    /**
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Magento\Framework\Api\SearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria): SearchResultsInterface;

    /**
     * @param \OM\Nospam\Api\Data\LogInterface $log
     * @return mixed
     */
    public function delete(LogInterface $log);

    /**
     * @param int $id
     * @return mixed
     */
    public function deleteById(int $id);
}