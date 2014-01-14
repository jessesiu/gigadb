<?php

/**
 * This is the model class for table "link".
 *
 * The followings are the available columns in table 'link':
 * @property integer $id
 * @property integer $dataset_id
 * @property boolean $is_primary
 * @property string $link
 *
 * The followings are the available model relations:
 * @property Dataset $dataset
 */
class Link extends MyActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return Link the static model class
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
		return 'link';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('dataset_id, link', 'required'),
			array('dataset_id', 'numerical', 'integerOnly'=>true),
			array('link', 'length', 'max'=>100),
			array('is_primary', 'safe'),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, dataset_id, is_primary, link, doi_search', 'safe', 'on'=>'search'),
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
			'is_primary' => 'Is Primary',
			'link' => 'Link',
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
        $criteria->with = array( 'dataset' );
		$criteria->compare('t.id',$this->id);
		$criteria->compare('dataset_id',$this->dataset_id);
		$criteria->compare('is_primary',$this->is_primary);
		$criteria->compare('LOWER(link)',strtolower($this->link),true);
		$criteria->compare('dataset.identifier',$this->doi_search,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	public function getLink(){
    	$temp=explode(":", trim($this->link));
    	$prefix=$temp[0];
    	$value=$temp[1];

    	if($prefix=="AE"){
    		return "http://www.ebi.ac.uk/arrayexpress/experiments/".$value;
    	}
    	if($prefix=="dbGaP"){
    		return "http://www.ncbi.nlm.nih.gov/projects/gap/cgi-bin/study.cgi?study_id=".$value;
    	}
    	if($prefix=="dbSNP"){
    		return "http://www.ncbi.nlm.nih.gov/projects/SNP/".$value;
    	}
    	if($prefix=="dbVar"){
    		return "http://www.ncbi.nlm.nih.gov/dbvar/studies/estd3/".$value;
    	}
    	if($prefix=="DDBJ"){
    		return "http://www.ddbj.nig.ac.jp/".$value;
    	}
    	if($prefix=="DOI"){
    		return "http://dx.doi.org/10.1186/".$value;
    	}
    	if($prefix=="DOID"){
    		return "http://purl.obolibrary.org/obo/DOID_".$value;
    	}
    	if($prefix=="EGA"){
    		return "https://www.ebi.ac.uk/ega/studies/".$value;
    	}
    	if($prefix=="ENA"){
    		return "http://www.ebi.ac.uk/ena/data/view/".$value;
    	}
    	if($prefix=="GENBANK"){
    		return "http://www.ncbi.nlm.nih.gov/nuccore/?term=".$value;
    	}
    	if($prefix=="GEO"){
    		return "http://www.ncbi.nlm.nih.gov/geo/query/acc.cgi?acc=".$value;
    	}
    	if($prefix=="MedDRA"){
    		return "http://purl.bioontology.org/ontology/MDR/".$value;
    	}
    	if($prefix=="PMID"){
    		return "http://www.ncbi.nlm.nih.gov/pubmed/".$value;
    	}
    	if($prefix=="PROJECT"){
    		return "http://www.ncbi.nlm.nih.gov/bioproject?term=".$value;
    	}
    	if($prefix=="SAMPLE"){
    		return "http://www.ncbi.nlm.nih.gov/biosample?term=".$value;
    	}
    	if($prefix=="SRA"){
    		return "http://www.ncbi.nlm.nih.gov/sra?term=".$value;
    	}
    	if($prefix=="TRACE"){
    		return "http://www.ncbi.nlm.nih.gov/Traces/home/".$value;
    	}
		if($prefix=="ProteomeXchange"){
    		return "http://dx.doi.org/10.6019/".$value;
    	}
		if($prefix=="PRIDE"){
    		return "http://www.ebi.ac.uk/pride/".$value;
    	}
		if($prefix=="BioProject"){
    		return "http://www.ncbi.nlm.nih.gov/bioproject?term=".$value;
    	}
		if($prefix=="PeptideAtlas"){
    		return "https://db.systemsbiology.net/sbeams/cgi/PeptideAtlas/PASS_View?identifier=".$value;
    	}


    	return "";


    }
}
