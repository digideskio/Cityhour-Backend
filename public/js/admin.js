$(document).ready(function() {
    if (/\/complaints/.test(location.href)) $('#l_complaints').addClass('active');
    else if (/\/users/.test(location.href)) $('#l_users').addClass('active');


    $('.pagination a').click(function() {
        var page = 1;
        var arr = this.href.split('/');
        for (var i = 0; i < arr.length; i++)
            if (arr[i] == 'page') {
                page = arr[i + 1];
                break;
            }
        $('#page').val(page);
        $('#search').append('<input type="hidden" name="page" value="' + page + '">');
        $('#search').submit();
        return false;
    });
});