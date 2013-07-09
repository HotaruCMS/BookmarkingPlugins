/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

$('#activity_refresh').click(function() {       
   
  var fullString = $('ul#activity_items_list li:first').attr('id');
  var fromId = fullString.replace('activity_li_', '');
  
  var sendurl =  "/index.php?page=ajax_activity&fromId=" + fromId;

$.ajax(
            {
            type: 'get',
                    url: sendurl,
                    cache: false,
                    //data: formdata,
                    beforeSend: function () {                                    
                                    $('#activity_items_list').prepend('.....');
                            },
                    error: 	function(XMLHttpRequest, textStatus, errorThrown) {                                                                    
                                    $('#activity_items_list').prepend('ERROR');                                    
                    },
                    success: function(data) { // success means it returned some form of json code to us. may be code with custom error msg                                                                                                                                                   
                                    $('#activity_items_list').prepend(data).fadeIn(2000);
                                   
                                    
                                     
                    },
                    dataType: 'html'
    });
});