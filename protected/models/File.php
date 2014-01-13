<?php

/**
 * This is the model class for table "file".
 *
 * The followings are the available columns in table 'file':
 * @property integer $id
 * @property integer $dataset_id
 * @property string $name
 * @property string $location
 * @property string $extension
 * @property string $size
 * @property string $description
 * @property string $date_stamp
 * @property integer $format_id
 * @property integer $type_id
 *
 * The followings are the available model relations:
 * @property Dataset $dataset
 * @property FileFormat $format
 * @property FileType $type
 */
class File extends MyActiveRecord
{
    public $doi_search;
    public $format_search;
    public $type_search;
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return File the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'file';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('dataset_id, name, location, extension, size', 'required'),
			array('dataset_id, format_id, type_id', 'numerical', 'integerOnly'=>true),
			array('name', 'length', 'max'=>100),
			array('location', 'length', 'max'=>200),
			array('extension', 'length', 'max'=>30),
			array('description, date_stamp, code', 'safe'),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, dataset_id, name, location, extension, size, description, date_stamp, format_id, type_id , doi_search,format_search, type_search', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
			'dataset' => array(self::BELONGS_TO, 'Dataset', 'dataset_id'),
			'format' => array(self::BELONGS_TO, 'FileFormat', 'format_id'),
			'type' => array(self::BELONGS_TO, 'FileType', 'type_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'dataset_id' => 'Dataset',
			'name' => Yii::t('app' , 'File Name'),
			'location' => 'Location',
			'extension' => 'Extension',
			'size' => Yii::t('app' , 'Size'),
			'description' => 'Description',
			'date_stamp' => Yii::t('app' , 'Release Date'),
			'format_id' => Yii::t('app' , 'File Format'),
			'type_id' => Yii::t('app' , 'File Type'),
			'code' => Yii::t('app' , 'Sample ID') ,
      'doi_search' => 'DOI',
      'format_search' => 'File Format',
      'type_search' => 'File Type'
		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
	public function search()
	{
		// Warning: Please modify the following code to remove attributes that
		// should not be searched.

		$criteria=new CDbCriteria;

    $criteria->with = array( 'dataset' , 'format' , 'type' );
		$criteria->compare('t.id',$this->id);

		$criteria->compare('LOWER(t.name)',strtolower($this->name),true);
		$criteria->compare('location',$this->location,true);
		$criteria->compare('extension',$this->extension,true);
		$criteria->compare('size',$this->size,true);
		$criteria->compare('description',$this->description,true);
		$criteria->compare('date_stamp',$this->date_stamp,true);
		//$criteria->compare('format_id',$this->format_id);
		//$criteria->compare('type_id',$this->type_id);
		$criteria->compare('LOWER(code)',strtolower($this->code),true);

		$criteria->compare('LOWER(dataset.identifier)',strtolower($this->doi_search),true);
		$criteria->compare('LOWER(format.name)',strtolower($this->format_search),true);
		$criteria->compare('LOWER(type.name)',strtolower($this->type_search),true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	    /**
	 * Convert bytes to human readable format
	 *
	 * @param integer bytes Size in bytes to convert
	 * @return string
	 */
	public function bytesToSize($precision = 2)
	{
		$bytes = $this->size;
	    $kilobyte = 1024;
	    $megabyte = $kilobyte * 1024;
	    $gigabyte = $megabyte * 1024;
	    $terabyte = $gigabyte * 1024;

	    if ($bytes < $megabyte) {
	        return round($bytes / $kilobyte, $precision) . ' KB';
	    } elseif (($bytes >= $megabyte) && ($bytes < $gigabyte)) {
	        return round($bytes / $megabyte, $precision) . ' MB';

	    } elseif (($bytes >= $gigabyte) && ($bytes < $terabyte)) {
	        return round($bytes / $gigabyte, $precision) . ' GB';

	    } elseif ($bytes >= $terabyte) {
	        return round($bytes / $terabyte, $precision) . ' TB';
	    } else {
	        return $bytes . ' B';
	    }
	}
	public function getSizeType(){
		$bytes = $this->size;
	    $kilobyte = 1024;
	    $megabyte = $kilobyte * 1024;
	    $gigabyte = $megabyte * 1024;
	    $terabyte = $gigabyte * 1024;

	    if ($bytes < $megabyte) {
	        return 1;
	    } elseif (($bytes >= $megabyte) && ($bytes < $gigabyte)) {
	        return 2;

	    } elseif (($bytes >= $gigabyte) && ($bytes < $terabyte)) {
	        return 3;

	    } elseif ($bytes >= $terabyte) {
	        return 4;
	    } else {
	        return 0;
	    }
	}


	public static function staticGetSizeType($bytes){
	    $kilobyte = 1024;
	    $megabyte = $kilobyte * 1024;
	    $gigabyte = $megabyte * 1024;
	    $terabyte = $gigabyte * 1024;

	    if ($bytes < $megabyte) {
	        return 1;
	    } elseif (($bytes >= $megabyte) && ($bytes < $gigabyte)) {
	        return 2;

	    } elseif (($bytes >= $gigabyte) && ($bytes < $terabyte)) {
	        return 3;

	    } elseif ($bytes >= $terabyte) {
	        return 4;
	    } else {
	        return 0;
	    }

	}

	public static function staticBytesToSize($bytes,$precision = 2)
	{

	    $kilobyte = 1024;
	    $megabyte = $kilobyte * 1024;
	    $gigabyte = $megabyte * 1024;
	    $terabyte = $gigabyte * 1024;

	    if ($bytes < $megabyte) {
	        return round($bytes / $kilobyte, $precision) . ' KB';
	    } elseif (($bytes >= $megabyte) && ($bytes < $gigabyte)) {
	        return round($bytes / $megabyte, $precision) . ' MB';

	    } elseif (($bytes >= $gigabyte) && ($bytes < $terabyte)) {
	        return round($bytes / $gigabyte, $precision) . ' GB';

	    } elseif ($bytes >= $terabyte) {
	        return round($bytes / $terabyte, $precision) . ' TB';
	    } else {
	        return $bytes . ' B';
	    }
	}

	public static function getDatasetIdsByFileIds($fileIds) {
        $fileIds = implode(' , ' , $fileIds);
        if(!$fileIds) return array();
        $result = Yii::app()->db->createCommand()
            ->selectDistinct('dataset_id')
            ->from('file')
            ->where("id in ($fileIds)")
            ->queryColumn();
	#	$criteria = new CDbCriteria();
	#	$criteria->select='id, dataset_id';
    #$criteria->addInCondition('id', $fileIds);
    #$criteria->distinct = true;
    #$criteria->group = 'id, dataset_id';
  	#$files = File::model()->query($criteria,true);
  	#$result = CHtml::listData($files,'id','dataset_id');
        return $result;
    }
}
