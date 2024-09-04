<?php
namespace OM\Nospam\Block\Adminhtml\System;

class Info extends \Magento\Config\Block\System\Config\Form\Field\Heading
{
    /**
     * @var \Magento\Framework\Module\PackageInfoFactory
     */
    protected \Magento\Framework\Module\PackageInfoFactory $_packageInfoFactory;

    /**
     * @param \Magento\Backend\Block\Context $context
     * @param \Magento\Framework\Module\PackageInfoFactory $packageInfoFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Framework\Module\PackageInfoFactory $packageInfoFactory,
        array $data = []
    ) {
        $this->_packageInfoFactory = $packageInfoFactory;
        parent::__construct($context, $data);
    }

    /**
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     *
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        return __($element->getComment());
    }
}