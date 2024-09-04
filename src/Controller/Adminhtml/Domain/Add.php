<?php

namespace OM\Nospam\Controller\Adminhtml\Domain;

use Magento\Framework\Controller\ResultFactory;

class Add extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected \Magento\Framework\Registry $_coreRegistry;

    /**
     * @var \OM\Nospam\Model\DomainFactory
     */
    protected \OM\Nospam\Model\DomainFactory $_domainFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \OM\Nospam\Model\DomainFactory $domainFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \OM\Nospam\Model\DomainFactory $domainFactory
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->_domainFactory = $domainFactory;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Page|\Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|\Magento\Framework\View\Result\Page|(\Magento\Framework\View\Result\Page&\Magento\Framework\Controller\ResultInterface)|void
     */
    public function execute()
    {
        $id = (int) $this->getRequest()->getParam('id');
        $domain = $this->_domainFactory->create();

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        if ($id) {
            $domain = $domain->load($id);
            $title = $domain->getTitle();

            if (!$domain->getId()) {
                $this->messageManager->addError(__('Domain no longer exist.'));
                $this->_redirect('*/*/rowdata');
                return;
            }
        }

        $this->_coreRegistry->register('domain', $domain);
        $resultPage = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_PAGE);
        $title = $id ? __('Edit Domain: "%1"', $title) : __('Add new Domain');
        $resultPage->getConfig()->getTitle()->prepend($title);
        return $resultPage;
    }

    /**
     * @return bool
     */
    protected function _isAllowed(): bool
    {
        return $this->_authorization->isAllowed('OM_Nospam::add_row');
    }
}
