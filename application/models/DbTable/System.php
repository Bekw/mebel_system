<?php

class Application_Model_DbTable_System extends Application_Model_DbTable_Parent{

    /* samples
    public function checkToken($token,$user_id){
        $p['user_id'] = $user_id;
        $p['token'] = $token;
        $result = $this->scalarSP('check_token(:user_id, :token) cnt', $p, 'cnt');
        return $result;
    }

    public function get_student($student_id){
        $p['student_id_'] = $student_id;
        $result = $this->getSP("get_student('cur', :student_id_)", $p);
        return $result;
    }

    public function get_control_student($student_id){
        $p['student_id'] = $student_id;
        $result = $this->readSP("get_control_student('cur',:student_id)",$p);
        return $result;
    }

    */
    public function check_grant_boolean($grant_code){
        $p['employee_id'] = getCurUser();
        $p['grant_code'] = $grant_code;
        $result = $this->scalarSP(__FUNCTION__, "admin.check_grant_boolean(:employee_id, :grant_code) result", $p, "result");
        return $result;
    }

    public function employee_set_last_login(){
        $p['employee_id'] = getCurUser();
        $result = $this->execSP(__FUNCTION__, 'admin.employee_set_last_login(:employee_id) id', $p, 'id');
        return $result;
    }

    public function menu_read_for_employee_deleted(){
        $p['employee_id'] = getCurUser();
        $result = $this->readSP(__FUNCTION__, "admin.menu_read_for_employee('cur',:employee_id)",$p);
        return $result;
    }
    public function menu_read_recursive($menu_pid, $menu_global_id){
        $p['employee_id'] = getCurUser();
        $p['menu_pid'] = $menu_pid;
        $p['menu_global_id'] = $menu_global_id;
        $result = $this->readSP(__FUNCTION__, "admin.menu_read_recursive('cur',:employee_id, :menu_pid, :menu_global_id)",$p);
        return $result;
    }
    public function get_first_menu_action(){
        $p['employee_id'] = getCurUser();
        $result = $this->getSP(__FUNCTION__, "admin.get_first_menu_action('cur', :employee_id) result", $p);
        return $result;
    }

    public function read_group(){
        $result = $this->readSP(__FUNCTION__, "admin.read_group('cur')");
        return $result;
    }

    public function get_group($group_id){
        $p['group_id'] = $group_id;
        $result = $this->getSP(__FUNCTION__, "admin.get_group('cur', :group_id)", $p);
        return $result;
    }

    public function upd_group($a){
        $p['group_id'] = $a['group_id'];
        $p['group_name'] = $a['group_name'];
        $p['group_code'] = $a['group_code'];
        $result = $this->execSP(__FUNCTION__, 'admin.upd_group(:group_id, :group_name, :group_code) id', $p, 'id');
        return $result;
    }

    public function del_group($group_id){
        $p['group_id'] = $group_id;
        $result = $this->execSP(__FUNCTION__, 'admin.del_group(:group_id) id', $p, 'id');
        return $result;
    }

    public function menu_read($group_id){
        $p['group_id'] = $group_id;
        $result = $this->readSP(__FUNCTION__, "admin.menu_read('cur', :group_id)", $p);
        return $result;
    }

    public function menu_read_for_select(){
        $result = $this->readSP(__FUNCTION__, "admin.menu_read_for_select('cur')");
        return $result;
    }

    public function get_menu($menu_id){
        $p['menu_id'] = $menu_id;
        $result = $this->getSP(__FUNCTION__, "admin.get_menu('cur', :menu_id)", $p);
        return $result;
    }

    public function upd_menu($a){
        $p['menu_id'] = $a['menu_id'];
        $p['menu_pid'] = null;
        if($a['menu_pid'] > 0){
            $p['menu_pid'] = $a['menu_pid'];
        }
        $p['menu_name'] = $a['menu_name'];
        $p['order_num'] = $a['order_num'];
        $p['menu_action'] = $a['menu_action'];
        $p['is_active'] = $a['is_active'];
        $p['icon'] = $a['icon'];
        $result = $this->execSP(__FUNCTION__, 'admin.upd_menu(:menu_id, :menu_pid, :menu_name, :order_num, :menu_action, :is_active, :icon) id', $p, 'id');
        return $result;
    }

    public function del_menu($menu_id){
        $p['menu_id'] = $menu_id;
        $result = $this->execSP(__FUNCTION__, 'admin.del_menu(:menu_id) id', $p, 'id');
        return $result;
    }

    public function read_employee($email, $fio){
        $p['email'] = $email;
        $p['fio'] = $fio;
        $result = $this->readSP(__FUNCTION__, "admin.read_employee('cur', :email, :fio)", $p);
        return $result;
    }

    public function get_employee($employee_id){
        $p['employee_id'] = $employee_id;
        $result = $this->getSP(__FUNCTION__, "admin.get_employee('cur', :employee_id)", $p);
        return $result;
    }

    public function block_employee($employee_id){
        $p['employee_id'] = $employee_id;
        $result = $this->scalarSP(__FUNCTION__, "admin.block_employee(:employee_id) result", $p, "result");
        return $result;
    }

    public function unblock_employee($employee_id){
        $p['employee_id'] = $employee_id;
        $result = $this->scalarSP(__FUNCTION__, "admin.unblock_employee(:employee_id) result", $p, "result");
        return $result;
    }

    public function upd_employee($a){
        $p['employee_id'] = $a['employee_id'];
        $p['email'] = $a['email'];
        $p['fio'] = $a['fio'];
        $p['is_active'] = $a['is_active'];
        $p['position_id'] = $a['position_id'];
        $p['phone'] = $a['phone'];
        $result = $this->execSP(__FUNCTION__, 'admin.upd_employee(:employee_id, :email, :fio, :is_active, :position_id, :phone) id', $p, 'id');
        return $result;
    }

    public function del_employee($employee_id){
        $p['employee_id'] = $employee_id;
        $result = $this->execSP(__FUNCTION__, 'admin.del_employee(:employee_id) id', $p, 'id');
        return $result;
    }

    public function group_menu_link($group_id, $menu_id){
        $p['group_id'] = $group_id;
        $p['menu_id'] = $menu_id;
        $result = $this->execSP(__FUNCTION__, 'admin.group_menu_link(:group_id, :menu_id) id', $p, 'id');
        return $result;
    }

    public function group_grant_link($group_id, $grant_id){
        $p['group_id'] = $group_id;
        $p['grant_id'] = $grant_id;
        $result = $this->execSP(__FUNCTION__, 'admin.group_grant_link(:group_id, :grant_id) id', $p, 'id');
        return $result;
    }

    public function employee_group_link($group_id, $employee_id){
        $p['group_id'] = $group_id;
        $p['employee_id'] = $employee_id;
        $result = $this->execSP(__FUNCTION__, 'admin.employee_group_link(:group_id, :employee_id) id', $p, 'id');
        return $result;
    }

    public function group_read($employee_id){
        $p['employee_id'] = $employee_id;
        $result = $this->readSP(__FUNCTION__, "admin.group_read('cur', :employee_id)", $p);
        return $result;
    }

    public function change_password($a){
        $p['employee_id'] = getCurUser();
        $p['old_password'] = $a['old_password'];
        $p['new_password'] = $a['new_password'];
        $p['confirm_new_password'] = $a['confirm_new_password'];
        $result = $this->execSP(__FUNCTION__, 'admin.change_password(:employee_id, :old_password, :new_password, :confirm_new_password) id', $p, 'id');
        return $result;
    }

    public function reset_password($employee_id){
        $p['employee_id'] = $employee_id;
        $result = $this->scalarSP(__FUNCTION__, "admin.reset_password(:employee_id) result", $p, "result");
        return $result;
    }
    public function read_position(){
        $result = $this->readSP(__FUNCTION__, "admin.read_position('cur')");
        return $result;
    }
    public function read_menu_json($group_id){
        $p['group_id'] = $group_id;
        $result = $this->scalarSP(__FUNCTION__, "admin.read_menu_json(:group_id) result", $p, "result");
        return $result;
    }

}