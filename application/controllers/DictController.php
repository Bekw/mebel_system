<?php
require_once 'ParentController.php';

class DictController extends ParentController{

    public function preDispatch(){
        $this->session = new Zend_Session_Namespace('Global');
        $action_name =  (Zend_Controller_Front::getInstance()->getRequest()->getActionName());
        //Прописать action-ы в которых нужна сессия
        if ($action_name == 'remont-list' ||
            $action_name == 'client-request-list' ||
            $action_name == 'room-list')
        {
            $params = $this->_getAllParams();
            foreach ($params as $key => $value) {
                $this->session->param[$key] = $value;
            }
        }

        $params = $this->_getAllParams();
        foreach ($params as $key => $value) {
            if (is_array($value)){
                $this->param[$key] = $value;
            } else {
                $this->param[$key] = htmlspecialchars($value, ENT_QUOTES);
            }
        }

    }

    function getParam($param_name, $second_value=null){
        if (isset($this->param[$param_name])){
            return $this->param[$param_name];
        } else{
            return $second_value;
        }
    }

    function getAllParams(){
        return $this->param;
    }

    public function init(){
        $this->_helper->layout->setLayout('layout-system');
        parent::init();
        $action_name =  (Zend_Controller_Front::getInstance()->getRequest()->getActionName());
        $actions_except = array();
        if (!in_array($action_name, $actions_except)){
            if (!Zend_Auth::getInstance()->hasIdentity()){
                $this->_redirect('/system/login');
            }
        }

        $this->view->filter_btn_text = 'Показать фильтры';
    }

    public function indexJsonAction()
    {
        $mode = $this->_getParam('mode', '');
        $this->view->mode = $mode;
        $dict = new Application_Model_DbTable_Dict();

        if ($mode == 'work-mark-check'){
            $ob = new Application_Model_DbTable_Dict();
            $params = $this->getAllParams();
            $result = $ob->upd_work_mark($params);
            $this->view->result = $result;
        }
        if ($mode == 'is_paid'){
            $ob = new Application_Model_DbTable_Dict();
            $params = $this->getAllParams();
            $result = $ob->request_is_paid($params);
            $this->view->result = $result;
        }
        if ($mode == 'is_accepted'){
            $ob = new Application_Model_DbTable_Dict();
            $params = $this->getAllParams();
            $result = $ob->request_is_accepted($params);
            $this->view->result = $result;
        }
        if ($mode == 'upd_work_stage'){
            $ob = new Application_Model_DbTable_Dict();
            $params = $this->getAllParams();
            $result = $ob->upd_work_stage_accept($params);
            $this->view->result = $result;
        }
        if ($mode == 'upd_stage'){
            $ob = new Application_Model_DbTable_Dict();
            $params = $this->getAllParams();
            $result = $ob->upd_stage($params);
            $this->view->result = $result;
        }

    }

    public function officeListAction(){
        $dict = new Application_Model_DbTable_Dict();
        $params = null;
        $params['filial_id'] = $this->_getParam('filial_id', 0);
        if (isset($_GET['show_filter'])){
            $this->view->filter_btn_text = 'Скрыть фильтры';
            $this->view->is_filter_show = true;
        }
        if (isset($_GET['btn_filter'])){
            $params = $this->_getAllParams();
            $this->view->params = $params;
            $this->view->filter_btn_text = 'Скрыть фильтры';
            $this->view->is_filter_show = true;
        }
        $this->view->row_filial = $dict->filial_read_select()['value'];
        $this->view->row = $dict->office_read($params)['value'];
    }

    public function officeEditAction(){
        $dict = new Application_Model_DbTable_Dict();
        $mode = $this->_getParam('mode', '');
        if ($mode == 'edit'){
            $this->_helper->AjaxContext()->addActionContext('office-edit', 'json')->initContext('json');
            $params = $this->getAllParams();
            $this->view->result = $dict->office_upd($params);
            return;
        }
        if ($mode == 'delete'){
            $this->_helper->AjaxContext()->addActionContext('office-edit', 'json')->initContext('json');
            $office_id = $this->_getParam('office_id', 0);
            $this->view->result = $dict->office_del($office_id);
            return;
        }
        $this->view->office_id = $this->_getParam('office_id', 0);
        $this->view->row_filial = $dict->filial_read_select()['value'];
        $this->view->row = $dict->office_get($this->view->office_id)['value'];
    }

    public function createRequestAction(){
        $ob = new Application_Model_DbTable_Dict();

//        if ($this->getRequest()->isPost()) {
//            $a = $this->_getAllParams();
//            $result = $ob->create_request($a);
//            if ($result['status'] == false) {
//                $a['error'] = $result['error'];
//                $this->view->row = $a;
//                return;
//            }
//            $this->_redirector->gotoUrl('/dict/request-list/status_id/1');
//        }
        $mode = $this->_getParam('mode', '');
        if ($mode == 'upd'){
            $this->_helper->AjaxContext()->addActionContext('create-request', 'json')->initContext('json');
            $params = $this->getAllParams();
            $this->view->result = $ob->create_request($params);
            return;
        }
        $this->view->row = [];
    }

    public function requestListAction(){
        $dict = new Application_Model_DbTable_Dict();
        $this->view->status_id = $this->_getParam('status_id', 0);
        $mode = $this->_getParam('mode', '');
        if ($mode == 'upd-status'){
            $this->_helper->AjaxContext()->addActionContext('request-list', 'json')->initContext('json');
            $params = $this->getAllParams();
            $this->view->result = $dict->upd_request_status($params);
            return;
        }
        $this->view->row = $dict->read_request($this->view->status_id)['value'];
    }

    public function requestEditAction(){
        $dict = new Application_Model_DbTable_Dict();
        $this->view->request_id = $this->_getParam('request_id', 0);
        if (isset($_POST["save_common"])) {
            $a = $this->_getAllParams();
            $result = $dict->upd_request($a);
            if ($result['status'] == false) {
                $this->view->globalError = $result['error'];
                $this->view->row = $a;
                return;
            }
            $this->_redirector->gotoUrl('/dict/request-edit/request_id/'.$this->view->request_id);
        }
        $mode = $this->_getParam('mode', '');
        if ($mode == 'upd-request'){
            $this->_helper->AjaxContext()->addActionContext('request-edit', 'json')->initContext('json');
            $params = $this->getAllParams();
            $this->view->result = $dict->upd_request($params);
            return;
        }
        if ($mode == 'upd-doc'){
            $this->_helper->AjaxContext()->addActionContext('request-edit', 'json')->initContext('json');
            $params = $this->getAllParams();
            $this->view->result = $dict->insert_document_request($params);
            return;
        }
        if ($mode == 'upd-payment'){
            $this->_helper->AjaxContext()->addActionContext('request-edit', 'json')->initContext('json');
            $params = $this->getAllParams();
            $this->view->result = $dict->upd_payment($params);
            return;
        }
        if ($mode == 'del-doc'){
            $this->_helper->AjaxContext()->addActionContext('request-edit', 'json')->initContext('json');
            $document_id = $this->_getParam('document_id', 0);
            $this->view->result = $dict->delete_document($document_id);
            return;
        }
        if ($mode == 'upd-work'){
            $this->_helper->AjaxContext()->addActionContext('request-edit', 'json')->initContext('json');
            $params = $this->getAllParams();
            $this->view->result = $dict->insert_work($params);
            return;
        }
        if ($mode == 'del-work'){
            $this->_helper->AjaxContext()->addActionContext('request-edit', 'json')->initContext('json');
            $work_id = $this->_getParam('work_id', 0);
            $this->view->result = $dict->del_work($work_id);
            return;
        }
        if ($mode == 'del-payment'){
            $this->_helper->AjaxContext()->addActionContext('request-edit', 'json')->initContext('json');
            $payment_id = $this->_getParam('payment_id', 0);
            $this->view->result = $dict->del_payment($payment_id);
            return;
        }
        if ($mode == 'is-accept-payment'){
            $this->_helper->AjaxContext()->addActionContext('request-edit', 'json')->initContext('json');
            $payment_id = $this->_getParam('payment_id', 0);
            $this->view->result = $dict->upd_is_accept_payment($payment_id);
            return;
        }
        $this->view->request_doc = $dict->read_document_by_request($this->view->request_id)['value'];
        $this->view->request_work = $dict->read_work_request($this->view->request_id)['value'];
        $this->view->row_client = $dict->get_request($this->view->request_id)['value'];
        $this->view->row_payment = $dict->read_payment($this->view->request_id)['value'];
    }

    public function documentEditRequestAction(){
        $this->_helper->layout->disableLayout();
        $dict = new Application_Model_DbTable_Dict();
        $this->view->request_id = $this->_getParam('request_id', 0);

        $this->view->doc_type = $dict->read_document_type_fs()['value'];
    }
    public function documentMeasureWorkAction(){
        $this->_helper->layout->disableLayout();
        $dict = new Application_Model_DbTable_Dict();
        $this->view->work_id = $this->_getParam('work_id', 0);
    }
    public function documentEditWorkAction(){
        $this->_helper->layout->disableLayout();
        $dict = new Application_Model_DbTable_Dict();
        $this->view->work_id = $this->_getParam('work_id', 0);

        $this->view->doc_type = $dict->read_document_type_fs()['value'];
    }

    public function workEditAction(){
        $this->_helper->layout->disableLayout();
        $dict = new Application_Model_DbTable_Dict();
        $this->view->request_id = $this->_getParam('request_id', 0);

        $this->view->work_type = $dict->read_work_type_fs()['value'];
    }

    public function workDetailAction(){
        $dict = new Application_Model_DbTable_Dict();
        $this->view->work_id = $this->_getParam('work_id', 0);
        if (isset($_POST["save_common"])) {
            $a = $this->_getAllParams();
            $result = $dict->upd_work($a);
            if ($result['status'] == false) {
                $this->view->globalError = $result['error'];
                $this->view->row = $a;
                return;
            }
            $this->_redirector->gotoUrl('/dict/work-detail/work_id/'.$this->view->work_id);
        }
        if (isset($_POST["save_design"])) {
            $a = $this->_getAllParams();
            $result = $dict->upd_designer_comment($a);
            if ($result['status'] == false) {
                $this->view->globalError = $result['error'];
                $this->view->row = $a;
                return;
            }
            $this->_redirector->gotoUrl('/dict/work-detail/work_id/'.$this->view->work_id);
        }
        if (isset($_POST["print"])) {
            $today = gmdate("d.m.y H-i-s", time() + 21600);
            $row = $dict->read_measure($this->view->work_id, 1);
            $api = new Api_Flexcell();
            $tempFile = $_SERVER['DOCUMENT_ROOT'] . "/reports/templates/cutting.xlsx";
            $api->load($tempFile);
            $api->setActiveSheet(0);
            $api->setVariable('TITLE', 'Замеры для распила ЛДСП');
            $api->setVariable('PRINT_DATE', $today);
            $api->setMultipleRowData($row['value'], 'scan');
            if (check_ob_content()){
                $this->_helper->layout->setLayout('layout-system');
                $this->_helper->viewRenderer->setNoRender(true);
                return;
            };

            $api->export('Распил ЛДСП '. $today);
            unset($_GET['print']);
            return;
        }
        if (isset($_POST["print_mdf"])) {
            $today = gmdate("d.m.y H-i-s", time() + 21600);
            $row = $dict->read_measure($this->view->work_id, 2);
            $api = new Api_Flexcell();
            $tempFile = $_SERVER['DOCUMENT_ROOT'] . "/reports/templates/cutting.xlsx";
            $api->load($tempFile);
            $api->setActiveSheet(0);
            $api->setVariable('TITLE', 'Замеры для распила МДФ');
            $api->setVariable('PRINT_DATE', $today);
            $api->setMultipleRowData($row['value'], 'scan');
            if (check_ob_content()){
                $this->_helper->layout->setLayout('layout-system');
                $this->_helper->viewRenderer->setNoRender(true);
                return;
            };

            $api->export('Распил МДФ '. $today);
            unset($_GET['print_mdf']);
            return;
        }
        if (isset($_POST["print_lhdf"])) {
            $today = gmdate("d.m.y H-i-s", time() + 21600);
            $row = $dict->read_measure($this->view->work_id, 2);
            $api = new Api_Flexcell();
            $tempFile = $_SERVER['DOCUMENT_ROOT'] . "/reports/templates/cutting.xlsx";
            $api->load($tempFile);
            $api->setActiveSheet(0);
            $api->setVariable('TITLE', 'Замеры для распила ЛХДФ');
            $api->setVariable('PRINT_DATE', $today);
            $api->setMultipleRowData($row['value'], 'scan');
            if (check_ob_content()){
                $this->_helper->layout->setLayout('layout-system');
                $this->_helper->viewRenderer->setNoRender(true);
                return;
            };

            $api->export('Распил ЛХДФ '. $today);
            unset($_GET['print_lhdf']);
            return;
        }
        $mode = $this->_getParam('mode', '');
        if ($mode == 'upd-item'){
            $this->_helper->AjaxContext()->addActionContext('work-detail', 'json')->initContext('json');
            $params = $this->getAllParams();
            $this->view->result = $dict->upd_work_item($params);
            return;
        }
        if ($mode == 'del-item'){
            $this->_helper->AjaxContext()->addActionContext('work-detail', 'json')->initContext('json');
            $params = $this->getAllParams();
            $this->view->result = $dict->del_work_item($params['work_item_id']);
            return;
        }
        if ($mode == 'del-measure'){
            $this->_helper->AjaxContext()->addActionContext('work-detail', 'json')->initContext('json');
            $params = $this->getAllParams();
            $this->view->result = $dict->del_measure($params['measure_id']);
            return;
        }
        if ($mode == 'upd-measure'){
            $this->_helper->AjaxContext()->addActionContext('work-detail', 'json')->initContext('json');
            $params = $this->getAllParams();
            $this->view->result = $dict->insert_measure($params);
            return;
        }
        if ($mode == 'upd-furniture'){
            $this->_helper->AjaxContext()->addActionContext('work-detail', 'json')->initContext('json');
            $params = $this->getAllParams();
            $this->view->result = $dict->upd_furniture($params);
            return;
        }
        if ($mode == 'upd-total-measure'){
            $this->_helper->AjaxContext()->addActionContext('work-detail', 'json')->initContext('json');
            $params = $this->getAllParams();
            $this->view->result = $dict->upd_work_measure_total($params);
            return;
        }
        if ($mode == 'upd-ldsp'){
            $this->_helper->AjaxContext()->addActionContext('work-detail', 'json')->initContext('json');
            $params = $this->getAllParams();
            $this->view->result = $dict->upd_work_ldsp($params);
            return;
        }
        if ($mode == 'del-furniture'){
            $this->_helper->AjaxContext()->addActionContext('work-detail', 'json')->initContext('json');
            $params = $this->getAllParams();
            $this->view->result = $dict->del_furniture($params['furniture_id']);
            return;
        }
        if ($mode == 'upd-doc'){
            $this->_helper->AjaxContext()->addActionContext('work-detail', 'json')->initContext('json');
            $params = $this->getAllParams();
            $this->view->result = $dict->upd_work_doc($params);
            return;
        }
        if ($mode == 'upd-status-provider'){
            $this->_helper->AjaxContext()->addActionContext('work-detail', 'json')->initContext('json');
            $params = $this->getAllParams();
            $this->view->result = $dict->upd_provider_request_status($params);
            return;
        }
        if ($mode == 'del-doc'){
            $this->_helper->AjaxContext()->addActionContext('work-detail', 'json')->initContext('json');
            $document_id = $this->_getParam('document_id', 0);
            $this->view->result = $dict->delete_document($document_id);
            return;
        }

        $this->view->row_tehnolog = $dict->read_employee_fs('tehnolog')['value'];
        $this->view->row_ldsp = $dict->read_employee_fs('ldsp')['value'];
        $this->view->row_multicat = $dict->read_employee_fs('multicat')['value'];
        $this->view->row_stolyar = $dict->read_employee_fs('stolyar')['value'];
        $this->view->row_master= $dict->read_employee_fs('master')['value'];
        $this->view->row_shlif= $dict->read_employee_fs('shlif')['value'];
        $this->view->row_malyar= $dict->read_employee_fs('malyar')['value'];
        $this->view->row_upack= $dict->read_employee_fs('upack')['value'];
        $this->view->row_delivery= $dict->read_employee_fs('delivery')['value'];
        $this->view->row_ustan= $dict->read_employee_fs('ustan')['value'];

        $this->view->row_stage = $dict->read_work_stage($this->view->work_id)['value'];
        $this->view->row_doc = $dict->read_work_docs($this->view->work_id)['value'];
        $this->view->row_control_measure = $dict->read_control_measure($this->view->work_id)['value'];
        $this->view->row_provider_request = $dict->read_provider_request($this->view->work_id)['value'];

        $this->view->row = $dict->get_work($this->view->work_id)['value'];
    }

    public function providerRequestListAction(){
        $dict = new Application_Model_DbTable_Dict();

        $this->view->row_provider_request = $dict->read_provider_request(0)['value'];
    }

    public function providerRequestEditAction(){
        $dict = new Application_Model_DbTable_Dict();
        $this->view->work_id = $this->_getParam('work_id', 0);
        if (isset($_POST["save_common"])) {
            $a = $this->_getAllParams();
            $result = $dict->upd_provider_request($a);
            if ($result['status'] == false) {
                $a['error'] = $result['error'];
                $this->view->row_material = $dict->read_material()['value'];
                return;
            }
            $this->_redirector->gotoUrl('/dict/work-detail/work_id/'.$this->view->work_id);
        }
        $this->view->row_material = $dict->read_material()['value'];
    }

    public function workItemListAction(){
        $this->_helper->layout->disableLayout();
        $dict = new Application_Model_DbTable_Dict();
        $this->view->work_id = $this->_getParam('work_id', 0);

        $this->view->row = $dict->read_work_item($this->view->work_id)['value'];
    }

    public function workItemEditAction(){
        $this->_helper->layout->disableLayout();
        $dict = new Application_Model_DbTable_Dict();
        $this->view->work_id = $this->_getParam('work_id', 0);
        $this->view->work_item_id = $this->_getParam('work_item_id', 0);

        $this->view->item_type = $dict->read_item_type_fs()['value'];
        $this->view->item_common = $dict->read_common_measure_type()['value'];
        $this->view->row = $dict->get_work_item($this->view->work_item_id)['value'];
    }

    public function workMeasureListAction(){
        $this->_helper->layout->disableLayout();
        $dict = new Application_Model_DbTable_Dict();
        $this->view->work_id = $this->_getParam('work_id', 0);
        $this->view->measure_type_id = $this->_getParam('measure_type_id', 0);

        $this->view->row = $dict->read_measure($this->view->work_id, $this->view->measure_type_id)['value'];
    }

    public function workMeasureEditAction(){
        $this->_helper->layout->disableLayout();

        $this->view->work_id = $this->_getParam('work_id', 0);
        $this->view->measure_type_id = $this->_getParam('measure_type_id', 0);
    }

    public function workFurnitureListAction(){
        $this->_helper->layout->disableLayout();
        $dict = new Application_Model_DbTable_Dict();
        $this->view->work_id = $this->_getParam('work_id', 0);

        $this->view->row = $dict->read_furniture($this->view->work_id)['value'];
    }

    public function workFurnitureEditAction(){
        $this->_helper->layout->disableLayout();
        $dict = new Application_Model_DbTable_Dict();
        $this->view->work_id = $this->_getParam('work_id', 0);
        $this->view->furniture_id = $this->_getParam('furniture_id', 0);

        $this->view->row = $dict->get_furniture($this->view->furniture_id)['value'];
        $this->view->row_material = $dict->read_material_by_type(2)['value'];
    }

    public function workMarkListAction(){
        $this->_helper->layout->disableLayout();
        $dict = new Application_Model_DbTable_Dict();
        $this->view->work_id = $this->_getParam('work_id', 0);

        $this->view->row_mark = $dict->read_work_mark($this->view->work_id)['value'];
    }

    public function workCommentListAction(){
        $this->_helper->layout->disableLayout();
        $dict = new Application_Model_DbTable_Dict();
        $this->view->work_id = $this->_getParam('work_id', 0);

        $this->view->row = $dict->read_work_comment($this->view->work_id)['value'];
    }

    public function workCommentEditAction(){
        $this->_helper->layout->disableLayout();
        $dict = new Application_Model_DbTable_Dict();

        $mode = $this->_getParam('mode', '');
        if ($mode == 'upd'){
            $this->_helper->AjaxContext()->addActionContext('work-comment-edit', 'json')->initContext('json');
            $params = $this->getAllParams();
            $this->view->result = $dict->insert_comment($params);
            return;
        }
        $this->view->work_id = $this->_getParam('work_id', 0);
    }

    public function workListAction(){
        $dict = new Application_Model_DbTable_Dict();
        $this->view->stage_id = $this->_getParam('stage_id', 0);

        $this->view->row = $dict->read_work($this->view->stage_id)['value'];
    }

    public function workStageEditAction(){
        $this->_helper->layout->disableLayout();
        $dict = new Application_Model_DbTable_Dict();

        $this->view->work_id = $this->_getParam('work_id', 0);
        $this->view->row_stage = $dict->read_stage_fs()['value'];
    }

    public function reportForManagementAction(){
        $dict = new Application_Model_DbTable_Dict();

        $this->view->row = $dict->read_indicator()['value'];
    }
    public function indicatorItemListAction(){
        $this->_helper->layout->disableLayout();
        $ob = new Application_Model_DbTable_Dict();

        $indicator_id = $this->_getParam('indicator_id', '');
        $this->view->indicator_id = $indicator_id;
        $color = $this->_getParam('color', '');
        $this->view->color = $color;

        if($indicator_id == '1'){
            $this->view->row = $ob->read_indicator_design($color)['value'];
        }
        if($indicator_id == '2'){
            $this->view->row = $ob->read_indicator_ready($color)['value'];
        }
        if($indicator_id == '3'){
            $this->view->row = $ob->read_indicator_ldsp($color)['value'];
        }
        if($indicator_id == '4'){
            $this->view->row = $ob->read_indicator_multicat($color)['value'];
        }
        if($indicator_id == '5'){
            $this->view->row = $ob->read_indicator_stolyar($color)['value'];
        }
        if($indicator_id == '6'){
            $this->view->row = $ob->read_indicator_master($color)['value'];
        }
        if($indicator_id == '7'){
            $this->view->row = $ob->read_indicator_shlif($color)['value'];
        }
        if($indicator_id == '8'){
            $this->view->row = $ob->read_indicator_malyar($color)['value'];
        }
        if($indicator_id == '9'){
            $this->view->row = $ob->read_indicator_upack($color)['value'];
        }
        if($indicator_id == '10'){
            $this->view->row = $ob->read_indicator_delivery($color)['value'];
        }
    }
    public function workTypeListAction(){
        $dict = new Application_Model_DbTable_Dict();
        $mode = $this->_getParam('mode', '');
        if ($mode == 'upd'){
            $this->_helper->AjaxContext()->addActionContext('work-type-list', 'json')->initContext('json');
            $params = $this->getAllParams();
            $this->view->result = $dict->upd_work_type($params);
            return;
        }
        if ($mode == 'del'){
            $this->_helper->AjaxContext()->addActionContext('work-type-list', 'json')->initContext('json');
            $params = $this->getAllParams();
            $this->view->result = $dict->del_work_type($params['work_type_id']);
            return;
        }
        $this->view->row = $dict->read_work_type()['value'];
    }
    public function workTypeEditAction(){
        $this->_helper->layout->disableLayout();
        $dict = new Application_Model_DbTable_Dict();
        $this->view->work_type_id = $this->_getParam('work_type_id', 0);

        $this->view->row = $dict->get_work_type($this->view->work_type_id)['value'];
    }
    public function documentTypeListAction(){
        $dict = new Application_Model_DbTable_Dict();
        $mode = $this->_getParam('mode', '');
        if ($mode == 'upd'){
            $this->_helper->AjaxContext()->addActionContext('document-type-list', 'json')->initContext('json');
            $params = $this->getAllParams();
            $this->view->result = $dict->upd_document_type($params);
            return;
        }
        if ($mode == 'del'){
            $this->_helper->AjaxContext()->addActionContext('document-type-list', 'json')->initContext('json');
            $params = $this->getAllParams();
            $this->view->result = $dict->del_document_type($params['document_type_id']);
            return;
        }
        $this->view->row = $dict->read_document_type()['value'];
    }
    public function documentTypeEditAction(){
        $this->_helper->layout->disableLayout();
        $dict = new Application_Model_DbTable_Dict();
        $this->view->document_type_id = $this->_getParam('document_type_id', 0);

        $this->view->row = $dict->get_document_type($this->view->document_type_id)['value'];
    }
    public function materialTypeListAction(){
        $dict = new Application_Model_DbTable_Dict();
        $mode = $this->_getParam('mode', '');
        if ($mode == 'upd'){
            $this->_helper->AjaxContext()->addActionContext('material-type-list', 'json')->initContext('json');
            $params = $this->getAllParams();
            $this->view->result = $dict->upd_material_type($params);
            return;
        }
        if ($mode == 'del'){
            $this->_helper->AjaxContext()->addActionContext('material-type-list', 'json')->initContext('json');
            $params = $this->getAllParams();
            $this->view->result = $dict->del_material_type($params['material_type_id']);
            return;
        }
        $this->view->row = $dict->read_material_type()['value'];
    }
    public function materialTypeEditAction(){
        $this->_helper->layout->disableLayout();
        $dict = new Application_Model_DbTable_Dict();
        $this->view->material_type_id = $this->_getParam('material_type_id', 0);

        $this->view->row = $dict->get_material_type($this->view->material_type_id)['value'];
    }
    public function materialListAction(){
        $dict = new Application_Model_DbTable_Dict();
        $mode = $this->_getParam('mode', '');
        if ($mode == 'upd'){
            $this->_helper->AjaxContext()->addActionContext('material-list', 'json')->initContext('json');
            $params = $this->getAllParams();
            $this->view->result = $dict->upd_material($params);
            return;
        }
        if ($mode == 'del'){
            $this->_helper->AjaxContext()->addActionContext('material-list', 'json')->initContext('json');
            $params = $this->getAllParams();
            $this->view->result = $dict->del_material($params['material_id']);
            return;
        }
        $this->view->row = $dict->read_material()['value'];
    }
    public function materialEditAction(){
        $this->_helper->layout->disableLayout();
        $dict = new Application_Model_DbTable_Dict();
        $this->view->material_id = $this->_getParam('material_id', 0);

        $this->view->row = $dict->get_material($this->view->material_id)['value'];
        $this->view->row_type = $dict->read_material_type()['value'];
    }
    public function materialMoveListAction(){
        $dict = new Application_Model_DbTable_Dict();
        $mode = $this->_getParam('mode', '');
        if ($mode == 'upd'){
            $this->_helper->AjaxContext()->addActionContext('material-move-list', 'json')->initContext('json');
            $params = $this->getAllParams();
            $this->view->result = $dict->insert_material_move($params);
            return;
        }
        $this->view->material_id = $this->_getParam('material_id', 0);

        $this->view->row = $dict->read_material_move($this->view->material_id)['value'];
        $this->view->row_material = $dict->get_material($this->view->material_id)['value'];
    }
    public function materialMoveEditAction(){
        $this->_helper->layout->disableLayout();
        $dict = new Application_Model_DbTable_Dict();
        $this->view->material_id = $this->_getParam('material_id', 0);
    }
    public function workEmployeeListAction(){
        $dict = new Application_Model_DbTable_Dict();
        $mode = $this->_getParam('mode', '');
        if ($mode == 'upd'){
            $this->_helper->AjaxContext()->addActionContext('work-employee-list', 'json')->initContext('json');
            $params = $this->getAllParams();
            $this->view->result = $dict->upd_employee_work($params);
            return;
        }
        if ($mode == 'del'){
            $this->_helper->AjaxContext()->addActionContext('work-employee-list', 'json')->initContext('json');
            $params = $this->getAllParams();
            $this->view->result = $dict->del_work_employee($params['employee_work_id']);
            return;
        }
        $this->view->row = $dict->read_employee_work()['value'];
    }

    public function workEmployeeEditAction(){
        $this->_helper->layout->disableLayout();
        $dict = new Application_Model_DbTable_Dict();
        $this->view->employee_id = $this->_getParam('employee_id', 0);
    }
    public function paymentEditRequestAction(){
        $this->_helper->layout->disableLayout();
        $dict = new Application_Model_DbTable_Dict();
        $this->view->request_id = $this->_getParam('request_id', 0);

        $this->view->payment_type = $dict->read_payment_type()['value'];
    }
    public function reportWorkPaymentAction(){
        $dict = new Application_Model_DbTable_Dict();
        $this->view->employee_id = $this->_getParam('employee_id', 0);

        $this->view->row_employee = $dict->read_employee_payment_report()['value'];
        $this->view->row = $dict->report_employee_payment($this->view->employee_id)['value'];

    }

    public function paymentTypeListAction(){
        $dict = new Application_Model_DbTable_Dict();
        $mode = $this->_getParam('mode', '');
        if ($mode == 'upd'){
            $this->_helper->AjaxContext()->addActionContext('payment-type-list', 'json')->initContext('json');
            $params = $this->getAllParams();
            $this->view->result = $dict->upd_payment_type($params);
            return;
        }
        if ($mode == 'del'){
            $this->_helper->AjaxContext()->addActionContext('payment-type-list', 'json')->initContext('json');
            $params = $this->getAllParams();
            $this->view->result = $dict->del_payment_type($params['payment_type_id']);
            return;
        }
        $this->view->row = $dict->read_payment_type()['value'];
    }
    public function paymentTypeEditAction(){
        $this->_helper->layout->disableLayout();
        $dict = new Application_Model_DbTable_Dict();
        $this->view->payment_type_id = $this->_getParam('payment_type_id', 0);

        $this->view->row = $dict->get_payment_type($this->view->payment_type_id)['value'];
    }
    public function commonMeasureTypeListAction(){
        $dict = new Application_Model_DbTable_Dict();
        $mode = $this->_getParam('mode', '');
        if ($mode == 'upd'){
            $this->_helper->AjaxContext()->addActionContext('common-measure-type-list', 'json')->initContext('json');
            $params = $this->getAllParams();
            $this->view->result = $dict->upd_common_measure_type($params);
            return;
        }
        if ($mode == 'del'){
            $this->_helper->AjaxContext()->addActionContext('common-measure-type-list', 'json')->initContext('json');
            $params = $this->getAllParams();
            $this->view->result = $dict->del_common_measure_type($params['common_measure_type_id']);
            return;
        }
        $this->view->row = $dict->read_common_measure_type()['value'];
    }
    public function commonMeasureTypeEditAction(){
        $this->_helper->layout->disableLayout();
        $dict = new Application_Model_DbTable_Dict();
        $this->view->common_measure_type_id = $this->_getParam('common_measure_type_id', 0);

        $this->view->row = $dict->get_common_measure_type($this->view->common_measure_type_id)['value'];
    }
    public function employeeNeedsAction(){
        $dict = new Application_Model_DbTable_Dict();
        $mode = $this->_getParam('mode', '');
        if ($mode == 'upd'){
            $this->_helper->AjaxContext()->addActionContext('employee-needs', 'json')->initContext('json');
            $params = $this->getAllParams();
            $this->view->result = $dict->insert_employee_needs($params);
            return;
        }
        if ($mode == 'upd-status'){
            $this->_helper->AjaxContext()->addActionContext('employee-needs', 'json')->initContext('json');
            $params = $this->getAllParams();
            $this->view->result = $dict->upd_needs_status($params);
            return;
        }
        $this->view->row = $dict->read_employee_needs()['value'];
    }
    public function employeeNeedsEditAction(){
        $this->_helper->layout->disableLayout();

    }
}

