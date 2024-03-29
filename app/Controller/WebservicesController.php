<?php

/**
 * Webservices Controller
 *
 * PHP version 5
 *
 * @category Controller
 * @package  Webservices
 * @version  1.0
 * @author   Apurav Gaur
 */
class WebservicesController extends AppController {

    /**
     * Controller name
     *
     * @var string
     * @access public
     */
    public $name = 'Webservices';

    /**
     * Components
     *
     * @var array
     * @access public
     */
    public $components = array(
        'Email', 'RequestHandler'
    );

    /**
     * Models used by the Controller
     *
     * @var array
     * @access public
     */
    public $uses = array('User');
    public $status = false;
    public $output = null;
    public $message = '';
    public $timestamp = '';
    public $requestData = null;

    /**
     * beforeFilter
     *
     * @return void
     * @access public 
     */
    public function beforeFilter() {
        parent::beforeFilter();
        $this->Auth->allow();
    }

    public function index() {


        $this->requestData = $this->request->query;
        //set data from post
        if ($this->request->is('post')) {

            $this->requestData = $this->request->data;

            CakeLog::write('activity_' . date('dmY'), json_encode($this->request->data)); // add json
        }
        if (!empty($this->requestData['action'])) {
            $fun = "_" . $this->requestData['action'];
            $this->$fun();
        }
        $this->_output();
    }

    private function _output() {
        // How long my cache should last
        $cacheDuration = 300; // in seconds
        // Client is told to cache these results for set duration
        header('Cache-Control: public,max-age=' . $cacheDuration . ',must-revalidate');
        header('Expires: ' . gmdate('D, d M Y H:i:s', ($_SERVER['REQUEST_TIME'] + $cacheDuration)) . ' GMT');
        header('Last-modified: ' . gmdate('D, d M Y H:i:s', $_SERVER['REQUEST_TIME']) . ' GMT');

        // Pragma header removed should the server happen to set it automatically
        // Pragma headers can make browser misbehave and still ask data from server
        header_remove('Pragma');

        $this->output['message'] = $this->message;
        $this->output['status'] = $this->status;
        $output = json_encode($this->output);

        header('Content-Type: application/json');
        echo $output;
        exit();
    }
    public function _dataSync(){
    	$this->checkRequiredParameters(array('timestamp'));
    	$this->loadModel('Country');
    	$this->loadModel('Category');
    	$this->loadModel('Sitelink');
    	if($this->requestData['timestamp']==0){
    	
    		$categories = $this->Category->find('all',array(
    				'conditions'=>array(
    						'Category.status'=>1
    				)
    		)
    		);
    		/*
    		 * sitelinks listing
    		*/
    		$sitelinks = $this->Sitelink->find('all',array(
    				'conditions'=>array(
    						'Sitelink.status'=>1
    				)
    		)
    		);
    		if (count($categories) > 0) {
    			
    			foreach ($categories as $key1 => $catvalue) {
    				$categories[$key1] = $catvalue['Category'];
    			}
    			foreach ($sitelinks as $key2 => $sitevalue) {
    				$sitelinks[$key2] = $sitevalue['Sitelink'];
    			}
                         $this->output['logo_url'] = Router::url('/files/sitelink/image',true);
    			$this->output['categories'] = $categories;
    			$this->output['subcategory'] = $sitelinks;
    			$this->output['timestamp'] =time();
    			$this->status = true;
    			$this->message = "Data sync Listing";
    		} else {
    			$this->status = false;
    			$this->message = "Data not available";
    		}
    	}else{
    		$modifiedDate = date('Y-m-d h:i:s',$this->requestData['timestamp']);
    		
    		
    		/*
    		 * categories listing
    		*/
    		$categories = $this->Category->find('all',array(
    				'conditions'=>array(
    						'Category.status'=>1,
    						'Category.modified >'=>$modifiedDate,
    				)
    		)
    		);
    		/*
    		 * sitelinks listing
    		*/
    		$sitelinks = $this->Sitelink->find('all',array(
    				'conditions'=>array(
    						'Sitelink.status'=>1,
    						'Sitelink.modified >'=>$modifiedDate,
    				)
    		)
    		);
    		if (count($categories) > 0) {
    			
    			foreach ($categories as $key1 => $catvalue) {
    				$categories[$key1] = $catvalue['Category'];
    			}
    			foreach ($sitelinks as $key2 => $sitevalue) {
    				$sitelinks[$key2] = $sitevalue['Sitelink'];
    			}
    			$this->output['categories'] = $categories;
    			$this->output['sitelinks'] = $sitelinks;
    			$this->output['timestamp'] =time();
    			$this->status = true;
    			$this->message = "Country Listing";
    		} else {
    			$this->status = false;
    			$this->message = "Country not available";
    		}
    	}
    }
    
    public function _images(){
        $this->loadModel('Brand');
        
       
        $images = $this->Brand->find('all',array('conditions'=>array('status'=>1)));
        if(count($images)>0){
            foreach ($images as $key => $image) {
    		$images[$key] = $image['Brand'];
    	}
                        $this->output['image_base_url'] = Router::url('/files/brand/image',true);
                        $this->output['images'] = $images;
    			$this->status = true;
    			$this->message = "Images Listing";
        }else{
            $this->status = false;
    	$this->message = "Images not available";
        }
        
    }
    public function _collection_save(){
    	$this->checkRequiredParameters(array('collection_id','name','image','summary','category_id','subcategory_id'));
        $this->loadModel('Collection');
       $data['Collection']=$this->requestData;
       //pr($data);die;
       $data['Collection']['sitelink_id'] =$data['Collection']['subcategory_id'];
       if($data['Collection']['collection_id']==0){
           //create data 
           $message='Collection Data are Saved';
          }else{
              $data['Collection']['id']=$data['Collection']['collection_id'];
           //update data 
               $message='Collection Data are updated';
       } 
           if($this->Collection->save($data)){
               $this->status = true;
               $this->message = $message;
           }else{
               pr($this->Collection->validationErrors);die;
                $this->status = false;
               $this->message = "Date are not save. please provide correct data";
           }
       
    }
    public function _collection_list(){
         $this->loadModel('Collection');
          $this->loadModel('Sitelink');
        // $this->Collection->bindModel(array('hasOne'=>array('Category')));
         $data = $this->Collection->find('all',array('conditions'=>array('Collection.status'=>1)));
        // pr($data);die;
         if(count($data)>0){
             foreach ($data as $key => $datas) {
                $ides= explode(',',$datas['Collection']['sitelink_id']);
               // pr($ides);die;
                 $sitelink_data = $this->Sitelink->find('all',array('conditions'=>array('Sitelink.status'=>1,'Sitelink.id'=>$ides)));
                // pr($sitelink_data);die;
                 $sitedatasss = Set::extract($sitelink_data, '{n}.Sitelink');
                 //pr($sitedatasss);die;
    		$data[$key] = $datas['Collection'];
                $data[$key]['category_name']=$datas['Category'];
                $data[$key]['subcategory']=$sitedatasss;
            }
            $this->output['Collection_data'] = $data;
            $this->status = true;
            $this->message = 'Collection Data list';
         }
    }
    /**
     * @author 		Virender saini
     * email : virendersaini50@gmail.com
     * @uses		Function used to check passed parameter is in request or not
     * @access		private
     */
    public function checkRequiredParameters($reqParameters) {
    	$requestObj = array_keys($this->requestData);
    	$resp = true;
    	$missingParam = array();
    	foreach ($reqParameters as $v) {
    		if (!in_array($v, $requestObj)) {
    			$resp = false;
    			$missingParam[] = $v;
    		}
    	}
    	if (!$resp) {
    		$this->status = false;
    		$this->message = 'Insufficient Parameters.Missing Parameters are ' . implode(', ', $missingParam);
    		$this->output['message'] = $this->message;
    		$this->output['status'] = $this->status;
    		$output = json_encode($this->output);
    		header('Content-Type: application/json');
    		echo $output;
    		exit();
    	}
    }
    
}
