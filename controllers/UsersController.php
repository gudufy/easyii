<?php
namespace yii\easyii\controllers;

use Yii;
use yii\web\UploadedFile;
use yii\easyii\helpers\Image;
use yii\data\ActiveDataProvider;
use yii\widgets\ActiveForm;
use yii\easyii\models\User;
use yii\easyii\modules\rbac\models\Assignment;
use yii\easyii\behaviors\StatusController;

class UsersController extends \yii\easyii\components\Controller
{
    public function behaviors()
    {
        return [
            [
                'class' => StatusController::className(),
                'model' => User::className()
            ]
        ];
    }

    public function actionIndex()
    {
        $data = new ActiveDataProvider([
            'query' => User::findByRole('user')->desc(),
        ]);
        Yii::$app->user->setReturnUrl(['/admin/users']);

        return $this->render('index', [
            'data' => $data
        ]);
    }

    public function actionCreate()
    {
        $model = new User;
        $model->scenario = 'create';

        if ($model->load(Yii::$app->request->post())) {
            if(Yii::$app->request->isAjax){
                Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
                return ActiveForm::validate($model);
            }
            else{
                if(($fileInstanse = UploadedFile::getInstance($model, 'image')))
                {
                    $model->image = $fileInstanse;
                    if($model->validate(['image'])){
                        $model->image = Image::upload($model->image, 'users');
                    }
                }

                $model->status = User::STATUS_ON;
                
                if($model->save()){
                    $role = new Assignment($model->id);
                    $success = $role->assign(['user']);

                    $this->flash('success', Yii::t('easyii', 'User created'));
                    return $this->redirect(['/admin/users']);
                }
                else{
                    $this->flash('error', Yii::t('easyii', 'Create error. {0}', $model->formatErrors()));
                    return $this->refresh();
                }
            }
        }
        else {
            return $this->render('create', [
                'model' => $model
            ]);
        }
    }

    public function actionEdit($id)
    {
        $model = User::findOne($id);

        if($model === null){
            $this->flash('error', Yii::t('easyii', 'Not found'));
            return $this->redirect(['/admin/users']);
        }

        if ($model->load(Yii::$app->request->post())) {
            if(Yii::$app->request->isAjax){
                Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
                return ActiveForm::validate($model);
            }
            else{
                if(($fileInstanse = UploadedFile::getInstance($model, 'image')))
                {
                    $model->image = $fileInstanse;
                    if($model->validate(['image'])){
                        $model->image = Image::upload($model->image, 'users');
                    }
                }
                else{
                    $model->image = $model->oldAttributes['image'];
                }

                if($model->save()){
                    $this->flash('success', Yii::t('easyii', 'User updated'));
                }
                else{
                    $this->flash('error', Yii::t('easyii', 'Update error. {0}', $model->formatErrors()));
                }
                return $this->refresh();
            }
        }
        else {
            return $this->render('edit', [
                'model' => $model
            ]);
        }
    }

    public function actionDelete($id)
    {
        if(($model = User::findOne($id))){
            $model->delete();
        } else {
            $this->error = Yii::t('easyii', 'Not found');
        }
        return $this->formatResponse(Yii::t('easyii', 'User deleted'));
    }

    public function actionOn($id)
    {
        return $this->changeStatus($id, User::STATUS_ON);
    }

    public function actionOff($id)
    {
        return $this->changeStatus($id, User::STATUS_OFF);
    }
}