<?php
namespace Nextorder\Menue\Model\ResourceModel;
class Ngoals extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb{

    public function _construct(){
        //table name and primary key
        $this->_init('no_nutrition_goals', 'id');
    }
}