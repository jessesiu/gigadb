<?php

class DatasetController extends Controller
{
	/**
	 * @var string the default layout for the views. Defaults to '//layouts/column2', meaning
	 * using two-column layout. See 'protected/views/layouts/column2.php'.
	 */
	public $layout='//layouts/column2';

	/**
	 * @return array action filters
	 */
	public function filters()
	{
		return array(
			'accessControl', // perform access control for CRUD operations
		);
	}

	/**
	 * Specifies the access control rules.
	 * This method is used by the 'accessControl' filter.
	 * @return array access control rules
	 */
	public function accessRules()
	{
		return array(
			array('allow',  // allow all users to perform 'index' and 'view' actions
				'actions'=>array('view','checkDOIExist' , 'aSetSortCookies','ResetPageSize'),
				'users'=>array('*'),
			),
			array('allow',  // allow logged-in users to perform 'upload'
				'actions'=>array('upload','create1'),
				'users'=>array('@'),
			),
			array('allow', // allow admin user to perform 'admin' and 'delete' actions
			      'actions'=>array('admin','delete','update','create','updateMetadata','private', 'index'),
			      'roles'=>array('admin'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}
        
            public function actionResetPageSize() {



        if (isset($_POST['filePageSize'])) {

            $cookie = new CHttpCookie('filePageSize', $_POST['filePageSize']);
            //half year
            $cookie->expire = time() + 60 * 60 * 24 * 180;
            Yii::app()->request->cookies['filePageSize'] = $cookie;
        }
        if (isset($_POST['url'])) {
            $this->redirect($_POST['url']);
        }
    }

	/**
	 * Displays a particular model.
	 * @param integer $id the ID of the model to be displayed
	 */
	public function actionView($id)	{
		$form=new SearchForm;  // Use for Form
        $dataset = new Dataset; // Use for auto suggestion
        $model= Dataset::model()->find("identifier=?",array($id));
        if (!$model) {
          $this->redirect('/site/index');
	}
        if ($model->upload_status == "Pending") {
          if (isset($_GET['token']) && $model->token == $_GET['token']) {
          } else {
            $this->redirect('/site/index');
          }
        }

        #$files = $model->files;
        //$samples = $model->samples;

        // Prepare File Sort, Pagination and get the list of file results
        // check cookie for file sorted column -- TODO: This is duplicated with the search controller
        $defaultFileSortColumn = 'dataset.name';
        $defaultFileSortOrder = CSort::SORT_DESC;
        if (isset($_GET['filesort'])) {
            // use new sort and save to cookie
            // check if desc or not
            $order = substr($_GET['filesort'], strlen($_GET['filesort'])-5,5);
            $columnName = $defaultFileSortColumn;
            if ($order == '.desc') {
                $columnName = substr($_GET['filesort'], 0,strlen($_GET['filesort'])-5);
                $order = 1;
            } else {
                $columnName = $_GET['filesort'];
                $order = 0;
            }
            $defaultFileSortColumn = $columnName;
            $defaultFileSortOrder = $order;
            Yii::app()->request->cookies['file_sort_column'] = new CHttpCookie('file_sort_column', $columnName);
            Yii::app()->request->cookies['file_sort_order'] = new CHttpCookie('file_sort_order', $order);

        } else {
            // use old sort if exists
            if (isset(Yii::app()->request->cookies['file_sort_column'])){
                $cookie = Yii::app()->request->cookies['file_sort_column']->value;
                $defaultFileSortColumn = $cookie;
            }
            if (isset(Yii::app()->request->cookies['file_sort_order'])){
                $cookie = Yii::app()->request->cookies['file_sort_order']->value;
                $defaultFileSortOrder = $cookie;
            }
        }
        $fsort = new MySort;
        $fsort->attributes=array('*');
        $fsort->attributes[]="dataset.identifier";
        $fsort->defaultOrder = array($defaultFileSortColumn => $defaultFileSortOrder);

        $fpagination = new CPagination;
        $fpagination->pageVar = 'files_page';
        $files = new CActiveDataProvider('File' , array(
            'criteria'=>array(
                'condition' => "dataset_id = $model->id",
                'join' => 'JOIN dataset ON dataset.id = t.dataset_id',
            ),
            'sort' => $fsort,
            'pagination' => $fpagination
        ));

        //Sample
        $spagination = new CPagination;
        $spagination->pageVar = 'samples_page';
        $samples = new CActiveDataProvider('Sample' , array(
            'criteria'=>array(
                'join' => 'JOIN dataset_sample ON sample_id = t.id',
                'condition' => "dataset_id = $model->id",
            ),
            'pagination' => $spagination
        ));
        
        $email = 'no_submitter@bgi.com';
        $result = Dataset::model()->findAllBySql("select email from gigadb_user g,dataset d where g.id=d.submitter_id and d.identifier='" . $id . "';");
        if (count($result) > 0) {
            $email = $result[0]['email'];
        }
        
        $result = Dataset::model()->findAllBySql("select identifier from dataset where identifier > '" . $id . "' and upload_status='Published' order by identifier asc limit 1;");
        if (count($result) == 0) {
            $result = Dataset::model()->findAllBySql("select identifier from dataset where upload_status='Published' order by identifier asc limit 1;");
            $next_doi = $result[0]->identifier;
        } else {
            $next_doi = $result[0]->identifier;
        }

        $result = Dataset::model()->findAllBySql("select identifier from dataset where identifier < '" . $id . "' and upload_status='Published' order by identifier desc limit 1;");
        if (count($result) == 0) {
            $result = Dataset::model()->findAllBySql("select identifier from dataset where upload_status='Published' order by identifier desc limit 1;");
            $previous_doi = $result[0]->identifier;
        } else {
            $previous_doi = $result[0]->identifier;
        }
        
        
        // $files = File::model()->findAll("dataset_id=?",array($model->id));
//        $file_command = Yii::app()->db->createCommand("SELECT file.id , file.code,file.description,file.size as size ,file.date_stamp as date_stamp,file.location as location ,file.name,file_type.name as type, file_format.name as format, file_format.description as format_description FROM file LEFT JOIN file_type ON file_type.id=file.type_id LEFT JOIN file_format ON file_format.id=file.format_id WHERE file.dataset_id=?");
//        $files = $file_command->query(array($model->id));
//
//		$sample_command = Yii::app()->db->createCommand("SELECT sample.id, sample.code as id,code,sample.s_attrs,tax_id,common_name,genbank_name,scientific_name FROM sample LEFT JOIN species ON sample.species_id=species.id WHERE sample.id IN (SELECT sample_id FROM dataset_sample WHERE dataset_id=?)");
//        $samples = $sample_command->query(array($model->id));

		$this->render('view',array(
			'model'=>$model,
			'form'=>$form,
			'dataset'=>$dataset,
			'files'=>$files,
			'samples'=>$samples,
                        'email' => $email,
                        'previous_doi' => $previous_doi,
                        'next_doi' => $next_doi,
		));
	}


	public function actionPrivate() {
          $id = $_GET['identifier'];
          $model= Dataset::model()->find("identifier=?",array($id));
          if (!$model) {
            $this->redirect('/site/index');
          } else if ($model->upload_status != 'Pending') {
            $this->redirect('/dataset/'.$model->identifier);
          }

          $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
          $model->token = substr(str_shuffle($chars),0,16);
          $model->save();

          $this->redirect('/dataset/view/id/'.$model->identifier.'/token/'.$model->token);
        }

	/**
	 * Updates a particular model.
	 * If update is successful, the browser will be redirected to the 'view' page.
	 * @param integer $id the ID of the model to be updated
	 */
	public function actionUpdate($id)
	{
		$model=$this->loadModel($id);

		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if(isset($_POST['Dataset']))
		{
			$model->attributes=$_POST['Dataset'];
			$model->image->attributes=$_POST['Images'];
			if ($model->publication_date == "")
				$model->publication_date = null;
			if ($model->modification_date == "")
				$model->modification_date = null;
			if($model->save() && $model->image->save()) {
				if (isset($_POST['datasettypes'])) {
						$datasettypes = $_POST['datasettypes'];
					}

					$datasetTypeMaps = DatasetType::model()->findAllByAttributes(array('dataset_id'=>$id));

					for ($i = 0 ; $i < count($datasetTypeMaps) ; ++$i) {
						$datasetTypeMap = $datasetTypeMaps[$i];
						if ((isset($datasettypes) && !in_array($datasetTypeMap->type_id, array_keys($datasettypes),true)) || !isset($datasettypes)) {
							$datasetTypeMap->delete();
						}
					}

					if (isset($datasettypes)) {
						foreach ($datasettypes as $datasetTypeId => $datasettype) {
							$currDatasetTypeMap = DatasetType::model()->findByAttributes(array('dataset_id'=>$model->id,'type_id'=>$datasetTypeId));
							if (!$currDatasetTypeMap) {
								$newDatasetTypeRelationship = new DatasetType;
								$newDatasetTypeRelationship->dataset_id = $model->id;
								$newDatasetTypeRelationship->type_id = $datasetTypeId;
								$newDatasetTypeRelationship->save();
							}
						}
					}
					if ($model->upload_status=='Pending') {
					  //$this->redirect('/dataset/private/identifier/'.$model->identifier);
					    $this->redirect(array('/dataset/view/' , 'id' => $model->identifier , 'token' => $model->token));
					} else {
					  $this->redirect(array('/dataset/'.$model->identifier));
					}
			}
		}

		$this->render('update',array(
			'model'=>$model,
		));
	}

	/**
	 * Deletes a particular model.
	 * If deletion is successful, the browser will be redirected to the 'admin' page.
	 * @param integer $id the ID of the model to be deleted
	 */
	public function actionDelete($id)
	{
		if(Yii::app()->request->isPostRequest)
		{
			// we only allow deletion via POST request
			$this->loadModel($id)->delete();

			// if AJAX request (triggered by deletion via admin grid view), we should not redirect the browser
			if(!isset($_GET['ajax']))
				$this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('admin'));
		}
		else
			throw new CHttpException(400,'Invalid request. Please do not repeat this request again.');
	}
	/**
	 * Lists all models.
	 */
	public function actionIndex()
	{
		$dataProvider=new CActiveDataProvider('Dataset');
		$this->render('index',array(
			'dataProvider'=>$dataProvider,
		));
	}

	/**
	 * Manages all models.
	 */
	public function actionAdmin()
	{
		$model=new Dataset('search');
		$model->unsetAttributes();  // clear any default values
		if(isset($_GET['Dataset']))
			$model->attributes=$_GET['Dataset'];

		$this->render('admin',array(
			'model'=>$model,
		));
	}

	public function actionUpload() {
		if (isset($_POST['userId'])) {
			$user = User::model()->findByPk(Yii::app()->user->id);


			$excelFile = CUploadedFile::getInstanceByName('xls');
            // print_r($excelFile);die;
			$excelTempFileName = $excelFile->tempName;

			// email fields: to, from, subject, and so on
		    $from = Yii::app()->params['app_email_name']." <".Yii::app()->params['app_email'].">";
		    $to = Yii::app()->params['adminEmail'];
		    $subject = "New dataset uploaded by user ".$user->id." - ".$user->first_name.' '.$user->last_name;
		    $receiveNewsletter = $user->newsletter ? 'Yes' : 'No';
		    $message = <<<EO_MAIL

New dataset is uploaded by:
<br/>
<br/>
Id:  <b>{$user->id}</b>
<br/>
Email: <b>{$user->email}</b>
<br/>
First Name:  <b>{$user->first_name}</b>
<br/>
Last Name:  <b>{$user->last_name}</b>
<br/>
Affiliation:  <b>{$user->affiliation}</b>
<br/>
Receiving Newsletter:  <b>{$receiveNewsletter}</b>
<br/><br/>
EO_MAIL;

		    $headers = "From: $from";

		    /* prepare attachments */

			// boundary
		    $semi_rand = md5(time());
		    $mime_boundary = "==Multipart_Boundary_x{$semi_rand}x";

		    // headers for attachment
		    $headers .= "\nMIME-Version: 1.0\n" . "Content-Type: multipart/mixed;\n" . " boundary=\"{$mime_boundary}\"";
		     // multipart boundary
		    $message = "--{$mime_boundary}\n" . "Content-Type: text/html; charset=\"utf-8\"\n" ."Content-Transfer-Encoding: 7bit\n\n" . $message . "\n\n";
		    $message .= "--{$mime_boundary}\n";
            $fp =    @fopen($excelTempFileName,"rb");
	        $data =    @fread($fp,filesize($excelTempFileName));
            @fclose($fp);
            $data = chunk_split(base64_encode($data));
            // $newFileName = 'dataset_upload_'.$user->id.'.xls';
            $newFileName = $excelFile->name;
            $message .= "Content-Type: application/octet-stream; name=\"".$newFileName."\"\n" .
            "Content-Description: ".$newFileName."\n" ."Content-Disposition: attachment;\n" . " filename=\"".$newFileName."\"; size=".filesize($excelTempFileName).";\n" ."Content-Transfer-Encoding: base64\n\n" . $data . "\n\n";

            $message .= "--{$mime_boundary}--";
		    $returnpath = "-f" . Yii::app()->params['adminEmail'];

	        $ok = @mail($to, $subject, $message, $headers, $returnpath);

	        if ($ok)  {
	        	$this->redirect('/dataset/upload/status/successful');
	        	return;
	        } else {
	        	$this->redirect('/dataset/upload/status/failed');
	        	return;
	        }
		}
		$this->render('upload');
	}



	public function actionCreate(){
		$dataset = new Dataset;
		$dataset->image = new Images;

		if(isset($_POST['Dataset']) && isset($_POST['Images'])){
			$dataset->attributes=$_POST['Dataset'];
			$dataset->image->attributes = $_POST['Images'];

			if ($dataset->publication_date == "")
				$dataset->publication_date = null;
			if ($dataset->modification_date == "")
				$dataset->modification_date = null;

			if ($dataset->image->validate('update') && $dataset->validate('update') && $dataset->image->save()) {
				// save image
				$dataset->image_id = $dataset->image->id;

				// save dataset
				if ($dataset->save()) {
					// link datatypes
					if (isset($_POST['datasettypes'])) {
						$datasettypes = $_POST['datasettypes'];
    					foreach ($datasettypes as $id => $datasettype) {
    						$newDatasetTypeRelationship = new DatasetType;
    						$newDatasetTypeRelationship->dataset_id = $dataset->id;
    						$newDatasetTypeRelationship->type_id = $id;
    						$newDatasetTypeRelationship->save();
    					}
					}


					Yii::app()->user->setFlash('saveSuccess', 'saveSuccess');
					if ($dataset->upload_status=='Pending') {
					  $this->redirect('/dataset/private/identifier/'.$dataset->identifier);
					} else {
					  $this->redirect(array('/dataset/'.$dataset->identifier));
					}
				}
			}
		}
		$this->render('create', array('model'=>$dataset)) ;
	}
	
	
	
	public function actionCreate1() {


        $dataset = new Dataset;
        $dataset->image = new Images;
//read dataset from session

        if (isset($_POST['Dataset'])) {

            $_SESSION['dataset'] = $_POST['Dataset'];

            $dataset->attributes = $_POST['Dataset'];

            $dataset->upload_status = "Incomplete";
            $dataset->dataset_size = $_SESSION['dataset']['dataset_size'];
// $dataset->ftp_site = $_SESSION['dataset']['ftp_site'];
            $dataset->submitter_id = Yii::app()->user->_id;
            
            if (isset($_POST['Images'])) {
                $_SESSION['images'] = $_POST['Images'];
                $dataset->image->attributes = $_POST['Images'];
            } else if (isset($_POST['no-image'])) {
                $_SESSION['images'] = 'no-image';
            }
            $checkedTypes= array();
            if (isset($_POST['datasettypes'])) {
                $_SESSION['datasettypes'] = $_POST['datasettypes'];
                
                 $types = array();
                foreach ($_SESSION['datasettypes'] as $id => $datasettype) {
                    $type = new Type;
                    $type->id = $id;
                    array_push($types, $type);
                }
                $dataset->datasetTypes = $types;
            }
            //check
            if ($this->storeDataset($dataset)) {
                
                $vars = array('dataset', 'images', 'datasettypes', 'dataset_id');
                Dataset::storeSession($vars);


                $this->redirect('/adminDatasetAuthor/create1');
            }
        } else if (isset($_SESSION['dataset']) && isset($_SESSION['images'])) {
// $dataset = $_SESSION['dataset'];

            $dataset->attributes = $_SESSION['dataset'];
//attributes that's not safe 
            $dataset->dataset_size = $_SESSION['dataset']['dataset_size'];


            if (isset($_SESSION['datasettypes'])) {

                $datasettypes = $_SESSION['datasettypes'];
                $types = array();
                foreach ($datasettypes as $id => $datasettype) {
                    $type = new Type;
                    $type->id = $id;
                    array_push($types, $type);
                }


                $dataset->datasetTypes = $types;
            }
            if ($_SESSION['images'] != 'no-image')
                $dataset->image->attributes = $_SESSION['images'];
        } else {

            $dataset->submitter_id = Yii::app()->user->_id;
        }

//to determine if there are files assosiated with this dataset
        if (isset($_SESSION['dataset_id']) && !isset($_SESSION['filecount'])) {

            $filecount = 0;
            $connection = Yii::app()->db;

            $sql = " select count(1) from file where dataset_id = :name";
            $command = Yii::app()->db->createCommand($sql);
            $command->bindValue(":name", $_SESSION['dataset_id'], PDO::PARAM_INT);
            $res = $command->queryAll();

            if (!empty($res))
                $filecount = $res[0]['count'];

            $_SESSION['filecount'] = $filecount;
        }
//        var_dump($_SESSION['filecount']. "count");
        $this->render('create1', array('model' => $dataset));
    }
	

	private function createManuScript($dataset_id , $doi , $pmid){

		if(empty($doi) && empty($pmid)){
			return ;
		}

		$manuscript = new Manuscript;
		if(!empty($doi)){
			$manuscript->identifier = $doi;
		}else{
			$manuscript->identifier = " ";
		}

		if(!empty($pmid)){
			$manuscript->pmid = $pmid;
		}

		$manuscript->dataset_id = $dataset_id;

		$manuscript->save(false);
	}

	private function setProject($dataset_id,$project){

		$new_project_url = $project['new_project_url'];
		$new_project_name = $project['new_project_name'];
		$new_project_image = $project['new_project_image'];

		$rows = max (count($new_project_url) , count($new_project_name) , count($new_project_image));
		for($i = 1; $i < $rows;$i++){
			$project_url = isset($new_project_url[$i])?$new_project_url[$i]:" ";
			$project_name = isset($new_project_name[$i])?$new_project_name[$i]:" ";
			$project_image = isset($new_project_image[$i])?$new_project_image[$i]:" ";

			$project = new Project;
			$project->url = $project_url;
			$project->name = $project_name;
			$project->image_location = $project_image;

			if($project->save(false)){
				$dataset_project = new DatasetProject;
				$dataset_project->dataset_id = $dataset_id;
				$dataset_project->project_id = $project->id;
				$dataset_project->save(false);
			}

		}

	}

	private function setAuthorList($dataset_id,$authors){


		$temp = explode(";", $authors);

		foreach ($temp as $key => $value) {
			$value=trim($value);

			if(strlen($value)>0){
				$author = Author::model()->find("name=?",array($value));
				if(!$author){ //  Author not found
					$author = new Author;
					$author->name =$value;
					$author->orcid ="orcid";
					$author->rank=0;
					$author->save(true);
				}



				$dataset_author = new DatasetAuthor;
				$dataset_author->dataset_id = $dataset_id;
				$dataset_author->author_id = $author->id;

				$dataset_author->save(true);
			}
		}
	}

	private function setDatesetType($dataset_id,$dataset_types){
		$temp = explode(",", $dataset_types);

		foreach ($temp as $key => $value) {
			$value=trim($value);
			if(strlen($value)>0){
				$type = Type::model()->find("name=?",array($value));
				if(!$type){ // Type not found
					$type = new Type;
					$type->name = $value;
					$type->description="description";
					$type->save(true);

				}

				$dataset_type = new DatasetType;
				$dataset_type->dataset_id = $dataset_id;
				$dataset_type->type_id = $type->id;
				$dataset_type->save(false);

			}
		}

	}

	private function addExternalLink($dataset_id,$additional_information,$genome_browser){
		if(!empty($additional_information)){
			$external_link_type = ExternalLinkType::model()->find("name=?",array("additional_information"));

			$external_link = new ExternalLink;
			$external_link->dataset_id = $dataset_id;
			$external_link->external_link_type_id = $external_link_type->id;
			$external_link->url = $additional_information;

			$external_link->save(false);


		}

		if(!empty($genome_browser)){
			$external_link_type = ExternalLinkType::model()->find("name=?",array("genome_browser"));
			$external_link = new ExternalLink;
			$external_link->dataset_id = $dataset_id;
			$external_link->external_link_type_id = $external_link_type->id;
			$external_link->url = $genome_browser;

			$external_link->save(false);

		}

	}

	private function getFileType($type){

		$file_type = FileType::model()->find("name=?",array($type));
		if($file_type == null){
			$file_type = new FileType;

			$file_type->name= $type;
			$file_type->description = " ";

			$file_type->save(false);
		}

		return $file_type->id;
	}

	private function getFileFormat($format){

		$file_format = FileFormat::model()->find("name=?",array($format));
		if($file_format == null){
			$file_format = new FileFormat;

			$file_format->name= $format;
			$file_format->description = " ";

			$file_format->save(false);
		}

		return $file_format->id;
	}

	public function actioncheckDOIExist(){

		$result = array();
		$result['status'] = false;
		if(isset($_POST['doi'])){
			$doi = $_POST['doi'];
			if(stristr($doi, "/")){
				$temp = explode("/", $doi);
				$doi = $temp[1];
			}

			$doi = trim($doi);

			$dataset = Dataset::model()->find("identifier=?",array($doi));
			if($dataset){
				$result['status'] = true;
			}
		}
		echo json_encode($result);
	}

	private function userExist($email){
		$model = User::model()->find("email=?",array($email));
		if($model){
		    return $model->id;
		}else {
		    return false;
		}
	}

	private function sendHtmlEmailWithAttachment() {

	}

	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer the ID of the model to be loaded
	 */
	public function loadModel($id)
	{
		$model=Dataset::model()->findByPk($id);
		if($model===null)
			throw new CHttpException(404,'The requested page does not exist.');
		return $model;
	}

	/**
	 * Performs the AJAX validation.
	 * @param CModel the model to be validated
	 */
	protected function performAjaxValidation($model)
	{
		if(isset($_POST['ajax']) && $_POST['ajax']==='dataset-form')
		{
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}
	}
}
