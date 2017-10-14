if( jQuery('form#account_subscriber_tigo_money').length )         // use this if you are using id to check
{
    jQuery('input[name=number_subscriber_tigo_money]').focus();
}
jQuery('form#account_subscriber_tigo_money').submit(function (e) {
    e.preventDefault();
    var  number_suscribir =jQuery('input[name=number_subscriber_tigo_money]').val();

    jQuery.ajax({
        data : jQuery(this).serialize() + '&action=tigo_money_form',
        type: 'post',
        url: tigo_money_woo.ajaxurl,
        beforeSend : function(){
            jQuery('div.overlay-tigo-money-woo').show();
            jQuery('div.overlay-tigo-money-woo div.overlay-content-tigo-money-woo').append("<p><strong>"+tigo_money_woo.loading+"</strong></p>");
        },
        success: function(r) {
            var obj = JSON.parse(r);
            if (obj.status == true){
                jQuery('div.overlay-tigo-money-woo div.overlay-content-tigo-money-woo p strong').html('');
                jQuery('div.overlay-tigo-money-woo div.overlay-content-tigo-money-woo p strong').html(tigo_money_woo.message_redirect);
                window.location.replace(obj.url);
            }else{
                jQuery('div.overlay-tigo-money-woo div.overlay-content-tigo-money-woo img').hide();
                jQuery('div.overlay-tigo-money-woo div.overlay-content-tigo-money-woo p strong').html('');
                jQuery('div.overlay-tigo-money-woo div.overlay-content-tigo-money-woo p strong').html(obj.message);
            }
        },
        error: function(x, s, e) {
            jQuery('div.overlay-tigo-money-woo div.overlay-content-tigo-money-woo').html(x.responseText + s.status + e.error);
        }
    });
});