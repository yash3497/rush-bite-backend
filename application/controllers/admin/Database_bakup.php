<?php
defined('BASEPATH') or exit('No direct script access allowed');


class Database_backup extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->helper(['url', 'language', 'timezone_helper']);
        $this->load->model('Setting_model');
        
    }

    public function index()
    {
        if ($this->ion_auth->logged_in() && $this->ion_auth->is_admin()) {
            $this->data['main_page'] = FORMS . 'database-bakup';
            $settings = get_settings('system_settings', true);
            $this->data['title'] = 'Database | ' . $settings['app_name'];
            $this->data['meta_description'] = 'Database | ' . $settings['app_name'];
            $this->data['about_us'] = get_settings('about_us');
            $tables = $this->db->list_tables();
            $this->data['tables'] = $tables;
            $this->load->view('admin/template', $this->data);
        } else {
            redirect('admin/login', 'refresh');
        }
    }

    public function perform_action()
    {
        // Validate AJAX request
        $tables = $this->input->post('tables');
        $action = $this->input->post('action');

        if (empty($tables)) {
            echo json_encode(['status' => false, 'message' => 'No tables selected.']);
            return;
        }

        if ($action === 'backup') {
            // Perform backup
            $backup = $this->dbutil->backup(['tables' => $tables]);

            // Save the backup file
            $this->load->helper('file');
            $file_path = './backups/database_backup_' . date('Y-m-d_H-i-s') . '.sql.gz';
            if (write_file($file_path, $backup)) {
                echo json_encode(['status' => true, 'message' => 'Backup successful.']);
            } else {
                echo json_encode(['status' => false, 'message' => 'Failed to save backup file.']);
            }
        } elseif ($action === 'delete') {
            // Perform delete
            foreach ($tables as $table) {
                $this->db->empty_table($table); // Empty the table data
            }
            echo json_encode(['status' => true, 'message' => 'Data deleted from selected tables.']);
        } else {
            echo json_encode(['status' => false, 'message' => 'Invalid action.']);
        }
    }



}
