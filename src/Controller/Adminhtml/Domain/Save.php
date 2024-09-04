<?php

namespace OM\Nospam\Controller\Adminhtml\Domain;

class Save extends \Magento\Backend\App\Action
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
            $this->_redirect('*/*/add');
            return;
        }

        try {
            $domain = $this->_domainFactory->create();
            $domainname = trim($data['domain']);
            $res = preg_match('/^(?:(?:https?|ftp):\/\/)?(?:www\.)?([a-zA-Z0-9-]+(?:\.[a-zA-Z]{2,})+)$/i', $domainname);

            if ($res !== 1) {
                $this->messageManager->addError(__("'%1' is not a valid domain name", $domainname));
            } else {
                $domain->setDomain($domainname);

                if (isset($data['entity_id'])) {
                    $domain->setId($data['entity_id']);
                }

                $domain->save();
                $this->messageManager->addSuccess(__('Domain has been successfully saved.'));
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
        return $this->_authorization->isAllowed('OM_Nospam::save');
    }
}
