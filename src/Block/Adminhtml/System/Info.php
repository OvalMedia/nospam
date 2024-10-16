<?php
declare(strict_types=1);

namespace OM\Nospam\Block\Adminhtml\System;

use Magento\Config\Block\System\Config\Form\Field\Heading;
use Magento\Backend\Block\Context;
use Magento\Framework\Module\PackageInfoFactory;
use Magento\Framework\Data\Form\Element\AbstractElement;

class Info extends Heading
{
    /**
     * @var \Magento\Framework\Module\PackageInfoFactory
     */
    protected PackageInfoFactory $_packageInfoFactory;

    /**
     * @param \Magento\Backend\Block\Context $context
     * @param \Magento\Framework\Module\PackageInfoFactory $packageInfoFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        PackageInfoFactory $packageInfoFactory,
        array $data = []
    ) {
        $this->_packageInfoFactory = $packageInfoFactory;
        parent::__construct($context, $data);
    }

    /**
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return \Magento\Framework\Phrase|string
     */
    public function render(AbstractElement $element)
    {
        return __($element->getComment());
    }
}