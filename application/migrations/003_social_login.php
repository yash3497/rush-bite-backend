<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Migration_social_login extends CI_Migration
{
    public function up()
    {
        /* adding new fields in partner_data table  */
        $fields = array(
            'licence_name' => array(
                'type'           => 'VARCHAR',
                'constraint'     => '2048',
                'NULL'           => FALSE,
                'after'          => 'cooking_time'
            ),
            'licence_code' => array(
                'type'           => 'VARCHAR',
                'constraint'     => '2048',
                'NULL'           => FALSE,
                'after'          => 'licence_name'
            ),
            'licence_proof' => array(
                'type'           => 'VARCHAR',
                'constraint'     => '2048',
                'NULL'           => FALSE,
                'after'          => 'licence_code'
            ),
            'licence_status' => array(
                'type'           => 'TINYINT',
                'constraint'     => '2',
                'NULL'           => FALSE,
                'after'          => 'licence_proof'
            )
        );
        $this->dbforge->add_column('partner_data', $fields);

        $fields = array(
            'type' => [
                'type'           => 'VARCHAR',
                'constraint'     => '256',
                'default'        => 'phone',
                'after'          => 'mobile'
            ],
        );
        $this->dbforge->add_column('users', $fields);
    }
    public function down()
    {
        $this->dbforge->drop_column('users', 'type');
        $this->dbforge->drop_column('partner_data', 'licence_status');
        $this->dbforge->drop_column('partner_data', 'licence_proof');
        $this->dbforge->drop_column('partner_data', 'licence_code');
        $this->dbforge->drop_column('partner_data', 'licence_name');
    }
}
