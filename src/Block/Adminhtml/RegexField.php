<?php
declare(strict_types=1);

namespace OM\Nospam\Block\Adminhtml;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;

class RegexField extends AbstractFieldArray
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
