(function($) {
    'use strict';

    $( window ).ready(function() {

    });

})(jQuery);

function hgi_copy_code_to_clipboard(e) {
    var copyText = document.getElementById("code_area");
    copyText.select();
    document.execCommand("Copy");
}
