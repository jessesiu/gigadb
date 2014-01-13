<?php

/**
 * This is the model class for table "relation".
 *
 * The followings are the available columns in table 'relation':
 * @property integer $id
 * @property integer $dataset_id
 * @property string $related_doi
 * @property string $relationship
 *
 * The followings are the available model relations:
 * @property Dataset $dataset
 */
class Relation extends MyActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return Relation the static model class
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
		return 'relation';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('dataset_id, related_doi, relationship', 'required'),
			array('dataset_id', 'numerical', 'integerOnly'=>true),
			array('related_doi', 'length', 'max'=>15),
			array('relationship', 'length', 'max'=>30),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, dataset_id, related_doi, relationship , doi_search', 'safe', 'on'=>'search'),
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
			'related_doi' => 'Related Doi',
			'relationship' => 'Relationship',
            'doi_search' => 'DOI'
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

        $criteria->with = array( 'dataset' );
		$criteria->compare('t.id',$this->id);
		$criteria->compare('dataset_id',$this->dataset_id);
		$criteria->compare('related_doi',$this->related_doi,true);
		$criteria->compare('LOWER(relationship)',strtolower($this->relationship),true);

		$criteria->compare('dataset.identifier',$this->doi_search,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
}
