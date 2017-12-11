<?php
namespace Nextorder\Menue\Model;
//use Magento\Cron\Exception;
class Ngoals extends \Magento\Framework\Model\AbstractModel{

    protected function _construct(){
        $this->_init('Nextorder\Menue\Model\ResourceModel\Ngoals');
    }
}