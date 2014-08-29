$(function() {
    $('#yammExportButton').click(function(evt) {
        var w = window.open(window.location + '&fnc=export', 'yammExport', 'width=800,height=680,scrollbars=yes,resizable=yes');
    });
});
