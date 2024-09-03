function contraentrega_change(_checked) {
    if(_checked){
    	document.body.classList.add('wc_contraentrega_on')
    }else{
    	document.body.classList.remove('wc_contraentrega_on')
    }
    e = document.documentElement.querySelector('.shipping_method:checked')
    
    id = ""
    if(!_checked){
        id = e.id.replace("wc_contraentrega_on","wc_contraentrega_off")
        r = document.documentElement.querySelectorAll('[id*="wc_contraentrega_off"]')[0]
    }else{
        id = e.id.replace("wc_contraentrega_off","wc_contraentrega_on")
        r = document.documentElement.querySelectorAll('[id*="wc_contraentrega_on"]')[0]
    }
    p = document.getElementById(id)
    if(p == null || p == undefined){
        if(r != null & r != undefined)
            r.click()
    }else{
        p.click()
    }
}
function init_WC_contraentrega() {
    payment_method = document.getElementsByName('payment_method')
    for (var i = 0; i < payment_method.length; i++) {
        payment_method[i].onchange = (e) => contraentrega_change(e.target.id == "payment_method_contraentrega");
    }
}
function load_script_contraentrega() {
    init_WC_contraentrega()
    jQuery(document.body).on('updated_checkout', function () {
        init_WC_contraentrega()
    });
    contraentrega_payment = document.getElementById('payment_method_WC_contraentrega')
    if(contraentrega_payment!=null && contraentrega_payment!=undefined)
        contraentrega_change(contraentrega_payment.checked)
}
//load_script_contraentrega()