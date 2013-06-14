<div class="navbar">
  <div class="navbar-inner">
    <div class="container">
      <a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </a>
<!--      <small><a class="brand" style="text-shadow: 0px 2px 3px #eee; font-weight: 100; font-size:16px;" href="#">Categories</a></small>-->
      <div class="nav-collapse">
        <ul class="nav">          
          
            <?php $h->pluginHook('category_bar_start'); ?>
            <?php echo $h->vars['output']; ?>
            <?php $h->pluginHook('category_bar_end'); ?>
          
        </ul>
        <?php $h->pluginHook('search_box_nav'); ?>
      </div><!-- /.nav-collapse -->
    </div><!-- /.container -->
  </div><!-- /.navbar-inner -->
</div><!-- /.navbar -->