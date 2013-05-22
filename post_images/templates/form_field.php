<?php
/**
 * Create the form field for the Post Images PLugin 
 */

$url = $h->post->vars['img'];
if(strlen($url) > 0 && !strstr($url,'http://')){
	$url = SITEURL.'content/images/post_images/'.$url;
}
?>
<tr>
    <td colspan="2" style="background-color:#EEE;">
    	<?php echo $h->lang['post_images_form_intro']; ?>
        <div style="text-align:center; padding:5px; margin:5px; background-color:#FFF;">
            <input id="post_img" name="post_img" type="text" value="<?php echo $url; ?>" style="width:30em;" />
            <button onclick="changeImage(); return false;"><?php echo $h->lang['post_images_button']; ?></button>
            <input id="post_img_coords" name="post_img_coords" type="hidden" value="<?php if(isset($h->post->vars['img_coords'])) echo $h->post->vars['img_coords']; ?>" />
        </div>
        <div style="margin-bottom:5px;">
            
                Click <a href="#" onclick="getRemoteImages('<?php
            
            if($h->pageName == 'submit2') echo $h->vars['submitted_data']['submit_orig_url'];
            else echo $h->vars['submit_orig_url'];
            
            ?>'); return false;">here</a> to load images (again).<a href="#" onclick="$('#thumbs_from_source_space').toggle(); return false;" style="float:right;">Toggle thumbs</a>
            
            
	            <?php echo $h->lang['post_images_ie_msg']; ?>
            
        </div>
        
    	<div id="thumbs_from_source_space">
        </div>
        <div id="get_image_original_size"></div>
        <div><a href="#" onclick="$('#cropper_table').toggle(); return false;" style="float:right;">Toggle cropper</a></div>
        <table border="0" width="100%" id="cropper_table">
        	<tr>
            	<td id="post_image_image_field">
                	Crop image
                    <div id="cropbox_wrapper">
                    </div>
                </td>
            	<td>
                	Preview: <small>(100%)</small>
                    <div id="preview_wrapper">
                    </div>
                </td>
            </tr>
        </table>
        <script type="text/javascript">
		$(document).ready(function(){
			changeImage();
			getRemoteImages('<?php
		
		if($h->pageName == 'submit2') echo $h->vars['submitted_data']['submit_orig_url'];
		else echo $h->vars['submit_orig_url'];
		
		?>');
		});
		</script>
    </td>
</tr>