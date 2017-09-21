<?php
namespace Nexorder\Menue\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\DB\Ddl\Table;

class UpgradeSchema implements UpgradeSchemaInterface{

    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context){

    $installer = $setup;
    $installer->startSetup();

//        if(version_compare($context->getVersion(), '1.0.1') < 0){
        $installer->getConnection()
            ->addColumn(
                $installer->getTable('no_subaccounts_order_item'),
                'delivery_date',
                array(
                    'type' => Table::TYPE_INTEGER,
                    'nullable' => false,
                    'default' => 0,
                    'comment' => 'Delivery Date '
                )
            );
//        }
    $installer->endSetup();
    }
}
