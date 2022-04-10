$(document).ready( function () {
    $('#worktime').DataTable();
} );

$('.companyObject').on("click", function () {

    let objectId = this.value;
    $.ajax({
        url: '/load-worktime',
        data: {
            objectId: objectId
        },
        method: 'POST'
    }).then(function (response) {
        $('#worktimeContent').html(response)
    })
})