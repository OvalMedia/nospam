<?php
declare(strict_types=1);

namespace OM\Nospam\Controller\Adminhtml\Domain;

use Magento\Backend\App\Action;
use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\Controller\ResultInterface;
use OM\Nospam\Model\DomainFactory;

class Add extends Action
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
        Context $context,
        Registry $coreRegistry,
        DomainFactory $domainFactory
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->_domainFactory = $domainFactory;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute(): ResultInterface
    {
        $id = (int) $this->getRequest()->getParam('id');
        $domain = $this->_domainFactory->create();

        if ($id) {
            $domain = $domain->load($id);
            $title = $domain->getTitle();

            if (!$domain->getId()) {
                $this->messageManager->addError(__('Domain no longer exist.'));
                $this->_redirect('*/*/rowdata');
                //return;
            }
        }

        $this->_coreRegistry->register('domain', $domain);
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
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
