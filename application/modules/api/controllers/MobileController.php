<?php

class Api_MobileController extends Zend_Controller_Action
{
    public function init()
    {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
    }

    /************** Аутентификация пользователя *****************************************************/

    public function loginAction()
    {
        $mobile = new Application_Model_DbTable_Mobile();
        $i = 1;
//        if (!isset($_SERVER['PHP_AUTH_USER'])) {
        if ($i == 0) {
            header('WWW-Authenticate: Basic realm="My Realm"');
            header('HTTP/1.0 401 Unauthorized');
            return;
        }
        else
        {
            $login = $this->_getParam("email");
            $password = $this->_getParam("password");

            $isValid = $mobile->userAuthorize($login, $password);

            if (!$isValid){
                $this->view->error="error http";
                header('WWW-Authenticate: Basic realm="My Realm"');
                header('HTTP/1.0 401 Unauthorized');
                $this->getResponse()->setHeader('Content-type', 'application/json');
                $row['error'] = "1";
                echo json_encode($row);
                return;
            }
            else
            {
                $token = $mobile->tokenGet($login,$password);
                if (!$token){
                    $token = md5(uniqid($login.time(), true));
                    $mobile->tokenEdit($login, $token);
                } else {
                    $mobile->tokenUpdate($token);
                }

                $this->getResponse()->setHeader('Content-type', 'application/json');
                $row['token'] = $token;
                $row['user_info'] = $isValid;
                echo json_encode($row);
            }
        }
    }

    /************** Новые заказы *****************************************************/
    public function getNewRequestsAction(){
        $mobile = new Application_Model_DbTable_Mobile();
        $token = $this->_getParam('token', '');
        $user = $mobile->tokenIsValid($token);
        if (!$user) {
            header('WWW-Authenticate: Basic realm="My Realm"');
            header('HTTP/1.0 401 Unauthorized');
            return;
        }
        else if($user['role'] == 1 || $user['role'] == 2){
            $requests = $mobile->getNewRequests($user['user_id']);
        }
        else{
            $requests = false;
        }
        $this->getResponse()->setHeader('Content-type', 'application/json');
        $row['response'] = $requests;
        echo json_encode($row);
    }

    /************** Принятые заказы *****************************************************/
    public function getReceivedRequestsAction(){
        $mobile = new Application_Model_DbTable_Mobile();
        $token = $this->_getParam('token', '');
        $user = $mobile->tokenIsValid($token);
        if (!$user) {
            header('WWW-Authenticate: Basic realm="My Realm"');
            header('HTTP/1.0 401 Unauthorized');
            return;
        }
        else if($user['role'] == 1 || $user['role'] == 2){
            $requests = $mobile->getReceivedRequests($user['user_id']);
        }
        else{
            $requests = false;
        }
        $this->getResponse()->setHeader('Content-type', 'application/json');
        $row['response'] = $requests;
        echo json_encode($row);
    }

    /************** Доставленные заказы *****************************************************/
    public function getDeliveredRequestsAction(){
        $mobile = new Application_Model_DbTable_Mobile();
        $token = $this->_getParam('token', '');
        $user = $mobile->tokenIsValid($token);
        if (!$user) {
            header('WWW-Authenticate: Basic realm="My Realm"');
            header('HTTP/1.0 401 Unauthorized');
            return;
        }
        else if($user['role'] == 1 || $user['role'] == 2){
            $requests = $mobile->getDeliveredRequests($user['user_id']);
        }
        else{
            $requests = false;
        }
        $this->getResponse()->setHeader('Content-type', 'application/json');
        $row['response'] = $requests;
        echo json_encode($row);
    }

    /************** Отмененные заказы *****************************************************/
    public function getCancelledRequestsAction(){
        $mobile = new Application_Model_DbTable_Mobile();
        $token = $this->_getParam('token', '');
        $user = $mobile->tokenIsValid($token);
        if (!$user) {
            header('WWW-Authenticate: Basic realm="My Realm"');
            header('HTTP/1.0 401 Unauthorized');
            return;
        }
        else if($user['role'] == 1 || $user['role'] == 2){
            $requests = $mobile->getCancelledRequests($user['user_id']);
        }
        else{
            $requests = false;
        }
        $this->getResponse()->setHeader('Content-type', 'application/json');
        $row['response'] = $requests;
        echo json_encode($row);
    }

    /************** Детали заказа *****************************************************/
    public function getRequestDetailAction(){
        $mobile = new Application_Model_DbTable_Mobile();
        $token = $this->_getParam('token', '');
        $user = $mobile->tokenIsValid($token);
        if (!$user) {
            header('WWW-Authenticate: Basic realm="My Realm"');
            header('HTTP/1.0 401 Unauthorized');
            return;
        }
        $array_items = array();
        $request_id = $this->_getParam("request_id");
        $request = $mobile->getRequestById($request_id);
        $request_item_tab = $mobile->getRequestItemList($request['request_id']);
        array_push($array_items, $request, $request_item_tab);
        $this->getResponse()->setHeader('Content-type', 'application/json');
        $row['response'] = $array_items;
        echo json_encode($row);
    }

    /************** Заказ принят курьером *****************************************************/
    public function setRequestAcceptedAction(){
        $mobile = new Application_Model_DbTable_Mobile();
        $token = $this->_getParam('token', '');
        $user = $mobile->tokenIsValid($token);
        if (!$user) {
            header('WWW-Authenticate: Basic realm="My Realm"');
            header('HTTP/1.0 401 Unauthorized');
            return;
        }
        else if($user['role'] == 1 || $user['role'] == 2){
            $request_id = $this->_getParam("request_id");
            $request = $mobile->setRequestAcceptedByCourier($request_id);
        }
        else{
            $request = false;
        }
        $this->getResponse()->setHeader('Content-type', 'application/json');
        $row['response'] = $request;
        echo json_encode($row);
    }

    /************** Заказ доставлен *****************************************************/
    public function setRequestDeliveredAction(){
        $mobile = new Application_Model_DbTable_Mobile();
        $token = $this->_getParam('token', '');
        $user = $mobile->tokenIsValid($token);
        if (!$user) {
            header('WWW-Authenticate: Basic realm="My Realm"');
            header('HTTP/1.0 401 Unauthorized');
            return;
        }
        else if($user['role'] == 1 || $user['role'] == 2){
            $request_id = $this->_getParam("request_id");
            $request = $mobile->setRequestDelivered($request_id);
        }
        else{
            $request = false;
        }
        $this->getResponse()->setHeader('Content-type', 'application/json');
        $row['response'] = $request;
        echo json_encode($row);
    }

    /************** Список всех товаров *****************************************************/
    public function productListAction(){
        $mobile = new Application_Model_DbTable_Mobile();

        $city_id = $this->_getParam("city_id","");
        $category_id = $this->_getParam("category_id","");
        $brand_id = $this->_getParam("brand_id","");
        $product_list = $mobile->getProductsByCatBrand($city_id,$category_id,$brand_id,10000);

        $page_number = $this->_getParam("page_number");
        $items_per_page = $this->_getParam("items_per_page");

        if(count($product_list) > 0){
            $page = $this->_getParam('page',$page_number);
            $paginator = Zend_Paginator::factory($product_list);
            $paginator->setItemCountPerPage($items_per_page);
            $paginator->setCurrentPageNumber($page);
            $arr = array();
            foreach($paginator as $key => $pag){
                array_push($arr, $pag);
            }
            $result = $arr;
        }
        else{
            $result = false;
        }
        $this->getResponse()->setHeader('Content-type', 'application/json');
        $row['response'] = $result;
		$row['response2'] = count($product_list);
        echo json_encode($row);
    }

    /************** Список всех товаров (новый) *****************************************************/
    public function productListByFiltersAction(){
        $mobile = new Application_Model_DbTable_Mobile();

        $city_id = $this->_getParam("city_id","");
        $category_group_id = $this->_getParam("category_group_id","");
        $category_id = $this->_getParam("category_id","");
        $brand_id = $this->_getParam("brand_id","");
        $product_list = $mobile->getProductsByCategoryGroupCatBrand($city_id,$category_group_id,$category_id,$brand_id);

        $page_number = $this->_getParam("page_number","1");
        $items_per_page = $this->_getParam("items_per_page","1000");

        if(count($product_list) > 0){
            $page = $this->_getParam('page',$page_number);
            $paginator = Zend_Paginator::factory($product_list);
            $paginator->setItemCountPerPage($items_per_page);
            $paginator->setCurrentPageNumber($page);
            $arr = array();
            foreach($paginator as $key => $pag){
                array_push($arr, $pag);
            }
            $result = $arr;
        }
        else{
            $result = false;
        }
        $this->getResponse()->setHeader('Content-type', 'application/json');
        $row['response'] = $result;
		$row['response2'] = count($product_list);
        echo json_encode($row);
    }

    /************** Список новых товаров *****************************************************/
    public function newProductListAction(){
        $mobile = new Application_Model_DbTable_Mobile();

        $city_id = $this->_getParam("city_id","");
        $product_list = $mobile->getNewProductsByCity($city_id);

        $page_number = $this->_getParam("page_number");
        $items_per_page = $this->_getParam("items_per_page");

        if(count($product_list) > 0){
            $page = $this->_getParam('page',$page_number);
            $paginator = Zend_Paginator::factory($product_list);
            $paginator->setItemCountPerPage($items_per_page);
            $paginator->setCurrentPageNumber($page);
            $arr = array();
            foreach($paginator as $key => $pag){
                array_push($arr, $pag);
            }
            $result = $arr;
        }
        else{
            $result = false;
        }
        $this->getResponse()->setHeader('Content-type', 'application/json');
        $row['response'] = $result;
        $row['response2'] = count($product_list);
        echo json_encode($row);
    }

    /************** Список популярных товаров *****************************************************/
    public function popularProductListAction(){
        $mobile = new Application_Model_DbTable_Mobile();

        $city_id = $this->_getParam("city_id","");
        $product_list = $mobile->getPopularProductsByCity($city_id);

        $page_number = $this->_getParam("page_number");
        $items_per_page = $this->_getParam("items_per_page");

        if(count($product_list) > 0){
            $page = $this->_getParam('page',$page_number);
            $paginator = Zend_Paginator::factory($product_list);
            $paginator->setItemCountPerPage($items_per_page);
            $paginator->setCurrentPageNumber($page);
            $arr = array();
            foreach($paginator as $key => $pag){
                array_push($arr, $pag);
            }
            $result = $arr;
        }
        else{
            $result = false;
        }
        $this->getResponse()->setHeader('Content-type', 'application/json');
        $row['response'] = $result;
        $row['response2'] = count($product_list);
        echo json_encode($row);
    }

    /************** Детали товара *****************************************************/
    public function productDetailAction(){
        $mobile = new Application_Model_DbTable_Mobile();

        $product_id = $this->_getParam("product_id");
        $city_id = $this->_getParam("city_id",0);
        $product = $mobile->getProductDetail($product_id,$city_id);

        if(count($product) > 0){
            $product['description'] = strip_tags($product['description'],"<p><a><b><i><u><span><img><br><textarea><div><article><blockquote><body><button><em><embed><fieldset><footer><head><h1><h2><h3><h4><h5><h6><input><label><nav><q><table><tr><td><small><style><tbody><thead><title><ul>");
            $product['image_list'] = $mobile->getProductPhotoById($product_id);
            $result = $product;
        }
        else{
            $result = false;
        }
        $this->getResponse()->setHeader('Content-type', 'application/json');
        $row['response'] = $result;
        echo json_encode($row);
    }

    /************** Список активных акции *****************************************************/
    public function activeOfferListAction(){
        $mobile = new Application_Model_DbTable_Mobile();

        $offer_list = $mobile->getActiveOfferList();

        if(count($offer_list) > 0){
            $result = $offer_list;
        }
        else{
            $result = false;
        }
        $this->getResponse()->setHeader('Content-type', 'application/json');
        $row['response'] = $result;
        echo json_encode($row);
    }

    /************** Детали акции *****************************************************/
    public function offerDetailAction(){
        $mobile = new Application_Model_DbTable_Mobile();

        $offer_id = $this->_getParam("offer_id");
        $offer = $mobile->getOfferDetail($offer_id);

        if(count($offer) > 0){
            $result = $offer;
        }
        else{
            $result = false;
        }
        $this->getResponse()->setHeader('Content-type', 'application/json');
        $row['response'] = $result;
        echo json_encode($row);
    }

    /************** Данные пользователя *****************************************************/
    public function userDetailAction(){
        $mobile = new Application_Model_DbTable_Mobile();
        $token = $this->_getParam('token', '');
        $user = $mobile->tokenIsValid($token);
        if (!$user) {
            header('WWW-Authenticate: Basic realm="My Realm"');
            header('HTTP/1.0 401 Unauthorized');
            return;
        }
        else if($user['role'] == 1 || $user['role'] == 2 || $user['role'] == 3 || $user['role'] == 4){
            $user_id = $user['user_id'];
            $user_row = $mobile->getUserDetail($user_id);
            $last_request_address = "";
            $last_request_city_id = 0;
            $last_user_request_row = $mobile->getLastUserRequest($user_id);
            if(count($last_user_request_row) > 0){
                $last_request_address = $last_user_request_row['address'];
                $last_request_city_id = $last_user_request_row['city_id'];
            }
            $user_row['last_user_request_address'] = $last_request_address;
            $user_row['last_request_city_id'] = $last_request_city_id;

            if(count($user_row) > 0){
                $result = $user_row;
            }
            else{
                $result = false;
            }
            $this->getResponse()->setHeader('Content-type', 'application/json');
            $row['response'] = $result;
            echo json_encode($row);
        }
        else {
            $this->getResponse()->setHeader('Content-type', 'application/json');
            $row['response'] = "ACCESS_RESTRICT";
            echo json_encode($row);
            return;
        }
    }

    /************** Список подкатегории *****************************************************/
    public function categoryListAction(){
        $mobile = new Application_Model_DbTable_Mobile();

        $city_id = $this->_getParam("city_id");
        $category_list = $mobile->getCategoryList($city_id);

        if(count($category_list) > 0){
            $result = $category_list;
        }
        else{
            $result = false;
        }
        $this->getResponse()->setHeader('Content-type', 'application/json');
        $row['response'] = $result;
        echo json_encode($row);
    }

    /************** Список категории *****************************************************/
    public function getCategoryGroupListAction(){
        $mobile = new Application_Model_DbTable_Mobile();

        $city_id = $this->_getParam("city_id");
        $category_group_list = $mobile->getCategoryGroups();
        $a = array();
        $i = 0;
        $b = 0;
        $category_arr = array();
        foreach($category_group_list as $key => $category_group){
            $products_by_city = $mobile->checkProductByCategoryGroupCity($category_group['category_group_id'],$city_id);
            if(count($products_by_city) > 0){
                $a[$i] = $category_group;
                $category_list = $mobile->getCategoryListByCatGroup($category_group['category_group_id']);
                if(count($category_list) > 0){
                    $b = 0;
                    foreach($category_list as $key2 => $category){
                        $products_by_city_2 = $mobile->checkProductByCategoryCity($category['category_id'], $city_id);
                        if(count($products_by_city_2) > 0){
                            $category_arr[$b] = $category;
                            $b++;
                        }
                    }
                }
                if(count($category_arr) > 0){
                    $a[$i]['category_list'] = $category_arr;
                }
                unset($category_arr);
                $i++;
            }
        }

        if(count($a) > 0){
            $result = $a;
        }
        else{
            $result = false;
        }
        $this->getResponse()->setHeader('Content-type', 'application/json');
        $row['response'] = $result;
        echo json_encode($row);
    }

    /************** Список подкатегории по категории *****************************************************/
    public function getCategoryListByCategoryGroupAction(){
        $mobile = new Application_Model_DbTable_Mobile();

        $city_id = $this->_getParam("city_id");
        $category_group_id = $this->_getParam("category_group_id");
        $products_by_city = $mobile->checkProductByCategoryGroupCity($category_group_id,$city_id);

        $category_arr = array();
        $category_list = "";
        if(count($products_by_city) > 0){
            $category_list = $mobile->getCategoryListByCatGroup($category_group_id);
            if(count($category_list) > 0){
                $b = 0;
                foreach($category_list as $key2 => $category){
                    $products_by_city_2 = $mobile->checkProductByCategoryCity($category['category_id'], $city_id);
                    if(count($products_by_city_2) > 0){
                        $category_arr[$b] = $category;
                        $b++;
                    }
                }
            }
        }

        if(count($category_arr) > 0){
            $result = $category_arr;
        }
        else{
            $result = false;
        }
        $this->getResponse()->setHeader('Content-type', 'application/json');
        $row['response'] = $result;
        echo json_encode($row);
    }

    /************** Список брендов *****************************************************/
    public function brandListAction(){
        $mobile = new Application_Model_DbTable_Mobile();

        $city_id = $this->_getParam("city_id");
        $brand_list = $mobile->getBrandList($city_id);

        if(count($brand_list) > 0){
            $result = $brand_list;
        }
        else{
            $result = false;
        }
        $this->getResponse()->setHeader('Content-type', 'application/json');
        $row['response'] = $result;
        echo json_encode($row);
    }

    /************** Добавление товара в корзину *****************************************************/
    public function addItemAction(){
        $mobile = new Application_Model_DbTable_Mobile();

        $token = $this->_getParam('token', '');
        $user = $mobile->tokenIsValid($token);
        if (!$user) {
            header('WWW-Authenticate: Basic realm="My Realm"');
            header('HTTP/1.0 401 Unauthorized');
            return;
        }

        $user_id = $user['user_id'];
        $city_id = $this->_getParam("city_id");
        $product_id = $this->_getParam("product_id");
        $result = "";
        if($user_id > 0 && $city_id > 0 && $product_id > 0){
            $add_product = $mobile->addProduct($user_id,$city_id,$product_id);
            if($add_product > 0){
                $result = true;
            }
            else{
                $result = false;
            }
        }
        else{
            $result = false;
        }

        $this->getResponse()->setHeader('Content-type', 'application/json');
        $row['response'] = $result;
        echo json_encode($row);
    }

    /************** Удаление товара в корзину *****************************************************/
    public function deleteItemAction(){
        $mobile = new Application_Model_DbTable_Mobile();

        $token = $this->_getParam('token', '');
        $user = $mobile->tokenIsValid($token);
        if (!$user) {
            header('WWW-Authenticate: Basic realm="My Realm"');
            header('HTTP/1.0 401 Unauthorized');
            return;
        }

        $user_id = $user['user_id'];
        $city_id = $this->_getParam("city_id");
        $product_id = $this->_getParam("product_id");
        $result = "";
        if($user_id > 0 && $city_id > 0 && $product_id > 0){
            $delete_product = $mobile->deleteProduct($user_id,$city_id,$product_id);
            if($delete_product != false){
                $result = true;
            }
            else{
                $result = false;
            }
        }
        else{
            $result = false;
        }

        $this->getResponse()->setHeader('Content-type', 'application/json');
        $row['response'] = $result;
        echo json_encode($row);
    }

    /************** Список товаров в корзине *****************************************************/
    public function basketProductListAction(){
        $mobile = new Application_Model_DbTable_Mobile();

        $token = $this->_getParam('token', '');
        $user = $mobile->tokenIsValid($token);
        if (!$user) {
            header('WWW-Authenticate: Basic realm="My Realm"');
            header('HTTP/1.0 401 Unauthorized');
            return;
        }

        $user_id = $user['user_id'];
        $user_product_list = $mobile->getUserProductList($user_id);

        if(count($user_product_list) > 0){
            $result = $user_product_list;
        }
        else{
            $result = false;
        }
        $this->getResponse()->setHeader('Content-type', 'application/json');
        $row['response'] = $result;
        echo json_encode($row);
    }

    /************** Удаление всех продуктов пользователя в корзине *****************************************************/
    public function deleteAllUserProductsAction(){
        $mobile = new Application_Model_DbTable_Mobile();

        $token = $this->_getParam('token', '');
        $user = $mobile->tokenIsValid($token);
        if (!$user) {
            header('WWW-Authenticate: Basic realm="My Realm"');
            header('HTTP/1.0 401 Unauthorized');
            return;
        }

        $user_id = $user['user_id'];
        $delete_all_products = $mobile->deleteAllUserProducts($user_id);

        $result = $delete_all_products;
        $this->getResponse()->setHeader('Content-type', 'application/json');
        $row['response'] = $result;
        echo json_encode($row);
    }

    /************** Отправка заявки *****************************************************/
    public function makeRequestAction(){
        $mobile = new Application_Model_DbTable_Mobile();

        $token = $this->_getParam('token', '');
        $user = $mobile->tokenIsValid($token);
        if (!$user) {
            header('WWW-Authenticate: Basic realm="My Realm"');
            header('HTTP/1.0 401 Unauthorized');
            return;
        }

        $allparam = $this->_getAllParams();
        $user_id = $user['user_id'];
        $user_products = $mobile->getUserProductList($user_id);

        $sum = 0;
        $result = "";
        if(count($user_products) > 0){
            foreach($user_products as $key => $user_item){
                $sum += $user_item['price']*$user_item['unit'];
            }
        }
        else{
            $result = false;
        }

        $bonus_by_city = $mobile->getBonusByCity($allparam['city_id']);

        if(count($bonus_by_city) > 0){
            $bonus_count = intval(($sum*$bonus_by_city['mob_bonus_count']/100));
            $allparam['bonus_percent'] = $bonus_by_city['mob_bonus_count'];
        }
        else{
            $bonus_count = intval(($sum*0/100));
            $allparam['bonus_percent'] = 0;
        }

        $allparam['bonus_count'] = $bonus_count;
        $allparam['is_epay'] = 0;
        if($allparam['status_id'] == "null"){
            $allparam['is_epay'] = 1;
            $allparam['status_id'] = null;
        }
        $request_row = $mobile->getUserActiveRequest($user_id);
        $make_request = $mobile->makeRequest($allparam,$user_id);
        $order_id = 0;
        $appendix = "";
        $amount = 0;
        $currency_id = 0;
        $content = "";
        if($make_request == true){
            $result = $make_request;
            if($allparam['status_id'] == null){
                require_once("kkb_cert/real_paysys/kkb.utils.php");
                $self = $_SERVER['PHP_SELF'];
                $path1 = 'kkb_cert/real_paysys/config.txt';	// Путь к файлу настроек config.dat
                if(count($request_row) > 0){
                    $order_id = $request_row['request_id'];				// Порядковый номер заказа - преобразуется в формат "000001"
                }
                $currency_id = "398"; 			// Шифр валюты  - 840-USD, 398-Tenge
                $amount = $sum;				// Сумма платежа
                $content = process_request($order_id,$currency_id,$amount,$path1); // Возвращает подписанный и base64 кодированный XML документ для отправки в банк

                $xml = "<document>";
                $i = 0;
                foreach($user_products as $key => $product_r){
                    $i++;
                    $xml .= '<item number="' . $i .  '" name="' . $product_r['name'] . '" quantity="' . $product_r['unit'] . '" amount="' . $product_r['unit']*$product_r['price'] .'"/>';
                }
                $xml .= "</document>";
                $appendix = base64_encode($xml);

                $row['currency_id'] = $currency_id;
                $row['amount'] = $amount;
                $row['content'] = $content;
                $row['appendix'] = $appendix;
                $row['order_id'] = $order_id;
            }
            else{
                $send_mail = $mobile->sendAdminNoticeEmail($allparam,$user_id,$user_products);
            }
        }

        $this->getResponse()->setHeader('Content-type', 'application/json');
        $row['response'] = $result;
        echo json_encode($row);
    }

    /************** Изменение количество продукта *****************************************************/
    public function changeItemCountAction(){
        $mobile = new Application_Model_DbTable_Mobile();

        $token = $this->_getParam('token', '');
        $user = $mobile->tokenIsValid($token);
        if (!$user) {
            header('WWW-Authenticate: Basic realm="My Realm"');
            header('HTTP/1.0 401 Unauthorized');
            return;
        }

        $user_id = $user['user_id'];
        $product_id = $this->_getParam("product_id");
        $city_id = $this->_getParam("city_id",0);
        $count = $this->_getParam("count");
        $result = "";
        if($user_id > 0 && $product_id > 0){
            $change_product_count = $mobile->changeItemCount($user_id,$product_id,$count,$city_id);
            $result = $change_product_count;
        }
        else{
            $result = false;
        }

        $this->getResponse()->setHeader('Content-type', 'application/json');
        $row['response'] = $result;
        echo json_encode($row);
    }

    /************** Список заявок пользователя *****************************************************/
    public function userRequestListAction(){
        $mobile = new Application_Model_DbTable_Mobile();

        $token = $this->_getParam('token', '');
        $user = $mobile->tokenIsValid($token);
        if (!$user) {
            header('WWW-Authenticate: Basic realm="My Realm"');
            header('HTTP/1.0 401 Unauthorized');
            return;
        }

        $user_id = $user['user_id'];
        $result = "";
        if($user_id > 0){
            $request_list = $mobile->userRequestList($user_id);
            if(count($request_list) > 0 && $request_list != false){
                $i = 0;
                $request_product_arr = array();
                foreach($request_list as $key => $request){
                    $request_product_list = $mobile->userRequestProductList($request['request_id']);
                    if(count($request_product_list) > 0){
                        $request_product_arr[$i] = $request;
                        $request_product_arr[$i]['request_product_list'] = $request_product_list;
                        $i++;
                    }
                }
                $result = $request_product_arr;
            }
            else{
                $result = false;
            }
        }
        else{
            $result = false;
        }

        $this->getResponse()->setHeader('Content-type', 'application/json');
        $row['response'] = $result;
        echo json_encode($row);
    }

    /************** Список продуктов в заявке пользователя *****************************************************/
    public function userRequestProductListAction(){
        $mobile = new Application_Model_DbTable_Mobile();

        $token = $this->_getParam('token', '');
        $user = $mobile->tokenIsValid($token);
        if (!$user) {
            header('WWW-Authenticate: Basic realm="My Realm"');
            header('HTTP/1.0 401 Unauthorized');
            return;
        }

        $user_id = $user['user_id'];
        $request_id = $this->_getParam('request_id');
        $result = "";
        if($user_id > 0 && $request_id > 0){
            $request_product_list = $mobile->userRequestProductList($request_id);
            if(count($request_product_list) > 0){
                $result = $request_product_list;
            }
            else{
                $result = false;
            }
        }
        else{
            $result = false;
        }

        $this->getResponse()->setHeader('Content-type', 'application/json');
        $row['response'] = $result;
        echo json_encode($row);
    }

    /************** Переотправка заявки пользователя *****************************************************/
    public function requestReorderAction(){
        $mobile = new Application_Model_DbTable_Mobile();

        $token = $this->_getParam('token', '');
        $user = $mobile->tokenIsValid($token);
        if (!$user) {
            header('WWW-Authenticate: Basic realm="My Realm"');
            header('HTTP/1.0 401 Unauthorized');
            return;
        }

        $user_id = $user['user_id'];
        $request_id = $this->_getParam("request_id");
        $city_id = $this->_getParam("city_id");
        $result = "";
        if($user_id > 0 && $request_id > 0){
            $result_reorder = $mobile->requestReorder($user_id,$request_id,$city_id);
            if($result_reorder['response'] == false){
                $row['response'] = false;
            }
            else{
                $row = $result_reorder;
            }
        }
        else{
            $row['response'] = false;
        }

        $this->getResponse()->setHeader('Content-type', 'application/json');

        echo json_encode($row);
    }

    /************** Бонус пользователя *****************************************************/
    public function userBonusAction(){
        $mobile = new Application_Model_DbTable_Mobile();

        $token = $this->_getParam('token', '');
        $user = $mobile->tokenIsValid($token);
        if (!$user) {
            header('WWW-Authenticate: Basic realm="My Realm"');
            header('HTTP/1.0 401 Unauthorized');
            return;
        }

        $user_id = $user['user_id'];
//        $user_bonus = $mobile->getUserBonus($user_id);
        $user_bonus['bonus'] = $mobile->getUserBonus2($user_id);
        $user_bonus['friends_bonus'] = $mobile->getUserBonusByFriends($user_id);
        $user_bonus['balans'] = $mobile->getUserBonusNew($user_id);
        $user_bonus['spend_bonus'] = $mobile->getUserSpendedBonus($user_id);

        $result = $user_bonus;

        $this->getResponse()->setHeader('Content-type', 'application/json');
        $row['response'] = $result;
        echo json_encode($row);
    }

    /************** Оплата покупку бонусом *****************************************************/
    public function payByBonusAction(){
        $mobile = new Application_Model_DbTable_Mobile();

        $token = $this->_getParam('token', '');
        $user = $mobile->tokenIsValid($token);
        if (!$user) {
            header('WWW-Authenticate: Basic realm="My Realm"');
            header('HTTP/1.0 401 Unauthorized');
            return;
        }

        $allparam = $this->_getAllParams();
        $user_id = $user['user_id'];
        $user_products = $mobile->getUserProductList($user_id);

        $sum = 0;
        if(count($user_products) > 0){
            foreach($user_products as $key => $user_item){
                $sum += $user_item['price']*$user_item['unit'];
            }
        }

//        $user_bonus = $mobile->getUserBonus($user_id);
        $user_bonus = $mobile->getUserBonusNew($user_id);

        if($user_bonus >= $sum){
            $bonus_count = -$sum;
            $allparam['bonus_count'] = $bonus_count;
            $result = $mobile->makeRequestByBonus($allparam,$user_id);
            if($result == true){
                $send_mail = $mobile->sendAdminNoticeEmail($allparam,$user_id,$user_products);
            }
        }
        else{
            $result = false;
        }

        $this->getResponse()->setHeader('Content-type', 'application/json');
        $row['response'] = $result;
        echo json_encode($row);
    }
	
	/************** Список городов *****************************************************/
	public function getCityListAction(){
		$mobile = new Application_Model_DbTable_Mobile();

//        $city_list = $mobile->getCityList();
        $city_list = $mobile->mainCityList();

        if(count($city_list) > 0){
            $result = $city_list;
        }
        else{
            $result = false;
        }
        $this->getResponse()->setHeader('Content-type', 'application/json');
        $row['response'] = $result;
        echo json_encode($row);
	}

    /************** Поиск *****************************************************/
	public function searchAction(){
		$mobile = new Application_Model_DbTable_Mobile();

        $search_str = $this->_getParam("search_str","");
        $city_id = $this->_getParam("city_id","");
        $search_result = $mobile->getProductBySearchChar($search_str,$city_id);

        if(count($search_result) > 0){
            $result = $search_result;
        }
        else{
            $result = false;
        }
        $this->getResponse()->setHeader('Content-type', 'application/json');
        $row['response'] = $result;
        echo json_encode($row);
	}

    /************** Бренд по подкатегории *****************************************************/
    public function brandByCategoryGroupAction(){
        $mobile = new Application_Model_DbTable_Mobile();
        $category_id = $this->_getParam("category_id",0);
        $category_group_id = $this->_getParam("category_group_id",0);
        $city_id = $this->_getParam("city_id");
        $brand_item_list = $mobile->getBrandByCategoryGroup($category_id,$category_group_id,$city_id);

        if(count($brand_item_list) > 0){
            $result = $brand_item_list;
        }
        else{
            $result = false;
        }
        $this->getResponse()->setHeader('Content-type', 'application/json');
        $row['response'] = $result;
        echo json_encode($row);
    }

    /************** Товары по акции *****************************************************/
    public function discountProductListAction(){
        $mobile = new Application_Model_DbTable_Mobile();

        $city_id = $this->_getParam("city_id","");
        $stock_product_list = $mobile->getWholeSaleProductsAll($city_id);

        if(count($stock_product_list) > 0){
            $result = $stock_product_list;
        }
        else{
            $result = false;
        }
        $this->getResponse()->setHeader('Content-type', 'application/json');
        $row['response'] = $result;
        echo json_encode($row);
    }

    /************** Оптовая продажа *****************************************************/
    public function wholesaleProductListAction(){
        $mobile = new Application_Model_DbTable_Mobile();

        $wholesale_product_list = $mobile->getOptProducts();

        if(count($wholesale_product_list) > 0){
            $result = $wholesale_product_list;
        }
        else{
            $result = false;
        }
        $this->getResponse()->setHeader('Content-type', 'application/json');
        $row['response'] = $result;
        echo json_encode($row);
    }

    /************** Сообщить о продукте *****************************************************/
    public function productInformAction(){
        $mobile = new Application_Model_DbTable_Mobile();

        $token = $this->_getParam('token', '');
        $user = $mobile->tokenIsValid($token);
        if (!$user) {
            header('WWW-Authenticate: Basic realm="My Realm"');
            header('HTTP/1.0 401 Unauthorized');
            return;
        }

        $allparam = $this->_getAllParams();
        $result = $mobile->sendWaitingRequest($allparam);

        $this->getResponse()->setHeader('Content-type', 'application/json');
        $row['response'] = $result;
        echo json_encode($row);
    }

    /************** Регистрация *****************************************************/
    public function registerAction(){
        $mobile = new Application_Model_DbTable_Mobile();

        $allparam = $this->_getAllParams();
        $result = $mobile->registerUser($allparam);

        $this->getResponse()->setHeader('Content-type', 'application/json');
        $row['response'] = $result;
        echo json_encode($row);
    }

//   Новая версия моб. приложения

    public function getCityListHtmlAction(){
        $mobile = new Application_Model_DbTable_Mobile();

        $city_list = $mobile->mainCityList();

        $this->view->city_list = $city_list;
        echo $this->renderScript('city-list-json.phtml');
    }

    public function getBrandListHtmlAction(){
        $mobile = new Application_Model_DbTable_Mobile();

        $city_id = $this->_getParam("city_id");
        $brand_list = $mobile->getBrandListNew($city_id);

        $this->view->brand_list = $brand_list;
        echo $this->renderScript('brand-list-json.phtml');
    }

    public function getCategoryGroupListHtmlAction(){
        $mobile = new Application_Model_DbTable_Mobile();

        $city_id = $this->_getParam("city_id",0);
        $category_group_list = $mobile->getCategoryGroups();
        $a = array();
        $i = 0;
        $b = 0;
        $category_arr = array();
        foreach($category_group_list as $key => $category_group){
            $products_by_city = $mobile->checkProductByCategoryGroupCity($category_group['category_group_id'],$city_id);
            if(count($products_by_city) > 0){
                $a[$i] = $category_group;
                $category_list = $mobile->getCategoryListByCatGroup($category_group['category_group_id']);
                if(count($category_list) > 0){
                    $b = 0;
                    foreach($category_list as $key2 => $category){
                        $products_by_city_2 = $mobile->checkProductByCategoryCity($category['category_id'], $city_id);
                        if(count($products_by_city_2) > 0){
                            $category_arr[$b] = $category;
                            $b++;
                        }
                    }
                }
                if(count($category_arr) > 0){
                    $a[$i]['category_list'] = $category_arr;
                }
                unset($category_arr);
                $i++;
            }
        }

        $this->view->category_list = $a;
        echo $this->renderScript('category-list-json.phtml');
    }

    public function productListHtmlAction()
    {
        $mobile = new Application_Model_DbTable_Mobile();

        $city_id = $this->_getParam("city_id", "");
        $category_id = $this->_getParam("category_id", "");
        $brand_id = $this->_getParam("brand_id", "");
        $page = $this->_getParam("page", 0);
        $product_list = $mobile->getProductsByCatBrandNew($city_id, $category_id, $brand_id,$page);

        $this->view->product_list = $product_list;
        echo $this->renderScript('product-list-json.phtml');
    }

    public function productInfoHtmlAction()
    {
        $mobile = new Application_Model_DbTable_Mobile();

        $product_id = $this->_getParam("product_id",0);
        $city_id = $this->_getParam("city_id",0);
        $row = $mobile->getProductDetail($product_id,$city_id);

        $this->view->row = $row;
        echo $this->renderScript('product-info-json.phtml');
    }

    public function productListByCategoryHtmlAction()
    {
        $mobile = new Application_Model_DbTable_Mobile();

        $city_id = $this->_getParam("city_id",0);
        $category_group_id = $this->_getParam("category_group_id",0);
        $category_id = $this->_getParam("category_id",0);
        $brand_id = $this->_getParam("brand_id",0);
        $page = $this->_getParam("page",1);
        $product_list = $mobile->getProductsByCategoryGroupCatBrandNew($city_id,$category_group_id,$category_id,$brand_id, $page);
        $product_list_all = $mobile->getProductsByCategoryGroupCatBrandAllNew($city_id,$category_group_id,$category_id,$brand_id, $page);

        $last_page = 0;
        if($product_list_all > 0){
            $last_page = ceil($product_list_all/10);
        }
        $this->view->product_list = $product_list;
        $this->view->last_page = intval($last_page);
        $this->view->current_page = intval($page);
        $this->view->category_group_id = $category_group_id;
        $this->view->category_id = $category_id;
        $this->view->brand_id = $brand_id;

        $page_type = 0;
        if($category_group_id > 0 && $category_id == 0 && $brand_id == 0){
            $page_type = 1;
        }
        else if($category_group_id > 0 && $category_id > 0 && $brand_id == 0){
            $page_type = 1;
        }
        else if($brand_id > 0 && $category_group_id == 0 && $category_id == 0){
            $page_type = 2;
        }
        else if($category_group_id > 0 && $category_id > 0 && $brand_id > 0){
            $page_type = 1;
        }
        $this->view->page_type = $page_type;

        echo $this->renderScript('product-list-json.phtml');
    }

    public function productInfoAction(){
        $mobile = new Application_Model_DbTable_Mobile();
        $product_id = $this->_getParam("product_id",0);
        $city_id = $this->_getParam("city_id",0);
        $row1 = $mobile->getProductDetail($product_id,$city_id);
        $this->getResponse()->setHeader('Content-type', 'application/json');
        $row['response'] = $row1;
        echo json_encode($row);
    }

    public function authorizeAction(){
        $mobile = new Application_Model_DbTable_Mobile();
        $i = 1;
        if ($i == 0) {
            header('WWW-Authenticate: Basic realm="My Realm"');
            header('HTTP/1.0 401 Unauthorized');
            return;
        }
        else
        {
            $login = $this->_getParam("email");
            $password = $this->_getParam("password");

            if(strlen(trim($login)) < 1 || strlen(trim($password)) < 1){
                header('WWW-Authenticate: Basic realm="My Realm"');
                header('HTTP/1.0 401 Unauthorized');
                $this->getResponse()->setHeader('Content-type', 'application/json');
                $row['error'] = "1";
                echo json_encode($row);
                return;
            }

            $isValid = $mobile->userAuthorize($login, $password);

            if (!$isValid){
                $this->view->error="error http";
                header('WWW-Authenticate: Basic realm="My Realm"');
                header('HTTP/1.0 401 Unauthorized');
                $this->getResponse()->setHeader('Content-type', 'application/json');
                $row['error'] = "1";
                echo json_encode($row);
                return;
            }
            else
            {
                $token = $mobile->tokenGet($login,$password);
                if (!$token){
                    $token = md5(uniqid($login.time(), true));
                    $mobile->tokenEdit($login, $token);
                } else {
                    $mobile->tokenUpdate($token);
                }

                $this->getResponse()->setHeader('Content-type', 'application/json');
                $row['token'] = $token;
                $row['user_info'] = $isValid;
                echo json_encode($row);
            }
        }
    }

    public function checkTokenAction(){
        $mobile = new Application_Model_DbTable_Mobile();
        $token = $this->_getParam('token', '');
        $user = $mobile->tokenIsValid($token);
        if (!$user) {
            $row['response'] = 0;
        }
        else{
            $row['response'] = 1;
            $row['user_info'] = $user;
        }
        $this->getResponse()->setHeader('Content-type', 'application/json');
        echo json_encode($row);
    }

    public function offerListHtmlAction(){
        $mobile = new Application_Model_DbTable_Mobile();

        $offer_list = $mobile->getActiveOfferList();

        $this->view->offer_list = $offer_list;
        echo $this->renderScript('offer-list-json.phtml');
    }

    public function basketProductListCountAction(){
        $mobile = new Application_Model_DbTable_Mobile();

        $token = $this->_getParam('token', '');
        $user = $mobile->tokenIsValid($token);
        if (!$user) {
            header('WWW-Authenticate: Basic realm="My Realm"');
            header('HTTP/1.0 401 Unauthorized');
            return;
        }

        $user_id = $user['user_id'];
        $user_product_list = $mobile->getUserProductList($user_id);

        $this->getResponse()->setHeader('Content-type', 'application/json');
        $row['response'] = count($user_product_list);
        echo json_encode($row);
    }

    public function basketProductListHtmlAction(){
        $mobile = new Application_Model_DbTable_Mobile();

        $token = $this->_getParam('token', '');
        $user = $mobile->tokenIsValid($token);
        if (!$user) {
            header('WWW-Authenticate: Basic realm="My Realm"');
            header('HTTP/1.0 401 Unauthorized');
            return;
        }

        $user_id = $user['user_id'];
        $user_product_list = $mobile->getUserProductList($user_id);

        $this->view->user_product_list = $user_product_list;
        echo $this->renderScript('basket-list-json.phtml');
    }

    public function changeRequestItemCountAction(){
        $mobile = new Application_Model_DbTable_Mobile();

        $token = $this->_getParam('token', '');
        $user = $mobile->tokenIsValid($token);
        if (!$user) {
            header('WWW-Authenticate: Basic realm="My Realm"');
            header('HTTP/1.0 401 Unauthorized');
            return;
        }

        $user_id = $user['user_id'];
        $request_item_id = $this->_getParam("request_item_id");
        $city_id = $this->_getParam("city_id",0);
        $count = $this->_getParam("count");
        $result = "";
        $sum = 0;
        $bonus_count = 0;

        if($user_id > 0 && $request_item_id > 0){
            $change_product_count = $mobile->changeRequestItemCount($user_id,$request_item_id,$count,$city_id);
            $result = $change_product_count;

            $user_products = $mobile->getUserProductList($user_id);

            if(count($user_products) > 0){
                foreach($user_products as $key => $user_item){
                    $sum += $user_item['price']*$user_item['unit'];
                }
            }
            $bonus_by_city = $mobile->getBonusByCity($city_id);

            if(count($bonus_by_city) > 0){
                $bonus_count = intval(($sum*$bonus_by_city['mob_bonus_count']/100));
            }
        }
        else{
            $result = false;
        }

        $this->getResponse()->setHeader('Content-type', 'application/json');
        $row['response'] = $result;
        $row['sum'] = $sum;
        $row['bonus_count'] = $bonus_count;
        echo json_encode($row);
    }

    public function deleteRequestItemAction(){
        $mobile = new Application_Model_DbTable_Mobile();

        $token = $this->_getParam('token', '');
        $user = $mobile->tokenIsValid($token);
        if (!$user) {
            header('WWW-Authenticate: Basic realm="My Realm"');
            header('HTTP/1.0 401 Unauthorized');
            return;
        }

        $user_id = $user['user_id'];
        $city_id = $this->_getParam("city_id");
        $request_item_id = $this->_getParam("request_item_id");
        $result = "";
        $sum = 0;
        $bonus_count = 0;
        if($user_id > 0 && $city_id > 0 && $request_item_id > 0){
            $delete_product = $mobile->deleteRequestItem($user_id,$city_id,$request_item_id);
            if($delete_product != false){
                $result = true;

                $user_products = $mobile->getUserProductList($user_id);

                if(count($user_products) > 0){
                    foreach($user_products as $key => $user_item){
                        $sum += $user_item['price']*$user_item['unit'];
                    }
                }
                $bonus_by_city = $mobile->getBonusByCity($city_id);

                if(count($bonus_by_city) > 0){
                    $bonus_count = intval(($sum*$bonus_by_city['mob_bonus_count']/100));
                }
            }
            else{
                $result = false;
            }
        }
        else{
            $result = false;
        }

        $this->getResponse()->setHeader('Content-type', 'application/json');
        $row['response'] = $result;
        $row['sum'] = $sum;
        $row['bonus_count'] = $bonus_count;
        echo json_encode($row);
    }

    public function sendRequestAction(){
        $mobile = new Application_Model_DbTable_Mobile();

        $token = $this->_getParam('token', '');
        $user = $mobile->tokenIsValid($token);
        if (!$user) {
            header('WWW-Authenticate: Basic realm="My Realm"');
            header('HTTP/1.0 401 Unauthorized');
            return;
        }

        $allparam = $this->_getAllParams();
        $user_id = $user['user_id'];
        $user_products = $mobile->getUserProductList($user_id);

        $sum = 0;
        $result = "";
        if(count($user_products) > 0){
            foreach($user_products as $key => $user_item){
                $sum += $user_item['price']*$user_item['unit'];
            }
        }
        else{
            $result = false;
        }

        $bonus_by_city = $mobile->getBonusByCity($allparam['city_id']);

        if(count($bonus_by_city) > 0){
            $bonus_count = intval(($sum*$bonus_by_city['mob_bonus_count']/100));
            $allparam['bonus_percent'] = $bonus_by_city['mob_bonus_count'];
        }
        else{
            $bonus_count = intval(($sum*0/100));
            $allparam['bonus_percent'] = 0;
        }

        $allparam['bonus_count'] = $bonus_count;
        $allparam['is_epay'] = 0;
        if($allparam['status_id'] == 0){
            $allparam['is_epay'] = 1;
            $allparam['status_id'] = null;
        }
        $request_row = $mobile->getUserActiveRequest($user_id);
        $make_request = $mobile->makeRequest($allparam,$user_id);
        $order_id = 0;
        $appendix = "";
        $amount = 0;
        $currency_id = 0;
        $content = "";
        if($make_request == true){
            $result = $make_request;
            if($allparam['status_id'] == 0){
                require_once("kkb_cert/real_paysys/kkb.utils.php");
                $self = $_SERVER['PHP_SELF'];
                $path1 = 'kkb_cert/real_paysys/config.txt';	// Путь к файлу настроек config.dat
                if(count($request_row) > 0){
                    $order_id = $request_row['request_id'];				// Порядковый номер заказа - преобразуется в формат "000001"
                }
                $currency_id = "398"; 			// Шифр валюты  - 840-USD, 398-Tenge
                $amount = $sum;				// Сумма платежа
                $content = process_request($order_id,$currency_id,$amount,$path1); // Возвращает подписанный и base64 кодированный XML документ для отправки в банк

                $xml = "<document>";
                $i = 0;
                foreach($user_products as $key => $product_r){
                    $i++;
                    $xml .= '<item number="' . $i .  '" name="' . $product_r['name'] . '" quantity="' . $product_r['unit'] . '" amount="' . $product_r['unit']*$product_r['price'] .'"/>';
                }
                $xml .= "</document>";
                $appendix = base64_encode($xml);

                $row['currency_id'] = $currency_id;
                $row['amount'] = $amount;
                $row['content'] = $content;
                $row['appendix'] = $appendix;
                $row['order_id'] = $order_id;
            }
            else{
                $send_mail = $mobile->sendAdminNoticeEmail($allparam,$user_id,$user_products);
            }
        }

        $this->getResponse()->setHeader('Content-type', 'application/json');
        $row['response'] = $result;
        echo json_encode($row);
    }

    public function deliveryInfoHtmlAction(){
        $city_id = $this->_getParam('city_id', '');

        $this->view->city_id = $city_id;
        echo $this->renderScript('delivery-info-json.phtml');
    }

    public function cityPhoneHtmlAction(){
        $city_id = $this->_getParam('city_id', '');

        $this->view->city_id = $city_id;
        echo $this->renderScript('city-phone-json.phtml');
    }

    public function shopListHtmlAction(){
        $city_id = $this->_getParam('city_id', '');
        $this->view->city_id = $city_id;
        echo $this->renderScript('shop-list-json.phtml');
    }

    public function popularProductListHtmlAction(){
        $mobile = new Application_Model_DbTable_Mobile();

        $city_id = $this->_getParam("city_id","");
        $product_list = $mobile->getPopularProductsByCityNew($city_id);

        $this->view->product_list = $product_list;
        echo $this->renderScript('product-list-json.phtml');
    }

    public function newProductListHtmlAction(){
        $mobile = new Application_Model_DbTable_Mobile();

        $city_id = $this->_getParam("city_id","");
        $product_list = $mobile->getNewProductsByCityNew($city_id);

        $this->view->product_list = $product_list;
        echo $this->renderScript('product-list-json.phtml');
    }

    public function discountProductListHtmlAction(){
        $mobile = new Application_Model_DbTable_Mobile();

        $city_id = $this->_getParam("city_id","");
        $product_list = $mobile->getWholeSaleProductsAll($city_id);

        $this->view->product_list = $product_list;
        echo $this->renderScript('product-list-json.phtml');
    }

    public function wholesaleProductListHtmlAction(){
        $mobile = new Application_Model_DbTable_Mobile();

        $city_id = $this->_getParam("city_id","");
        $product_list = $mobile->getOptProductsNew($city_id);

        $this->view->product_list = $product_list;
        echo $this->renderScript('product-list-json.phtml');
    }

    public function userRequestListNewAction(){
        $mobile = new Application_Model_DbTable_Mobile();

        $token = $this->_getParam('token', '');
        $user = $mobile->tokenIsValid($token);
        if (!$user) {
            header('WWW-Authenticate: Basic realm="My Realm"');
            header('HTTP/1.0 401 Unauthorized');
            return;
        }

        $user_id = $user['user_id'];
        $result = "";
        if($user_id > 0){
            $request_list = $mobile->userRequestList($user_id);
            if(count($request_list) > 0 && $request_list != false){
                $i = 0;
                $request_product_arr = array();
                foreach($request_list as $key => $request){
                    $i++;
                    $request_product_list = $mobile->userRequestProductList($request['request_id']);
                    $request_product_arr[$i] = $request;
                    if(count($request_product_list) > 0){
                        $request_product_arr[$i]['request_product_list'] = $request_product_list;
                    }
                }
                $result = $request_product_arr;
            }
            else{
                $result = false;
            }
        }
        else{
            $result = false;
        }

        $this->view->user_request_list = $result;
        echo $this->renderScript('user-request-list-json.phtml');
    }

    public function activeBannerListAction(){
        $mobile = new Application_Model_DbTable_Mobile();

        $city_id = $this->_getParam("city_id",0);
        $banner_list = $mobile->getActiveBannerList($city_id);

        if(count($banner_list) > 0){
            $result = $banner_list;
        }
        else{
            $result = false;
        }
        $this->getResponse()->setHeader('Content-type', 'application/json');
        $row['response'] = $result;
        echo json_encode($row);
    }

}