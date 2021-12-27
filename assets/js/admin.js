(function($){
    $('#rep_info').on('submit', function(e) {
        e.preventDefault();
        $.ajax({
            url: globalVars.url,
            type: "POST",
            data: {
                'org' : 'apache',
                'action' : 'get_repos',
                'page' : 1,
                'per_page' : 20
            },
            dataType: "html",
            success: function (data) {
               $('#response').html(data);
            }
        });
    })
    $('.pagination').on('click', 'button', function(e) {
        e.preventDefault();
        console.log($(e.target).attr('data-page'));
        $.ajax({
            url: globalVars.url,
            type: "POST",
            data: {
                'org' : 'apache',
                'action' : 'get_repos',
                'page' : $(e.target).attr('data-page'),
                'per_page' : 20
            },
            dataType: "html",
            success: function (data) {
                $('#response').html(data);
            }
        });
    })
})(jQuery)
