<?php
/**
 * Display the image for the Post Images Plugin 
 */
if(isset($h->post->vars['img']) && strlen($h->post->vars['img']) > 0){
	$img = $h->post->vars['img'];
	if(substr($img,0,32) != 'http://images.sitethumbshot.com/'){
		// Make it 0.1 compatible and add SITEURL
		$img = SITEURL.'content/images/post_images/' . preg_replace('/.*content\/images\/post_images\//','',$img);
	}
        $img = '<img class="media-object" src="'.$img.'" alt="Image of '. $h->post->title.'" title="Image of '. $h->post->title.'" width="' . $h->vars['post_images_settings']['w'] . '" height="' . $h->vars['post_images_settings']['h'] . '" />';
	$pull = !isset($h->vars['post_images_settings']['pullRight']) ||  $h->vars['post_images_settings']['pullRight'] == 'unchecked' ? 'post_image_wrapper' : 'pull-right';  
        ?>

        <div class="<?php echo $pull;?>">
            <?php if ($h->vars['link_action'] == 'source') { ?>
            <a class='' href='<?php echo $h->post->origUrl; ?>' <?php echo $h->vars['target']; ?> class="click_to_source b">
                <?php echo $img; ?>
            </a>
            <?php } else { ?>
            <a class='' href='<?php echo $h->url(array('page'=>$h->post->id)); ?>' <?php echo $h->vars['target']; ?> class="click_to_post a">
                <?php echo $img?>
            </a>
            <?php } ?>
	</div>

<?php } ?>