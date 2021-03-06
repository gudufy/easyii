<?php
namespace yii\easyii\controllers;

use Yii;
use yii\web\UploadedFile;
use yii\data\ActiveDataProvider;
use yii\widgets\ActiveForm;
use yii\easyii\models\User;
use yii\easyii\models\ChangeRootPassword;
use yii\easyii\modules\rbac\models\Assignment;

class AdminsController extends \yii\easyii\components\Controller
{
    public function actionIndex()
    {
        $data = new ActiveDataProvider([
            'query' => User::findByRole('administrator')->desc(),
        ]);

        if(Yii::$app->request->isAjax){
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            return $data->models;
        }

        Yii::$app->user->setReturnUrl(['/admin/admins']);

        return $this->render('index', [
            'data' => $data,
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
                        $model->image = Image::upload($model->image, 'admins');
                    }
                }

                $model->status = User::STATUS_ON;

                if($model->save()){
                    $role = new Assignment($model->id);
                    $success = $role->assign(['administrator']);

                    $this->flash('success', Yii::t('easyii', 'Admin created'));
                    return $this->redirect(['/admin/admins/assignment/'.$model->primaryKey]);
                }
                else{
                    $this->flash('error', Yii::t('easyii', 'Create error. {0}', $model->formatErrors()));
                }
            }
        }
        
        return $this->render('create', [
            'model' => $model
        ]);
    }

    public function actionEdit($id)
    {
        $model = User::findOne($id);

        if($model === null){
            $this->flash('error', Yii::t('easyii', 'Not found'));
            return $this->redirect(['/admin/admins']);
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
                    $this->flash('success', Yii::t('easyii', 'Admin updated'));
                    return $this->refresh();
                }
                else{
                    $this->flash('error', Yii::t('easyii', 'Update error. {0}', $model->formatErrors()));
                }

            }
        }
        
        return $this->render('edit', [
            'model' => $model
        ]);
    }

     public function actionAssignment($id)
     {
         $model = self::findModel($id);
 
         return $this->render('@easyii/modules/rbac/views/assignment/view', [
                 'model' => $model,
                 'idField' => 'id',
                 'usernameField' => 'username',
                 'fullnameField' => '',
         ]);
    }

     /**
     * Assign items
     * @param string $id
     * @return array
     */
    public function actionAssign($id)
    {
        $items = Yii::$app->getRequest()->post('items', []);
        $model = new Assignment($id);
        $success = $model->assign($items);
        Yii::$app->getResponse()->format = 'json';
        return array_merge($model->getItems(), ['success' => $success]);
    }

    /**
     * Assign items
     * @param string $id
     * @return array
     */
    public function actionRevoke($id)
    {
        $items = Yii::$app->getRequest()->post('items', []);
        $model = new Assignment($id);
        $success = $model->revoke($items);
        Yii::$app->getResponse()->format = 'json';
        return array_merge($model->getItems(), ['success' => $success]);
    }

    public function actionDelete($id)
    {
        if(($model = User::findOne($id))){
            $model->delete();
        } else {
            $this->error = Yii::t('easyii', 'Not found');
        }
        return $this->formatResponse(Yii::t('easyii', 'Admin deleted'));
    }

    public function actionChangePwd($id)
    {
        $model = User::findOne($id);

        if($model === null){
            $this->flash('error', Yii::t('easyii', 'Not found'));
            return $this->redirect(['/admin']);
        }

        if ($model->load(Yii::$app->request->post())) {
            if(Yii::$app->request->isAjax){
                Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
                return ActiveForm::validate($model);
            }
            else{
                if($model->save()){
                    $this->flash('success', Yii::t('easyii', 'Password updated'));
                }
                else{
                    $this->flash('error', Yii::t('easyii', 'Update error. {0}', $model->formatErrors()));
                }
                return $this->refresh();
            }
        }
        else {
            return $this->render('change_pwd', [
                'model' => $model
            ]);
        }
    }

    public function actionChangeRootPwd()
    {
        $model = new ChangeRootPassword;

        if ($model->load(Yii::$app->request->post())) {
            if(Yii::$app->request->isAjax){
                Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
                return ActiveForm::validate($model);
            }
            else{
                if($model->change()){
                    $this->flash('success', Yii::t('easyii', 'Password updated'));
                }
                else{
                    $this->flash('error', Yii::t('easyii', 'Update error. {0}', $model->formatErrors()));
                }
                return $this->refresh();
            }
        }

        return $this->render('change_root_pwd', [
            'model' => $model
        ]);
    }

    public function actionOn($id)
    {
        return $this->changeStatus($id, User::STATUS_ON);
    }

    public function actionOff($id)
    {
        return $this->changeStatus($id, User::STATUS_OFF);
    }

    protected function findModel($id)
    {
        $userClassName = Yii::$app->getUser()->identityClass;
        $userClassName = $userClassName ? : 'yii\easyii\modules\rbac\models\User';
        $class = $userClassName;
        if (($user = $class::findIdentity($id)) !== null) {
            return new Assignment($id, $user);
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}