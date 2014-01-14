<?php

class AdminFileController extends Controller
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
			array('allow', // admin only
				'actions'=>array('linkFolder','admin','delete','index','view','create','update','update1'),
				'roles'=>array('admin'),
			),
                        array('allow',
                                'actions' => array('create1'),
                                'users' => array('@'),
            ),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

	/**
	 * Displays a particular model.
	 * @param integer $id the ID of the model to be displayed
	 */
	public function actionView($id)
	{
		$this->render('view',array(
			'model'=>$this->loadModel($id),
		));
	}
        
           public function getFilesInfo($conn_id, $ftp_dir, $ftp, &$model, &$count) {

        $buff = ftp_rawlist($conn_id, $ftp_dir);
        $file_count = count($buff);
        $date = new DateTime("2050-01-01");
        $date = $date->format("Y-m-d");
        foreach ($buff as $key => $value) {
            $info = preg_split("/\s+/", $value);
            $name = $info[8];
            $new_dir = $ftp_dir . "/" . $name;
            if ($this->is_dir($conn_id, $new_dir)) {
                $new_ftp = $ftp . "/" . $name;
                if (!$this->getFilesInfo($conn_id, $new_dir, $new_ftp, $model, $count))
                    return false;
            } else {
                $count++;
                //var_dump($info);
                $size = $info[4];
                $stamp = date("F d Y", ftp_mdtm($conn_id, $name));
                // var_dump($name);
                $file = new File;
                $file->dataset_id = $model->dataset_id;
                $file->name = $name;
                $file->size = $size;
                $file->location = $ftp . "/" . $name;
                $file->code = "None";
                $file->date_stamp = $date;
                $extension = "";
                $format = "";
                $this->getFileExtension($file->name, $extension, $format);
                $file->extension = $extension;
                $fileformat = FileFormat::model()->findByAttributes(array('name' => $format));
                if ($fileformat != null)
                    $file->format_id = $fileformat->id;
                $file->type_id = 1;
                $file->date_stamp = $stamp;
                if (!$file->save()) {

                    $model->addError('error', "Files are not saved correctly");
                    return false;
                    //how to 
//                    var_dump($file->name);
                }
            }
        }
        return true;
    }

	/**
	 * Creates a new model.
	 * If creation is successful, the browser will be redirected to the 'view' page.
	 */
	public function actionCreate()
	{
		$model=new File;

		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if(isset($_POST['File']))
		{
			$model->attributes=$_POST['File'];
			if($model->save())
				$this->redirect(array('view','id'=>$model->id));
		}

		$this->render('create',array(
			'model'=>$model,
		));
	}

	/**
	 * Updates a particular model.
	 * If update is successful, the browser will be redirected to the 'view' page.
	 * @param integer $id the ID of the model to be updated
	 */
         public function actionUpdate1() {
        if (isset($_GET['id']))
            $dataset_id = $_GET['id'];
        else
            return false;
        //add privilidge
//        var_dump($dataset_id);
        $defaultFileSortColumn = 'dataset.name';
        $defaultFileSortOrder = CSort::SORT_DESC;
        if (isset($_GET['filesort'])) {
            // use new sort and save to cookie
            // check if desc or not
            $order = substr($_GET['filesort'], strlen($_GET['filesort']) - 5, 5);
            $columnName = $defaultFileSortColumn;
            if ($order == '.desc') {
                $columnName = substr($_GET['filesort'], 0, strlen($_GET['filesort']) - 5);
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
            if (isset(Yii::app()->request->cookies['file_sort_column'])) {
                $cookie = Yii::app()->request->cookies['file_sort_column']->value;
                $defaultFileSortColumn = $cookie;
            }
            if (isset(Yii::app()->request->cookies['file_sort_order'])) {
                $cookie = Yii::app()->request->cookies['file_sort_order']->value;
                $defaultFileSortOrder = $cookie;
            }
        }

        $fsort = new MySort;
        $fsort->attributes = array('*');
        $fsort->attributes[] = "dataset.identifier";
        $fsort->defaultOrder = array($defaultFileSortColumn => $defaultFileSortOrder);

        $fpagination = new CPagination;
        $fpagination->pageVar = 'files_page';
        $files = new CActiveDataProvider('File', array(
            'criteria' => array(
                'condition' => "dataset_id = " . $dataset_id,
                'join' => 'JOIN dataset ON dataset.id = t.dataset_id',
            ),
            'sort' => $fsort,
            'pagination' => $fpagination
        ));
        $updateAll = 0;
        if (isset($_POST['File'])) {
            if (isset($_POST['files']))
                $updateAll = 1;
            $count = count($_POST['File']);
            for ($i = 0; $i < $count; $i++) {
                if ($updateAll == 0 && !isset($_POST[$i])) {
//                    var_dump($i." passed");
                    continue;
                }
                $model = $this->loadModel($_POST['File'][$i]['id']);
//            $model->dataset_id = $dataset_id;
                $model->attributes = $_POST['File'][$i];
                if ($model->date_stamp == "")
                    $model->date_stamp = NULL;
                //$model->dataset_id = $_POST['File']['dataset_id'];
                if (!$model->save()) {
                    var_dump($_POST['File'][$i]);
                }
            }
        }


        $fileModels = File::model()->findAll("dataset_id=" . $dataset_id);
        $identifier = Dataset::model()->findByAttributes(array('id' => $dataset_id))->identifier;

        $model = new File;
        
        $dataset = Dataset::model()->findByAttributes(array('id' => $dataset_id));
        $samples = $dataset->samples;
        $samples_data = array();
        foreach($samples as $sample){
            $samples_data[$sample->code] = $sample->code;
        }
        //add none and All , Multiple
        $samples_data['none']='none';
        $samples_data['All']='All';
        $samples_data['Multiple']='Multiple';

        $this->render('update1', array('files' => $files, 'fileModels' => $fileModels, 'identifier' => $identifier, 'model' => $model, 'samples_data'=>$samples_data));
    }
    
     public function getFileExtension($file_name, &$extension, &$format) {
        //extensions is <extension, format> array
        $extensions = array('agp' => 'AGP', 'bam' => 'BAM', 'chain' => 'CHAIN', 'contig' => 'CONTIG',
            'xls' => 'EXCEL', 'xlsx' => 'EXCEL', 'chr' => 'FASTA', 'fasta' => 'FASTA', 'fa' => 'FASTA',
            'seq' => 'FASTA', 'cds' => 'FASTA', 'pep' => 'FASTA', 'scanffold' => 'FASTA', 'scafseq' => 'FASTA',
            'fq' => 'FASTQ', 'fastq' => 'FASTQ', 'gff' => 'GFF', 'ipr' => 'IPR', 'kegg' => 'KEGG', 'maf' => 'MAF',
            'md5' => 'MD5', 'net' => 'NET', 'pdf' => 'PDF', 'png' => 'PNG', 'qmap' => 'QMAP', 'rpkm' => 'RPKM',
            'sam' => 'SAM', 'tar' => 'TAR', 'readme' => 'TEXT', 'doc' => 'TEXT', 'text' => 'TEXT', 'txt' => 'TEXT', 'vcf' => 'VCF',
            'wego' => 'WEGO', 'wig' => 'WIG', 'iprscan' => 'IPR', 'stat' => 'UNKNOWN', 'qual' => 'QUAL'
        );

        $comExt = array("7z", "arj", "bz2", "bzip2", "cab", "cpio",
            "deb", "dmg", "gz", "gzip", "hfs", "iso", "lha", "lzh", "lzma",
            "rar", "rpm", "split", "swm", "tar", "taz", "tbz", "tbz2", "tgz",
            "tpz", "wim", "xar", "z", "zip");

        $extensionArray = explode(".", $file_name);

        $extension = "";
        $length = count($extensionArray);
        if ($length == 1) {
            $extension = 'UNKNOWN';
        }
        // the first one shouldn't be extension
        foreach ($extensionArray as $temp) {
            $temp = trim($temp);
            // all extension are lower case in map, so when camparing,
            // I need to change temp to lowercase
            // if readme then the extension before it is removed
            if ($temp == "readme") {
                $extension = "";
                continue;
            }
            if (array_key_exists(strtolower($temp), $extensions)) {
                if ($extension != "" && $temp == "txt")
                    continue;
                $extension = $temp;
            }
        }
        if ($extension == "") {
            $index = $length - 1;
            while (in_array(strtolower($extensionArray[$index]), $extensions
            ))
                $index = $index - 1;
            $extension = $extensionArray[$index];
        }
        if (array_key_exists($extension, $extensions))
            $format = $extensions[$extension];
        else
            $format = 'UNKNOWN';
        return;
    }
        
	public function actionUpdate($id)
	{
		$model=$this->loadModel($id);

		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if(isset($_POST['File']))
		{
			$model->attributes=$_POST['File'];
			if($model->save())
				$this->redirect(array('view','id'=>$model->id));
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
		$dataProvider=new CActiveDataProvider('File');
		$this->render('index',array(
			'dataProvider'=>$dataProvider,
		));
	}

	/**
	 * Manages all models.
	 */
	public function actionAdmin()
	{
		$model=new File('search');
		$model->unsetAttributes();  // clear any default values
		if(isset($_GET['File']))
			$model->attributes=$_GET['File'];

		$this->render('admin',array(
			'model'=>$model,
		));
	}
        
            /**
     * Link files through a folder 
     */
    public function actionLinkFolder() {
        $model = new Folder;
        $buff = array();
        // Uncomment the following line if AJAX validation is needed
        // $this->performAjaxValidation($model);
     
         if(isset($_GET['id'])){
            
            $model->dataset_id=$_GET['id'];
        }       
        if (isset($_POST['Folder'])) {


            $model->attributes = $_POST['Folder'];

            if (!$model->validate()) {
                $this->render('linkFolder', array(
                    'model' => $model, 'buff' => $buff
                ));
                return;
            }
            $ftp = $model->folder_name;
            $ftps = explode("/", $ftp, 2);
            $ftp_server = $ftps[0];
//            var_dump($ftp_server);
            if (isset($ftps[1]))
                $ftp_dir = "/" . $ftps[1];
            else
                $ftp_dir = "/";

//            if($ftp_dir=="")
//                $ftp_dir=
            $ftp_user_name = $_POST['Folder']['username'];
            $ftp_user_pass = $_POST['Folder']['password'];

            // set up basic connection

            $conn_id = @ftp_connect($ftp_server);
            if ($conn_id === false) {
                $model->addError('error', 'Unable to connect to ' . $ftp_server);
                $this->render('linkFolder', array(
                    'model' => $model, 'buff' => $buff
                ));
                return;
            }
            // login with username and password
            $login_result = @ftp_login($conn_id, $ftp_user_name, $ftp_user_pass);
            if ($login_result !== true) {
                $model->addError('error', "Couldn't connect as $ftp_user_name\n");
                $this->render('linkFolder', array(
                    'model' => $model, 'buff' => $buff
                ));
                return;
            }
            $file_count = 0;
            $transaction = Yii::app()->db->beginTransaction();

            $ok = $this->getFilesInfo($conn_id, $ftp_dir, $ftp, $model, $file_count);

            ftp_close($conn_id);
            //email 
            if ($ok && $file_count>0) {
                $user = User::model()->findByPk(Yii::app()->user->id);

                $from = Yii::app()->params['app_email_name'] . " <" . Yii::app()->params['app_email'] . ">";
                $dataset = Dataset::model()->findByattributes(array('id' => $model->dataset_id));
                $dataset->upload_status = 'Uploaded';
                if (!$dataset->save()) {
                    $model->addError('error', "Failure: Dataset status is not updated successfully.");
                    $this->render('linkFolder', array(
                        'model' => $model, 'buff' => $buff
                    ));
                    return;
                }
                $transaction->commit();

                $submitter = $dataset->submitter;
                $to = $dataset->submitter->email;
                
               // $subject = "Files are added to Your dataset: " . $model->dataset_id;
                //$subject= 
                $subject = "GigaDB submission \"" . $dataset->title . '"'.' ['.$dataset->id.']';
                $receiveNewsletter = $user->newsletter ? 'Yes' : 'No';
                $link = Yii::app()->params['home_url'] . "/dataset/updateFile/?id=" . $model->dataset_id;
                $message = <<<EO_MAIL
Dear $submitter->first_name,<br/><br/>

$file_count Files have been added to your GigaDB submission "$dataset->title".<br/><br/>

Please complete the submission by clicking the
    link below and adding the sample(s) from which each file was generated, 
        along with the File type, File format and a description of the file.
            Once all file information has been added, click the “Complete submission” button
                to let the curator know that you have completed the required information.<br/><br/>

Please review the files here: $link<br/><br/>

Kind regards<br/>
GigaDB team
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
                $message = "--{$mime_boundary}\n" . "Content-Type: text/html; charset=\"utf-8\"\n" . "Content-Transfer-Encoding: 7bit\n\n" . $message . "\n\n";
                $message .= "--{$mime_boundary}\n";

                $message .= "--{$mime_boundary}--";
                $returnpath = "-f" . Yii::app()->params['adminEmail'];

                $ok = @mail($to, $subject, $message, $headers, $returnpath);

                //send to database@gigasciencejournal.com
                $from = Yii::app()->params['app_email_name'] . " <" . Yii::app()->params['app_email'] . ">";

                $to = Yii::app()->params['app_email'];
                $subject = "Files are added to  dataset: " . $model->dataset_id;
                $receiveNewsletter = $user->newsletter ? 'Yes' : 'No';
                $link = Yii::app()->params['home_url'] . "/dataset/update/id/" . $model->dataset_id;
                $message = <<<EO_MAIL
Dear GigaDB,<br/><br/>

Files have been updated by:<br/>
User: $user->id<br/>
Email: $user->email<br/>
First Name: $user->first_name<br/>
Last Name: $user->last_name<br/>
Affiliation: $user->affiliation<br/>
Submission ID: $model->dataset_id<br/>
$link<br/><br/>                    
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

                $ok = @mail($to, $subject, $message, $headers, $returnpath);

                $this->redirect("/adminFile/update1/?id=" . $model->dataset_id);
                return;
            } else {
                $transaction->rollback();
                $model->addError('error', "Files are not saved!\n");
            }
        }
        
        $this->render('linkFolder', array(
            'model' => $model, 'buff' => $buff
        ));
    }
    
    public function actionCreate1() {
        if (isset($_SESSION['dataset_id'])){
            
            $dataset_id = $_SESSION['dataset_id'];
            
        }
        else {
            Yii::app()->user->setFlash('error', "Can't retrieve the files");
//            var_dump("here");
            $this->redirect("/user/view_profile");
        }
        
        $defaultFileSortColumn = 'dataset.name';
        $defaultFileSortOrder = CSort::SORT_DESC;
        if (isset($_GET['filesort'])) {
            // use new sort and save to cookie
            // check if desc or not
            $order = substr($_GET['filesort'], strlen($_GET['filesort']) - 5, 5);
            $columnName = $defaultFileSortColumn;
            if ($order == '.desc') {
                $columnName = substr($_GET['filesort'], 0, strlen($_GET['filesort']) - 5);
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
            if (isset(Yii::app()->request->cookies['file_sort_column'])) {
                $cookie = Yii::app()->request->cookies['file_sort_column']->value;
                $defaultFileSortColumn = $cookie;
            }
            if (isset(Yii::app()->request->cookies['file_sort_order'])) {
                $cookie = Yii::app()->request->cookies['file_sort_order']->value;
                $defaultFileSortOrder = $cookie;
            }
        }

        $fsort = new MySort;
        $fsort->attributes = array('*');
        $fsort->attributes[] = "dataset.identifier";
        $fsort->defaultOrder = array($defaultFileSortColumn => $defaultFileSortOrder);

        $fpagination = new CPagination;
        $fpagination->pageVar = 'files_page';
        $files = new CActiveDataProvider('File', array(
            'criteria' => array(
                'condition' => "dataset_id = " . $dataset_id,
                'join' => 'JOIN dataset ON dataset.id = t.dataset_id',
                'order'=> 't.id'
            ),
            'sort' => $fsort,
            'pagination' => $fpagination
        ));
        $updateAll = 0;

        if (isset($_POST['File'])) {
            if (isset($_POST['files']))
                $updateAll = 1;
            $count = count($_POST['File']);
            $page = $_POST['page'];
            $pageCount = $_POST['pageCount'];
            if ($page < $pageCount) {
                $page++;
                $files->getPagination()->setCurrentPage($page);
            }
            for ($i = 0; $i < $count; $i++) {
                if ($updateAll == 0 && !isset($_POST[$i])) {
//                    var_dump($i." passed");
                    continue;
                }

               $model = $this->loadModel($_POST['File'][$i]['id']);
//            $model->dataset_id = $dataset_id;
                $model->attributes = $_POST['File'][$i];
                if ($model->date_stamp == "")
                    $model->date_stamp = NULL;

                if (!$model->save()) {
                    var_dump($_POST['File'][$i]);
                }
            }
            //determine if it want to submit
//             if (isset($_POST['file'])) {
//                 $this->redirect("/dataset/submit");
//             }
            
        }
        $dataset = Dataset::model()->findByAttributes(array('id' => $dataset_id));
        $samples = $dataset->samples;
        $samples_data = array();
        foreach($samples as $sample){
            $samples_data[$sample->code] = $sample->code;
        }
        //add none and All , Multiple
        $samples_data['none']='none';
        $samples_data['All']='All';
        $samples_data['Multiple']='Multiple';
        
        $identifier = $dataset->identifier;
        $action = 'create1';

        $this->render($action, array('files' => $files, 'identifier' => $identifier,
            'samples_data'=>$samples_data));
    }
       
    function is_dir($conn_id, $dir) {
        // get current directory
        $original_directory = ftp_pwd($conn_id);
        // test if you can change directory to $dir
        // suppress errors in case $dir is not a file or not a directory
        if (@ftp_chdir($conn_id, $dir)) {
            // If it is a directory, then change the directory back to the original directory
            ftp_chdir($conn_id, $original_directory);
            return true;
        } else {
            return false;
        }
    }
	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer the ID of the model to be loaded
	 */
	public function loadModel($id)
	{
		$model=File::model()->findByPk($id);
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
		if(isset($_POST['ajax']) && $_POST['ajax']==='file-form')
		{
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}
	}
}
