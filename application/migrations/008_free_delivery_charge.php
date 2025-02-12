<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Migration_free_delivery_charge extends CI_Migration
{
    public function up()
    {

         if (!$this->db->field_exists('is_spicy', 'products')) {
             /* adding new fields in products table */
              $fields = array(
                'is_spicy' => array(
                    'type' => 'INT',
                    'constraint' => '11',
                    'null' => FALSE,    
                    'after' => 'is_cancelable'
                ),
               
            );
            $this->dbforge->add_column('products', $fields);
         }

         /* Modifying field in users table */

         $fields = array(
            'serviceable_city' => array(
                'type' => 'TEXT',
                'null' => TRUE,
                'default' => NULL,
            ),
        );

        // Modify the column
        $this->dbforge->modify_column('users', $fields);

         /* adding new fields in orders table */
        $fields = array(
            'order_items_snapshot' => array(
                'type' => 'LONGTEXT',
                'null' => TRUE,
                'after' => 'partner_commission_amount'
            ),
        );
        $fields = array(
            'user_mobile' => array(
                'type' => 'VARCHAR',
                'constraint' => '12',
                'null' => TRUE,
                'after' => 'mobile'
            ),
        );
        $fields = array(
            'user_email' => array(
                'type' => 'VARCHAR',
                'constraint' => '254',
                'null' => TRUE,
                'after' => 'tax_percent'
            ),
        );
        $this->dbforge->add_column('orders', $fields);


        /* adding new fields in orders table */
        $fields = array(
            'partner_detail_snapshot' => array(
                'type' => 'LONGTEXT',
                'null' => TRUE,
                'after' => 'sub_total'
            ),
        );
        $this->dbforge->add_column('order_items', $fields);



        /* adding new fields in product_rating table */
          $fields = array(
            'order_id' => array(
                'type' => 'INT',
                'constraint' => '11',
                'null' => FALSE,    
                'after' => 'user_id'
            ),
           
        );
        $this->dbforge->add_column('product_rating', $fields);

        /* adding new fields in rider_rating table */
          $fields = array(
            'order_id' => array(
                'type' => 'INT',
                'constraint' => '11',
                'null' => FALSE,    
                'after' => 'user_id'
            ),
           
        );
        $this->dbforge->add_column('rider_rating', $fields);

        /* adding new fields in cities table */
          $fields = array(
            'min_order_amount_for_free_delivery' => array(
                'type' => 'DOUBLE',
                'null' => TRUE,    
                'after' => 'longitude'
            ),
           
        );
        $this->dbforge->add_column('cities', $fields);

    }
    public function down()
    {
   
        if ($this->db->field_exists('is_spicy', 'products')) {
            $this->dbforge->drop_column('products', 'is_spicy');
        }
        $this->dbforge->drop_column('users', 'serviceable_city');
        $this->dbforge->drop_column('order_items', 'partner_detail_snapshot');
        $this->dbforge->drop_column('orders', 'order_detail_snapshot');
        $this->dbforge->drop_column('orders', 'user_mobile');
        $this->dbforge->drop_column('orders', 'user_email');
        $this->dbforge->drop_column('product_rating', 'order_id');
        $this->dbforge->drop_column('rider_rating', 'order_id');
        $this->dbforge->drop_column('cities', 'min_order_amount_for_free_delivery');
    }
}
