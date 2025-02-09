<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Migration_product_timing extends CI_Migration
{

    public function up()
    {
        /* adding new fields in products table  */
        $fields = array(
            'available_time' => array(
                'type'           => 'INT',
                'constraint'     => '11',
                'NULL'           => FALSE,
                'after'          => 'cod_allowed'
            ),
            'start_time' => array(
                'type'           => 'TIME',
                'NULL'           => FALSE,
                'after'          => 'available_time'
            ),
            'end_time' => array(
                'type'           => 'TIME',
                'NULL'           => FALSE,
                'after'          => 'start_time'
            )
        );
        $this->dbforge->add_column('products', $fields);
    }
    public function down()
    {
        $this->dbforge->drop_column('products', 'available_time');
        $this->dbforge->drop_column('products', 'start_time');
        $this->dbforge->drop_column('products', 'end_time');
    }
}
