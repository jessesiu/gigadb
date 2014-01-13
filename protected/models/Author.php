<?php

/**
 * This is the model class for table "author".
 *
 * The followings are the available columns in table 'author':
 * @property integer $id
 * @property string $name
 * @property string $orcid
 * @property integer $position
 *
 * The followings are the available model relations:
 * @property DatasetAuthor[] $datasetAuthors
 */
class Author extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return Author the static model class
	 */
	public $dois_search;

	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'author';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('name, orcid, rank', 'required'),
			array('rank', 'numerical', 'integerOnly'=>true),
			array('name', 'length', 'max'=>255),
			array('orcid', 'length', 'max'=>128),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, name, orcid, rank, dois_search', 'safe', 'on'=>'search'),
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
			'datasetAuthors' => array(self::HAS_MANY, 'DatasetAuthor', 'author_id'),
			'datasets' => array(self::MANY_MANY, 'Dataset', 'dataset_author(dataset_id,author_id)')
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'name' => 'Name',
			'orcid' => 'Orcid',
			'rank' => 'Rank',
			'dois_search' => 'DOIs',
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

		$criteria->compare('id',$this->id);
		$criteria->compare('LOWER(name)', strtolower($this->name),true);
		$criteria->compare('LOWER(orcid)',strtolower($this->orcid),true);
		$criteria->compare('rank',$this->rank);
		if ($this->dois_search) {
			$matchedSql = 'SELECT dataset_id, author_id FROM dataset, dataset_author WHERE dataset.identifier LIKE \'%'.$this->dois_search.'%\' AND dataset.id = dataset_author.dataset_id';
			$criteria->addInCondition('t.id',CHtml::listData(DatasetAuthor::model()->findAllBySql($matchedSql),'dataset_id','author_id'));
		}

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

    public function getFullAuthor(){
        return $this->name . ' - ORCID:' . $this->orcid . ' - RANK:' . $this->rank;
    }
}
