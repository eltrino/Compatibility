<?php

class Eltrino_Compatibility_Model_Observer
{
    /** @var bool */
    static $isInitialized = false;

    /** @var array */
    protected $_allowedPools = array(
        'local',
        'community',
    );
    /** @var array */
    protected $_loadedModules = array();

    public function init($observer)
    {
        if ($observer->getTableName() !== 'core_website') {
            return;
        }

        if (static::$isInitialized) {
            //return;
        }

        $files = $this->getMagento2Modules();
        $loadedModules = Mage::getModel('eltrino_compatibility/xml_modules')->loadModules($files);

        if (count($loadedModules)) {
            $this->_loadedModules = array_merge($this->_loadedModules, $loadedModules);
        }

        Mage::getModel('eltrino_compatibility/xml_config')->loadConfig($loadedModules);
        static::$isInitialized = true;

    }

    public function getMagento2Modules()
    {
        //TODO: implement cache
        $magento2modules = array();
        foreach ($this->_allowedPools as $pool) {
            foreach (glob(Mage::getBaseDir() . '/app/code/' . $pool . '/*/*/') as $moduleDir) {
                $file = realpath($moduleDir . 'etc/module.xml');
                if (file_exists($file)) {
                    $magento2modules[] = $file;
                }
            }
        }

        return $magento2modules;
    }

    /**
     * Now works only on frontend
     */
    public function addLayoutUpdates($observer)
    {
        /** @var Mage_Core_Controller_Front_Action $action */
        $action = Mage::app()->getFrontController()->getAction();
        /** @var Mage_Core_Model_Layout $layout */
        $layout = $action->getLayout();
        /** @var Mage_Core_Model_Layout_Update $update */
        $update = $layout->getUpdate();
        /** @var array $handles */
        $handles = $update->getHandles();

        foreach ($this->_loadedModules as $moduleName) {
            $layoutDir = Mage::getModuleDir('', $moduleName) . '/view/frontend/layout/';
            if (!is_readable($layoutDir)) {
                continue;
            }

            $handlesToUpdate = array();
            foreach (glob($layoutDir . '*.xml') as $layoutFile) {
                $handleName = basename($layoutFile, '.xml');
                if (in_array($handleName, $handles)) {
                    $handlesToUpdate[] = $handleName;
                }
            }

            if (!count($handlesToUpdate)) {
                return;
            }

            $action->loadLayoutUpdates();

            foreach ($handlesToUpdate as $handleName) {
                $layoutFile = $layoutDir . $handleName . '.xml';
                $xml = new Varien_Simplexml_Element(file_get_contents($layoutFile));
                $newXml = new Mage_Core_Model_Layout_Element('<update/>');
                /** @var Varien_Simplexml_Element $child */

                if ($xml->getName() == 'page') {
                    $layoutPageUpdate = $xml->getAttribute('layout');
                    if ($layoutPageUpdate) {
                        $reference = $newXml->addChild('reference');
                        $reference->addAttribute('name', 'root');
                        $reference->addChild('action');
                        $reference->action->addAttribute('method', 'setTemplate');
                        $reference->action->addChild('template', 'page/' . $layoutPageUpdate . '.phtml');
                    }
                }

                foreach ($xml as $child) {
                    if ($child->getName() == 'head') {
                        $reference = $newXml->addChild('reference');
                        $reference->addAttribute('name', 'head');
                        foreach ($child as $element) {
                            if ($element->getName() == 'css') {
                                $block = $reference->addChild('block');
                                $block->addAttribute('name', 'head.item');
                                $block->addAttribute('type', 'eltrino_compatibility/head_item');
                                $xmlAction = $block->addChild('action');
                                $xmlAction->addAttribute('method', 'setCss');
                                $xmlAction->addChild('src', $element['src']);
                            }
                        }
                    }
                    if ($child->getName() == 'body') {
                        foreach ($child as $element) {
                            if ($element->getName() == 'referenceContainer') {
                                $reference = $newXml->addChild('reference');
                                $reference->addAttribute('name', $element['name']);
                                foreach ($element as $subElement) {
                                    if ($subElement->getName() == 'block') {
                                        $block = $reference->addChild('block');
                                        /** TODO: do check is attribute exists before add */
                                        $block->addAttribute('name', $subElement['name']);
                                        $block->addAttribute('type', $subElement['class']);
                                        $block->addAttribute('after', $subElement['after']);
                                        $block->addAttribute('template', $subElement['template']);
                                    }
                                }
                            }
                        }
                    }
                }
                $newXml = $newXml->asNiceXml();
                $newXml = str_replace('<update>', '', $newXml);
                $newXml = str_replace('</update>', '', $newXml);
                $update->addUpdate($newXml);
            }
            $action->generateLayoutXml()->generateLayoutBlocks();
        }
    }
}