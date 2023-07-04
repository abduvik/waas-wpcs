jQuery(function($){
    const wpcsProductCheckbox = $('#is_wpcs_product');
    const wpcsProductOptionsTab = $('.wpcs_options');

    function manageProductOptionsTab() {
        if (wpcsProductCheckbox.is(':checked')) {
            wpcsProductOptionsTab.show();
        } else {
            wpcsProductOptionsTab.hide();
        }
    }

    manageProductOptionsTab();

    wpcsProductCheckbox.change(function(){
        manageProductOptionsTab();
    });
});
