<?php
namespace OM\Nospam\Block\Adminhtml;

class InputField extends \Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray
{
    /**
     * @return void
     */
    protected function _prepareToRender()
    {
        $this->addColumn('field', ['label' => __('Field')]);
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add');
    }
}
