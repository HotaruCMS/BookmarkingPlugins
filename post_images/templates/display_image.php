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
	?>
    <div class="post_image_wrapper">
		<?php if ($h->vars['link_action'] == 'source') { ?>
            <a href='<?php echo $h->post->origUrl; ?>' <?php echo $h->vars['target']; ?> class="click_to_source b">
                <?php echo '<img src="'.$img.'" alt="Image of '. $h->post->title.'" />'; ?>
            </a>
        <?php } else { ?>
            <a href='<?php echo $h->url(array('page'=>$h->post->id)); ?>' <?php echo $h->vars['target']; ?> class="click_to_post a">
                <?php echo '<img src="'.$img.'" alt="Image of '. $h->post->title.'" />'; ?>
            </a>
        <?php } ?>
	</div>
<?php } ?>