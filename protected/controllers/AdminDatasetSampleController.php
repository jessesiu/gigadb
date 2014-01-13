<?php

class AdminDatasetSampleController extends Controller
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
				'actions'=>array('admin','delete','index','view','create','update'),
				'roles'=>array('admin'),
			),
                         array('allow',
                'actions' => array('create1', 'delete1', 'autocomplete'),
                'users' => array('@')),
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

	/**
	 * Creates a new model.
	 * If creation is successful, the browser will be redirected to the 'view' page.
	 */
	public function actionCreate()
	{
		$model=new DatasetSample;

		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if(isset($_POST['DatasetSample']))
		{
			$model->attributes=$_POST['DatasetSample'];
			if($model->save())
				$this->redirect(array('view','id'=>$model->id));
		}

		$this->render('create',array(
			'model'=>$model,
		));
	}
        public function actionAutocomplete() {
        $res = array();
        $result = array();

        if (isset($_GET['term'])) {
            $term = $_GET['term'];
            $connection = Yii::app()->db;
            if (is_numeric($term)) {
//                $sql = "
//                    
//                    (select distinct scientific_name as name,tax_id from species where cast(tax_id as text) like :name)
//                    union
//                    (select distinct common_name as name,tax_id from species where cast(tax_id as text) like :name)
//                    order by name;
//                   
//";
                $sql = "select tax_id,common_name,scientific_name from species where cast(tax_id as text) like :name";
                $command = Yii::app()->db->createCommand($sql);
                $command->bindValue(":name", $term . '%', PDO::PARAM_STR);
                $res = $command->queryAll();
            } else {
//                $sql = "select (p.tax_id || '-' || p.common_name || ',' || p.scientific_name) as name from (
//                    select distinct on (tax_id) * from 
//                    species where common_name ilike :name or scientific_name ilike :name ) p 
//                    order by length(p.common_name)";
                $sql = "select tax_id , common_name ,scientific_name from
                    species where common_name ilike :name or scientific_name ilike :name
                    order by length(common_name)";
//                $sql = "Select ( tax_id || '-' || scientific_name ) as name from species where scientific_name ilike :name order by length(scientific_name)";
                $command = Yii::app()->db->createCommand($sql);
                $command->bindValue(":name", '%' . $_GET['term'] . '%', PDO::PARAM_STR);
                $res = $command->queryAll();

//                        $result[] = $mres['tax_id']."-".$mres['scientific_name'];
//                        $result[] = (string)($mres['tax_id']);
//                        var_dump($mres['tax_id']);
            }
//                $sql = "Select ( tax_id || '-' || common_name ) as name from species where common_name ilike :name order by length(common_name)";
//                $command = Yii::app()->db->createCommand($sql);
//                $command->bindValue(":name", $_GET['term'] . '%', PDO::PARAM_STR);
//                $res = $command->queryAll();
//                if (!empty($res))
//                    foreach ($res as $mres) {
//                        $result[] = $mres['name'];
////                        $result[] = $mres['tax_id']."-".$mres['common_name'];
////                        $result[] = (string)($mres["tax_id"]);
//                    }

            if (!empty($res)) {
                foreach ($res as $mres) {
                    $name = $mres['tax_id'] . ":";
                    $has_common_name = false;
                    if ($mres['common_name'] != null) {
                        $has_common_name = true;
                        $name.= $mres['common_name'];
                    }

                    if ($mres['scientific_name'] != null) {
                        if ($has_common_name)
                            $name.=",";
                        $name.= $mres['scientific_name'];
                    }

                    $result[] = $name;
                }
            }

//            sort($result);
//            var_dump($result);
            echo CJSON::encode($result);
            Yii::app()->end();
        }
    }
    
     public function actionDelete1($id) {
        if (isset($_SESSION['samples'])) {
            $info = $_SESSION['samples'];
            foreach ($info as $key => $value) {
                if ($value['id'] == $id) {
                    unset($info[$key]);
                    $_SESSION['samples'] = $info;
                    $vars = array('samples');
                    Dataset::storeSession($vars);
                    $condition = 'id=' . $id;

                    $sample_id = DatasetSample::model()->findByAttributes(array('id' => $id))->sample_id;
                    DatasetSample::model()->deleteAll($condition);
                    Sample::model()->deleteAll('id=' . $sample_id);

                    $this->redirect("/adminDatasetSample/create1");
                }
            }
        }
    }

    public function storeSample(&$model, &$id) {

        
        if (isset($_SESSION['dataset_id'])) {
            $dataset_id = $_SESSION['dataset_id'];
            //1) find species id
            $species_id = 0;
            $tax_id = $model->tax_id;
            $name = $model->species;
            $model->sample_id=0;
            //validate
            if (!$model->validate()) {
                var_dump("here");
                return false;
            }
            //-1 means it doesn't exit in our database
            if ($model->tax_id != -1) {
                
                $species = Species::model()->findByAttributes(array('tax_id' => $tax_id));
                 $species_id = $species->id;
            } else {
                $species = Species::model()->findByAttributes(array('common_name' => $name));
                if ($species != NULL) {
                    $species_id = $species->id;
                } else {
                    $species = Species::model()->findByAttributes(array('scientific_name' => $name));
                    if ($species != NULL)
                        $species_id = $species->id;
                    else {
                        //insert a new species record
                        $model->addError('comment', 'The species you input is not in our database, please
                            input 0:new organism and contact 
                        <a href=&quot;mailto:database@gigasciencejournal.com&quot;>database@gigasciencejournal.com</a>.');
                       //ac $model = new DatasetSample;
                        return false;
                    }
                }
            }
            //2) insert sample 
            $sample = new Sample;
            $sample->species_id = $species_id;
            $sample->code = $model->code;
            $sample->s_attrs = $model->attribute;
           // $sample_id = 0;
            if (!$sample->save()) {
                $model->addError('error', 'Sample save error');
                return false;
            }
            $sample_id = $sample->id;
           

            //3) insert dataset_sample 

            $model->sample_id = $sample_id;
            $model->dataset_id = $dataset_id;
            
            if (!$model->save()) {
                $model->addError('keyword', 'Dataset_Sample is not stored!');
                return false;
            }

            $id = $model->id;
            return true;
        }

        return false;
        echo('xxxxxxx');
    }

    /**
     * Creates a new model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     */
    public function actionCreate1() {

        $model = new DatasetSample;
        $model->dataset_id = 1;
        //$model->
        //update 
        if (!isset($_SESSION['samples']))
            $_SESSION['samples'] = array();

        $samples = $_SESSION['samples'];

        if (isset($_POST['DatasetSample'])) {


            $model->attributes = $_POST['DatasetSample'];

            $name = $_POST['DatasetSample']['code'];
            $tax_id = -1;
            $species = 0;
            if (strpos($_POST['DatasetSample']['species'], ":") !== false) {
                $array = explode(":",$_POST['DatasetSample']['species']);
//                var_dump($array);
                $tax_id = $array[0];
                $species = $_POST['DatasetSample']['species'];
//                var_dump($tax_id);
            } else {
                $species = $_POST['DatasetSample']['species'];
            }
            $attrs = $_POST['DatasetSample']['attribute'];

            $model->code = $name;
            $model->species = $species;
            $model->tax_id = $tax_id;
            $model->attribute = $attrs;
          //  var_dump( $model->code, $model->attribute);

            $id = 0;
         

            if ($this->storeSample($model, $id)) {

                $newItem = array('id' => $id, 'name' => $name, 'species' => $species, 'attrs' => $attrs);

                array_push($samples, $newItem);
                $_SESSION['samples'] = $samples;
                $vars = array('samples');
                Dataset::storeSession($vars);
                $model = new DatasetSample;
            }
            else{
                $model->species="";
            }
        }

        $sample_model = new CArrayDataProvider($samples);

        $this->render('create1', array(
            'model' => $model,
            'sample_model' => $sample_model,
        ));
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

		if(isset($_POST['DatasetSample']))
		{
			$model->attributes=$_POST['DatasetSample'];
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
		$dataProvider=new CActiveDataProvider('DatasetSample');
		$this->render('index',array(
			'dataProvider'=>$dataProvider,
		));
	}

	/**
	 * Manages all models.
	 */
	public function actionAdmin()
	{
		$model=new DatasetSample('search');
		$model->unsetAttributes();  // clear any default values
		if(isset($_GET['DatasetSample']))
			$model->attributes=$_GET['DatasetSample'];

		$this->render('admin',array(
			'model'=>$model,
		));
	}

	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer the ID of the model to be loaded
	 */
	public function loadModel($id)
	{
		$model=DatasetSample::model()->findByPk($id);
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
		if(isset($_POST['ajax']) && $_POST['ajax']==='dataset-sample-form')
		{
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}
	}
}
