<?php
namespace Magento\Framework\App\Action;

class Action extends \Mage_Core_Controller_Front_Action
{
    public function __construct($context)
    {
        parent::__construct(\Mage::app()->getRequest(), \Mage::app()->getResponse());
    }
}
