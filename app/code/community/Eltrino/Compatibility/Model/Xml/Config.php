<?php

class Eltrino_Compatibility_Model_Xml_Config extends Mage_Core_Model_Config_Base
{
    /**
     * @param $modules array
     */
    public function loadConfig($modules)
    {
        foreach ($modules as $modName) {
            $module = Mage::getModel('eltrino_compatibility/xml_config_module',
                array(null, $modName)
            );
            $module->loadModuleConfig();
        }
    }
}