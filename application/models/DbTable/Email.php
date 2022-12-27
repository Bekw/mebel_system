<?php

class Application_Model_DbTable_Email extends Application_Model_DbTable_Parent{
    public function init() {
        $this->smtp = "smtp.yandex.com";
        $this->config = array(
            'ssl' => 'tls',
            'auth' => 'login',
            'username' => 'order@emen.kz',
            'password' => 'smartorder',
            'port' => 587);
    }
    //Проверка доступности почты
    public function checkAvailableSmtpServer(){
        try {
            $transport = new Zend_Mail_Transport_Smtp($this->smtp, $this->config);
            $mail = new Zend_Mail('utf8');
            $mail->setBodyText('test message before send email for check available smtp server (message from cheesenology)', 'utf-8');
            $mail->setFrom('order@emen.kz', 'SR');
            $mail->addTo('aidar_q1983@mail.ru', '');
            $mail->setSubject('test message');
            $mail->send($transport);
            return true;
        } catch(Exception $e){
            $file = $_SERVER['DOCUMENT_ROOT']."/log/log_email.txt";
            file_put_contents($file, date("d.m.y H:i:s").": ERROR CHECK AVAILABLE ".$e->getMessage()."\n\n", FILE_APPEND);
            echo $e->getMessage();
            return false;
        }
    }
    //отправка почты
    public function sendEmail(){
        /*
        $this->checkAvailableSmtpServer();
        return;
        */
        $db = Zend_Db_Table::getDefaultAdapter();
        try {
            $db->beginTransaction();
            $db->query("
                SELECT read_send_email( 'cur');
            ");
            $stmt = $db->query("FETCH ALL FROM cur;");
            $rows = $stmt->fetchAll();
            $db->commit();
            $transport = new Zend_Mail_Transport_Smtp($this->smtp, $this->config);
            if (count($rows) > 0){
                if ($this->checkAvailableSmtpServer() == false){
                    return;
                }
            }

            foreach($rows as $key => $value){
                try{
                    $dir = $_SERVER['DOCUMENT_ROOT']. '/log/email';
                    if (!file_exists($dir)) {
                        mkdir($dir, 0777, true);
                    }
                    $file = $dir . "/" . date('Y.m.d') . ".log";
                    file_put_contents($file, date("d.m.y H:i:s").": start ".$value['email']."\n\n", FILE_APPEND);

                    $attachments = $this->read_send_email_attachment($value['send_email_id']);
                    $attachments = $attachments['value'];

                    $mail = new Zend_Mail('utf8');
                    $mail->setBodyHtml($value['email_content'], 'utf-8');
                    $pieces = explode(";", $value['email']);
                    $mail->setFrom('order@emen.kz', 'SR');
                    for ($i=0; $i<count($pieces); $i++){
                        if (trim($pieces[$i]) != '') {
                            $mail->addTo($pieces[$i], '');
                        }
                    }
                    $mail->addBcc('aidar_q1983@mail.ru');
                    $mail->setSubject($value['email_title']);
                    file_put_contents($file, date("d.m.y H:i:s").": sending ".$value['email']."\n\n", FILE_APPEND);
                    if (count($attachments)>0){
                        foreach($attachments as $key_attachment => $value_attachment){
                            $content = file_get_contents($_SERVER['DOCUMENT_ROOT'].$value_attachment['file_url']);
                            $attachment = new Zend_Mime_Part($content);
                            $attachment->type = 'application/pdf';
                            $attachment->disposition = Zend_Mime::DISPOSITION_ATTACHMENT;
                            $attachment->encoding = Zend_Mime::ENCODING_BASE64;
                            $attachment->filename = $value_attachment['file_name']; // name of file
                            $mail->addAttachment($attachment);
                        }
                    }

                    $mail->send($transport);

                    $this->updateEmailSend($value['send_email_id']);
                    file_put_contents($file, date("d.m.y H:i:s").": ".$value['email']."\n\n", FILE_APPEND);
                }catch (Exception $e) {
                    $this->updateEmailSendFailed($value['send_email_id'], $e->getMessage());
                    echo $e->getMessage();
                    file_put_contents($file, date("d.m.y H:i:s").": ".$e->getMessage()."\n\n", FILE_APPEND);
                    $transport = new Zend_Mail_Transport_Smtp($this->smtp, $this->config);
                }
            }

        } catch(Exception $e){
            echo $e->getMessage();
        }
        return true;
    }

    //делаем признак отправлен для почты
    public function updateEmailSend($send_email_id){
        $db = Zend_Db_Table::getDefaultAdapter();
        try {
            $p['send_email_id_'] = $send_email_id;
            $p['is_send_'] = 1;
            $stmt = $db->query("
                SELECT public.upd_email_send(:send_email_id_, :is_send_) id;
            ", $p);
        } catch(Exception $e){
        }
        return true;
    }
    //делаем признак отправлен для почты когда ошибка
    public function updateEmailSendFailed($send_email_id, $error){
        $db = Zend_Db_Table::getDefaultAdapter();
        try {
            $p['send_email_id_'] = $send_email_id;
            $p['is_send_'] = 2;
            $p['error_'] = $error;
            $stmt = $db->query("
                SELECT public.upd_email_send_failed(:send_email_id_, :is_send_, :error_) id;
            ", $p);
        } catch(Exception $e){
        }
        return true;
    }

    public function fill_send_email(){
        $db = Zend_Db_Table::getDefaultAdapter();
        try {
            $stmt = $db->query("SELECT public.fill_send_email() id;");
        } catch(Exception $e){
            echo $e->getMessage();
        }
        return true;
    }

    public function read_send_email_user(){
        $p['employee_id'] = getCurUser();
        $result = $this->readSP(__FUNCTION__,"read_send_email_user('cur', :employee_id)", $p);
        return $result;
    }
    public function set_send_email_user_reviewed($send_email_user_id){
        $p['send_email_user_id'] = $send_email_user_id;
        $result = $this->execSP(__FUNCTION__,"set_send_email_user_reviewed(:send_email_user_id)", $p);
        return $result;
    }
    public function get_send_email($send_email_id){
        $p['send_email_id'] = $send_email_id;
        $result = $this->getSP(__FUNCTION__,"get_send_email('cur', :send_email_id)", $p);
        return $result;
    }
    public function get_send_email_user_cnt(){
        $p['employee_id'] = getCurUser();
        $result = $this->scalarSP(__FUNCTION__,"get_send_email_user_cnt(:employee_id) id", $p, "id");
        return $result;
    }
    public function read_send_email_user_all($search, $is_reviewed){
        $p['employee_id'] = getCurUser();
        $p['search'] = $search;
        $p['is_reviewed'] = $is_reviewed;
        $result = $this->readSP(__FUNCTION__,"read_send_email_user_all('cur', :employee_id, :search, :is_reviewed)", $p);
        return $result;
    }
    public function get_send_email_user($send_email_id){
        $p['send_email_id'] = $send_email_id;
        $p['employee_id'] = getCurUser();
        $result = $this->getSP(__FUNCTION__,"get_send_email_user('cur', :send_email_id, :employee_id)", $p);
        return $result;
    }
    public function email_change_review($send_email_user_id){
        $p['send_email_user_id'] = $send_email_user_id;
        $result = $this->execSP(__FUNCTION__,"email_change_review(:send_email_user_id) id", $p, 'id');
        return $result;
    }

    public function read_send_email_attachment($send_email_id){
        $p['send_email_id'] = $send_email_id;
        $result = $this->readSP(__FUNCTION__,"read_send_email_attachment('cur', :send_email_id)", $p);
        return $result;
    }



}