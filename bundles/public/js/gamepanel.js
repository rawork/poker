function onSync() {
    var id = $(this).attr('data-board-id');
    $.post('/game/sync2/'+ id, {},
        function(data){
            onReload();
        }, "json");
}

function onReload() {
    console.log('reloaded');
    window.location.reload();
}

function initPanel() {
    $(document).on('click', 'button[data-action=sync]', onSync);
    setInterval(onReload, 10000);
}

$(document).ready(function() {
    initPanel();
});