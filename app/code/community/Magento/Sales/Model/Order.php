<?php
namespace Magento\Sales\Model;

class Order extends \Mage_Sales_Model_Order
{
    public function getStatus()
    {
        return $this->getData('state');
    }
}
