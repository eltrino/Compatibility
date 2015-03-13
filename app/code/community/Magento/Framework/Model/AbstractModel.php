<?php
namespace Magento\Framework\Model;

class AbstractModel extends \Mage_Core_Model_Abstract
{
    /**
     * Set resource names
     *
     * If collection name is ommited, resource name will be used with _collection appended
     *
     * @param string $resourceName
     * @param string|null $resourceCollectionName
     */
    protected function _setResourceModel($resourceName, $resourceCollectionName = null)
    {
        $resourceName = str_replace('\Model\Resource', '', $resourceName);
        list($scope, $module, $model) = explode('\\', $resourceName);
        /**
         * See: Eltrino_Compatibility_Model_Xml_Config_Module line:77
         */
        $resourceName = strtolower($scope . '_' . $module . '/' /*. $model */);

        $this->_resourceName = $resourceName;
        if (is_null($resourceCollectionName)) {
            $resourceCollectionName = $resourceName . '\\Collection';
        }
        $this->_resourceCollectionName = $resourceCollectionName;
    }
}
