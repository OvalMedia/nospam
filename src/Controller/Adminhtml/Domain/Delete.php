<?php

namespace OM\Nospam\Controller\Adminhtml\Domain;

class Delete extends \Magento\Backend\App\Action
{
    /**
     * @var \OM\Nospam\Model\DomainFactory
     */
    protected \OM\Nospam\Model\DomainFactory $_domainFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \OM\Nospam\Model\DomainFactory $domainFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \OM\Nospam\Model\DomainFactory $domainFactory
    ) {
        $this->_domainFactory = $domainFactory;
        parent::__construct($context);
    }

    /**
     * @return void
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();

        if (!$data) {
            $this->_redirect('*/*/index');
            return;
        }

        try {
            $id = (int) $this->getRequest()->getParam('id');
            $domain = $this->_domainFactory->create();

            /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
            if ($id) {
                $domain = $domain->load($id);
                $domain->delete();
                $this->messageManager->addSuccess(__("Domain '%1' has been successfully deleted.", $domain->getDomain()));
            }
        } catch (\Exception $e) {
            $this->messageManager->addError(__($e->getMessage()));
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
