<?php

class Application_Model_DbTable_Parent extends Zend_Db_Table_Abstract
{
    public function get_new_db_factory(){
        $config = new Zend_Config_Ini(APPLICATION_PATH.'/configs/application.ini', 'production');
        $host = $config->resources->db->params->host;
        $username = $config->resources->db->params->username;
        $password = $config->resources->db->params->password;
        $dbname = $config->resources->db->params->dbname;
        $port = $config->resources->db->params->port;

        $mydb = Zend_Db::factory(
            'PDO_PGSQL',
            array(
                'host' => $host,
                'username' => $username,
                'password' => $password,
                'dbname' => $dbname,
                'port' => $port
            )
        );


        $user_id = getCurUser();
        if ($user_id > 0){
            $mydb->query('set session "myapp.user_id"= '.$user_id);
        }

        return $mydb;
    }

    public function get_db_factory(){
        if (isOnlyRead() == '1'){
            $config = new Zend_Config_Ini(APPLICATION_PATH.'/configs/application.ini', 'production');
            $host = $config->resources->db->params->host;
            $username = 'readuser';
            $password = 'odk#dck32Ld85$#';
            $dbname = $config->resources->db->params->dbname;
            $port = $config->resources->db->params->port;

            $db = Zend_Db::factory(
                'PDO_PGSQL',
                array(
                    'host' => $host,
                    'username' => $username,
                    'password' => $password,
                    'dbname' => $dbname,
                    'port' => $port
                )
            );
            $user_id = getCurUser();
            if ($user_id > 0){
                $db->query('set session "myapp.user_id"= '.$user_id);
            }
        } elseif (getCurUser() == 780000006){
            $config = new Zend_Config_Ini(APPLICATION_PATH.'/configs/application.ini', 'production');
            $host = $config->resources->db->params->host;
            $username = $config->resources->db->params->username2;
            $password = $config->resources->db->params->password2;
            $dbname = $config->resources->db->params->dbname2;
            $port = $config->resources->db->params->port;

            $db = Zend_Db::factory(
                'PDO_PGSQL',
                array(
                    'host' => $host,
                    'username' => $username,
                    'password' => $password,
                    'dbname' => $dbname,
                    'port' => $port
                )
            );
            $user_id = getCurUser();
            if ($user_id > 0){
                $db->query('set session "myapp.user_id"= '.$user_id);
            }
        } else {
            $db = Zend_Db_Table::getDefaultAdapter();
        }

        return $db;
    }

    public function set_session(){
        try{
            $user_id = getCurUser();
            if ($user_id > 0){
                //$user_id = $user_id + 1; //Для тестирования доступов
                $db = $this->get_db_factory();
                $db->query('set session "myapp.user_id"= '.$user_id);
            }
        }
        catch(exception $e){
            _write_error_db_log(__FUNCTION__, $e->getMessage());
        }
    }

    public function get_session_notice(){
        try{
            $db = $this->get_db_factory();
            $row = $db->fetchRow('select get_session_notice() id');
            $result = $row['id'];
            if ($result == ''){
                return array();
            } else{
                $tmp = explode('@', $result);
                array_pop($tmp);
                return $tmp;
            }
        }
        catch(exception $e){
            _write_error_db_log(__FUNCTION__, $e->getMessage());
        }
    }

    public function get_session_user_id()
    {
        try{
            $db = $this->get_db_factory();
            $stmt = $db->query("select get_session_user_id() user_id;");
            $rows = $stmt->fetchAll();
            return $rows;
        } catch(Exception $e){
            _write_error_db_log(__FUNCTION__, $e->getMessage());
        }
    }

    public function check_func_grant($func)
    {
        if (getHttpHost() == 'http://localhost'){
            return 1;
        }
        $result = 0;
        try{
            $db = Zend_Db_Table::getDefaultAdapter();
            $p['employee_id'] = getCurUser();
            $p['func'] = $func;
            $stmt = $db->query("select admin.check_func_grant(:employee_id, :func) id;", $p);
            $rows = $stmt->fetchAll();
            $result = $rows[0]['id'];
        } catch(Exception $e){
            _write_error_db_log(__FUNCTION__, $e->getMessage());
        }
        return $result;
    }

    public function error_print($error, $sql){
        $sql = '<a href="javascript:void(0)" onclick="mycopy(this)">'.$sql.'</a>';
        if (!in_array(getCurPositionCode(), array("ADMIN", "EXCEPTION"))){
            $error = 'Недостаточно прав';
            $sql = '';
        }

        if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')
        {
            echo "
                <div class='alert alert-danger col-lg-12 exception_class' style='margin-bottom: 10px;'>
                    ".$error.' '.$sql."
                </div>
                <div class='clearfix'></div>
            ";
        }else{
            echo "
                <div class='alert alert-danger col-lg-12 exception_class exception_class_hide' style='margin-bottom: 10px;'>
                    ".$error.' '.$sql."
                </div>
                <div class='clearfix'></div>
            ";
        }
    }

    public function error_throw($error, $sql){
        $sql = '<a href="javascript:void(0)" onclick="mycopy(this)">'.$sql.'</a>';
        if (!in_array(getCurPositionCode(), array("ADMIN", "EXCEPTION"))){
            $error = 'Недостаточно прав';
            $sql = '';
        }

        if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')
        {
            throw new Exception($error.' '.$sql, 20403);
        } else {
            echo "
                <div class='alert alert-danger col-lg-12 exception_class' style='margin-top: 10px;'>
                    ".$error.' '.$sql."
                </div>
                <div class='clearfix'></div>
            ";
        }
    }

    //Получение множественных данных
    public function readSP($method, $sql, $p=0, $cur2 = false, $db=null){
        if ($this->check_func_grant($sql) == 0){
            $this->error_print('Нет доступа на выполнение функции ', $sql);
            return;
        }
        $method = strtolower($method);
        $sql = str_replace("'cur'","'".$method."'",$sql);

        $flag = false;
        $this->set_session();
        _write_db_functions($sql, $p);
        $start_time = microtime(true);
        try{
            if ($db == null){
                $flag = true;
                $db = $this->get_db_factory();
                $db->beginTransaction();
            }
            if ($p === 0){
                $db->query('select '.$sql);
            } else {
                $db->query('select '.$sql, $p);
            }
            $stmt = $db->query("FETCH ALL FROM ".$method.";");
            $rows = $stmt->fetchAll();
            if (!empty($rows)) {
                $result['value'] = $rows;
            }
            else{
                $result['value'] = array();
            }

            if ($cur2){
                $stmt = $db->query("FETCH ALL FROM cur2;");
                $rows = $stmt->fetchAll();
                if (!empty($rows)) {
                    $result['value2'] = $rows;
                }
                else{
                    $result['value2'] = array();
                }
            }

            if($flag){
                $db->commit();
            }
            $result['status'] = true;
        }
        catch(exception $e){
            $db->rollBack();
            $result['value'] = array();
            $result['error'] = _getErrorClean($method, $e->getMessage());
            $result['error_debug'] = _getErrorDebug($e->getMessage());
            $result['status'] = false;
        }
        _write_long_time_db_log($method, $start_time);
        return $result;
    }
    //Получение одной строки данных
    public function getSP($method, $sql, $p=0, $cur2 = false, $db=null){
        if ($this->check_func_grant($sql) == 0){
            $this->error_print('Нет доступа на выполнение функции ', $sql);
            return;
        }
        $method = strtolower($method);
        $sql = str_replace("'cur'","'".$method."'",$sql);
        $flag = false;
        $this->set_session();
        _write_db_functions($sql, $p);
        $start_time = microtime(true);
        try{
            if ($db == null){
                $flag = true;
                $db = $this->get_db_factory();
                $db->beginTransaction();
            }
            if ($p === 0){
                $db->query('select '.$sql);
            } else {
                $db->query('select '.$sql, $p);
            }
            $stmt = $db->query("FETCH ALL FROM ".$method.";");
            $rows = $stmt->fetch();
            if (!empty($rows)) {
                $result['value'] = $rows;
            }
            else{
                $result['value'] = null;
            }

            if ($cur2){
                $stmt = $db->query("FETCH ALL FROM cur2;");
                $rows = $stmt->fetchAll();
                if (!empty($rows)) {
                    $result['value2'] = $rows;
                }
                else{
                    $result['value2'] = array();
                }
            }
            if($flag){
                $db->commit();
            }
            $result['status'] = true;
        }
        catch(exception $e){
            $db->rollBack();
            $result['value'] = null;
            $result['error'] = _getErrorClean($method, $e->getMessage());
            $result['error_debug'] = _getErrorDebug($e->getMessage());
            $result['status'] = false;
        }
        _write_long_time_db_log($method, $start_time);
        return $result;
    }
    //Получение одного значения
    public function scalarSP($method, $sql, $p, $result_column, $db=null){
        if ($this->check_func_grant($sql) == 0){
            $this->error_throw('Нет доступа на выполнение функции ', $sql);
            return;
        }
        $this->set_session();
        _write_db_functions($sql, $p);
        $start_time = microtime(true);
        try{
            if ($db == null){
                $db = $this->get_db_factory();
            }
            $row = $db->fetchRow('select '.$sql, $p);
            if (!empty($row)) {
                $result['value'] = $row[$result_column];
            }
            else{
                $result['value'] = null;
            }
            $result['status'] = true;
        }
        catch(exception $e){
            $result['value'] = null;
            $result['error'] = _getErrorClean($method, $e->getMessage());
            $result['error_debug'] = _getErrorDebug($e->getMessage());
            $result['status'] = false;
        }
        _write_long_time_db_log($method, $start_time);
        return $result;
    }
    //Выполнение insert, update, delete
    public function execSP($method, $sql, $p, $result_column=0, $db=null){
        if ($this->check_func_grant($sql) == 0){
            $this->error_throw('Нет доступа на выполнение функции ', $sql);
            return;
        }
        $this->set_session();
        _write_db_functions($sql, $p);
        $start_time = microtime(true);
        try{
            if ($db == null){
                $db = $this->get_db_factory();
            }
            $row = $db->fetchRow('select '.$sql, $p);
            if (!empty($row)) {
                if ($result_column === 0){
                    $result['value'] = null;
                } else {
                    $result['value'] = $row[$result_column];
                }
            }
            else{
                $result['value'] = null;
            }
            $result['status'] = true;
            $result['notice'] = $this->get_session_notice();
        }
        catch(exception $e){
            $result['value'] = null;
            $result['notice'] = array();
            $result['error'] = _getErrorClean($method, $e->getMessage());
            $result['error_debug'] = _getErrorDebug($e->getMessage());
            $result['status'] = false;
        }
        _write_long_time_db_log($method, $start_time);
        return $result;
    }

}

