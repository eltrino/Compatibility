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
        $this->loadString('<config/>');
        $loadedModules = array();
        foreach ($files as $file) {

            $this->_magento2config->loadFile($file);
            $moduleName = $this->_magento2config->getNode('module')->getAttribute('name');

            if ($this->isModuleLoaded($moduleName)) {
                $loadedModules[] = $moduleName;
                continue;
            }

            $loadedModules[] = $moduleName;
            $version = $this->_magento2config->getNode('module')->getAttribute('schema_version');
            $modules = Mage::getConfig()->getNode('modules');
            $child = $modules->addChild($moduleName);
            $child->addChild('active', 'true');
            $child->addChild('codePool', 'community');
            $child->addChild('version', $version);
            $this->loadString('<config/>');
        }

        return $loadedModules;
    }

    public function isModuleLoaded($moduleName)
    {
        $node = Mage::getConfig()->getNode('modules/' . $moduleName);

        return (bool)$node;
    }

}