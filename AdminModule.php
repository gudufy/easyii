<?php
namespace yii\easyii;

use Yii;
use yii\web\View;
use yii\base\Application;
use yii\base\BootstrapInterface;
use yii\helpers\Inflector;

use yii\easyii\models\Module;
use yii\easyii\models\Setting;
use yii\easyii\assets\LiveAsset;

class AdminModule extends \yii\base\Module implements BootstrapInterface
{
    const VERSION = 0.9;

    public $settings;
    public $activeModules;
    public $controllerLayout = '@easyii/views/layouts/main';

    private $_installed;
    
     /**
      * @var array Nav bar items.
      */
     public $navbar;
     /**
      * @var array
      * @see [[menus]]
      */
     private $_menus = [];
     /**
      * @var array
      * @see [[menus]]
      */
     private $_coreItems = [
         'user' => 'Users',
         'assignment' => 'Assignments',
         'role' => 'Roles',
         'permission' => 'Permissions',
         'route' => 'Routes',
         'rule' => 'Rules',
         'menu' => 'Menus',
     ];
     /**
      * @var array
      * @see [[items]]
      */
     private $_normalizeMenus;
 
     /**
      * @var string Default url for breadcrumb
      */
     public $defaultUrl;
 
     /**
      * @var string Default url label for breadcrumb
      */
     public $defaultUrlLabel;

    public function init()
    {
        parent::init();
        if (!isset(Yii::$app->i18n->translations['rbac-admin'])) {
            Yii::$app->i18n->translations['rbac-admin'] = [
                'class' => 'yii\i18n\PhpMessageSource',
                'sourceLanguage' => 'en-US',
                'basePath' => '@easyii/modules/rbac/messages',
            ];
        }

        if (Yii::$app->cache === null) {
            throw new \yii\web\ServerErrorHttpException('Please configure Cache component.');
        }

        $this->activeModules = Module::findAllActive();

        $modules = [];
        $modules['rbac']['class'] = 'yii\easyii\modules\rbac\Module';
        foreach ($this->activeModules as $name => $module) {
            $modules[$name]['class'] = $module->class;
            if (is_array($module->settings)) {
                $modules[$name]['settings'] = $module->settings;
            }
        }
        $this->setModules($modules);

        if (Yii::$app instanceof yii\web\Application) {
            define('IS_ROOT', !Yii::$app->user->isGuest && Yii::$app->user->identity->isRoot());
            define('LIVE_EDIT', !Yii::$app->user->isGuest && Yii::$app->session->get('easyii_live_edit'));
        }

        Yii::$app->set('mailer', [
            'class' => 'yii\swiftmailer\Mailer',
            'transport' => [
                'class' => 'Swift_SmtpTransport',
                'host' => Setting::get('smtp_host'),
                'username' =>Setting::get('smtp_username'),
                'password' => Setting::get('smtp_password'),
                'port' => Setting::get('smtp_port'),
                'encryption' => Setting::get('smtp_encryption'),
            ],
        ]);

        Yii::$app->set('ucpass', [
            'class' => 'yii\easyii\components\Ucpaas',
            'accountSid' => Setting::get('ucpass_sid'),
            'token' =>Setting::get('ucpass_token'),
            'appId' => Setting::get('ucpass_appId'),
            'templateId' => Setting::get('ucpass_templateId'),
        ]);

        if (class_exists('yii\jui\JuiAsset')) {
            Yii::$container->set('yii\easyii\modules\rbac\AutocompleteAsset', 'yii\jui\JuiAsset');
        }
    }

    public function bootstrap($app)
    {
        Yii::setAlias('easyii', '@vendor/gudufy/easyii');

        if (!$app->user->isGuest && isset($app->param['liveEdit'])){
            if (($app->user->can('/admin/*') || $app->user->identity->isRoot()) && strpos($app->request->pathInfo, 'admin') === false) {
                $app->on(Application::EVENT_BEFORE_REQUEST, function () use ($app) {
                    $app->getView()->on(View::EVENT_BEGIN_BODY, [$this, 'renderToolbar']);
                });
            }
        }
        
    }

    public function renderToolbar()
    {
        $view = Yii::$app->getView();
        echo $view->render('@easyii/views/layouts/frontend-toolbar.php');
    }

    public function getInstalled()
    {
        if ($this->_installed === null) {
            try {
                $this->_installed = Yii::$app->db->createCommand("SHOW TABLES LIKE 'easyii_%'")->query()->count() > 0 ? true : false;
            } catch (\Exception $e) {
                $this->_installed = false;
            }
        }
        return $this->_installed;
    }

    /**
     * Get available menu.
     * @return array
     */
     public function getMenus()
     {
         if ($this->_normalizeMenus === null) {
             $mid = '/' . $this->getUniqueId() . '/';
             // resolve core menus
             $this->_normalizeMenus = [];
 
             $config = components\Configs::instance();
             $conditions = [
                 'user' => $config->db && $config->db->schema->getTableSchema($config->userTable),
                 'assignment' => ($userClass = Yii::$app->getUser()->identityClass) && is_subclass_of($userClass, 'yii\db\BaseActiveRecord'),
                 'menu' => $config->db && $config->db->schema->getTableSchema($config->menuTable),
             ];
             foreach ($this->_coreItems as $id => $lable) {
                 if (!isset($conditions[$id]) || $conditions[$id]) {
                     $this->_normalizeMenus[$id] = ['label' => Yii::t('rbac-admin', $lable), 'url' => [$mid . $id]];
                 }
             }
             foreach (array_keys($this->controllerMap) as $id) {
                 $this->_normalizeMenus[$id] = ['label' => Yii::t('rbac-admin', Inflector::humanize($id)), 'url' => [$mid . $id]];
             }
 
             // user configure menus
             foreach ($this->_menus as $id => $value) {
                 if (empty($value)) {
                     unset($this->_normalizeMenus[$id]);
                     continue;
                 }
                 if (is_string($value)) {
                     $value = ['label' => $value];
                 }
                 $this->_normalizeMenus[$id] = isset($this->_normalizeMenus[$id]) ? array_merge($this->_normalizeMenus[$id], $value)
                 : $value;
                 if (!isset($this->_normalizeMenus[$id]['url'])) {
                     $this->_normalizeMenus[$id]['url'] = [$mid . $id];
                 }
             }
         }
         return $this->_normalizeMenus;
     }
 
     /**
      * Set or add available menu.
      * @param array $menus
      */
     public function setMenus($menus)
     {
         $this->_menus = array_merge($this->_menus, $menus);
         $this->_normalizeMenus = null;
     }
}
