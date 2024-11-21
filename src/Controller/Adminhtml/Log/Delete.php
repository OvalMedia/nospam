<?php
declare(strict_types=1);

namespace OM\Nospam\Controller\Adminhtml\Log;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Controller\Result\Redirect;
use OM\Nospam\Model\LogRepository;

class Delete extends Action
{
    /**
     * @var string[]
     */
    protected $_publicActions = ['delete'];

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected PageFactory $_resultPageFactory;


    protected LogRepository $_logRepository;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \OM\Nospam\Model\LogRepository $logRepository
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        LogRepository $logRepository
    ) {
        parent::__construct($context);
        $this->_resultPageFactory = $resultPageFactory;
        $this->_logRepository = $logRepository;
    }

    /**
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute(): Redirect
    {
        $id = (int) $this->getRequest()->getParam('id');

        if ($id) {
            try {
                $this->_logRepository->deleteById($id);
                $this->messageManager->addSuccessMessage(__('Log entry has been deleted.'));
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            }
        }

        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath('*/*/index');
    }

    /**
     * @return bool
     */
    protected function _isAllowed(): bool
    {
        return $this->_authorization->isAllowed('OM_Nospam::log');
    }
}