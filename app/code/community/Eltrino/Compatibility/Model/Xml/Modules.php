<?php

class Eltrino_Compatibility_Model_Xml_Modules extends Mage_Core_Model_Config_Base
{
    /** @var Mage_Core_Model_Config_Base|null */
    protected $_magento2config = null;

    public function __construct($sourceData = null)
    {
        parent::__construct($sourceData = null);
        $this->_magento2config = new Mage_Core_Model_Config_Base();
    }

    public function loadModules(array $files)
    {
        // TODO: Refactor this piece of shit
        $this->loadString('<config/>');
        $loadedModules = array();
        foreach ($files as $file) {
            $this->_magento2config->loadFile($file);
            $moduleName = $this->_magento2config->getNode('module')->getAttribute('name');
            if (count(Mage::getConfig()->_xml->xpath('//' . $moduleName))) {
                continue;
            }
            $loadedModules[] = $moduleName;
            $version = $this->_magento2config->getNode('module')->getAttribute('schema_version');
            $currentConfig = Mage::getConfig()->_xml->xpath('modules');
            $modules = $currentConfig[0];
            if (count($modules->xpath($moduleName))) {
                return $loadedModules;
            }
            $child = $modules->addChild($moduleName);
            $child->addChild('active', 'true');
            $child->addChild('codePool', 'community');
            $child->addChild('version', $version);
            $this->loadString('config');
        }
        Mage::getConfig()->saveCache();

        return $loadedModules;
    }

}