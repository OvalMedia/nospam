<?php
namespace OM\Nospam\Block\Adminhtml;

class WhitelistField extends \Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray
{
    /**
     * @return void
     */
    protected function _prepareToRender()
    {
        $this->addColumn('name', ['label' => __('Name')]);
        $this->addColumn('user_agent', ['label' => __('User Agent')]);
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add');
    }
}
