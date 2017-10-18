if( jQuery('form#account_subscriber_tigo_money').length )
{
    jQuery('input[name=number_subscriber_tigo_money]').focus();
}
jQuery('form#account_subscriber_tigo_money').submit(function (e) {
    e.preventDefault();
    var  number_suscribir = jQuery('input[name=number_subscriber_tigo_money]').val();
    number_suscribir = number_suscribir.replace(/[-.()\s]/g,'');
    jQuery('input[name=number_subscriber_tigo_money]').val(number_suscribir);

    var res = number_suscribir.substring(0, 2);
    var count = number_suscribir.length;

    if (count !== 10){
        jQuery(this).find('div.message_valid').html('<strong style="color: red;">Número debe ser de 10 dígitos</strong>');
        return;
    }

    if (res !== '09'){
        jQuery(this).find('div.message_valid').html('<strong style="color: red;">Número debe empezar por 09</strong>');
        return;
    }

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