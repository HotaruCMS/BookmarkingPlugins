<?php
/**
 * Create the crop scrip for the Post Images PLugin 
 */
$cropboxwidth = 450 - $h->vars['post_images_settings']['w'];
?>
<script type="text/javascript">
var SITEURL = '<?php echo SITEURL; ?>';
var imgWidth = <?php echo $h->vars['post_images_settings']['w']; ?>; // default w
var imgHeight = <?php echo $h->vars['post_images_settings']['h']; ?>; // default h
var imgSize;
var JcropObject;
function changeImage(){
	imgLink = $('#post_img').val();
	$('#cropbox_wrapper').addClass('loading');
	var img = new Image();
	$(img).load(function() {    // when image has loaded...
		$(this).css('display', 'none'); // hide image by default
		$('#cropbox_wrapper, #preview_wrapper').removeClass('loading').html('<img src="'+imgLink+'" title="" alt="" />');
		orig_size = originalImgSize('<img src="'+imgLink+'" alt="" title="" />');
		$('#cropbox_wrapper img').attr('id','cropbox');
		$('#preview_wrapper img').attr('id','preview').show();
		imgHeight = parseInt($('#cropbox').height());
		imgWidth = parseInt($('#cropbox').width());
		thumbAspectRatio = <?php echo $h->vars['post_images_settings']['w']; ?>/<?php echo $h->vars['post_images_settings']['h']; ?>;
		imgAspectRatio = imgWidth / imgHeight;
		boxHeight = imgHeight;
		boxWidth = imgWidth;
		offsetY = 0;
		offsetX = 0;		
		if(thumbAspectRatio >= imgAspectRatio){
			boxWidth = imgWidth;
			boxHeight = imgWidth *1/thumbAspectRatio;
			offsetY = (imgHeight - boxHeight)/2;
			offsetX = 0;
		}
		else {
			boxHeight = imgHeight;
			boxWidth = imgHeight * thumbAspectRatio;
			offsetY = 0;
			offsetX = (imgWidth - boxWidth)/2;
		}
		boxX1 = offsetX;
		boxY1 = offsetY;
		boxX2 = offsetX+boxWidth;
		boxY2 = offsetY+boxHeight;
		
		minSizeX = <?php echo $h->vars['post_images_settings']['w']; ?>;
		minSizeY = <?php echo $h->vars['post_images_settings']['h']; ?>;
		if(orig_size[0] > <?php echo $cropboxwidth; ?>){
			minSizeX = <?php echo $cropboxwidth; ?>/orig_size[0]*<?php echo $h->vars['post_images_settings']['w']; ?>;
		}
		if(orig_size[1] > <?php echo $cropboxwidth; ?>){
			minSizeY = <?php echo $cropboxwidth; ?>/orig_size[0]*<?php echo $h->vars['post_images_settings']['h']; ?>;
		}
		$('#cropbox').Jcrop({
			onChange: showPreview,
			onSelect: showPreview,
			boxWidth: imgWidth,
			boxHeight: imgHeight,
			aspectRatio: thumbAspectRatio,
			minSize: [minSizeX,minSizeY], // default w, h
			<?php
			if(isset($h->vars['submitted_data']['submit_img_coords']) && strlen($h->vars['submitted_data']['submit_img_coords']) > 0){
				$coordsArray = explode(' , ',$h->vars['submitted_data']['submit_img_coords']);
				$posx1 = (int) trim($coordsArray[0]);
				$posy1 = (int) trim($coordsArray[1]);
				$posx2 = (int) trim($coordsArray[2]);
				$posx2 = $posx2 + $posx1;
				$posy2 = (int) trim($coordsArray[3]);
				$posy2 = $posy2 + $posy1;
				echo 'setSelect: ['. $posx1 .','. $posy1 .','. $posx2 .','. $posy2 .']';
			}
			else echo 'setSelect: [boxX1,boxY1,boxX2,boxY2]';
			?>
		});
	}).attr('src', imgLink);
}
function showPreview(coords){
	if (parseInt(coords.w) > 0){
		var rx = <?php echo $h->vars['post_images_settings']['w']; ?> / coords.w; 
		var ry = <?php echo $h->vars['post_images_settings']['h']; ?> / coords.h;
		jQuery('#preview').css({
			width: Math.round(rx*imgWidth) + 'px',
			height: Math.round(ry*imgHeight) + 'px',
			marginLeft: '-' + Math.round(rx * coords.x) + 'px',
			marginTop: '-' + Math.round(ry * coords.y) + 'px'
		});
	}
	setImageCoords(coords);
}
function setImageCoords(c){
	$('#post_img_coords').val(c.x+' , '+c.y+' , '+c.w+' , '+c.h+' , '+imgWidth+' , '+imgHeight);
}
function getRemoteImages(url){
	$('#thumbs_from_source_space').html('<div id="thumb_loading_messasge"><?php echo $h->lang['post_images_loading_msg']; ?></div>');
	$.post('<?php echo parse_url(SITEURL, PHP_URL_PATH);?>index.php',{ type:'postImages', url:url }, function(data) {
		images_found = $(data).find('img');
		$('#thumb_loading_messasge').remove();		
		images_found.each(function(){
			current_image = $(this);
			var img = new Image();
			$(img).load(function(){
				orig_size = originalImgSize($(this));
				height = orig_size[1];
				width = orig_size[0];				
				if(width >= <?php echo $h->vars['post_images_settings']['w']; ?> && height >= <?php echo $h->vars['post_images_settings']['h']; ?>){					
					$('#thumbs_from_source_space').append('<a href="#" onclick="selectPostImage($(this)); return false;"></a>');
					$('#thumbs_from_source_space a:last').append(this);					
				}
			}).attr({
				src: $(this).attr('src'),
				alt: $(this).attr('alt'),
				title: $(this).attr('title')
			})
		});
	});
}
function selectPostImage(imglink){
	src = imglink.children('img').attr('src');
	$('#post_img').val(src);
	changeImage();
}
function originalImgSize(html){
	$('#get_image_original_size').html(html);
	height = $('#get_image_original_size img:first').height();
	width = $('#get_image_original_size img:first').width();
	$('#get_image_original_size').html('');
	return [width,height];
}
</script>
<style type="text/css">
#thumbs_from_source_space {
}
#thumbs_from_source_space a {
	padding:1px;
    float:left;
    border:none;
	display:block;
	height:50px;
	width:50px;
	border:#CCC solid 1px;
	margin:1px;
	text-align:center;
}
#thumbs_from_source_space a:hover {
	padding:1px;
	border:#666 solid 1px;
}
#thumbs_from_source_space img {
	max-height:50px;
	height:auto !important;
	height:50px;
	max-width:50px;
	width:auto !important;
	width:50px;
}
#cropbox_wrapper {
	width:<?php echo $cropboxwidth; ?>px;
}
#cropbox_wrapper img {
	max-width:<?php echo $cropboxwidth; ?>px;
	width: expression(this.width > <?php echo $cropboxwidth; ?> ? <?php echo $cropboxwidth; ?>: true);
}
#preview_wrapper {
	width:<?php echo $h->vars['post_images_settings']['w']; ?>px;
	height:<?php echo $h->vars['post_images_settings']['h']; ?>px;
	overflow:hidden;
}
#get_image_original_size { clear:both; border-bottom:#666 solid 1px; margin:5px 0; height:5px; }
</style>