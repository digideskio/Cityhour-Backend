$( document ).ready(function() {

    if (/\/run/.test(location.href)) $('#l_run').addClass('active');
    else $('#l_in').addClass('active');

    $('input[type="checkbox"]').checkbox({
        buttonStyle: 'btn-link btn-lg',
        checkedClass: 'glyphicon glyphicon-check',
        uncheckedClass: 'glyphicon glyphicon-unchecked'
    });


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

    function linksIcon(hel) {
        var el = $(hel),
            url = el.parent().parent().find('input[type="text"]').val();
        if (url) {
            el.parent().show();
            el.parent().attr('href',url);
        }
        else {
            el.parent().hide();
            el.parent().attr('href','');
        }
    }

    $(".link_icon").each(function(){
        linksIcon(this);
    });

    $("#url,#image_url,#url_buy,#url_comments,#auto_url").keyup(function() {
        var el = $(this).parent().parent().find(".link_icon");
        linksIcon(el);
    });

    var fixHelper = function(e, ui) {
        ui.children().each(function() {
            $(this).width($(this).width());
        });
        return ui;
    };

    $( ".sortable" ).sortable({
        items: "> tr.sort",
        forceHelperSize: true,
        handle: ".grey-palki",
        helper: fixHelper,
        update: function( event, ui ) {
            var sorted = $( ".sortable" ).sortable( "toArray" );

            $.ajax({
                url: '/admin/categories/order/',
                timeout: 30,
                type: "POST",
                dataType:'json',
                data: JSON.stringify({order: sorted}),
                contentType: 'application/json; charset=utf-8',
                error:  function (){
                    window.location.href = "/admin/categories/";
                    return false;
                }
            });
        }
    });
});
