<?php

namespace OM\Nospam\Api;

use OM\Nospam\Api\Data\DomainInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterface;

interface DomainRepositoryInterface
{
    /**
     * @param \OM\Nospam\Api\Data\DomainInterface $log
     * @return mixed
     */
    public function save(DomainInterface $log);

    /**
     * @param int $id
     * @return \OM\Nospam\Api\Data\DomainInterface
     */
    public function getById(int $id): DomainInterface;

    /**
     * @param string $name
     * @return \OM\Nospam\Api\Data\DomainInterface
     */
    public function getByName(string $name): DomainInterface;

    /**
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Magento\Framework\Api\SearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria): SearchResultsInterface;

    /**
     * @param \OM\Nospam\Api\Data\DomainInterface $log
     * @return mixed
     */
    public function delete(DomainInterface $log);

    /**
     * @param int $id
     * @return mixed
     */
    public function deleteById(int $id);
}