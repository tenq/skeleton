<?php
    /**
    * Users Model
    *
    * Availabe Methods are json, get_all, get_by_id, total_rows, get_limit_data,
    * insert, update, delete
    * Generated by Harviacode Codeigniter CRUD Generator @ 2017-12-16 12:22:21.
    * For more info about harviacode visit @link.
    *
    * @author YOUR_NAME_HERE
    * @copyright Copyright (c) 2014 -2017, YOUR_NAME, YOUR_LINK
    * @license License_LINK, License_NAME
    * @link http://harviacode.com
    * @filesource ../application/modules/users/models/Users_model.php
    *
    */
    if (!defined('BASEPATH')) exit('No direct script access allowed');

    class Users_model extends CI_Model {
        public $table = 'users';
        public $id = 'id';
        public $order = 'DESC';

        function __construct() {
            parent::__construct();
        }


        /**
        * JSON
        *
        * Get json fromat of users
        *
        * @param
        * @return JSON output
        */
        function json() {
            return json_encode($this->db->get($this->table)->result());
        }

        /**
        * XML
        *
        * Get XML fromat of books
        *
        * @param
        * @return XML output
        */
        function xml() {
            return xmlrpc_encode($this->db->get($this->table)->result());
        }

        /**
        * GET_ALL
        *
        * Get all records of users
        *
        * @param
        * @return object
        */
        function get_all() {
            $this->db->order_by($this->id, $this->order);
            return $this->db->get($this->table)->result();
        }

        /**
        * GET_BY_ID
        *
        * Get all records by $id of users
        *
        * @param $id
        * @return object
        */
        function get_by_id($id) {
            $this->db->where($this->id, $id);
            return $this->db->get($this->table)->row();
        }
    
        /**
        * GET_TOTAL_ROWS
        *
        * Get all rows of users
        *
        * @param $q
        * @return int
        */
        function total_rows($q = NULL) {
            $this->db->like('id', $q);
			$this->db->or_like('username', $q);
			$this->db->or_like('email', $q);
			$this->db->or_like('password', $q);
			$this->db->or_like('role', $q);
			$this->db->or_like('active', $q);
			$this->db->or_like('avatar', $q);
			$this->db->or_like('verification_code', $q);
			$this->db->or_like('remember_me', $q);
			$this->db->or_like('last_login', $q);
			$this->db->or_like('created', $q);
			$this->db->or_like('updated', $q);
			$this->db->from($this->table);
            return $this->db->count_all_results();
        }

        /**
        * GET_LIMIT_DATA
        *
        * Get limit & search by term(q) of users
        *
        * @param $limit
        * @param $start
        * @param $q
        * @return int
        */
        function get_limit_data($limit, $start = 0, $q = NULL) {
            $this->db->order_by($this->id, $this->order);
            $this->db->like('id', $q);
			$this->db->or_like('username', $q);
			$this->db->or_like('email', $q);
			$this->db->or_like('password', $q);
			$this->db->or_like('role', $q);
			$this->db->or_like('active', $q);
			$this->db->or_like('avatar', $q);
			$this->db->or_like('verification_code', $q);
			$this->db->or_like('remember_me', $q);
			$this->db->or_like('last_login', $q);
			$this->db->or_like('created', $q);
			$this->db->or_like('updated', $q);
			$this->db->limit($limit, $start);
            return $this->db->get($this->table)->result();
        }

        /**
        * INSERT_DATA
        *
        * Insert data @ users
        *
        * @param $data
        * @return void
        */
        function insert($data) {
            $this->db->insert($this->table, $data);
        }

        /**
        * UPDATE_DATA
        *
        * Update data @ users by $id
        *
        * @param $id
        * @param $data
        * @return void
        */
        function update($id, $data) {
            $this->db->where($this->id, $id);
            $this->db->update($this->table, $data);
        }

        /**
        * DELETE_DATA
        *
        * DELETE data @ users by $id
        *
        * @param $id
        * @return void
        */
        function delete($id) {
            $this->db->where($this->id, $id);
            $this->db->delete($this->table);
        }
    }

    /* End of file Users_model.php */
