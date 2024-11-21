<?php

namespace OM\Nospam\Model;

use OM\Nospam\Api\LogRepositoryInterface;
use OM\Nospam\Api\Data\LogInterface;
use OM\Nospam\Model\ResourceModel\Log as LogResource;
use OM\Nospam\Model\ResourceModel\Log\CollectionFactory;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterface;
use Magento\Framework\Api\SearchResultsFactory;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

class LogRepository implements LogRepositoryInterface
{
    protected $_logResource;
    protected $_logFactory;
    protected $_collectionFactory;
    protected $_searchResultsFactory;

    /**
     * @param \OM\Nospam\Model\ResourceModel\Log $logResource
     * @param \OM\Nospam\Model\LogFactory $logFactory
     * @param \OM\Nospam\Model\ResourceModel\Log\CollectionFactory $collectionFactory
     * @param \Magento\Framework\Api\SearchResultsFactory $searchResultsFactory
     */
    public function __construct(
        LogResource $logResource,
        LogFactory $logFactory,
        CollectionFactory $collectionFactory,
        SearchResultsFactory $searchResultsFactory
    ) {
        $this->_logResource = $logResource;
        $this->_logFactory = $logFactory;
        $this->_collectionFactory = $collectionFactory;
        $this->_searchResultsFactory = $searchResultsFactory;
    }

    /**
     * @param \OM\Nospam\Api\Data\LogInterface $log
     * @return \OM\Nospam\Api\Data\LogInterface
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function save(LogInterface $log): LogInterface
    {
        try {
            $this->_logResource->save($log);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__('Could not save the log: %1', $exception->getMessage()));
        }
        return $log;
    }

    /**
     * @param int $id
     * @return \OM\Nospam\Api\Data\LogInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById(int $id): LogInterface
    {
        $entity = $this->_logFactory->create();
        $this->_logResource->load($entity, $id);

        if (!$entity->getId()) {
            throw new NoSuchEntityException(__('Log with id "%1" does not exist.', $id));
        }

        return $entity;
    }

    /**
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Magento\Framework\Api\SearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria): SearchResultsInterface
    {
        $collection = $this->_collectionFactory->create();
        $searchResults = $this->_searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);

        foreach ($collection as $entity) {
            $searchResults->getItems()[] = $entity;
        }

        return $searchResults;
    }

    /**
     * @param \OM\Nospam\Api\Data\LogInterface $log
     * @return true
     */
    public function delete(LogInterface $log)
    {
        try {
            $this->_logResource->delete($log);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__('Could not delete the log: %1', $exception->getMessage()));
        }
        return true;
    }

    /**
     * @param int $id
     * @return true
     */
    public function deleteById(int $id)
    {
        return $this->delete($this->getById($id));
    }
}