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
				'actions'=>array('upload', 'create1','submit','updateSubmit', 'updateFile','cancel'),
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
	   public function actionUpdate($id) {
        $model = $this->loadModel($id);

// Uncomment the following line if AJAX validation is needed
// $this->performAjaxValidation($model);

        if (isset($_POST['Dataset'])) {
            $model->attributes = $_POST['Dataset'];
            $date = new DateTime();
            $date = $date->format('Y-m-d');
            if ($model->upload_status == 'Published') {
                $files = $model->files;
                $model->ftp_site="ftp://climb.genomics.cn/pub/10.5524/100001_101000/" . $model->identifier;
             
                
                if (count($files) > 0) {
                    foreach ($files as $file) {
                        $origin_location = $file->location;
                        $new_location = "";
                        $location_array = explode("/", $origin_location);
                        $count = count($location_array);
                        if ($count == 1) {
                            $new_location = "ftp://climb.genomics.cn/pub/10.5524/100001_101000/" .
                                    $model->identifier . "/" . $location_array[0];
                        } else if ($count >= 2) {
                            $new_location = "ftp://climb.genomics.cn/pub/10.5524/100001_101000/" .
                                    $model->identifier . "/" . $location_array[$count - 2] . "/" . $location_array[$count - 1];
                        }
                        $file->location = $new_location;
                        $file->date_stamp = $date;
                        if (!$file->save())
                            return false;
                    }
                }
            }
            $model->publication_date=$date;
            $model->image->attributes = $_POST['Images'];
            if ($model->publication_date == "")
                $model->publication_date = null;
            if ($model->modification_date == "")
                $model->modification_date = null;
            if ($model->save() && $model->image->save()) {
                if (isset($_POST['datasettypes'])) {
                    $datasettypes = $_POST['datasettypes'];
                }

                $datasetTypeMaps = DatasetType::model()->findAllByAttributes(array('dataset_id' => $id));

                for ($i = 0; $i < count($datasetTypeMaps); ++$i) {
                    $datasetTypeMap = $datasetTypeMaps[$i];
                    if ((isset($datasettypes) && !in_array($datasetTypeMap->type_id, array_keys($datasettypes), true)) || !isset($datasettypes)) {
                        $datasetTypeMap->delete();
                    }
                }

                if (isset($datasettypes)) {
                    foreach ($datasettypes as $datasetTypeId => $datasettype) {
                        $currDatasetTypeMap = DatasetType::model()->findByAttributes(array('dataset_id' => $model->id, 'type_id' => $datasetTypeId));
                        if (!$currDatasetTypeMap) {
                            $newDatasetTypeRelationship = new DatasetType;
                            $newDatasetTypeRelationship->dataset_id = $model->id;
                            $newDatasetTypeRelationship->type_id = $datasetTypeId;
                            $newDatasetTypeRelationship->save();
                        }
                    }
                }
                if ($model->upload_status == 'Pending') {
                    $this->redirect('/dataset/private/identifier/' . $model->identifier);
                } else {
                    $this->redirect(array('/dataset/' . $model->identifier));
                }
            }
        }

        $this->render('update', array(
            'model' => $model,
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
        
            public function actionCancel() {
//clear session
        unset($_SESSION['dataset']);
        unset($_SESSION['images']);
        unset($_SESSION['datasettypes']);
        $this->redirect("/user/view_profile");
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


 public function actionSubmit() { 
           if (isset($_POST['File'])) {
        
            $count = count($_POST['File']);
            var_dump('count'.$count);
            for ($i = 0; $i < $count; $i++) {
                $id=$_POST['File'][$i]['id'];
                $model = File::model()->findByPk($id);
                if ($model === null)
                         continue;
                $model->attributes = $_POST['File'][$i];
                if ($model->date_stamp == "")
                    $model->date_stamp = NULL;
               // var_dump($model->description);
                if (!$model->save()) {
                    var_dump($_POST['File'][$i]);
                }
            }
        }
        
        if (isset($_SESSION['dataset_id'])) {
//change dataset status to Request
            $dataset_id = $_SESSION['dataset_id'];
            $dataset = Dataset::model()->findByAttributes(array('id' => $dataset_id));
            $samples = DatasetSample::model()->findAll("dataset_id=:dataset_id", array(':dataset_id' => $dataset_id));
            $sampleLink = "";
            if ($samples != null) {
                $sampleLink .= "Samples:<br/>";
                foreach ($samples as $sample) {
                    $sampleLink = $sampleLink . Yii::app()->params['home_url'] . "/adminSample/view/id/" . $sample->sample_id . "<br/>";
                }
            }
            $fileLink = "";
            $isOld = 1;
            //
            if($dataset->upload_status == 'Incomplete')
                $isOld = 0;
            
            //change the upload status
            if (isset($_POST['file'])) {
                $fileLink .= 'Files:<br/>';
                $fileLink = $link = Yii::app()->params['home_url'] . "/dataset/updateFile/?id=" . $dataset_id;
                  $dataset->upload_status = 'Pending';
            } else {             
                $dataset->upload_status = 'Request';
            }
 
            if (!$dataset->save()){
                Yii::app()->user->setFlash('keyword', "Submit failure" . $dataset_id);
                $this->redirect("/user/view_profile");
                return;
            }
        }
        
        else {
            Yii::app()->user->setFlash('error', "Submit failure,no dataset_id in session");
            $this->redirect("/user/view_profile");
            return;
        }

        Dataset::clearDatasetSession();

        $link = Yii::app()->params['home_url'] . "/dataset/update/id/" . $dataset_id;
        $linkFolder ="Link File Folder:<br/>";
        $linkFolder .= (Yii::app()->params['home_url'] . "/adminFile/linkFolder/?id=".$dataset_id);
//        var_dump($link);
        $user = User::model()->findByPk(Yii::app()->user->id);

        $from = Yii::app()->params['app_email_name'] . " <" . Yii::app()->params['app_email'] . ">";
        $ok1 = false;
        $ok2 = false;
        if (!$isOld) {
            $to = Yii::app()->params['adminEmail'];

            $subject = "New dataset " . $dataset_id . " submitted online by user " . $user->id . " - " . $user->first_name . ' ' . $user->last_name;
            $receiveNewsletter = $user->newsletter ? 'Yes' : 'No';
            $date = getdate();

            $message = <<<EO_MAIL

New dataset is submitted by:
<br/>
<br/>
User:  <b>{$user->id}</b>
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
<br/>
Submission ID: <b>$dataset_id</b><br/>
$link      
<br/>
$sampleLink
    <br/>
$linkFolder
        <br/>
        
EO_MAIL;
            $headers = "Fcrrom: $from";

            /* prepare attachments */

// boundary
            $semi_rand = md5(time());
            $mime_boundary = "==Multipart_Boundary_x{$semi_rand}x";

// headers for attachment
            $headers .= "\nMIME-Version: 1.0\n" . "Content-Type: multipart/mixed;\n" . " boundary=\"{$mime_boundary}\"";
// multipart boundary
            $message = "--{$mime_boundary}\n" . "Content-Type: text/html; charset=\"utf-8\"\n" . "Content-Transfer-Encoding: 7bit\n\n" . $message . "\n\n";
            $message .= "--{$mime_boundary}\n";

            $message .= "--{$mime_boundary}--";
            $returnpath = "-f" . Yii::app()->params['adminEmail'];

            $ok1 = @mail($to, $subject, $message, $headers, $returnpath);

            //send email to user to 

            $to = $user->email;

            $subject = "GigaDB submission \"" . $dataset->title . '"'.' ['.$dataset_id.']';
            $receiveNewsletter = $user->newsletter ? 'Yes' : 'No';
            $timestamp = $date['mday'] . "-" . $date['mon'] . "-" . $date['year'];
            $message = <<<EO_MAIL
Dear $user->first_name $user->last_name,<br/>

Thank you for submitting your dataset information to GigaDB.
Our curation team will contact you shortly regarding your
submission "$dataset->title".<br/>
<br/>
In the meantime, please contact us at <a href="mailto:database@gigasciencejournal.com">database@gigasciencejournal.com</a> with any questions.<br/>
<br/>
Best regards,<br/>
<br/>
The GigaDB team<br/>
<br/>
Submission date: $timestamp
<br/>               
EO_MAIL;

            $headers = "From: $from";

            /* prepare attachments */

// boundary
            $semi_rand = md5(time());
            $mime_boundary = "==Multipart_Boundary_x{$semi_rand}x";

// headers for attachment
            $headers .= "\nMIME-Version: 1.0\n" . "Content-Type: multipart/mixed;\n" . " boundary=\"{$mime_boundary}\"";
// multipart boundary
            $message = "--{$mime_boundary}\n" . "Content-Type: text/html; charset=\"utf-8\"\n" . "Content-Transfer-Encoding: 7bit\n\n" . $message . "\n\n";
            $message .= "--{$mime_boundary}\n";

            $message .= "--{$mime_boundary}--";
            $returnpath = "-f" . Yii::app()->params['adminEmail'];

            $ok2 = @mail($to, $subject, $message, $headers, $returnpath);
        } else {
            $to = Yii::app()->params['adminEmail'];

            $subject = "Dataset " . $dataset_id . " updated online by user " . $user->id . " - " . $user->first_name . ' ' . $user->last_name;
            $receiveNewsletter = $user->newsletter ? 'Yes' : 'No';
            $date = getdate();
            $adminFileLink = Yii::app()->params['home_url'] . "/adminFile/update1/?id=" .$dataset_id;
            $message = <<<EO_MAIL
Dataset is updated by:
<br/>
<br/>
User:  <b>{$user->id}</b>
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
<br/>
Submission ID: <b>$dataset_id</b><br/>
$link      
<br/>
$adminFileLink
    <br/>
EO_MAIL;

            $headers = "From: $from";

            /* prepare attachments */

// boundary
            $semi_rand = md5(time());
            $mime_boundary = "==Multipart_Boundary_x{$semi_rand}x";

// headers for attachment
            $headers .= "\nMIME-Version: 1.0\n" . "Content-Type: multipart/mixed;\n" . " boundary=\"{$mime_boundary}\"";
// multipart boundary
            $message = "--{$mime_boundary}\n" . "Content-Type: text/html; charset=\"utf-8\"\n" . "Content-Transfer-Encoding: 7bit\n\n" . $message . "\n\n";
            $message .= "--{$mime_boundary}\n";

            $message .= "--{$mime_boundary}--";
            $returnpath = "-f" . Yii::app()->params['adminEmail'];

            $ok1 = @mail($to, $subject, $message, $headers, $returnpath);

            //send email to user to 

            $to = $user->email;

          //  $subject = "GigaDB update \"" . $dataset->title . '"';
            $subject = "GigaDB submission \"" . $dataset->title . '"'.' ['.$dataset_id.']';
            $receiveNewsletter = $user->newsletter ? 'Yes' : 'No';
            $timestamp = $date['mday'] . "-" . $date['mon'] . "-" . $date['year'];
            $message = <<<EO_MAIL
Dear $user->first_name $user->last_name,<br/>

Thank you for updating your dataset information to GigaDB.
Our curation team will contact you shortly regarding your
updates "$dataset->title".<br/>
<br/>
In the meantime, please contact us at <a href="mailto:database@gigasciencejournal.com">database@gigasciencejournal.com</a> with any questions.<br/>
<br/>
Best regards,<br/>
<br/>
The GigaDB team<br/>
<br/>
Submission date: $timestamp
<br/>               
EO_MAIL;

            $headers = "From: $from";

            /* prepare attachments */

// boundary
            $semi_rand = md5(time());
            $mime_boundary = "==Multipart_Boundary_x{$semi_rand}x";

// headers for attachment
            $headers .= "\nMIME-Version: 1.0\n" . "Content-Type: multipart/mixed;\n" . " boundary=\"{$mime_boundary}\"";
// multipart boundary
            $message = "--{$mime_boundary}\n" . "Content-Type: text/html; charset=\"utf-8\"\n" . "Content-Transfer-Encoding: 7bit\n\n" . $message . "\n\n";
            $message .= "--{$mime_boundary}\n";

            $message .= "--{$mime_boundary}--";
            $returnpath = "-f" . Yii::app()->params['adminEmail'];

            $ok2 = @mail($to, $subject, $message, $headers, $returnpath);
        }

        if ($ok1 && $ok2) {
            $uploadedDatasets = Dataset::model()->findAllByAttributes(array('submitter_id' => Yii::app()->user->id));
            
            foreach ($uploadedDatasets as $key => $dataset) {
                if ($dataset->id == $dataset_id)
                    $study = $key;
//                if($dataset->commonNames==""){
//                    $dataset->commonNames=$dataset->scientific_name;
//                }
            }
            $this->render("upload", array('study' => $study, 'uploadedDatasets' => $uploadedDatasets));
        }
        else {
            //add something
            $uploadedDatasets = Dataset::model()->findAllByAttributes(array('submitter_id' => Yii::app()->user->id));
            foreach ($uploadedDatasets as $key => $dataset) {
                if ($dataset->id == $dataset_id)
                    $study = $key;
            }
            $this->render("upload", array('study' => $study, 'uploadedDatasets' => $uploadedDatasets));
        }
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
    
        public function actionUpdateSubmit() {
        if (isset($_GET['id'])) {
            $id = $_GET['id'];
            $identifier = Dataset::model()->findByAttributes(array('id' => $id))->identifier;
            $dataset_session = DatasetSession::model()->findByAttributes(array('identifier' => $identifier));
            if ($dataset_session == NULL)
                return $this->redirect("/user/view_profile");
            $vars = array('dataset', 'images', 'identifier', 'dataset_id',
                'datasettypes', 'authors', 'projects',
                'links', 'externalLinks', 'relations', 'samples');
            foreach ($vars as $var) {
                $_SESSION[$var] = CJSON::decode($dataset_session->$var);
            }
            //indicate that this is an old dataset
            $_SESSION['isOld'] = 1;

            $this->redirect("/dataset/create1");
        }
        Yii::app()->user->setFlash('keyword', 'no dataset is specified');
        return $this->redirect("/user/view_profile");
    } 
    
    
    public function actionUpdateFile() {
        if (isset($_GET['id'])) {
            $id = $_GET['id'];
            $user = User::model()->findByPk(Yii::app()->user->id);
            $dataset = Dataset::model()->findByattributes(array('id' => $id));
            if ($user->id != $dataset->submitter_id) {
                return false;
            }
            $identifier = $dataset->identifier;
            $dataset_session = DatasetSession::model()->findByAttributes(array('identifier' => $identifier));
            if ($dataset_session == NULL)
                return $this->redirect("/user/view_profile");
            $vars = array('dataset', 'images', 'identifier', 'dataset_id',
                'datasettypes', 'authors', 'projects',
                'links', 'externalLinks', 'relations', 'samples');
            foreach ($vars as $var) {
                $_SESSION[$var] = CJSON::decode($dataset_session->$var);
            }                 
            $_SESSION['isOld'] = 1;
            $this->redirect("/adminFile/create1");
        }
        Yii::app()->user->setFlash('keyword', 'no dataset is specified');
        return $this->redirect("/user/view_profile");
    }
    
     public function storeDataset(&$dataset) {
        $dataset_id = 0;
        $identifier = 0;
        if (isset($_SESSION['dataset']) && isset($_SESSION['images'])) {

//determine if it's a new model
            if (isset($_SESSION['identifier'])) {
                $identifier = $_SESSION['identifier'];
                $dataset = Dataset::model()->findByAttributes(array('identifier' => $identifier));
            } else {
                $result = Dataset::model()->findAllBySql("select identifier from dataset order by identifier desc limit 1;");
                $max_doi = $result[0]->identifier;

                $identifier = $max_doi + 1;
            }
//convert 

            $dataset->attributes = $_SESSION['dataset'];


            $dataset->identifier = $identifier;

            $dataset->dataset_size = $_SESSION['dataset']['dataset_size'];
            if ($dataset->dataset_size == '')
                $dataset->dataset_size = 0;
            $dataset->ftp_site = "''";
            var_dump($dataset->ftp_site);

            $dataset->submitter_id = Yii::app()->user->_id;
            if ($dataset == null)
                return;
            try {
                if ($dataset->validate()
                ) {
                    if ($_SESSION['images'] != 'no-image') {
                        $dataset->image->attributes = $_SESSION['images'];
                        if (!( $dataset->image->validate() && $dataset->image->save() ))
                            return false;
                        $dataset->image_id = $dataset->image->id;
                    }
                    else {
                        //
                        $dataset->image_id = 72;
                        //
                        if (isset($_SESSION['datasettypes'])) {
                            $datasettypes = $_SESSION['datasettypes'];
                            if (count($datasettypes) == 1) {
                                foreach ($datasettypes as $id => $datasettype)
                                    $type_id = $id;
                                //workflow
                                if ($type_id == 5) {
                                    $dataset->image_id = 71;
                                } else if ($type_id == 2
                                ) {
                                    //genomics
                                    $dataset->image_id = 70;
                                } else if ($type_id == 4) {
                                    //transcriptomics
                                    $dataset->image_id = 69;
                                }
                            }
                        }
                    }
// save image
//                 $this->redirect("/site/?a=5");
//                 $this->redirect("/site/?a=6");

                    if ($dataset->save()) {
//the dataset has been saved
                        $_SESSION['identifier'] = $identifier;
                        $_SESSION['dataset_id'] = $dataset->id;

                        $dataset_id = $dataset->id;
//                    $this->redirect("/site/?a=0");
// link datatypes
                        if (isset($_SESSION['datasettypes'])) {
                            $datasettypes = $_SESSION['datasettypes'];
                            foreach ($datasettypes as $id => $datasettype) {
                                $newDatasetTypeRelationship = new DatasetType;
                                $newDatasetTypeRelationship->dataset_id = $dataset->id;
                                $newDatasetTypeRelationship->type_id = $id;
                                $newDatasetTypeRelationship->save();
                            }
                        }
                    }
                    else
                        return false;

                    return true;
                }
                else {
                    return false;
                }
            } catch (Exception $e) {
                $dataset->addError('error', $e->getMessage());
                return false;
            }
        }
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
