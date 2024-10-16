<?php
declare(strict_types=1);

namespace OM\Nospam\Controller\Adminhtml\Domain;

use Magento\Backend\App\Action;
use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\App\ResponseInterface;
use OM\Nospam\Model\DomainFactory;

class Save extends Action
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
    public function execute(): ResponseInterface
    {
        $data = $this->getRequest()->getPostValue();
        $redirect = '*/*/index';

        if ($data) {
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
        } else {
            $redirect = '*/*/add';
        }

        $this->_redirect($redirect);
    }

    /**
     * @return bool
     */
    protected function _isAllowed(): bool
    {
        return $this->_authorization->isAllowed('OM_Nospam::save');
    }
}
