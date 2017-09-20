<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Breadcrumbs;
use yii\easyii\assets\AdminAsset;
use yii\easyii\helpers\Data;
use yii\easyii\models\Setting;

$asset = AdminAsset::register($this);
$moduleName = $this->context->module->id;
?>
<?php $this->beginPage() ?>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>"/>
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?> - <?=Setting::get('site_name') ?></title>
    <link rel="shortcut icon" href="<?= $asset->baseUrl ?>/favicon.ico" type="image/x-icon">
    <link rel="icon" href="<?= $asset->baseUrl ?>/favicon.ico" type="image/x-icon">
    <?php $this->head() ?>
</head>
<body class="hold-transition skin-blue sidebar-mini">
<?php $this->beginBody() ?>
<div class="wrapper" id="admin-body">
    <header class="main-header">
        <!-- Logo -->
        <a href="<?= Url::to(['/site/index']) ?>" class="logo">
        <!-- mini logo for sidebar mini 50x50 pixels -->
        <span class="logo-mini">CMS</span>
        <!-- logo for regular state and mobile devices -->
        <span class="logo-lg"><?=Setting::get('site_name') ?></span>
        </a>
        <!-- Header Navbar: style can be found in header.less -->
        <nav class="navbar navbar-static-top">
        <!-- Sidebar toggle button-->
        <a href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
        </a>

        <div class="navbar-custom-menu">
            <ul class="nav navbar-nav">
            <!-- User Account: style can be found in dropdown.less -->
            <li class="dropdown user user-menu">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                <img src="http://cdn.bootcss.com/admin-lte/2.3.11/img/user2-160x160.jpg" class="user-image" alt="User Image">
                <span class="hidden-xs"><?= Yii::t('easyii','Welcome').', '.Yii::$app->user->identity->username ?></span>
                </a>
                <ul class="dropdown-menu">
                    <li class="user-footer">
                        <?php if(!IS_ROOT) : ?>
                        <div class="pull-left" style="width:45%;">
                            <?= Html::a(
                                Yii::t('easyii','Edit password'),
                                ['/admin/admins/change-pwd/'.Yii::$app->user->identity->id],
                                ['class' => 'btn btn-default btn-flat btn-block']
                            ) ?>
                        </div>
                        <?php endif; ?>
                        <div class="pull-right" style="<?=IS_ROOT ? 'width:90%;' : 'width:45%;' ?>">
                            <?= Html::a(
                                Yii::t('easyii','Logout'),
                                ['/admin/sign/out'],
                                ['data-method' => 'post', 'class' => 'btn btn-default btn-flat btn-block']
                            ) ?>
                        </div>
                    </li>
                </ul>
            </li>
            </ul>
        </div>
        </nav>
    </header>
    <!-- Left side column. contains the sidebar -->
  <aside class="main-sidebar">
    <!-- sidebar: style can be found in sidebar.less -->
    <section class="sidebar">
      <!-- sidebar menu: : style can be found in sidebar.less -->
      <ul class="sidebar-menu">
        <li class="header">MAIN NAVIGATION</li>
        <li>
          <a href="<?= Url::to(['/admin/default/index']) ?>">
            <i class="fa fa-dashboard"></i> <span><?=Yii::t('easyii','Dashboard') ?></span>
          </a>
        </li>
        <li class="treeview<?= ($moduleName != 'rbac' && $moduleName != 'admin') ? ' active' :'' ?>">
          <a href="#">
            <i class="fa fa-edit"></i>
            <span><?= Yii::t('easyii', 'Content Manage') ?></span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu">
            <?php foreach(Yii::$app->getModule('admin')->activeModules as $module) : ?>
                <li class="<?= ($moduleName == $module->name ? 'active' : '') ?>">
                <a href="<?= Url::to(["/admin/$module->name"]) ?>" class="menu-item">
                    <i class="fa fa-circle-o"></i>  <?=$module->title?>
                    <?php if($module->notice > 0) : ?>
                        <span class="badge"><?= $module->notice ?></span>
                    <?php endif; ?>
                </a></li>
            <?php endforeach; ?>
          </ul>
        </li>  
        <li class="<?= ($moduleName == 'admin' && $this->context->id == 'users') ? 'active' :'' ?>"><a href="<?= Url::to(['/admin/users']) ?>" class="menu-item">
                <i class="glyphicon glyphicon-user"></i>
                <span><?= Yii::t('easyii', 'Users') ?></span>
            </a></li>
        <li class="header">System</li>
        <li class="<?= ($moduleName == 'admin' && $this->context->id == 'settings') ? 'active' :'' ?>"><a href="<?= Url::to(['/admin/settings']) ?>" class="menu-item">
            <i class="glyphicon glyphicon-cog"></i>
            <span><?= Yii::t('easyii', 'Settings') ?></span>
        </a></li>
        <?php if(IS_ROOT) : ?>
        <li class="treeview<?= ($moduleName == 'rbac') ? ' active' :'' ?>">
          <a href="#">
            <i class="fa fa-pie-chart"></i>
            <span>权限</span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu">
            <li class="<?= ($moduleName == 'rbac' && $this->context->id == 'route') ? 'active' :'' ?>"><a href="<?= Url::to(['/admin/rbac/route']) ?>"><i class="fa fa-circle-o"></i> 路由</a></li>                                    
            <li class="<?= ($moduleName == 'rbac' && $this->context->id == 'permission') ? 'active' :'' ?>"><a href="<?= Url::to(['/admin/rbac/permission']) ?>"><i class="fa fa-circle-o"></i> 权限</a></li>                                    
            <li class="<?= ($moduleName == 'rbac' && $this->context->id == 'role') ? 'active' :'' ?>"><a href="<?= Url::to(['/admin/rbac/role']) ?>"><i class="fa fa-circle-o"></i> 角色</a></li>                                    
            <li class="<?= ($moduleName == 'rbac' && $this->context->id == 'assignment') ? 'active' :'' ?>"><a href="<?= Url::to(['/admin/rbac/assignment']) ?>"><i class="fa fa-circle-o"></i> 分配</a></li>                                    
          </ul>
        </li>  
        <?php endif; ?>
        
            <li class="<?= ($moduleName == 'admin' && $this->context->id == 'logs') ? 'active' :'' ?>"><a href="<?= Url::to(['/admin/logs']) ?>" class="menu-item">
                <i class="glyphicon glyphicon-align-justify"></i>
                <span><?= Yii::t('easyii', 'Logs') ?></span>
            </a></li>
        <?php if(IS_ROOT) : ?>
            <li class="<?= ($moduleName == 'admin' && $this->context->id == 'modules') ? 'active' :'' ?>"><a href="<?= Url::to(['/admin/modules']) ?>" class="menu-item">
                <i class="glyphicon glyphicon-folder-close"></i>
                <span><?= Yii::t('easyii', 'Modules') ?></span>
            </a></li>
            
            <li class="<?= ($moduleName == 'admin' && $this->context->id == 'system') ? 'active' :'' ?>"><a href="<?= Url::to(['/admin/system']) ?>" class="menu-item">
                <i class="glyphicon glyphicon-hdd"></i>
                <span><?= Yii::t('easyii', 'System') ?></span>
            </a></li>
            
        <?php endif; ?>
      </ul>
    </section>
    <!-- /.sidebar -->
  </aside>

  <!-- =============================================== -->

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
        <?= $this->title ?>
      </h1>
      <?=
        Breadcrumbs::widget([
            'itemTemplate' => "<li><i>{link}</i></li>\n", // template for all links
            'links' => [
                ['label' => Yii::t('easyii', 'Dashboard'), 'url' => ['/admin/default/index']],
                $this->title,
            ],
        ]);
         ?>
    </section>

    <!-- Main content -->
    <section class="content">
        <?php foreach(Yii::$app->session->getAllFlashes() as $key => $message) : ?>
            <div class="alert alert-<?= $key ?>"><?= $message ?></div>
            <?php $this->registerJs('setTimeout(function(){$(".alert").hide();},2000)'); ?>
        <?php endforeach; ?>
        <?php if (isset($this->blocks['content-top'])) { ?>
        <?= $this->blocks['content-top'] ?>
        <?php } ?>
        <div class="row">
            <div class="col-xs-12">
                <div class="box">
                <!-- /.box-header -->
                <div class="box-body">
                    <?= $content ?>
                </div>
                <!-- /.box-body -->
                </div>
                <!-- /.box -->
            </div>
            <!-- /.col -->
        </div>
        <!-- /.row -->
        <?php if (isset($this->blocks['content-footer'])) { ?>
        <?= $this->blocks['content-footer'] ?>
        <?php } ?>
        
    </section>
    <!-- /.content -->
  </div>
  <!-- /.content-wrapper -->

  <footer class="main-footer">
    <div class="pull-right hidden-xs">
      <b>Version</b> 2.3.11
    </div>
    <strong>Copyright &copy; <?= date('Y') ?> <a href="http://www.wang-zhan.cn" target="_blank">PLS</a>.</strong> All rights
    reserved.
  </footer>
</div>
<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
