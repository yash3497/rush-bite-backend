<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Migration_cancel_order_reason extends CI_Migration
{
    public function up()
    {

        /* adding new fields in orders table  */
        $fields = array(
            'cancel_by' => array(
                'type'           => 'INT',
                'constraint'     => '11',
                'NULL'           => FALSE,
                'default'        => 0,
                'after'          => 'otp'
            ),
            'reason' => array(
                'type' => 'VARCHAR',
                'constraint' => '1048',
                'null' => TRUE,
                'after' => 'cancel_by'
            ),
            'tax_percent' => array(
                'type' => 'DOUBLE',
                'constraint' => '15,2',
                'null' => FALSE,
                'default' => 0.00,
                'after' => 'mobile'
            ),
            'tax_amount' => array(
                'type' => 'DOUBLE',
                'constraint' => '15,2',
                'null' => FALSE,
                'default' => 0.00,
                'after' => 'tax_percent'
            ),
            'is_self_pick_up' => array(
                'type'           => 'TINYINT',
                'constraint'     => '4',
                'default'        => 0,
            ),
            'owner_note' => array(
                'type' => 'VARCHAR',
                'constraint' => '1048',
                'null' => TRUE,
                'after' => 'is_self_pick_up'
            ),
            'self_pickup_time' => array(
                'type' => 'DATETIME',
                'null' => TRUE,
                'after' => 'owner_note'
            ),
            
        );
        $this->dbforge->add_column('orders', $fields);

    }

    public function down()
    {
        // Drop columns >> $this->dbforge->drop_column('table_name', 'column_to_drop');
        $this->dbforge->drop_column('orders', 'cancel_by');
        $this->dbforge->drop_column('orders', 'reason');
    }
}
