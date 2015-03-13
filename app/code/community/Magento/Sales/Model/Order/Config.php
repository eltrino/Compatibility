<?php
namespace Magento\Sales\Model\Order;

class Config extends \Mage_Sales_Model_Order_Config
{
    public function getVisibleOnFrontStatuses()
    {
        return $this->getVisibleOnFrontStates();
    }
}
