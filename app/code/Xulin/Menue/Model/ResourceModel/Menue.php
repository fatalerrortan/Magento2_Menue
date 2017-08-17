<?php
namespace Nextorder\Menue\Model\ResourceModel;

class Menue extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb{
    /**
     * Initialize resource model
     *
     * @return void
     */
    public function _construct(){
        //table name and primary key
        $this->_init('nextorder_menue', 'id');
    }
//    protected $_date;
//
//    public function __construct(
//        \Magento\Framework\Stdlib\DateTime\DateTime $date,
//        \Magento\Framework\Model\ResourceModel\Db\Context $context
//    ){
//        $this->_date = $date;
//        parent::__construct($context);
//    }

//    public function getLabelById($id)
//    {
//        $adapter = $this->getConnection();
//        $select = $adapter->select()
//            // the 2. argument is the target column name
//            ->from($this->getMainTable())
//            ->where('id = :id');
//        $binds = ['id' => (int)$id];
//        return $adapter->fetchOne($select, $binds);
//    }
    /**
     * before save callback
     */
//    protected function _beforeSave(\Magento\Framework\Model\AbstractModel $object)
//    {
//        $object->setUpdatedAt($this->_date->date());
//        if ($object->isObjectNew()) {
//            $object->setCreatedAt($this->_date->date());
//        }
//        return parent::_beforeSave($object);
//    }
}
