<?php

namespace OM\Nospam\Plugin;

class AddAttributesToUiDataProvider
{
    /**
     * @var \Magento\Eav\Api\AttributeRepositoryInterface
     */
    protected \Magento\Eav\Api\AttributeRepositoryInterface $_attributeRepository;

    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    protected \Magento\Framework\App\ProductMetadataInterface $_productMetadata;

    /**
     * @param \Magento\Eav\Api\AttributeRepositoryInterface $attributeRepository
     * @param \Magento\Framework\App\ProductMetadataInterface $productMetadata
     */
    public function __construct(
        \Magento\Eav\Api\AttributeRepositoryInterface $attributeRepository,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata
    ) {
        $this->_attributeRepository = $attributeRepository;
        $this->_productMetadata = $productMetadata;
    }

    /**
     * Get Search Result after plugin
     *
     * @param CategoryDataProvider $subject
     * @param SearchResult $result
     *
     * @return SearchResult
     */
    public function afterGetSearchResult(\OM\Nospam\Ui\DataProvider\Blacklist\ListingDataProvider $subject, \Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult $result)
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