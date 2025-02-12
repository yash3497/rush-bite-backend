<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Migration_custom_notifications extends CI_Migration
{
    public function up()
    {
        /* adding new table custom_notification */
        $this->dbforge->add_field([
            'id' => [
                'type'           => 'INT',
                'constraint'     => '11',
                'auto_increment' => TRUE,
                'NULL'           => TRUE
            ],
            'title' => [
                'type'           => 'VARCHAR',
                'constraint'     => '128',
                'NULL'           => TRUE
            ],
            'message' => [
                'type'           => 'VARCHAR',
                'constraint'     => '512',
                'NULL'           => TRUE
            ],
            'type' => [
                'type'           => 'VARCHAR',
                'constraint'     => '64',
                'NULL'           => TRUE
            ],
            'date_sent TIMESTAMP default CURRENT_TIMESTAMP',
        ]);
        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->create_table('custom_notifications');
    }
    public function down()
    {
        $this->dbforge->drop_table('custom_notifications');
    }
}
