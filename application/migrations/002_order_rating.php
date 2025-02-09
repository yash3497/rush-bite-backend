<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Migration_order_rating extends CI_Migration
{
    public function up()
    {
        /* adding new fields in orders table  */
        $fields = array(
            'rating' => array(
                'type'           => 'DOUBLE',
                'NULL'           => FALSE,
                'default'        => 0,
                'after'          => 'self_pickup_time'
            ),
            'no_of_ratings' => array(
                'type'           => 'INT',
                'constraint'     => '11',
                'NULL'           => FALSE,
                'default'        => 0,
                'after'          => 'rating'
            )
        );
        $this->dbforge->add_column('orders', $fields);
        $fields = array(
            'banner' => [
                'type'           => 'TEXT',
                'NULL'           => FALSE,
                'after'          => 'image'
            ],
        );
        $this->dbforge->add_column('offers', $fields);
        $fields = array(
            'country_code' => [
                'type'           => 'VARCHAR',
                'constraint'     => '256',
                'NULL'           => FALSE,
                'after'          => 'code'
            ],
        );
        $this->dbforge->add_column('languages', $fields);
        $fields = array(
            'bordering_city_ids' => [
                'type'           => 'TEXT ',
                'NULL'           => TRUE,
                'default'        => NULL,
                'after'          => 'max_deliverable_distance'
            ],
        );
        $this->dbforge->add_column('cities', $fields);
        /* adding new table order_rating */
        $this->dbforge->add_field([
            'id' => [
                'type'           => 'INT',
                'constraint'     => '11',
                'auto_increment' => TRUE,
            ],
            'user_id' => [
                'type'           => 'INT',
                'constraint'     => '11',
                'NULL'           => FALSE
            ],
            'order_id' => [
                'type'           => 'INT',
                'constraint'     => '11',
                'NULL'           => FALSE
            ],
            'rating' => [
                'type'           => 'DOUBLE',
                'constraint'     => '15,2',
                'NULL'           => FALSE,
                'default'        => 0,
            ],
            'images' => [
                'type' => 'mediumtext',
                'null' =>  TRUE,
            ],
            'comment' => [
                'type' => 'VARCHAR',
                'constraint' => '1024',
                'null' => TRUE,
            ],
            'date_added TIMESTAMP default CURRENT_TIMESTAMP',
        ]);
        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->create_table('order_rating');


        /* adding new table bordering_cities */
        $this->dbforge->add_field([
            'id' => [
                'type'           => 'INT',
                'constraint'     => '11',
                'auto_increment' => TRUE,
            ],
            'city_id' => [
                'type'           => 'INT',
                'constraint'     => '11',
                'NULL'           => FALSE,
                'default'        => 0,
            ],
            'bordering_city_id' => [
                'type'           => 'INT',
                'constraint'     => '11',
                'NULL'           => FALSE,
                'default'        => 0,
            ],
            'date_added TIMESTAMP default CURRENT_TIMESTAMP',
        ]);
        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->create_table('bordering_cities');

        $fields = array(
            'apikey' => array(
                'type'           => 'TEXT',
                'NULL'           => TRUE,
            ),
        );
        $this->dbforge->modify_column('users', $fields);
    }



    public function down()
    {
        $this->dbforge->drop_column('orders', 'no_of_ratings');
        $this->dbforge->drop_column('orders', 'rating');
        $this->dbforge->drop_column('cities', 'bordering_city_ids');
        $this->dbforge->drop_column('offers', 'banner');
        $this->dbforge->drop_column('languages', 'country_code');
        $this->dbforge->drop_table('order_rating');
        $this->dbforge->drop_table('bordering_cities');
    }
}
