<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Migration_rider_registration extends CI_Migration
{
    public function up()
    {
        // Add the 'slug' column to the 'sections' table
        $this->dbforge->add_column('sections', [
            'slug' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
                'null' => TRUE,
                'after' => 'title',
            ],
        ]);

        // Auto-generate slugs for existing rows
        $query = $this->db->get('sections');
        foreach ($query->result() as $row) {
            $slug = create_unique_slug($row->title, 'sections');
            $this->db->update('sections', ['slug' => $slug], ['id' => $row->id]);
        }

        /* prmocode table */

        $fields = array(
            'promo_code' => array(
                'type' => 'MEDIUMTEXT',
                'null' => FALSE,
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
            ),
        );
        $this->dbforge->modify_column('promo_codes', $fields);

        /* orders table */
        $fields = array(
            'is_pos_order' => array(
                'type' => 'INT',
                'constraint' => 11,
                'null' => FALSE,
                'default' => 0,
                'comment' => '0|1',
                'after' => 'final_total', // Specify the position
            ),
            'promocode_type' => array(
                'type' => 'varchar',
                'constraint' => 50,
                'null' => TRUE,
                'comment' => 'default | partner	',
                'after' => 'promo_code', // Specify the position
            ),
        );
        $this->dbforge->add_column('orders', $fields);


        /* payment_requests table */
        $fields = array(
            'amount_requested' => array(
                'type' => 'FLOAT',
                'null' => FALSE,
            ),
        );
        $this->dbforge->modify_column('payment_requests', $fields);


        /* users table */
        $fields = array(
            'platform' => array(
                'type' => 'varchar',
                'constraint' => 10,
                'null' => TRUE,
                'comment' => 'android|ios',
                'after' => 'friends_code', // Specify the position
            ),
        );
        $this->dbforge->add_column('users', $fields);

    }

    public function down()
    {
        // Remove the 'slug' column
        $this->dbforge->drop_column('sections', 'slug');

        /* prmocode table */
        $fields = array(
                'promo_code' => array(
                    'type' => 'VARCHAR', // Replace this with the original column type and length if known
                    'constraint' => 255,
                    'null' => FALSE,
                ),
            );
        $this->dbforge->modify_column('promo_codes', $fields);

        /* orders table */
        $this->dbforge->drop_column('orders', 'is_pos_order');
        $this->dbforge->drop_column('orders', 'promocode_type');

        /* users table */
        $this->dbforge->drop_column('users', 'platform');

        /* payment_requests table */
        $fields = array(
            'amount_requested' => array(
                'type' => 'INT', // Replace with the original column type if known
                'constraint' => '11', // Adjust constraint based on the previous type
                'null' => FALSE,
            ),
        );
        $this->dbforge->modify_column('payment_requests', $fields);
    }


     /*
        => (done) "orders" table "promocode_type" field after "promo_code" field 

        => (done) "users" table "platform" field after "friends_code" field 

        => (done) ALTER TABLE `promo_codes` CHANGE `promo_code` `promo_code` MEDIUMTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;
    
        => (done) ALTER TABLE `orders` ADD `is_pos_order` INT(11) NOT NULL DEFAULT '0' COMMENT '0|1' AFTER `final_total`;

        => (done) ALTER TABLE `payment_requests` CHANGE `amount_requested` `amount_requested` FLOAT NOT NULL;
    */
}
