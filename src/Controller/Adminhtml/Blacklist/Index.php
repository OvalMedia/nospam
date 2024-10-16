<?php
declare(strict_types=1);

namespace OM\Nospam\Controller\Adminhtml\Blacklist;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\View\Result\Page;

class Index extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected PageFactory $_resultPageFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->_resultPageFactory = $resultPageFactory;
    }

    /**
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute(): Page
    {
        $resultPage = $this->_resultPageFactory->create();
        $resultPage->setActiveMenu('OM_Nospam::blacklist');
        $resultPage->getConfig()->getTitle()->prepend(__('Blacklist'));
        return $resultPage;
    }

    /**
     * @return bool
     */
    protected function _isAllowed(): bool
    {
        return $this->_authorization->isAllowed('OM_Nospam::blacklist');
    }
}