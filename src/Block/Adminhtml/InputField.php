<?php
declare(strict_types=1);

namespace OM\Nospam\Block\Adminhtml;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;

class InputField extends AbstractFieldArray
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
