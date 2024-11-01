jQuery(document).ready(function(){
   jQuery('.like_counter').click(function(event){
       event.preventDefault();
       var ele_count = jQuery(this).children().children('.count');
       jQuery(ele_count).text('...');
       var ele_msg = jQuery(this).children('.res-message');
       var product_id = jQuery(this).attr('data');
       var data = {
		'action': 'wcld_action',
                'type': 'like',
		'product_id': product_id // We pass php values differently!
	};
        
        jQuery.ajax({
            url: ajax_object.ajax_url,
            type: 'post',
            dataType: 'json',
            success: function (data) {
               if(data.type==false){
                    jQuery(ele_count).text(data.count);
                    jQuery(ele_msg).text('Your vote removed!');
                    jQuery(ele_msg).show().delay(3000).fadeOut();
                }
                else{
                    jQuery(ele_count).text(data.count);
                    jQuery(ele_msg).text('Thank you for you vote!');
                    jQuery(ele_msg).show().delay(3000).fadeOut();
                }
            },
            data: data
        });
   });
   jQuery('.unlike_counter').click(function(event){
       event.preventDefault();
       var ele_count = jQuery(this).children().children('.count');
       jQuery(ele_count).text('...');
       var ele_msg = jQuery(this).children('.res-message');
       var product_id = jQuery(this).attr('data');
       var data = {
		'action': 'wcld_action',
                'type': 'dislike',
		'product_id': product_id // We pass php values differently!
	};
	
       jQuery.ajax({
            url: ajax_object.ajax_url,
            type: 'post',
            dataType: 'json',
            success: function (data) {
                if(data.type==false){
                    jQuery(ele_count).text(data.count);
                    jQuery(ele_msg).text('Your vote removed!');
                    jQuery(ele_msg).show().delay(3000).fadeOut();
                }
                else{
                    jQuery(ele_count).text(data.count);
                    jQuery(ele_msg).text('Thank you for you vote!');
                    jQuery(ele_msg).show().delay(3000).fadeOut();
                }
            },
            data: data
        });
   });
});