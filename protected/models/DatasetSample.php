<?php

/**
 * This is the model class for table "dataset_sample".
 *
 * The followings are the available columns in table 'dataset_sample':
 * @property integer $id
 * @property integer $dataset_id
 * @property integer $sample_id
 *
 * The followings are the available model relations:
 * @property Dataset $dataset
 * @property Sample $sample
 */
class DatasetSample extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return DatasetSample the static model class
	 */

	public $doi_search;

	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'dataset_sample';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('dataset_id, sample_id', 'required'),
			array('dataset_id, sample_id', 'numerical', 'integerOnly'=>true),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, dataset_id, sample_id, doi_search', 'safe', 'on'=>'search'),
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
			'sample' => array(self::BELONGS_TO, 'Sample', 'sample_id'),
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
			'sample_id' => 'Sample',
			'doi_search' => 'DOI',
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

        $criteria->with = array('dataset', 'sample');

		$criteria->compare('id',$this->id);
		$criteria->compare('dataset_id',$this->dataset_id);
		$criteria->compare('sample_id',$this->sample_id);
		$criteria->compare('dataset.identifier',$this->doi_search,true);

        $sort = new CSort();
        $sort->attributes = array(
            'doi_search' => array(
                'asc' => '(SELECT identifier from dataset WHERE dataset.id = t.dataset_id) ASC',
                'desc' => '(SELECT identifier from dataset WHERE dataset.id = t.dataset_id) DESC',
            ),
        );

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
            'sort' => $sort,
		));
	}
}
