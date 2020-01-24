<?php

namespace webvimark\modules\UserManagement\controllers;

use webvimark\components\AdminDefaultController;
use Yii;
use webvimark\modules\UserManagement\models\User;
use webvimark\modules\UserManagement\models\search\UserSearch;
use yii\db\Query;
use yii\web\NotFoundHttpException;
use \common\models\Person;

/**
 * UserController implements the CRUD actions for User model.
 */
class UserController extends AdminDefaultController
{
    /**
     * @var User
     */
    public $modelClass = 'webvimark\modules\UserManagement\models\User';

    /**
     * @var UserSearch
     */
    public $modelSearchClass = 'webvimark\modules\UserManagement\models\search\UserSearch';

    /**
     * @return mixed|string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new User(['scenario'=>'newUser']);

        $max = (new Query())->select('max(id) as id')->from('person')->one();
        $next_id = $max['id'] + 1;
        $model->id = $next_id;

        $transaction = Yii::$app->db->beginTransaction();

        if ( $model->load(Yii::$app->request->post()) && $model->save() )
        {

            $person = new Person();
            $person->id = $model->id;
            $person->type = Person::TYPE_STAFF;
            $person->first_name = $model->username;
            $person->last_name = 'user';
            $person->email = ($model->email) ? $model->email : $model->username;

            if(!$person->save(false)) {
                $transaction->rollBack();
                print_r($person->errors); die();
            }

            $transaction->commit();


            return $this->redirect(['view',	'id' => $model->id]);
        }

        return $this->renderIsAjax('create', compact('model'));
    }

    /**
     * @param int $id User ID
     *
     * @throws \yii\web\NotFoundHttpException
     * @return string
     */
    public function actionChangePassword($id)
    {
        $model = User::findOne($id);

        if ( !$model )
        {
            throw new NotFoundHttpException('User not found');
        }

        $model->scenario = 'changePassword';

        if ( $model->load(Yii::$app->request->post()) && $model->save() )
        {
            return $this->redirect(['view',	'id' => $model->id]);
        }

        return $this->renderIsAjax('changePassword', compact('model'));
    }

}