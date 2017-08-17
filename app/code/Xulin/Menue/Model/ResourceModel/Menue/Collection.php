<?php
namespace Nextorder\Menue\Model\ResourceModel\Menue;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection{

//    protected $_idFieldName = 'id';
//    protected $_eventPrefix = 'nextorder_menue_test_collection';
//    protected $_eventObject = 'menue_collection';
    /**
     * Define resource model
     *
     * @return void
     */
    public function _construct(){
        // 1.argument => model class 2.argument => resource model class
        $this->_init('Nextorder\Menue\Model\Menue', 'Nextorder\Menue\Model\ResourceModel\Menue');
    }
    /**
     * Get SQL for get record count.
     * Extra GROUP BY strip added.
     *
     * @return \Magento\Framework\DB\Select
     */
//    public function getSelectCountSql()
//    {
//        $countSelect = parent::getSelectCountSql();
//        $countSelect->reset(\Zend_Db_Select::GROUP);
//        return $countSelect;
//    }
    /**
     * @param string $valueField
     * @param string $labelField
     * @param array $additional
     * @return array
     */
//    protected function _toOptionArray($valueField = 'id', $labelField = 'label', $additional = [])
//    {
//        return parent::_toOptionArray($valueField, $labelField, $additional);
//    }
}

