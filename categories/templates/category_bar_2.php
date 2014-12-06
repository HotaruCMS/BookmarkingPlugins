<div class="navbar" role="navigation">
  <div class="navbar-inner">
    <div class="container">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
        </button>
        </div>
      <div class="navbar-collapse collapse">
        <ul class="nav navbar-nav">        
          
            <?php $h->pluginHook('category_bar_start'); ?>
            <?php echo $h->categoriesDisplay; ?>
            <?php $h->pluginHook('category_bar_end'); ?>
          
        </ul>
        <?php $h->pluginHook('search_box_nav'); ?>
      </div><!-- /.nav-collapse -->
    </div><!-- /.container -->
  </div><!-- /.navbar-inner -->
</div><!-- /.navbar -->