/* **************************************************************************************************** 
 *  File: /javascript/bookmarking.js
 *  Purpose: Fetches the title of the url being submitted
 *  Notes: This file is part of the Vote plugin.
 *  License:
 *
 *   This file is part of Hotaru CMS (http://www.hotarucms.org/).
 *
 *   Hotaru CMS is free software: you can redistribute it and/or modify it under the terms of the 
 *   GNU General Public License as published by the Free Software Foundation, either version 3 of 
 *   the License, or (at your option) any later version.
 *
 *   Hotaru CMS is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without 
 *   even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License along with Hotaru CMS. If not, 
 *   see http://www.gnu.org/licenses/.
 *   
 *   Copyright (C) 2009 Hotaru CMS - http://www.hotarucms.org/
 *
 *
 **************************************************************************************************** */

/* Hide/Show Alert choices below each story */

$(document).ready(function(){
        
	// Show/Hide box 
	$(".alert_link").click(function () {	
                // post
                var post = $(this).parents('div .show_post');
                var id = post.attr('id').replace(/show_post_/, '');
                //alert(id);
		
                // the alet flags
                var flags = $('li.alert_choices');                
                flags.toggle();
                
                // target
                //var target = $(this).parents('div').next('div.show_post_extras').find('li.alert_choices').first();            
                var target = $(this).parents('div').next('div.show_post_extras');
                flags.prependTo(target);
                               
                // the links will have no post id, so find the //3 or //1 or //2 reference and change it to ?page=post&id=5042&alert=3
                flags.find('.btn').each(function() {
                    // for friendly urls
                    var href = $(this).attr('href').replace(/(\/)(#|\b[0-9]+\b)(\/alert)/, '/' + id + '/alert');
                    $(this).attr('href', href);
                    // do it again in case of non-friendly urls
                    var href = $(this).attr('href').replace(/(page=)(#|\b[0-9]+\b)/, 'page=' + id);
                    $(this).attr('href', href);
                });
                
                return false;
        });
        
        
//        function PostViewModel(){
//                this.post_title = ko.observable();
//                this.post_votes_up = ko.observable();
//                //this.Content = ko.observable();            
//        };
////        
//        var viewModel = new PostViewModel();
//        ko.applyBindings(viewModel);
//
//        //var viewModel = ko.mapping.fromJS(data);
////var viewModel;
//        // for passing data back to php
//        //var data = ko.toJS({"data":this.PostViewModel});
//        
//        var request = $.ajax({
//            url: "http://ipadrank.com/index.php?page=ajax_bookmarking",
//            type: 'get',
//            dataType: 'JSON'
//        });
//        
//        request.done(function (data) { 
//            //var parsed = JSON.parse(data);            
//            viewModel.post_votes_up(data.post_votes_up);
//            //viewModel.post_votes_up(5);
//            //this.DataArray = ko.mapping.fromJS([]); 
//            // var data = JSON.parse(result);
//            //viewModel = ko.mapping.fromJS(data);            
//            //ko.mapping.fromJS(data, viewModel);
//            
//            //viewModel.post_votes_up(result['post_votes_up']);
//            viewModel.post_title('test');
//        });
        
        
        
});