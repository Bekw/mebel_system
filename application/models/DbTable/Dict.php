<?php

class Application_Model_DbTable_Dict extends Application_Model_DbTable_Parent{

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

    public function create_request($a){
        $p['client_fio'] = $a['client_fio'];
        $p['address'] = $a['address'];
        $p['client_phone'] = $a['client_phone'];
        $p['house_area'] = zeroToNull($a['house_area']);
        $p['measure_time'] = zeroToNull($a['measure_time']);
        $p['client_comment'] = $a['client_comment'];
        $result = $this->execSP(__FUNCTION__, "public.create_request(:client_fio, :address, :client_phone, :house_area, to_date(:measure_time, 'dd.mm.yyyy'), :client_comment) res", $p, 'res');
        return $result;
    }

    public function read_request($status_id){
        $p['status_id'] = $status_id;
        $result = $this->readSP(__FUNCTION__, "public.read_request('cur', :status_id)", $p);
        return $result;
    }

    public function get_request($request_id){
        $p['request_id'] = $request_id;
        $result = $this->getSP(__FUNCTION__, "public.get_request('cur', :request_id)", $p);
        return $result;
    }

    public function del_request($request_id){
        $p['request_id'] = $request_id;
        $result = $this->execSP(__FUNCTION__, "public.del_request(:request_id_)", $p);
        return $result;
    }

    public function read_document_by_request($request_id){
        $p['request_id'] = $request_id;
        $result = $this->readSP(__FUNCTION__, "public.read_document_by_request('cur', :request_id)", $p);
        return $result;
    }

    public function read_document_type_fs(){
        $result = $this->readSP(__FUNCTION__, "public.read_document_type_fs('cur')");
        return $result;
    }

    public function read_work_type_fs(){
        $result = $this->readSP(__FUNCTION__, "public.read_work_type_fs('cur')");
        return $result;
    }

    public function read_item_type_fs(){
        $result = $this->readSP(__FUNCTION__, "public.read_item_type_fs('cur')");
        return $result;
    }

    public function insert_document_request($a){
        $p['document_url'] = null;
        $p['document_name'] = null;
        $tmpFilePath = $_FILES['upload']['tmp_name'];

        if ($tmpFilePath != ""){
            $ext = pathinfo($_FILES['upload']['name'], PATHINFO_EXTENSION);
            $dir = $_SERVER['DOCUMENT_ROOT']."/documents/request_docs/";
            if (!file_exists($dir)) {
                mkdir($dir, 0777, true);
            }
            $filename = "/documents/".date('Y.m.d')."/request_docs/".uniqid('im_doc_',true)."." . $ext;
            $p['document_name'] = $_FILES['upload']['name'];
            $p['document_url'] = $filename;
//            log_client_doc('TMP_FILE_PATH 4', json_encode($p));
            $newFilePath = $_SERVER['DOCUMENT_ROOT']. $filename;
            if(move_uploaded_file_smart($tmpFilePath, $newFilePath)) {
            }
        }
        $p['document_type_id'] = $a['document_type_id'];
        $p['request_id'] = $a['request_id'];
        $result = $this->execSP(__FUNCTION__, "public.insert_document_request(:document_type_id, :document_name, :document_url, :request_id)", $p);
        return $result;
    }

    public function upd_work_doc($a){
        $p['document_url'] = null;
        $p['document_name'] = null;
        $tmpFilePath = $_FILES['upload']['tmp_name'];

        if ($tmpFilePath != ""){
            $ext = pathinfo($_FILES['upload']['name'], PATHINFO_EXTENSION);
            $dir = $_SERVER['DOCUMENT_ROOT']."/documents/work_docs/";
            if (!file_exists($dir)) {
                mkdir($dir, 0777, true);
            }
            $filename = "/documents/".date('Y.m.d')."/work_docs/".uniqid('im_doc_',true)."." . $ext;
            $p['document_name'] = $_FILES['upload']['name'];
            $p['document_url'] = $filename;
//            log_client_doc('TMP_FILE_PATH 4', json_encode($p));
            $newFilePath = $_SERVER['DOCUMENT_ROOT']. $filename;
            if(move_uploaded_file_smart($tmpFilePath, $newFilePath)) {
            }
        }
        $p['document_type_id'] = $a['document_type_id'];
        $p['work_id'] = $a['work_id'];
        $result = $this->execSP(__FUNCTION__, "public.upd_work_doc(:document_type_id, :document_name, :document_url, :work_id)", $p);
        return $result;
    }

    public function delete_document($request_id){
        $p['request_id'] = $request_id;
        $result = $this->execSP(__FUNCTION__, "public.del_document(:request_id)", $p);
        return $result;
    }
    public function del_work($request_id){
        $p['request_id'] = $request_id;
        $result = $this->execSP(__FUNCTION__, "public.del_work(:request_id)", $p);
        return $result;
    }

    public function del_work_item($work_item_id){
        $p['work_item_id'] = $work_item_id;
        $result = $this->execSP(__FUNCTION__, "public.del_work_item(:work_item_id)", $p);
        return $result;
    }

    public function read_work_request($request_id){
        $p['request_id'] = $request_id;
        $result = $this->readSP(__FUNCTION__, "public.read_work_request('cur', :request_id)", $p);
        return $result;
    }

    public function insert_work($a){
        $p['request_id'] = $a['request_id'];
        $p['work_type_id'] = $a['work_type_id'];
        $p['work_name'] = $a['work_name'];
        $result = $this->execSP(__FUNCTION__, "public.insert_work(:request_id, :work_type_id, :work_name)", $p);
        return $result;
    }

    public function get_work($work_id){
        $p['work_id'] = $work_id;
        $result = $this->getSP(__FUNCTION__, "public.get_work('cur', :work_id)", $p);
        return $result;
    }

    public function get_work_item($work_item_id){
        $p['work_item_id'] = $work_item_id;
        $result = $this->getSP(__FUNCTION__, "public.get_work_item('cur', :work_item_id)", $p);
        return $result;
    }

    public function read_employee_fs($position){
        $p['position'] = $position;
        $result = $this->readSP(__FUNCTION__, "public.read_employee_fs('cur', :position)", $p);
        return $result;
    }

    public function read_work_stage($work_id){
        $p['work_id'] = $work_id;
        $result = $this->readSP(__FUNCTION__, "public.read_work_stage('cur', :work_id)", $p);
        return $result;
    }

    public function read_work_item($work_id){
        $p['work_id'] = $work_id;
        $result = $this->readSP(__FUNCTION__, "public.read_work_item('cur', :work_id)", $p);
        return $result;
    }

    public function upd_work($a){
        $p['work_id'] = $a['work_id'];
        $p['tehnolog_id'] = $a['tehnolog_id'];
        $p['ldsp_employee_id'] = $a['ldsp_employee_id'];
        $p['multicat_employee_id'] = $a['multicat_employee_id'];
        $p['stolyar_employee_id'] = $a['stolyar_employee_id'];
        $p['master_id'] = $a['master_id'];
        $p['grinder_employee_id'] = $a['grinder_employee_id'];
        $p['malyar_employee_id'] = $a['malyar_employee_id'];
        $p['collector_employee_id'] = $a['collector_employee_id'];
        $p['delivery_employee_id'] = $a['delivery_employee_id'];
        $p['ustan_employee_id'] = $a['ustan_employee_id'];
        $result = $this->execSP(__FUNCTION__, "public.upd_work(:work_id, :tehnolog_id, :ldsp_employee_id, :multicat_employee_id, :stolyar_employee_id, :master_id, :grinder_employee_id, :malyar_employee_id, :collector_employee_id, :delivery_employee_id, :ustan_employee_id)", $p);
        return $result;
    }

    public function upd_work_item($a){
        $p['work_item_id'] = $a['work_item_id'];
        $p['work_id'] = $a['work_id'];
        $p['item_type_id'] = $a['item_type_id'];
        $p['common_measure_type_id'] = $a['common_measure_type_id'];
        $p['cnt'] = zeroToNull($a['cnt']);
        $result = $this->execSP(__FUNCTION__, "public.upd_work_item(:work_item_id, :work_id, :item_type_id, :common_measure_type_id, :cnt)", $p);
        return $result;
    }

    public function upd_designer_comment($a){
        $p['work_id'] = $a['work_id'];
        $p['designer_comment'] = $a['designer_comment'];
        $result = $this->execSP(__FUNCTION__, "public.upd_designer_comment(:work_id, :designer_comment)", $p);
        return $result;
    }

    public function read_measure($work_id, $measure_type_id){
        $p['work_id'] = $work_id;
        $p['measure_type_id'] = $measure_type_id;
        $result = $this->readSP(__FUNCTION__, "public.read_measure('cur', :work_id, :measure_type_id)", $p);
        return $result;
    }

    public function del_measure($measure_id){
        $p['measure_id'] = $measure_id;
        $result = $this->execSP(__FUNCTION__, "public.del_measure(:measure_id)", $p);
        return $result;
    }

    public function insert_measure($a){
        $p['work_id'] = $a['work_id'];
        $p['measure_type_id'] = $a['measure_type_id'];
        $p['cnt'] = zeroToNull($a['cnt']);
        $p['height'] = zeroToNull($a['height']);
        $p['length'] = zeroToNull($a['length']);
        $result = $this->execSP(__FUNCTION__, "public.insert_measure(:work_id, :measure_type_id, :cnt, :height, :length)", $p);
        return $result;
    }

    public function read_furniture($work_id){
        $p['work_id'] = $work_id;
        $result = $this->readSP(__FUNCTION__, "public.read_furniture('cur', :work_id)", $p);
        return $result;
    }

    public function get_furniture($furniture_id){
        $p['furniture_id'] = $furniture_id;
        $result = $this->getSP(__FUNCTION__, "public.get_furniture('cur', :furniture_id)", $p);
        return $result;
    }

    public function del_furniture($furniture_id){
        $p['furniture_id'] = $furniture_id;
        $result = $this->execSP(__FUNCTION__, "public.del_furniture(:furniture_id)", $p);
        return $result;
    }

    public function upd_furniture($a){
        $p['furniture_id'] = $a['furniture_id'];
        $p['work_id'] = $a['work_id'];
        $p['material_id'] = $a['material_id'];
        $p['cnt'] = zeroToNull($a['cnt']);
        $p['price'] = zeroToNull($a['price']);
        $result = $this->execSP(__FUNCTION__, "public.upd_furniture(:furniture_id, :work_id, :material_id, :cnt, :price)", $p);
        return $result;
    }

    public function upd_work_measure_total($a){
        $p['work_id'] = $a['work_id'];
        $p['mdf_total'] = zeroToNull($a['mdf_total']);
        $p['color_type'] = $a['color_type'];
        $p['patina'] = $a['patina'];
        $p['color_consumption'] = zeroToNull($a['color_consumption']);
        $p['grunt_consumption'] = zeroToNull($a['grunt_consumption']);
        $p['lak_consumption'] = zeroToNull($a['lak_consumption']);
        $result = $this->execSP(__FUNCTION__, "public.upd_work_measure_total(:work_id, :mdf_total, :color_type, :patina, :color_consumption, :grunt_consumption, :lak_consumption)", $p);
        return $result;
    }

    public function upd_work_ldsp($a){
        $p['work_id'] = $a['work_id'];
        $p['ldsp_cnt'] = zeroToNull($a['ldsp_cnt']);
        $p['ldsp_color'] = zeroToNull($a['ldsp_color']);
        $p['pvh'] = zeroToNull($a['pvh']);
        $result = $this->execSP(__FUNCTION__, "public.upd_work_ldsp(:work_id, :ldsp_cnt, :ldsp_color, :pvh)", $p);
        return $result;
    }

    public function read_work_mark($work_id){
        $p['work_id'] = $work_id;
        $result = $this->readSP(__FUNCTION__, "public.read_work_mark('cur', :work_id)", $p);
        return $result;
    }

    public function upd_work_mark($a){
        $p['work_id'] = $a['work_id'];
        $p['mark_type_code'] = $a['mark_type_code'];
        $result = $this->execSP(__FUNCTION__, "public.upd_work_mark(:work_id, :mark_type_code)", $p);
        return $result;
    }

    public function request_is_paid($a){
        $p['request_id'] = $a['request_id'];
        $p['is_paid'] = $a['is_paid'];
        $result = $this->execSP(__FUNCTION__, "public.request_is_paid(:request_id, :is_paid)", $p);
        return $result;
    }
    public function request_is_accepted($a){
        $p['request_id'] = $a['request_id'];
        $p['is_accepted'] = $a['is_accepted'];
        $result = $this->execSP(__FUNCTION__, "public.request_is_accepted(:request_id, :is_accepted)", $p);
        return $result;
    }

    public function upd_work_stage_accept($a){
        $p['work_id'] = $a['work_id'];
        $p['is_accept'] = $a['is_accept'];
        $result = $this->execSP(__FUNCTION__, "public.upd_work_stage_accept(:work_id, :is_accept)", $p);
        return $result;
    }

    public function upd_stage($a){
        $p['work_id'] = $a['work_id'];
        $p['stage_id'] = $a['stage_id'];
        $p['comment'] = $a['comment'];
        $result = $this->execSP(__FUNCTION__, "public.upd_stage(:work_id, :stage_id, :comment)", $p);
        return $result;
    }

    public function read_work_docs($work_id){
        $p['work_id'] = $work_id;
        $result = $this->readSP(__FUNCTION__, "public.read_work_docs('cur', :work_id)", $p);
        return $result;
    }

    public function read_control_measure($work_id){
        $p['work_id'] = $work_id;
        $result = $this->readSP(__FUNCTION__, "public.read_control_measure('cur', :work_id)", $p);
        return $result;
    }

    public function read_stage_fs(){
        $result = $this->readSP(__FUNCTION__, "public.read_stage_fs('cur')");
        return $result;
    }

    public function read_work_comment($work_id){
        $p['work_id'] = $work_id;
        $result = $this->readSP(__FUNCTION__, "public.read_work_comment('cur', :work_id)", $p);
        return $result;
    }

    public function read_comment_file($work_comment_id){
        $p['work_comment_id'] = $work_comment_id;
        $result = $this->readSP(__FUNCTION__, "public.read_comment_file('cur', :work_comment_id)", $p);
        return $result;
    }

    public function read_work($stage_id){
        $p['stage_id'] = $stage_id;
        $result = $this->readSP(__FUNCTION__, "public.read_work('cur', :stage_id)", $p);
        return $result;
    }

    public function del_work_comment($work_comment_id){
        $p['work_comment_id'] = $work_comment_id;
        $result = $this->execSP(__FUNCTION__, "public.del_work_comment(:work_comment_id)", $p);
        return $result;
    }

    public function upd_is_accept_payment($payment_id){
        $p['payment_id'] = $payment_id;
        $result = $this->execSP(__FUNCTION__, "public.upd_is_accept_payment(:payment_id)", $p);
        return $result;
    }

    public function upd_request($a){
        $p['request_id'] = $a['request_id'];
        $p['total_price'] = zeroToNull($a['total_price']);
        $p['client_comment'] = $a['client_comment'];
        $p['measure_time'] = zeroToNull($a['measure_time']);
        $result = $this->execSP(__FUNCTION__, "public.upd_request(:request_id, :total_price, to_date(:measure_time, 'dd.mm.yyyy'), :client_comment)", $p);
        return $result;
    }

    public function insert_comment($a){
        $db = $this->get_new_db_factory();
        $db->beginTransaction();

        $p['work_id'] = $a['work_id'];
        $p['comment'] = $a['comment'];
        $result = $this->execSP(__FUNCTION__, "public.insert_comment(:work_id, :comment) id", $p, 'id');

        if ($result['status'] == false){
            $db->rollBack();
            return $result;
        }

        $p2['work_comment_id'] = $result['value'];
        $p2['document_url'] = '';
        $p2['document_name'] = '';
        if(!isset($_FILES['work_files'])){
            $_FILES['work_files'] = null;
        }
        try{
            $total = mycount($_FILES['work_files']['name']);
            for($i = 0; $i < $total; $i++) {
                $tmpFilePath = $_FILES['work_files']['tmp_name'][$i];
                if ($tmpFilePath != ""){
                    $ext = pathinfo($_FILES['work_files']['name'][$i], PATHINFO_EXTENSION);
                    $dir = $_SERVER['DOCUMENT_ROOT']."/documents/remont_comment_docs/";
                    if (!file_exists($dir)) {
                        mkdir($dir, 0777, true);
                    }
                    $uniq = uniqid('work_file_',true);
                    $filename = "/documents/".date('Y.m.d')."/remont_comment_docs/".$uniq."." . $ext;
                    $newFilePath = $_SERVER['DOCUMENT_ROOT']. $filename;
                    $p2['document_url'] = $filename;
                    $p2['document_name'] = $_FILES['work_files']['name'][$i];

                    if(move_uploaded_file_smart($tmpFilePath, $newFilePath)) {
                        $result = $this->execSP(__FUNCTION__, "public.insert_comment_file(:work_comment_id, :document_name, :document_url) id", $p2, "id", $db);
                        if ($result['status'] == false){
                            $db->rollBack();
                            return $result;
                        }
                    }
                }
            }
        } catch(Exception $e){
            $result['status'] = false;
            $result['error'] = $e->getMessage()."->>"."Ошибка при загрузке файла";
            $db->rollBack();
            return $result;
        }
        $db->commit();
        return $result;
    }

    public function read_indicator(){
        $result = $this->readSP(__FUNCTION__, "public.read_indicator('cur')");
        return $result;
    }
    public function read_indicator_design($color){
        $p['color'] = $color;
        $result = $this->readSP(__FUNCTION__, "public.read_indicator_design('cur', :color)", $p);
        return $result;
    }
    public function read_indicator_ready($color){
        $p['color'] = $color;
        $result = $this->readSP(__FUNCTION__, "public.read_indicator_ready('cur', :color)", $p);
        return $result;
    }
    public function read_indicator_ldsp($color){
        $p['color'] = $color;
        $result = $this->readSP(__FUNCTION__, "public.read_indicator_ldsp('cur', :color)", $p);
        return $result;
    }
    public function read_indicator_multicat($color){
        $p['color'] = $color;
        $result = $this->readSP(__FUNCTION__, "public.read_indicator_multicat('cur', :color)", $p);
        return $result;
    }
    public function read_indicator_stolyar($color){
        $p['color'] = $color;
        $result = $this->readSP(__FUNCTION__, "public.read_indicator_stolyar('cur', :color)", $p);
        return $result;
    }
    public function read_indicator_master($color){
        $p['color'] = $color;
        $result = $this->readSP(__FUNCTION__, "public.read_indicator_master('cur', :color)", $p);
        return $result;
    }
    public function read_indicator_shlif($color){
        $p['color'] = $color;
        $result = $this->readSP(__FUNCTION__, "public.read_indicator_shlif('cur', :color)", $p);
        return $result;
    }
    public function read_indicator_malyar($color){
        $p['color'] = $color;
        $result = $this->readSP(__FUNCTION__, "public.read_indicator_malyar('cur', :color)", $p);
        return $result;
    }
    public function read_indicator_upack($color){
        $p['color'] = $color;
        $result = $this->readSP(__FUNCTION__, "public.read_indicator_upack('cur', :color)", $p);
        return $result;
    }
    public function read_indicator_delivery($color){
        $p['color'] = $color;
        $result = $this->readSP(__FUNCTION__, "public.read_indicator_delivery('cur', :color)", $p);
        return $result;
    }
    public function read_work_type(){
        $result = $this->readSP(__FUNCTION__, "public.read_work_type('cur')");
        return $result;
    }

    public function get_work_type($work_type_id){
        $p['work_type_id'] = $work_type_id;
        $result = $this->getSP(__FUNCTION__, "public.get_work_type('cur', :work_type_id)", $p);
        return $result;
    }

    public function del_work_type($work_type_id){
        $p['work_type_id'] = $work_type_id;
        $result = $this->execSP(__FUNCTION__, "public.del_work_type(:work_type_id)", $p);
        return $result;
    }

    public function upd_work_type($a){
        $p['work_type_id'] = $a['work_type_id'];
        $p['name'] = $a['name'];
        $p['code'] = $a['code'];
        $result = $this->execSP(__FUNCTION__, "public.upd_work_type(:work_type_id, :name, :code)", $p);
        return $result;
    }

    public function read_document_type(){
        $result = $this->readSP(__FUNCTION__, "public.read_document_type('cur')");
        return $result;
    }

    public function get_document_type($document_type_id){
        $p['document_type_id'] = $document_type_id;
        $result = $this->getSP(__FUNCTION__, "public.get_document_type('cur', :document_type_id)", $p);
        return $result;
    }

    public function del_document_type($document_type_id){
        $p['document_type_id'] = $document_type_id;
        $result = $this->execSP(__FUNCTION__, "public.del_document_type(:document_type_id)", $p);
        return $result;
    }

    public function upd_document_type($a){
        $p['document_type_id'] = $a['document_type_id'];
        $p['name'] = $a['name'];
        $p['code'] = $a['code'];
        $result = $this->execSP(__FUNCTION__, "public.upd_document_type(:document_type_id, :name, :code)", $p);
        return $result;
    }

    public function read_material_type(){
        $result = $this->readSP(__FUNCTION__, "public.read_material_type('cur')");
        return $result;
    }

    public function get_material_type($material_type_id){
        $p['material_type_id'] = $material_type_id;
        $result = $this->getSP(__FUNCTION__, "public.get_material_type('cur', :material_type_id)", $p);
        return $result;
    }

    public function del_material_type($material_type_id){
        $p['material_type_id'] = $material_type_id;
        $result = $this->execSP(__FUNCTION__, "public.del_material_type(:material_type_id)", $p);
        return $result;
    }

    public function upd_material_type($a){
        $p['material_type_id'] = $a['material_type_id'];
        $p['material_type_name'] = $a['material_type_name'];
        $p['material_type_code'] = $a['material_type_code'];
        $result = $this->execSP(__FUNCTION__, "public.upd_material_type(:material_type_id, :material_type_name, :material_type_code)", $p);
        return $result;
    }

    public function read_material(){
        $result = $this->readSP(__FUNCTION__, "public.read_material('cur')");
        return $result;
    }

    public function get_material($material_id){
        $p['material_id'] = $material_id;
        $result = $this->getSP(__FUNCTION__, "public.get_material('cur', :material_id)", $p);
        return $result;
    }

    public function del_material($material_id){
        $p['material_id'] = $material_id;
        $result = $this->execSP(__FUNCTION__, "public.del_material(:material_id)", $p);
        return $result;
    }

    public function upd_material($a){
        $p['material_id'] = $a['material_id'];
        $p['material_type_id'] = $a['material_type_id'];
        $p['material_name'] = $a['material_name'];
        $result = $this->execSP(__FUNCTION__, "public.upd_material(:material_id, :material_type_id, :material_name)", $p);
        return $result;
    }

    public function read_employee_work(){
        $result = $this->readSP(__FUNCTION__, "public.read_employee_work('cur')");
        return $result;
    }

    public function read_employee_work_price($employee_id){
        $p['employee_id'] = $employee_id;
        $result = $this->readSP(__FUNCTION__, "public.read_employee_work_price('cur', :employee_id)", $p);
        return $result;
    }

    public function upd_employee_work($a){
        $p['employee_id'] = $a['employee_id'];
        $p['price'] = $a['price'];
        $p['date_begin'] = zeroToNull($a['date_begin']);
        $p['date_end'] = zeroToNull($a['date_end']);
        $result = $this->execSP(__FUNCTION__, "public.upd_employee_work(:employee_id, :price, to_date(:date_begin, 'dd.mm.yyyy'), to_date(:date_end, 'dd.mm.yyyy'))", $p);
        return $result;
    }

    public function del_work_employee($employee_work_id){
        $p['employee_work_id'] = $employee_work_id;
        $result = $this->execSP(__FUNCTION__, "public.del_work_employee(:employee_work_id)", $p);
        return $result;
    }
    public function read_payment_type(){
        $result = $this->readSP(__FUNCTION__, "public.read_payment_type('cur')");
        return $result;
    }

    public function read_payment($request_id){
        $p['request_id'] = $request_id;
        $result = $this->readSP(__FUNCTION__, "public.read_payment('cur', :request_id)", $p);
        return $result;
    }

    public function del_payment($payment_id){
        $p['payment_id'] = $payment_id;
        $result = $this->execSP(__FUNCTION__, "public.del_payment(:payment_id)", $p);
        return $result;
    }

    public function upd_payment($a){
        $p['payment_type_id'] = $a['payment_type_id'];
        $p['request_id'] = $a['request_id'];
        $p['payment_sum'] = zeroToNull($a['payment_sum']);
        $p['comment'] = $a['comment'];
        $p['document_name'] = null;
        $p['document_url'] = null;

        $tmpFilePath = $_FILES['upload']['tmp_name'];
        if ($tmpFilePath != ""){
            $ext = pathinfo($_FILES['upload']['name'], PATHINFO_EXTENSION);
            $dir = $_SERVER['DOCUMENT_ROOT']."/documents/payment_docs/";
            if (!file_exists($dir)) {
                mkdir($dir, 0777, true);
            }
            $filename = "/documents/".date('Y.m.d')."/payment_docs/".uniqid('im_doc_',true)."." . $ext;
            $p['document_name'] = $_FILES['upload']['name'];
            $p['document_url'] = $filename;
//            log_client_doc('TMP_FILE_PATH 4', json_encode($p));
            $newFilePath = $_SERVER['DOCUMENT_ROOT']. $filename;
            if(move_uploaded_file_smart($tmpFilePath, $newFilePath)) {
            }
        }
        $result = $this->execSP(__FUNCTION__, "public.upd_payment(:payment_type_id, :request_id, :payment_sum, :comment, :document_name, :document_url)", $p);
        return $result;
    }

    public function upd_request_status($a){
        $p['request_id'] = $a['request_id'];
        $p['status_id'] = $a['status_id'];
        $result = $this->execSP(__FUNCTION__, "public.upd_request_status(:request_id, :status_id)", $p);
        return $result;
    }

    public function read_material_by_type($type_id){
        $p['type_id'] = $type_id;
        $result = $this->readSP(__FUNCTION__, "public.read_material_by_type('cur', :type_id)", $p);
        return $result;
    }

    public function read_material_move($material_id){
        $p['material_id'] = $material_id;
        $result = $this->readSP(__FUNCTION__, "public.read_material_move('cur', :material_id)", $p);
        return $result;
    }
    public function read_employee_payment_report(){
        $result = $this->readSP(__FUNCTION__, "public.read_employee_payment_report('cur')");
        return $result;
    }
    public function report_employee_payment($employee_id){
        $p['employee_id'] = $employee_id;
        $result = $this->readSP(__FUNCTION__, "public.report_employee_payment('cur', :employee_id)", $p);
        return $result;
    }

    public function insert_material_move($a){
        $p['material_id'] = $a['material_id'];
        $p['cnt'] = $a['cnt'];
        $p['type'] = $a['type'];
        $result = $this->execSP(__FUNCTION__, "public.insert_material_move(:material_id, :cnt, :type)", $p);
        return $result;
    }

    public function get_payment_type($payment_type_id){
        $p['payment_type_id'] = $payment_type_id;
        $result = $this->getSP(__FUNCTION__, "public.get_payment_type('cur', :payment_type_id)", $p);
        return $result;
    }

    public function del_payment_type($payment_type_id){
        $p['payment_type_id'] = $payment_type_id;
        $result = $this->execSP(__FUNCTION__, "public.del_payment_type(:payment_type_id)", $p);
        return $result;
    }

    public function upd_payment_type($a){
        $p['payment_type_id'] = $a['payment_type_id'];
        $p['name'] = $a['name'];
        $p['code'] = $a['code'];
        $result = $this->execSP(__FUNCTION__, "public.upd_payment_type(:payment_type_id, :name, :code)", $p);
        return $result;
    }

    public function read_common_measure_type(){
        $result = $this->readSP(__FUNCTION__, "public.read_common_measure_type('cur')");
        return $result;
    }

    public function get_common_measure_type($common_measure_type_id){
        $p['common_measure_type_id'] = $common_measure_type_id;
        $result = $this->getSP(__FUNCTION__, "public.get_common_measure_type('cur', :common_measure_type_id)", $p);
        return $result;
    }

    public function del_common_measure_type($common_measure_type_id){
        $p['common_measure_type_id'] = $common_measure_type_id;
        $result = $this->execSP(__FUNCTION__, "public.del_common_measure_type(:common_measure_type_id)", $p);
        return $result;
    }

    public function upd_common_measure_type($a){
        $p['common_measure_type_id'] = $a['common_measure_type_id'];
        $p['name'] = $a['name'];
        $p['code'] = $a['code'];
        $result = $this->execSP(__FUNCTION__, "public.upd_common_measure_type(:common_measure_type_id, :name, :code)", $p);
        return $result;
    }
    public function upd_provider_request_status($a){
        $p['provider_request_item_id'] = $a['provider_request_item_id'];
        $p['status_id'] = $a['status_id'];
        $result = $this->execSP(__FUNCTION__, "public.upd_provider_request_status(:provider_request_item_id, :status_id)", $p);
        return $result;
    }
    public function read_provider_request($work_id){
        $p['work_id'] = $work_id;
        $result = $this->readSP(__FUNCTION__, "public.read_provider_request('cur', :work_id)", $p);
        return $result;
    }
    public function read_provider_request_item($provider_request_id){
        $p['provider_request_id'] = $provider_request_id;
        $result = $this->readSP(__FUNCTION__, "public.read_provider_request_item('cur', :provider_request_id)", $p);
        return $result;
    }
    public function upd_provider_request($a){
        $p['work_id'] = $a['work_id'];
        $p['provider_name'] = $a['provider_name'];
        $p['address'] = $a['address'];
        $p['delivery_date'] = $a['delivery_date'];
        $p['material_id_arr'] = '{'.implode(",", zeroToNull($a['material_id'])).'}';
        $p['cnt_arr'] = '{'.implode(",", zeroToNull($a['cnt'])).'}';
        $p['price_arr'] = '{'.implode(",", zeroToNull($a['price'])).'}';
        $result = $this->execSP(__FUNCTION__, "public.upd_provider_request(:work_id, :provider_name, to_date(:delivery_date, 'dd.mm.yyyy'), :address, :material_id_arr, :cnt_arr, :price_arr)", $p);
        return $result;
    }
    public function read_employee_needs(){
        $result = $this->readSP(__FUNCTION__, "public.read_employee_needs('cur')");
        return $result;
    }
    public function upd_needs_status($a){
        $p['employee_needs_id'] = $a['employee_needs_id'];
        $p['status_id'] = $a['status_id'];
        $result = $this->execSP(__FUNCTION__, "public.upd_needs_status(:employee_needs_id, :status_id)", $p);
        return $result;
    }
    public function insert_employee_needs($a){
        $p['comment'] = $a['comment'];
        $result = $this->execSP(__FUNCTION__, "public.insert_employee_needs(:comment)", $p);
        return $result;
    }
}

