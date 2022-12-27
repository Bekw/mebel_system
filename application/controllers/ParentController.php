<?php

class ParentController extends Zend_Controller_Action{

    public function getSessionParam($param_name, $second_value=0){
        if (isset($this->session->param[$param_name])){
            return $this->session->param[$param_name];
        } else{
            return $second_value;
        }
    }

    private function getSessionMenuId(){
        if (isset($this->session->menu_id)){
            return $this->session->menu_id;
        } else {
            return 0;
        }
    }

    public function postDispatch(){
        //echo $this->view->partial('error/func-error.phtml', array());
    }


    public function init(){
        $this->_redirector = $this->_helper->getHelper('Redirector');
        $this->_helper->AjaxContext()->addActionContext('index-json', 'json')->initContext('json');
        if ($this->getRequest ()->getActionName () == 'index-ajax-content'){
            $this->_helper->layout->disableLayout();
        }
        $this->session = new Zend_Session_Namespace('Global');

        if ($this->_getParam('menu_global_id', 0) > 0) {
            $this->session->menu_id = $this->_getParam('menu_global_id', 0);
        }
        $this->view->menu_global_id = $this->getSessionMenuId();
        $this->session->error_func_msg = array();

    }

}

