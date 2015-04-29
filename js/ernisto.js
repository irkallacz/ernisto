$(function(){
    $('<div id="ajax-spinner"></div>').appendTo('body').ajaxStop(function(){
        $(this).hide().css({
           position: 'fixed',
           left: '50%',
           top: '50%'
        });
    }).hide();

    $('a.ajax').live('click', function(event) {
        event.preventDefault();

        $('#ajax-spinner').show().css({
            position: 'absolute',
            left: event.pageX + 20,
            top: event.pageY + 20
        })

        $.get($.nette.href = this.href, function(data) {
            $.nette.success(data);
        }, "json");

    });
});