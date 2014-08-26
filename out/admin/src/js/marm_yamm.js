$(function() {
    $('#yammExportButton').click(function(evt) {
        var w = window.open($(this).attr('data-base') + '?sid=' + $(this).attr('data-sid'), 'yammExport', 'width=800,height=680,scrollbars=yes,resizable=yes');
    });
});
