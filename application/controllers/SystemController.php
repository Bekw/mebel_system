<?php
require_once 'ParentController.php';

class SystemController extends ParentController{

    public function preDispatch(){
        $action_name =  (Zend_Controller_Front::getInstance()->getRequest()->getActionName());
        //Прописать action-ы в которых нужна сессия
        if ($action_name == 'notification-list' || $action_name == 'myaction2'){
            $params = $this->_getAllParams();
            foreach ($params as $key => $value) {
                $this->session->param[$key] = $value;
            }
        }
    }

    public function init(){
        $this->_helper->layout->setLayout('layout-system');
        parent::init();
        $this->user_id = 0;
        $action_name =  (Zend_Controller_Front::getInstance()->getRequest()->getActionName());
        $actions_except = array('login',
                                'send-mail',
                                'check-mail');
        if (!in_array($action_name, $actions_except)){
            if (!Zend_Auth::getInstance()->hasIdentity()){
                $this->_redirect('/system/login');
            }
            if (getCurEmployee() == 0){
                Zend_Auth::getInstance()->clearIdentity();
                $this->_redirect('/system/login');
            }
        }
        if (Zend_Auth::getInstance()->hasIdentity()){
            $this->user_id = getCurUser();
        }
    }

    public function dbLogAction(){
        $dir = $_SERVER['DOCUMENT_ROOT']. '/log/db_func';
        $file_name = $dir . "/" . Zend_Session::getId() . ".log";

        if($this->getRequest()->isPost()){
            $a = $this->_getAllParams();
            if (isset($a['btn_active'])){
                $this->session->is_db_func_log = true;
            }
            if (isset($a['btn_clear'])){
                if (file_exists($file_name)) {
                    unlink($file_name);
                    $this->session->is_db_func_log = false;
                }
            }
        }
        if (file_exists($file_name)) {
            $a = file_get_contents($file_name);
            $this->view->content = $a;
        }

    }

    public function indexJsonAction(){
        $mode = $this->_getParam('mode', '');
        $this->view->mode = $mode;

        if($mode == "block-employee"){
            $ob = new Application_Model_DbTable_System();
            $employee_id = $this->_getParam('employee_id', 0);
            $result = $ob->block_employee($employee_id);
            $this->view->result = $result;
        }

        if($mode == "unblock-employee"){
            $ob = new Application_Model_DbTable_System();
            $employee_id = $this->_getParam('employee_id', 0);
            $result = $ob->unblock_employee($employee_id);
            $this->view->result = $result;
        }

        if($mode == "del-employee"){
            $ob = new Application_Model_DbTable_System();
            $employee_id = $this->_getParam('employee_id', 0);
            $result = $ob->del_employee($employee_id);
            $this->view->result = $result;
        }

        if($mode == "upd-employee"){
            $ob = new Application_Model_DbTable_System();
            $a = $this->_getAllParams();
            $result = $ob->upd_employee($a);
            if ($result['status'] == false){
                $a['status'] = false;
                $a['error'] = $result['error'];
                $this->view->result = $a;
                return;
            }
            $this->view->result = $result;
        }

        if($mode == "upd-group"){
            $ob = new Application_Model_DbTable_System();
            $a = $this->_getAllParams();
            $result = $ob->upd_group($a);
            if ($result['status'] == false){
                $a['status'] = false;
                $a['error'] = $result['error'];
                $this->view->result = $a;
                return;
            }
            $this->view->result = $result;
        }

        if($mode == "del-group"){
            $ob = new Application_Model_DbTable_System();
            $group_id = $this->_getParam('group_id', 0);
            $result = $ob->del_group($group_id);
            $this->view->result = $result;
        }

        if($mode == "upd-menu"){
            $ob = new Application_Model_DbTable_System();
            $a = $this->_getAllParams();
            $result = $ob->upd_menu($a);
            if ($result['status'] == false){
                $a['status'] = false;
                $a['error'] = $result['error'];
                $this->view->result = $a;
                return;
            }
            $this->view->result = $result;
        }

        if($mode == "del-menu"){
            $ob = new Application_Model_DbTable_System();
            $menu_id = $this->_getParam('menu_id', 0);
            $result = $ob->del_menu($menu_id);
            $this->view->result = $result;
        }

        if($mode == "group-menu-link"){
            $ob = new Application_Model_DbTable_System();
            $group_id = $this->_getParam('group_id', 0);
            $menu_id = $this->_getParam('menu_id', 0);
            $result = $ob->group_menu_link($group_id, $menu_id);
            $this->view->result = $result;
        }

        if($mode == "menu-read-by-group"){
            $ob = new Application_Model_DbTable_System();
            $group_id = $this->_getParam('group_id', 0);
            $row = $ob->menu_read($group_id);
            $this->view->result = $row;
        }

        if($mode == "employee-group-link"){
            $ob = new Application_Model_DbTable_System();
            $group_id = $this->_getParam('group_id', 0);
            $employee_id = $this->_getParam('employee_id', 0);
            $result = $ob->employee_group_link($group_id, $employee_id);
            $this->view->result = $result;
        }

        if($mode == "group-read-by-employee"){
            $ob = new Application_Model_DbTable_System();
            $employee_id = $this->_getParam('employee_id', 0);
            $row = $ob->group_read($employee_id);
            $this->view->result = $row;
        }

        if($mode == "reset-password"){
            $ob = new Application_Model_DbTable_System();
            $employee_id = $this->_getParam('employee_id', 0);
            $result = $ob->reset_password($employee_id);
            $this->view->result = $result;
        }
    }

    public function blankAction(){
    }

    private function login($login, $password){
        $authAdapter = new Zend_Auth_Adapter_DbTable(Zend_Db_Table::getDefaultAdapter());
        $authAdapter->setTableName('admin.employee_view');
        $authAdapter->setIdentityColumn('email');
        $authAdapter->setCredentialColumn('passwd');

        $authAdapter->setIdentity(strtolower(trim($login)));
        $authAdapter->setCredential($password);
        $auth = Zend_Auth::getInstance();
        $result = $auth->authenticate($authAdapter);

        if ($result->isValid()){
            $identity = $authAdapter->getResultRowObject();
            $authStorage = $auth->getStorage();
            $authStorage->write($identity);
            return true;
        }
        else{
            return false;
        }
    }

    public function loginAction(){
        $ob = new Application_Model_DbTable_System();
        if (Zend_Auth::getInstance()->hasIdentity()){
            $row = $ob->get_first_menu_action();
            $row = $row['value'];
            $this->_redirect($row['menu_action'].'menu_global_id/'.$row['menu_id']);
        }
        $this->_helper->layout()->disableLayout();
        if($this->getRequest()->isPost()){
            $this->view->login = $this->_getParam('login', 'empty');
            $password = $this->_getParam('password', 'empty');
            if ($this->login($this->view->login, $password)){
                $ob->employee_set_last_login();
                $row = $ob->get_first_menu_action();
                $row = $row['value'];
                $this->_redirect($row['menu_action'].'menu_global_id/'.$row['menu_id']);
            } else {
                $this->view->error = "Неверный логин или пароль";
            }
        }
    }

    public function logoutAction(){
        $this->_helper->viewRenderer->setNoRender(true);
        Zend_Auth::getInstance()->clearIdentity();
        $this->_redirect('/system/login');
    }

    public function menuListAction(){
        $ob = new Application_Model_DbTable_System();
        $mode = $this->_getParam('mode', '');

        if ($mode == "menu-json"){
            $a = $this->_getAllParams();
            $this->_helper->AjaxContext()->addActionContext('menu-list', 'json')->initContext('json');
            $this->view->result = $ob->read_menu_json($a['group_id']);
            return;
        }

        $row = $ob->read_group();
        $this->view->row_group = $row['value'];
        $row = $ob->menu_read(0);
        $this->view->row_menu = $row['value'];
    }

    public function groupEditAction(){
        $this->_helper->layout->disableLayout();
        $this->view->group_id = $this->_getParam('group_id', 0);
        $ob = new Application_Model_DbTable_System();
        $row = $ob->get_group($this->view->group_id);
        $this->view->row = $row['value'];
    }

    public function menuEditAction(){
        $this->_helper->layout->disableLayout();
        $this->view->menu_id = $this->_getParam('menu_id', 0);
        $ob = new Application_Model_DbTable_System();
        $row = $ob->get_menu($this->view->menu_id);
        $this->view->row = $row['value'];
        $row = $ob->menu_read_for_select();
        $this->view->row_menu_for_select = $row['value'];
    }
    public function employeeListAction(){
        $this->view->email = $this->_getParam('email', '');
        $this->view->fio = $this->_getParam('fio', '');
        $ob = new Application_Model_DbTable_System();
        $row = $ob->read_employee($this->view->email, $this->view->fio);
        $this->view->row = $row['value'];
    }

    public function employeeEditAction(){
        $this->_helper->layout->disableLayout();
        $this->view->employee_id = $this->_getParam('employee_id', 0);
        $ob = new Application_Model_DbTable_System();
        $dict = new Application_Model_DbTable_Dict();
        $row = $ob->read_position();
        $this->view->row_position = $row['value'];
        $row = $ob->get_employee($this->view->employee_id);
        $this->view->row = $row['value'];
    }

    public function employeeGroupAction(){
        $ob = new Application_Model_DbTable_System();
        $row = $ob->read_employee('', '');
        $this->view->row_employee = $row['value'];
        $row = $ob->group_read(0);
        $this->view->row_group = $row['value'];
    }

    public function changePasswordAction(){
        $ob = new Application_Model_DbTable_System();
        if (isset($_POST["change_password"])) {
            $a = $this->_getAllParams();
            $result = $ob->change_password($a);
            if($result['status'] == false){
                $a['error'] = $result['error'];
                $this->view->row = $a;
                return;
            }
            $this->view->success_password_change = 1;
        }
    }

    public function sendMailAction(){
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(TRUE);
        $ob = new Application_Model_DbTable_System();
        $row = $ob->unisender_read_not_send_mail();
        $row = $row['value'];
        if(count($row) > 0){
            foreach ($row as $value){
                //ваш API-ключ
                $api_key = "6et356won8yrpngugzs7gas8xye17jcppsy11mta";

                // Параметры сообщения
                $email_from_name = 'EmenJihaz';
                $email_from_email = 'bereke.iitu.01@gmail.com';
                $email_to = $value['email'];
                $email_body = $value['email_content'];
                $email_subject = $value['email_title'];
                $list_id = 12576461;

                // Создаём POST-запрос
                $request = [
                    'api_key' => $api_key,
                    'email' => $email_to,
                    'sender_name' => $email_from_name,
                    'sender_email' => $email_from_email,
                    'subject' => $email_subject,
                    'body' => $email_body,
                    'list_id' => $list_id,
                ];


                // Устанавливаем соединение
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
                curl_setopt($ch, CURLOPT_TIMEOUT, 10);
                curl_setopt($ch, CURLOPT_URL, 'https://api.unisender.com/ru/api/sendEmail?format=json');

                $result = curl_exec($ch);

                if ($result) {
                    // Раскодируем ответ API-сервера
                    $jsonObj = json_decode($result);
                    if (null === $jsonObj) {
                        // Ошибка в полученном ответе
                        $ob->unisender_upd_mail($value['send_email_id'], $jsonObj->result->email_id, 'Invalid JSON');

                    } elseif (!empty($jsonObj->error)) {
                        // Ошибка отправки сообщения
                        $ob->unisender_upd_mail($value['send_email_id'], $jsonObj->result->email_id, 'An error occured %s (code: %s)' . $jsonObj->error, $jsonObj->code);
                    } else {
                        // Сообщение успешно отправлено
                        $ob->unisender_upd_mail($value['send_email_id'], $jsonObj->result->email_id, null);

                    }
                } else {
                    // Ошибка соединения с API-сервером
                    $ob->unisender_upd_mail($value['send_email_id'], $jsonObj->result->email_id, 'API access error');
                }
            }
        }
    }

    public function checkMailAction(){
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(TRUE);
        $ob = new Application_Model_DbTable_System();
        $row = $ob->unisender_read_send_mail();
        $row = $row['value'];
        if(strlen($row) > 0){
            // Ваш ключ доступа к API (из Личного Кабинета)
            $api_key = "6et356won8yrpngugzs7gas8xye17jcppsy11mta";

            // Перечислим несколько id через запятую
            $email_id = $row;

            // Создаём POST-запрос
            $POST = array (
                'api_key' => $api_key,
                'email_id' => $email_id
            );

            // Устанавливаем соединение
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $POST);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_URL,'https://api.unisender.com/ru/api/checkEmail?format=json');
            $result = curl_exec($ch);

            if ($result) {

                $array = json_decode($result, true);

                // Раскодируем ответ API-сервера
                $jsonObj = json_decode($result);

                if(null===$jsonObj) {
                    // Ошибка в полученном ответе
                    echo "Invalid JSON";

                }
                elseif(!empty($jsonObj->error)) {
                    // Ошибка получения статуса сообщения
                    echo "An error occured: " . $jsonObj->error . "(code: " . $jsonObj->code . ")";

                } else {
                    // Статусы доставки сообщения получены
                    foreach($array['result']['statuses'] as $value){
                        $ob->unisender_upd_mail_status($value['id'], $value['status']);
                    }

                }
            } else {
                // Ошибка соединения с API-сервером
                echo "API access error";
            }
        }
    }
    function notificationAction(){
        $this->_helper->layout->disableLayout();
        $ob = new Application_Model_DbTable_Email();
        $mode = $this->_getParam('mode', '');
        if ($mode == "get-cnt"){
            $this->_helper->AjaxContext()->addActionContext('notification', 'json')->initContext('json');
            $this->view->result = $ob->get_send_email_user_cnt();
            return;
        }
        if ($mode == "set-reviewed"){
            $this->_helper->AjaxContext()->addActionContext('notification', 'json')->initContext('json');
            $this->view->result = $ob->set_send_email_user_reviewed($this->_getParam('send_email_user_id', 0));
            return;
        }
        $row = $ob->read_send_email_user();
        if ($row['status'] == false){
            var_dump($row['error_debug']);
        }
        $this->view->row = $row['value'];
    }
    function notificationListAction(){
        $ob = new Application_Model_DbTable_Email();
        $this->view->search = $this->_getParam('search', '');
        $this->view->is_reviewed = parent::getSessionParam('is_reviewed', -1);
        $mode = $this->_getParam('mode', '');

        if ($mode == "review"){
            $this->_helper->AjaxContext()->addActionContext('notification-list', 'json')->initContext('json');
            $this->view->result = $ob->email_change_review($this->_getParam('send_email_user_id', 0));
            return;
        }

        $row = $ob->read_send_email_user();
        if ($row['status'] == false){
            var_dump($row['error_debug']);
        }
        $this->view->row_review = $row['value'];

        $row = $ob->read_send_email_user_all($this->view->search, $this->view->is_reviewed);
        if ($row['status'] == false){
            var_dump($row['error_debug']);
        }
        $this->view->row = $row['value'];
    }
    function notificationViewAction(){
        $ob = new Application_Model_DbTable_Email();
        $this->view->send_email_user_id = $this->_getParam('send_email_user_id', 0);
        $mode = $this->_getParam('mode', '');
        $row = $ob->get_send_email_user($this->view->send_email_user_id);
        if ($row['status'] == false){
            var_dump($row['error_debug']);
        }
        $this->view->row = $row['value'];
    }

    public function permissionDeniedAction(){
    }

}

