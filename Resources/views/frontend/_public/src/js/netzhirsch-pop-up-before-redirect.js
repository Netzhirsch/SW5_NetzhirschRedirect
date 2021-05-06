(function ($, window) {
	'use strict';
    let redirect = getLocalStorage('netzhirsch-redirect');
    if (withoutConfirmation === undefined || active === 'off')
        return;

    if (withoutConfirmation === 'off' && redirect === '') {
        $.modal.open(
            $('#netzhirsch-content').html(),
            {
                title: $('#netzhirsch-title').html(),
                overlay: true,
                height:200
            }
        );
    }

    $('.netzhirsch-redirect').on('click',function (){
        $.modal.close();
        let redirect = $(this).data('netzhirsch-redirect');
        if (redirect === 1) {
            $.ajax({
                url: "/frontend/redirect/ajaxRedirect",
                method: 'GET',
                success: function(result) {
                    setLocalStorage('netzhirsch-redirect', '1');
                    if (result !== '')
                        window.location.replace(window.location+result);
                }
            });
        } else {
            setLocalStorage('netzhirsch-redirect', '0');
        }
    })

})(jQuery, window);

function getLocalStorage(storageKey) {
    let storageData = localStorage.getItem(storageKey);
    if (storageData !== 'null' && storageData !== null) {
        return JSON.parse(storageData);
    }
    return '';
}

function setLocalStorage(storageKey, storageValue) {
    localStorage.setItem(storageKey, storageValue)
}
