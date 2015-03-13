<?php
namespace Magento\Framework\Model\Resource\Db;

class AbstractDb extends \Mage_Core_Model_Resource_Db_Abstract
{
    protected function _construct()
    {
        parent::_construct();
    }

    protected function _init($mainTable, $idFieldName)
    {
        $config = \Mage::getConfig()->getNode('global/models');
        $resource = $config->xpath("//*[table='{$mainTable}']")[0];
        $table = $resource->xpath('.')[0]->getName();
        $resourceName = $resource->xpath('../..')[0]->getName();
        $model = str_replace('_resource', '', $resourceName);

        parent::_init($model . '/' . $table, $idFieldName);
    }
}
