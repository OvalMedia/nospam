<?php
declare(strict_types=1);

namespace OM\Nospam\Block\Adminhtml\Domain\Edit;

use Magento\Backend\Block\Widget\Form\Generic;

class Form extends Generic
{
    /**
     * Prepare form.
     *
     * @return $this
     */
    protected function _prepareForm(): Form
    {
        $domain = $this->_coreRegistry->registry('domain');

        $form = $this->_formFactory->create(
            array(
                'data' => array(
                    'id' => 'edit_form',
                    'enctype' => 'multipart/form-data',
                    'action' => $this->getData('action'),
                    'method' => 'post'
                )
            )
        );

        $form->setHtmlIdPrefix('om_nospam_domain');

        if ($domain->getId()) {
            $fieldset = $form->addFieldset(
                'base_fieldset',
                array(
                    'legend' => __('Edit Domain'),
                    'class' => 'fieldset-wide'
                )
            );

            $fieldset->addField(
                'entity_id',
                'hidden',
                array(
                    'name' => 'entity_id'
                )
            );
        } else {
            $fieldset = $form->addFieldset(
                'base_fieldset',
                array(
                    //'legend' => __('Add Domain'),
                    'class' => 'fieldset-wide'
                )
            );
        }

        $fieldset->addField(
            'domain',
            'text',
            array(
                'name' => 'domain',
                'label' => __('Domain Name'),
                'id' => 'domain',
                'title' => __('Domain Name'),
                'class' => 'required-entry',
                'required' => true
            )
        );

        $form->setValues($domain->getData());
        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }
}
