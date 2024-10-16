<?php
declare(strict_types=1);

namespace OM\Nospam\Plugin;

use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Framework\App\ProductMetadataInterface;
use OM\Nospam\Ui\DataProvider\Blacklist\ListingDataProvider;
use Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult;

class AddAttributesToUiDataProvider
{
    /**
     * @var \Magento\Eav\Api\AttributeRepositoryInterface
     */
    protected AttributeRepositoryInterface $_attributeRepository;

    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    protected ProductMetadataInterface $_productMetadata;

    /**
     * @param \Magento\Eav\Api\AttributeRepositoryInterface $attributeRepository
     * @param \Magento\Framework\App\ProductMetadataInterface $productMetadata
     */
    public function __construct(
        AttributeRepositoryInterface $attributeRepository,
        ProductMetadataInterface $productMetadata
    ) {
        $this->_attributeRepository = $attributeRepository;
        $this->_productMetadata = $productMetadata;
    }

    /**
     * @param \OM\Nospam\Ui\DataProvider\Blacklist\ListingDataProvider $subject
     * @param \Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult $result
     * @return \Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function afterGetSearchResult(ListingDataProvider $subject, SearchResult $result): SearchResult
    {
        if ($result->isLoaded()) {
            return $result;
        }

        $edition = $this->_productMetadata->getEdition();

        $column = 'entity_id';

        if ($edition == 'Enterprise') {
            $column = 'row_id';
        }

        $attribute = $this->_attributeRepository->get('catalog_category', 'name');

        $result->getSelect()->joinLeft(
            ['devgridname' => $attribute->getBackendTable()],
            'devgridname.' . $column . ' = main_table.' . $column . ' AND devgridname.attribute_id = '
            . $attribute->getAttributeId(),
            ['name' => 'devgridname.value']
        );

        $result->getSelect()->where('devgridname.value LIKE "B%"');
        return $result;
    }
}