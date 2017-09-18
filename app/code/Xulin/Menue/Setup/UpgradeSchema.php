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

    if(version_compare($context->getVersion(), '1.0.2', '<')){
        $installer->getConnection()
            ->addColumn(
            $installer->getTable('no_subaccounts_order'),
            'delivery_date_from',
            [
                'type' => Table::TYPE_TEXT,
                'nullable' => true,
                'comment' => 'Order Delivery Date Begin'
            ]
            )
            ->addColumn(
                $installer->getTable('no_subaccounts_order'),
                'delivery_date_to',
                [
                    'type' => Table::TYPE_TEXT,
                    'nullable' => true,
                    'comment' => 'Order Delivery Date End'
                ]
            );
    }

    $installer->endSetup();
    }
}
