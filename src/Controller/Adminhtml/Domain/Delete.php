<?php

declare(strict_types=1);

namespace OM\Nospam\Controller\Adminhtml\Domain;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultInterface;
use OM\Nospam\Model\DomainFactory;

class Delete extends Action
{
    /**
     * @var \OM\Nospam\Model\DomainFactory
     */
    protected DomainFactory $_domainFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \OM\Nospam\Model\DomainFactory $domainFactory
     */
    public function __construct(
        Context $context,
        DomainFactory $domainFactory
    ) {
        $this->_domainFactory = $domainFactory;
        parent::__construct($context);
    }

    /**
     * @return void
     */
    public function execute(): ResultInterface
    {
        $data = $this->getRequest()->getPostValue();

        if ($data) {
            try {
                $id = (int) $this->getRequest()->getParam('id');
                $domain = $this->_domainFactory->create();

                if ($id) {
                    $domain = $domain->load($id);
                    $domain->delete();
                    $this->messageManager->addSuccess(__("Domain '%1' has been successfully deleted.", $domain->getDomain()));
                }
            } catch (\Exception $e) {
                $this->messageManager->addError(__($e->getMessage()));
            }
        }

        $this->_redirect('*/*/index');
    }

    /**
     * @return bool
     */
    protected function _isAllowed(): bool
    {
        return true;
        //return $this->_authorization->isAllowed('OM_Nospam::delete');
    }
}
