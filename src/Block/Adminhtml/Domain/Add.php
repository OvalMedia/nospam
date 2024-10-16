<?php
declare(strict_types=1);

namespace OM\Nospam\Block\Adminhtml\Domain;

use Magento\Backend\Block\Widget\Form\Container;
use Magento\Backend\Block\Widget\Context;
use Magento\Framework\Registry;
use Magento\Framework\Phrase;

class Add extends Container
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected Registry $_coreRegistry;

    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * @return void
     */
    protected function _construct(): void
    {
        $this->_objectId = 'entity_id';
        $this->_blockGroup = 'OM_Nospam';
        $this->_controller = 'adminhtml_domain';
        parent::_construct();

        if ($this->_isAllowedAction('OM_Nospam::add')) {
            $this->buttonList->update('save', 'label', __('Save'));
        } else {
            $this->buttonList->remove('save');
        }

        $this->buttonList->remove('reset');
    }

    /**
     * @return \Magento\Framework\Phrase
     */
    public function getHeaderText(): Phrase
    {
        return __('Add Row Data');
    }

    /**
     * @param $resourceId
     * @return bool
     */
    protected function _isAllowedAction($resourceId): bool
    {
        return $this->_authorization->isAllowed($resourceId);
    }

    /**
     * @return string
     */
    public function getFormActionUrl(): string
    {
        if ($this->hasFormActionUrl()) {
            return $this->getData('form_action_url');
        }

        return $this->getUrl('*/*/save');
    }
}
