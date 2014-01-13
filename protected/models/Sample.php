<?php

/**
 * This is the model class for table "sample".
 *
 * The followings are the available columns in table 'sample':
 * @property integer $id
 * @property integer $species_id
 * @property string $s_attrs
 *
 * The followings are the available model relations:
 * @property File[] $files
 * @property Species $species
 * @property DatasetSample[] $datasetSamples
 */
class Sample extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return Sample the static model class
	 */
    public $species_search;
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
		return 'sample';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('species_id', 'required'),
			array('species_id', 'numerical', 'integerOnly'=>true),
			array('s_attrs', 'safe'),
			array('code', 'required'),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, species_id, s_attrs, code , species_search, dois_search', 'safe', 'on'=>'search'),
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
			'files' => array(self::HAS_MANY, 'File', 'sample_id'),
			'species' => array(self::BELONGS_TO, 'Species', 'species_id'),
			'datasetSamples' => array(self::HAS_MANY, 'DatasetSample', 'sample_id'),
			'datasets' => array(self::MANY_MANY, 'Dataset', 'dataset_sample(dataset_id,sample_id)')
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'species_id' => 'Species',
			's_attrs' => 'Attributes',
			'code' => 'Sample ID',
            'species_search' => 'Species Name',
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

        $criteria->with = array('species','datasets');
		$criteria->compare('t.id',$this->id);
		$criteria->compare('species_id',$this->species_id);
		$criteria->compare('LOWER(s_attrs)',strtolower($this->s_attrs),true);
		$criteria->compare('LOWER(code)',strtolower($this->code),true);

		$criteria->compare('LOWER(species.common_name)',strtolower($this->species_search),true);
		if ($this->dois_search) {
#			$matchedSql = 'SELECT dataset_id, sample_id FROM dataset, dataset_sample WHERE dataset.identifier LIKE \'%'.$this->dois_search.'%\' AND dataset.id = dataset_sample.dataset_id';
#			$criteria->addInCondition('t.id',CHtml::listData(DatasetSample::model()->findAllBySql($matchedSql),'dataset_id','sample_id'));
            $sql = <<<EO_SQL
SELECT sample_id FROM dataset_sample
WHERE dataset_id in (
SELECT dataset.id FROM dataset WHERE identifier LIKE '%{$this->dois_search}%'
)
EO_SQL;
            $connection = Yii::app()->db;
            $command = $connection->createCommand($sql);
            $criteria->addInCondition('t.id' , $command->queryColumn());
		}

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/*
	* Convert sample attributes to an array
	*/
	public function sampleAttributesToArray($sa) {
        $i = 0;
        $start_key = 0;
        $start_value = 0;
        $result = array();
        while($i < strlen($sa)){
            if($sa[$i] == '='){ // hitting =, start recording the key and value
                $key = trim(substr($sa , $start_key , $i - $start_key));
                while($sa[$i] != '"'){
                    ++$i;
                }
                ++$i; // get passed the first "
                $start_value = $i ;
                while($sa[$i] != '"'){ // get passed the second "
                    ++$i;
                }
                $result[$key] = substr($sa , $start_value , $i - $start_value);

                while($i < strlen($sa) && $sa[$i] != ','){
                    ++$i;
                }
                $start_key = $i+1;
            }
            ++$i;
        }
        return $result;
    }
#	public function sampleAttributesToArray($sampleAttributes) {
#		$i = 0;
#		$j = 0;
#		$result = array();
#		$currentKey = '';
#		while($i < strlen($sampleAttributes)) {
#			if ($sampleAttributes[$i] == '=' && $currentKey == '') { // equal sign, get the key!!
#				$currentKey = substr($sampleAttributes, $j,$i-$j);
#				$i += 2; // jump to first character of the value
#				$j = $i; // save the first index of the value
#			} elseif ($sampleAttributes[$i] == '"' && $sampleAttributes[$i-1] != '\\') {
#				// second double quote, get the value!! Erase the key!! Assign to result
#				$result[$currentKey] = substr($sampleAttributes,$j,$i-$j);
#				$currentKey = '';
#				$i += 2; // jump to the next key!
#				$j = $i;
#			} else {
#				++$i;
#			}
#		}
#		return $result;
#	}

	public function embedDiseaseLinkInAttributes($sampleAttributes){
		$attributesArray = $this->sampleAttributesToArray($sampleAttributes);
		if (isset($attributesArray['disease'])) {
			$value = $attributesArray['disease']; // value can be: X:Y:Z == "hepatitis B:DOID:2043"
			$firstColonIndex = strpos($value, ':',0); // first colon
			$secondColonIndex = strpos($value, ':',$firstColonIndex+1); // second colon

			$X = substr($value,0,$firstColonIndex);
			$Y = substr($value,$firstColonIndex+1,$secondColonIndex-$firstColonIndex-1);
			$Z = substr($value,$secondColonIndex+1,strlen($value)-$secondColonIndex-1);
			// generate a link like http://purl.obolibrary.org/obo_DOID_2043

			if ('DOID' == $Y)
				$websiteURL = 'http://purl.obolibrary.org/obo/';
			elseif ('MDR' == $Y)
				$websiteURL = 'http://purl.bioontology.org/ontology/';
            else
                return $sampleAttributes;

            if ($Z == '')
                return $sampleAttributes;

			$link = $websiteURL.$Y.'_'.$Z;

			return str_replace($value,"<a href='$link'>$X</a>",$sampleAttributes);

		} else {
			return $sampleAttributes;
		}
	}
}
