<?php

namespace OM\Nospam\Model;

use OM\Nospam\Api\DomainRepositoryInterface;
use OM\Nospam\Api\Data\DomainInterface;
use OM\Nospam\Model\ResourceModel\Domain as DomainResource;
use OM\Nospam\Model\ResourceModel\Domain\CollectionFactory;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterface;
use Magento\Framework\Api\SearchResultsFactory;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

class DomainRepository implements DomainRepositoryInterface
{
    /**
     * @var \OM\Nospam\Model\ResourceModel\Domain
     */
    protected $_resource;

    /**
     * @var \OM\Nospam\Model\DomainFactory
     */
    protected $_domainFactory;

    /**
     * @var \OM\Nospam\Model\ResourceModel\Domain\CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * @var \Magento\Framework\Api\SearchResultsFactory
     */
    protected $_searchResultsFactory;

    /**
     * @param \OM\Nospam\Model\ResourceModel\Domain $resource
     * @param \OM\Nospam\Model\DomainFactory $domainFactory
     * @param \OM\Nospam\Model\ResourceModel\Domain\CollectionFactory $collectionFactory
     * @param \Magento\Framework\Api\SearchResultsFactory $searchResultsFactory
     */
    public function __construct(
        DomainResource $resource,
        DomainFactory $domainFactory,
        CollectionFactory $collectionFactory,
        SearchResultsFactory $searchResultsFactory
    ) {
        $this->_resource = $resource;
        $this->_domainFactory = $domainFactory;
        $this->_collectionFactory = $collectionFactory;
        $this->_searchResultsFactory = $searchResultsFactory;
    }

    /**
     * @param \OM\Nospam\Api\Data\DomainInterface $log
     * @return \OM\Nospam\Api\Data\DomainInterface
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function save(DomainInterface $log)
    {
        try {
            $this->_resource->save($log);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__('Could not save the log: %1', $exception->getMessage()));
        }
        return $log;
    }

    /**
     * @param int $id
     * @return \OM\Nospam\Api\Data\DomainInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById(int $id): DomainInterface
    {
        $entity = $this->_domainFactory->create();
        $this->_resource->load($entity, $id);

        if (!$entity->getId()) {
            throw new NoSuchEntityException(__('Domain with id "%1" does not exist.', $id));
        }

        return $entity;
    }

    /**
     * @param string $name
     * @return \OM\Nospam\Api\Data\DomainInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getByName(string $name): DomainInterface
    {
        $entity = $this->_domainFactory->create();
        $this->_resource->load($entity, $name, DomainInterface::NAME);

        if (!$entity->getId()) {
            throw new NoSuchEntityException(__('Domain with name "%1" does not exist.', $name));
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
     * @param \OM\Nospam\Api\Data\DomainInterface $log
     * @return true
     */
    public function delete(DomainInterface $log): true
    {
        try {
            $this->_resource->delete($log);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__('Could not delete the log: %1', $exception->getMessage()));
        }
        return true;
    }

    /**
     * @param int $id
     * @return true
     */
    public function deleteById(int $id): true
    {
        return $this->delete($this->getById($id));
    }
}