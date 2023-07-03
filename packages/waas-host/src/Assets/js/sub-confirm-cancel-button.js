jQuery(function($){
    const dangerousCancelButton = $('.button.wps_sfw_cancel_subscription');
    const cancelConfirmButton = $(`
        <a class="button wpcs-cancel-confirm-button" id="wpcs-cancel-confirm-button">
            Cancel subscription
        </a>
    `)

    dangerousCancelButton
        .hide()
        .after(cancelConfirmButton);

    cancelConfirmButton.click(function(){
        cancelConfirmButton.hide();
        dangerousCancelButton
            .before(`<div class="wpcs-cancel-explanation-button">Are you sure you want to cancel? This is permanent!</div>`)
            .html('Yes, cancel my subscription')
            .show();
    });
});
