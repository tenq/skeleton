<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

/**
 * Vpx Migration Library
 *
 * Create a base file for migrations to start off with;
 *
 * @author Liaan vd Merwe <info@vpx.co.za>
 * @license Free to use and abuse
 * @version 0.4 Beta
 *
 */
class VpxMigration {

    var $db_user;
    var $db_pass;
    var $db_host;
    var $db_name;
    var $email;
    var $tables = '*';
    var $newline = '\n';
    var $write_file = true;
    var $file_name = '';
    var $file_per_table = true;
    var $path = 'migrations';
    var $skip_tables = array('migrations');
    var $add_view = false;

    /*
     * defaults;
     */
    function __construct($params = null) {
        // parent::__construct();
        isset($this->ci) OR $this->ci = get_instance();
        $this->ci->db_master = $this->ci->db;
        $this->db_user = $this->ci->db_master->username;
        $this->db_pass = $this->ci->db_master->password;
        $this->db_host = $this->ci->db_master->hostname;
        $this->db_name = $this->ci->db_master->database;
        $this->path = APPPATH . $this->path;
        
        if ($params) $this->init_config($params);
    }

    /**
     * Init Config if there is any passed
     *
     *
     * @param type $params
     */
    function init_config($params = array()) { //apply config
        if (count($params) > 0)
        {
            foreach ($params as $key => $val)
            {
                if (isset($this->$key))
                {
                    $this->$key = $val;
                }
            }
        }
    }

    /**
     * Generate the file.
     *
     * @param string $tables
     * @return boolean|string
     */
    function generate($tables = null) {
        $return = $up = $down = '';

        if ($tables) { $this->tables = $tables; }

        /* open file */
        if ($this->write_file) {
            if (!is_dir($this->path) OR !is_really_writable($this->path)) {
                $msg = "Unable to write migration file: " . $this->path;
                log_message('error', $msg);
                echo $msg;
                return;
            }

            if (!$this->file_per_table) {
                $file_path = $this->path.'/'.$this->file_name.'.sql';
                $file = fopen($file_path, 'w+');

                if (!$file) {
                    $msg = "no file";
                    log_message('error', $msg);
                    echo $msg;
                    return FALSE;
                }
            }
        }

        // if default, then run all tables, otherwise just do the list provided
        if ($this->tables == '*') {
            $query = $this->ci->db_master->query('SHOW full TABLES FROM '.$this->ci->db_master->protect_identifiers($this->ci->db_master->database));

            $retval = array();

            if ($query->num_rows() > 0) {
                foreach ($query->result_array() as $row) {
                    $tablename = 'Tables_in_' . $this->ci->db_master->database;
                    if (isset($row[$tablename])) {
                        /* check if table in skip arrays, if so, go next */
                        if (in_array($row[$tablename], $this->skip_tables))
                            continue;

                        /* check if views to be migrated */
                        if ($this->add_view) {
                            ## not implemented ##
                            //$retval[] = $row[$tablename];
                        } else {
                            /* skip views */
                            if (strtolower($row['Table_type']) == 'view') {
                                continue;
                            }
                            $retval[] = $row[$tablename];
                        }
                    }
                }
            }

            $this->tables = array();
            $this->tables = $retval;
        }
        else {
            if(!is_array($tables) && !$this->ci->db_master->table_exists($tables)) {
                echo 'Table Not Found!';
                exit;
            }
            
            if(is_array($tables)) {
                foreach ($tables as $table) {
                    if(!$this->ci->db_master->table_exists($table)) {
                        if (($key = array_search($table, $tables)) !== false) {
                            unset($tables[$key]);
                        }
                    }
                }
            }
            
            $this->tables = is_array($tables) ? $tables : explode(',', $tables);
        }

        ## if write file, check if we can
        if ($this->write_file) {
            /* make subdir */
            $path = $this->path.'/'.$this->file_name;

            if (!@is_dir($path)) {
                if (!@mkdir($path, DIR_WRITE_MODE, true)) {
                    return FALSE;
                }
                @chmod($path, DIR_WRITE_MODE);
            }

            if (!is_dir($path) OR !is_really_writable($path)) {
                $msg = "Unable to write backup per table file: " . $path;
                log_message('error', $msg);

                return;
            }

            if($tables)
                $file_path = $path.'/'.date('YmdHis').'_create_'.$tables.'.php';
            else
                $file_path = $path.'/'.date('YmdHis').'_create_base.php';
            
            $file = fopen($file_path, 'w+');

            if (!$file) {
                $msg = 'No File';
                log_message('error', $msg);
                echo $msg;

                return FALSE;
            }
        }

        //loop through tables
        foreach ($this->tables as $table) {
            // log_message('debug', print_r($table, true));

            if($table) {
                $q = $this->ci->db_master->query('describe '.$this->ci->db_master->protect_identifiers($this->ci->db_master->database.'.'.$table));
            
                // No result means the table name was invalid
                if ($q === FALSE) { continue; }

                $columns = $q->result_array();

                $q = $this->ci->db_master->query(' SHOW TABLE STATUS WHERE Name = \'' . $table . '\'');

                $engines = $q->row_array();

                $up .= "\n\t" . 'if($this->db_exists()) {';
                $up .= "\n\t\t" . '## Create Table ' . $table . "\n";
                foreach ($columns as $column)
                {
                    $up .= "\t\t" . '$this->dbforge->add_field("' . "`$column[Field]` $column[Type] " . ($column['Null'] == 'NO' ? 'NOT NULL' : 'NULL') .
                            (
                            #  if its timestamp column, don't '' around default value .... crap way, but should work for now
                            $column['Default'] ? ' DEFAULT ' . ($column['Type'] == 'timestamp' ? $column['Default'] : '\'' . $column['Default'] . '\'') : ''
                            )
                            . " $column[Extra]\");" . "\n";

                    if ($column['Key'] == 'PRI')
                        $up .= "\t\t" . '$this->dbforge->add_key("' . $column['Field'] . '",true);' . "\n";
                }
                $up .= "\t\t" . '$this->dbforge->create_table("' . $table . '", TRUE);' . "\n";
                if (isset($engines['Engine']) and $engines['Engine'])
                    $up .= "\t\t" . '$this->db->query(\'ALTER TABLE  ' . $this->ci->db_master->protect_identifiers($table) . ' ENGINE = ' . $engines['Engine']. '\');'."\n\t". '}';


                $down .= "\t\t" . '## Drop table ' . $table . ' ##' . "\n";
                $down .= "\t\t" . '$this->dbforge->drop_table("' . $table . '", TRUE);' . "\n";

                /* clear some mem */
                $q->free_result();
            }
        }
        
        echo "\nCount: ".count($this->tables)."\n";

        ### generate the text ##
        $return .= '<?php ';
        $return .= 'defined(\'BASEPATH\') OR exit(\'No direct script access allowed\');' . "\n\n";
        
        if(count($this->tables) > 1) {
            $return .= 'class Migration_create_base extends CI_Migration {' . "\n";
        }
        else {
            $return .= "class Migration_create_$tables extends CI_Migration {\n";
        }

        $return .= "\n\t".'public function db_exists() {
            $this->load->dbutil();
            $this->load->dbforge();

            if (!$this->dbutil->database_exists(\''.$this->db_name.'\')) {
                if ($this->dbforge->create_database(\''.$this->db_name.'\')) {
                    return TRUE;
                }
                else {
                    return FALSE;
                }
            }
        }' . "\n";

        $return .= "\n\t" . 'public function up() {' . "\n";
        $return .= $up;
        $return .= "\n\t}\n";

        $return .= "\n\t"."public function down() {\n\t\t";
        $return .= $down;
        $return .= "\n\t}\n}";

        ## write the file, or simply return if write_file false
        if ($this->write_file) {
            fwrite($file, $return);
            fclose($file);
            echo "Migration file/s Created  successfully!";
            exit;
            //return true;
        }
        else { return $return; }
    }

}

?>
