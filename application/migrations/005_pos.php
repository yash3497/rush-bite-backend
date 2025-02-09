<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Migration_pos extends CI_Migration
{

    public function up()
    {
        /* adding new fields in products table  */
        $fields = array(
            'alternate_country_code' => array(
                'type'           => 'INT',
                'constraint'     => '11',
                'NULL'           => TRUE,
                'default'        => '0',
                'after'          => 'is_default'
            ),
        );
        $this->dbforge->add_column('addresses', $fields);
    }
    public function down()
    {
        $this->dbforge->drop_column('addresses', 'alternate_country_code');
    }
}
