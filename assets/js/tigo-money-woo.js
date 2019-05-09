jQuery( function( $ ){
    'use strict';

    const checkout_form = $( 'form.woocommerce-checkout' );

    $(document.body).on('checkout_error', function () {
        swal.close();
    });

    checkout_form.on( 'checkout_place_order', function() {
        if($('form[name="checkout"] input[name="payment_method"]:checked').val() === 'tigo_money'){

            let number_tigo_money = checkout_form.find('#number_tigo_money').val();

            number_tigo_money = number_tigo_money.replace(/[-.()\s]/g,'');

            checkout_form.append($('<input name="number_tigo_money" type="hidden" />' ).val( number_tigo_money ));

            let numberMessage = checkNumber(number_tigo_money);

            if(numberMessage)
                checkout_form.append(`<input type="hidden" name="error_tigo_money" value="${numberMessage}">`);


            swal.fire({
                title: 'Espere por favor...',
                onOpen: () => {
                    swal.showLoading()
                },
                allowOutsideClick: false
            });
        }
    });

    function checkNumber(number){
        let numberData = number;
        let res = numberData.substring(0, 2);
        let count = numberData.length;

        if (count !== 10)
            return 'Número debe ser de 10 dígitos';

        if (res !== '09')
            return 'Número debe empezar por 09';

        return '';

    }
});