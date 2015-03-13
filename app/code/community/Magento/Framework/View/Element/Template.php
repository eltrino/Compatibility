<?php

namespace Magento\Framework\View\Element;

class Template extends \Mage_Core_Block_Template
{
    protected function _toHtml()
    {
        $parts = explode('\\', get_class($this));
        $moduleName = $parts[0] . '_' . $parts[1];
        $filePath = \Mage::getModuleDir('', $moduleName) . '/view/frontend/templates/' . $this->getTemplate();
        if (is_readable($filePath)) {
            $block = $this;
            include($filePath);
        }
        $this->setTemplate('');
    }
}