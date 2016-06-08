jQuery(document).ready(function(){
  jQuery("#wdm_message").hide();
	jQuery( "#wpbody-content" ).delegate( ".wdm_save_templates", "click", function() {
 	//console.log(jQuery("#whiltelisted_ips").val());
 	var whitelisted_ips=jQuery("#whiltelisted_ips").val();
 	var social_traffic=jQuery("#social_traffic").val();
 	jQuery.ajax({
 		type: 'POST',
 		url:ajax_url,
    data: {
                action: 'save_ip_templates',
                whitelisted_ips: whitelisted_ips,
                social_traffic:social_traffic
                },
            success: function (response) {//response is value returned from php
            	console.log('response : '+response);
              jQuery("#wdm_message").empty();
              jQuery("#wdm_message").show();
              jQuery('#wdm_message').removeClass('error');
              jQuery('#wdm_message').addClass('success');
              jQuery('#wdm_message').append('<p>Templates Saved Successfully</p>');
            }
 	});
  	return false;
});


jQuery( "#wpbody-content" ).delegate( ".wdm_add_social_domains", "click", function() {
 // console.log(jQuery("#whiltelisted_ips").val());
  var wdm_social_domain=jQuery("#wdm_social_domain").val();
 // var social_traffic=jQuery("#social_traffic").val();
  jQuery.ajax({
     type: 'POST',
    url:ajax_url,
    data: {
                action: 'save_social_domain',
                wdm_social_domain: wdm_social_domain,
                },
            success: function (response) {//response is value returned from php
              //console.log('response : '+response);
             // var domains = jQuery('#social_domains').val();
              //domains =domains +  response;
             // jQuery('#social_domains').val(domains);
             if(response == 'false' ){
              jQuery("#wdm_message").empty();
              jQuery("#wdm_message").show();
              jQuery('#wdm_message').addClass('error');
              jQuery('#wdm_message').append('<p>Domain Already Exists</p>');
             }
             else{
             jQuery('#wdm_social_domain_table').append(response);
              jQuery("#wdm_message").empty();
              jQuery("#wdm_message").show();
              jQuery('#wdm_message').removeClass('error');
              jQuery('#wdm_message').addClass('success');
              jQuery('#wdm_message').append('<p>Domain Added Successfully</p>');
            }
            }
  });
    return false;
});

jQuery( "#wpbody-content" ).delegate( ".wdm_remove_social_domain", "click", function() {
 // console.log(jQuery("#whiltelisted_ips").val());
  var wdm_social_domain=jQuery(this).closest('tr').attr('id');
  //console.log(wdm_social_domain);
 // var social_traffic=jQuery("#social_traffic").val();
 jQuery(this).parent().parent().remove();
  jQuery.ajax({
     type: 'POST',
    url:ajax_url,
    data: {
                action: 'delete_social_domain',
                wdm_social_domain: wdm_social_domain,
                },
            success: function (response) {//response is value returned from php
              //console.log('response : '+response);
              //var domains = jQuery('#social_domains').val();
              //domains =domains +  response;
              //jQuery('#social_domains').val(domains);
              jQuery("#wdm_message").empty();
              jQuery("#wdm_message").show();
              jQuery('#wdm_message').addClass('success');
              jQuery('#wdm_message').append('<p>Domain Deleted Successfully</p>');
            }
  });
   // return false;
});
jQuery('#wdm_subscription_tip').mouseover(function(){
  jQuery('#tiptip_content').html("Please enter IP address after creating subscription");
});

});

