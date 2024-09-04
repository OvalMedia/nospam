<?php
namespace OM\Nospam\Block\Adminhtml;

class RegexField extends \Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray
{
    /**
     * @return void
     */
    protected function _prepareToRender()
    {
        $this->addColumn('name', ['label' => __('Name')]);
        $this->addColumn('expression', ['label' => __('Expression')]);
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add');
    }
}
