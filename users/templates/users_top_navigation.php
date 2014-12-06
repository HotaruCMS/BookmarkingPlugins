<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
?>

 <li class="dropdown">
    <a class="dropdown-toggle" data-toggle="dropdown" href="#" id="user-dropdown-toggle">
        People <b class="caret"></b>
    </a>
    <ul class="dropdown-menu">
        <li class="posts" data-name="people">
            <a href="<?php echo $h->url(array('page' => 'users')); ?>"><i class="fa fa-search"></i> Browse</a>
        </li>
        <?php if ($h->currentUser->loggedIn) { ?>
        <li class="divider"></li>
        <li class="posts" data-name="you">
            <a href="<?php echo $h->url(array('page' => 'profile')); ?>"><i class="fa fa-user"></i> You</a>
        </li>
        <?php if ($h->isActive('follow')) { ?>
        <li class="posts" data-name="followers">
            <a href="<?php echo $h->url(array('page' => 'followers')); ?>">Followers</a>
        </li>
        <li class="posts" data-name="following">
            <a href="<?php echo $h->url(array('page' => 'following')); ?>">Following</a>
        </li>
        <?php } ?>
        <?php if ($h->isActive('messaging')) { ?>
        <li class="divider"></li>
        <li class="posts" data-name="messages">
            <a href="<?php echo $h->url(array('page' => 'inbox')); ?>"><i class="fa fa-inbox"></i> Messages</a>
        </li>
        <?php } ?>
        <?php } ?>
    </ul>
</li>