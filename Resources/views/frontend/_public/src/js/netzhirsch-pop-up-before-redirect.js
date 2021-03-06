(function ($, window) {
	'use strict';
    let redirect = getLocalStorage('netzhirsch-redirect');
    if (typeof (withoutConfirmation) === 'undefined' || active === 'off')
        return;

    let newUrl = '';
    if (withoutConfirmation === 'off' && redirect === '') {
        $.ajax({
            url: "/frontend/redirect/ajaxRedirect",
            method: 'GET',
            success: function(result) {
                setLocalStorage('netzhirsch-redirect', '1');
                if (result !== '')
                    newUrl = result;
                if (!window.location.href.indexOf(newUrl) >= 0) {
                    $.modal.open(
                        $('#netzhirsch-content').html(),
                        {
                            title: $('#netzhirsch-title').html(),
                            overlay: true,
                            sizing: 'content'
                        }
                    );
                    $('.netzhirsch-redirect').on('click',function (){
                        $.modal.close();
                        let redirect = $(this).data('netzhirsch-redirect');
                        if (redirect === 1) {
                            setLocalStorage('netzhirsch-redirect', '1');
                            window.location.assign(window.location.protocol + '//' + window.location.hostname+'/'+result);
                        } else {
                            setLocalStorage('netzhirsch-redirect', '0');
                        }
                    })
                }
            }
        });
    }

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

function getNewUrl() {
    return $.ajax({
        url: "/frontend/redirect/ajaxRedirect",
        method: 'GET',
        async: false,
        success: function(result) {
            return result;
        }
    });
}
