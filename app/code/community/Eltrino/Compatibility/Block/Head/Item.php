<?php

class Eltrino_Compatibility_Block_Head_Item extends Mage_Core_Block_Template
{
    public function _toHtml()
    {
        if ($this->getCss()) {
            return $this->getCssOutput();
        }
    }

    public function getCssOutput()
    {
        $args = explode('::', $this->getCss());
        $file = Mage::getModuleDir('', $args[0]) . '/view/frontend/web/' . $args[1];
        $url = str_replace(Mage::getBaseDir(), rtrim(Mage::getBaseUrl(), '/'), $file);

        return sprintf('<link rel="stylesheet" href="%s"/>', $url);
    }
}