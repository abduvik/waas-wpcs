jQuery(function($){
    const target = document.querySelector('.ssd-modal-wrapper')

    const observer = new MutationObserver(function() {
        $('.wpr-product-add-button .button').click((e) => {
            const buttonContainer = $(e.target).parent();
            buttonContainer.children().hide();
            buttonContainer.append('<img class="wpcs-product-added-spinner" src="/wp-admin/images/spinner.gif" />');
        });
    });

    observer.observe(target, {
        attributes:    true,
        childList:     true,
        characterData: true
    });
});
