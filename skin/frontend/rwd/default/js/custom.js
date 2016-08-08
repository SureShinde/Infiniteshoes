jQuery(document).ready(function ($){
    $("#narrow-by-list dt").each(function (){
        $(this).click(function (){
            //alert ('');
            $(this).toggleClass('active');
            $(this).next('dd').slideToggle(300);
        })
    })

    //remove layered on col-main
    $(".col-main #narrow-by-list").remove();
    //remove layerd on col-left
    $(".col-left .block-layered-nav .currently").remove();
    $(".col-left .block-layered-nav .actions").remove();

    //toggle cho detail
    $(".dl_detail dt").each(function (){
        $(this).click(function (){
            $(this).toggleClass('active');
            $(this).next('dd').slideToggle(300);
        })
    })

    //toggle cho cart
    $(".checkoutBox_title").each(function (){
        $(this).click(function (){
            $(this).toggleClass('active');
            $(this).next('.checkoutBox_contet').slideToggle(300);
        })
    })

    $("#account_login_wrap_parent_a").click(function (){
        $(".account_login_wrap_parent").toggleClass('active');
    })

    $(window).scroll(function (){
        var scrollTop = $(window).scrollTop();
        if(scrollTop>50){
            if(!$(".header_center").hasClass('header_fixed')){
                $(".header_center").addClass('header_fixed')
            }
        }else{
            $(".header_center").removeClass('header_fixed')
        }
    })

    //show newsletter
    var showNewsletter = localStorage['showNewsletter'];
    if (!showNewsletter) {
        // open popup
        localStorage['showNewsletter'] = "yes";
        jQuery(".newsletter_modal").fadeIn(300);
        jQuery("#hide_body").fadeIn(300);
    }

    //show button update khi chanage value cua select qty
    $(".itemSelectQty").each(function (){
        $(this).change(function (){
            $(this).next(".button").css('display', 'inline-block');
        })
    })
})
function showSizeChart(homeurl) {
    jQuery.ajax({
        url: homeurl+'size-chart.html',
        success: function (j) {
            jQuery("#sizeChart_popup .content").html(j)
            jQuery("#hide_body").fadeIn(300)
            var body_class = jQuery("body").attr('class');
            if (body_class.indexOf('categorypath-mens') != -1) {
                jQuery("#size_chart_mens").show();
                jQuery("#size_chart").hide();
            }else if( body_class.indexOf('categorypath-women') != -1){
                jQuery("#size_chart_mens").hide();
                jQuery("#size_chart").show();
            }else{
                jQuery("#size_chart_mens").show();
                jQuery("#size_chart").show();
            }
            jQuery("#sizeChart_popup").fadeIn(300);
        }
    });
}
function close_sizeChart(){
    jQuery("#hide_body").fadeOut(300)
    jQuery("#sizeChart_popup").fadeOut(300);
}

function show_quickView(homeurl, id){
    jQuery("#hide_body").fadeIn(300);
    jQuery("#loading").fadeIn(300);
    var url= homeurl+'quickview/index/view/id/'+id;
    jQuery("#mainFrame").attr("src", url);
    jQuery("#mainFrame").load( function() {
        jQuery("#loading").fadeOut(300);
        jQuery("#quickView_popup").fadeIn(300);
    } );
}

function close_quickView(){
    jQuery("#hide_body").fadeOut(300)
    jQuery("#quickView_popup").fadeOut(300);
}

function changeImageHome(image_url, id){
    if(image_url!=''){
        jQuery("#product-collection-image-"+id).attr('src', image_url);
    }
}

function close_newsletter(){
    jQuery(".newsletter_modal").fadeOut(300);
    jQuery("#hide_body").fadeOut(300);
}

//apply gift card o checkout page
function applyGiftcard(url){
    var giftcard_code = jQuery("#giftcard_code").val();
    if(!giftcard_code){
        alert('Please enter your gift card');
        return false;
    }
    var parameters = {
        giftcard_code: giftcard_code,
        remove: '1'
    };
    var request = new Ajax.Request(url, {
        method: 'post',
        onFailure: '',
        parameters: parameters,
        onSuccess: function (transport) {
            var response = getResponseText(transport);
            alert('thanh cong');
            paymentShow();
            reviewShow();
            console.log(response.error);
            console.log(response.content);
            /*if (response.error) {
                paymentShow();
                reviewShow();
            }
            else {
                save_shipping_method(shipping_method_url, 0, 1);
                $('coupon_code_onestepcheckout').value = '';
                $('remove_coupon_code_button').hide();
                jQuery("#message_coupon_checkout").html(response.message);
            }*/
        }
    });
    /*jQuery.ajax({
        url: url,
        type: "POST",
        data:{'giftcard_code':giftcard},
        onSuccess: function (j) {
            alert('thanh cong');
            var response = getResponseText(j);
            paymentShow();
            reviewShow();
            console.log(response.error);
            console.log(response.content);
        }
    });*/
}