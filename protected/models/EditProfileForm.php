<?php

class EditProfileForm extends CFormModel
{
	public $first_name;
	public $last_name;
	public $email;
	public $affiliation;
	public $newsletter;
	public $user_id;

	/**
	 * Declares the validation rules.
	 * The rules state that username and password are required,
	 * and password needs to be authenticated.
	 */
	public function rules()
	{
		return array(
			// username and password are required
			array('email, first_name, last_name, newsletter, user_id, affiliation', 'required')
		);
	}

	/**
	 * Declares attribute labels.
	 */
	public function attributeLabels()
	{
		return array(
            'email' => Yii::t('app' , 'Email'),
            'first_name' => Yii::t('app' , 'First Name'),
            'last_name' => Yii::t('app' , 'Last Name'),
            'affiliation' => Yii::t('app' , 'Affiliation'),
		);
	}

    public function updateInfo(){
        $user = User::model()->findByPk($this->user_id);
        if(isset($user)){
            $user->first_name = $this->first_name;
            $user->last_name = $this->last_name;
            $user->affiliation = $this->affiliation;
            $user->password_repeat ='NoNeed';
            $user->email = $this->email;
	    Yii::log($this->newsletter, 'debug');
            $user->newsletter = $this->newsletter;

            if($user->save()) {
            	Yii::log("Success","DEBUG");
                return true;
            }
            print_r($user->errors);die;
        }
        return false;
    }
}
