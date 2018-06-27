<?php
namespace Nextorder\Menue\Model\ResourceModel\Ngoals;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection{

    public function _construct(){
        // 1.argument => model class 2.argument => resource model class
        $this->_init('Nextorder\Menue\Model\Ngoals', 'Nextorder\Menue\Model\ResourceModel\Ngoals');
    }
}