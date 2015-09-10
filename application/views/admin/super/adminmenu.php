  <nav class="navbar">
    <div class="navbar-header">
    	<button class="navbar-toggle" type="button" data-toggle="collapse" data-target=".js-navbar-collapse">
			<span class="sr-only">Toggle navigation</span>
			<span class="icon-bar"></span>
			<span class="icon-bar"></span>
			<span class="icon-bar"></span>
		</button>
		<a class="navbar-brand" href="<?php echo $this->createUrl("/admin/survey/sa/index"); ?>"><?php echo $sitename; ?></a>
	</div>
	
	<div class="collapse navbar-collapse js-navbar-collapse">
		<ul class="nav navbar-nav">
            
		</ul>
		
        <ul class="nav navbar-nav navbar-right">

        <?php $this->renderPartial( "/admin/super/_configuration_menu" ); ?>

        <li class="dropdown">
          <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false"><?php eT("Surveys");?> <span class="caret"></span></a>
          <ul class="dropdown-menu" role="menu">
            <li><a href="<?php echo $this->createUrl("admin/survey/sa/newsurvey"); ?>"><?php eT("Create a new survey");?></a></li>
            <li class="divider"></li>
            <li><a href="<?php echo $this->createUrl("admin/survey/sa/listsurveys"); ?>"><?php eT("List surveys");?></a></li>
          </ul>
        </li>

			
        	
        <li class="dropdown">
          <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false" ><?php echo Yii::app()->session['user'];?> <span class="caret"></span></a>
          <ul class="dropdown-menu" role="menu">
            <li><a href="<?php echo $this->createUrl("/admin/user/sa/personalsettings"); ?>"><?php eT("Edit your personal preferences");?></a></li>
            <li class="divider"></li>
            <li><a href="<?php echo $this->createUrl("admin/authentication/sa/logout"); ?>"><?php eT("Logout");?></a></li>
          </ul>
        </li>

        <li><a href="#">0 active surveys</a></li>
      </ul>
	</div><!-- /.nav-collapse -->
  </nav>