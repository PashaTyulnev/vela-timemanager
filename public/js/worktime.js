$('.companyObject').on("click", function () {

    let objectId = this.value;
    $.ajax({
        url: '/load-worktime',
        data: {
            objectId: objectId
        },
        method: 'POST'
    }).then(function (response) {
        $('#workTimeTableContainer').html(response)
    })
})


function exportFormAction(){
    $( "#exportForm" ).submit(function( event ) {
        event.preventDefault();
        let formData = new FormData(this)
        let objectId = formData.get("objectId")
        let month = formData.get("month")

        console.log(formData.get("objectId"))
        $.ajax({
            url: '/export-pdf',
            data: {
                objectId:objectId,
                month:month
            },
            method: 'POST'
        }).then(function (response) {

        })

    });
}


function createWorktimeTable() {
    $('#worktime').DataTable({
        "language": {
            "url": "//cdn.datatables.net/plug-ins/9dcbecd42ad/i18n/German.json"
        },
        "order": [[ 1, "asc" ]],
        paging: false
    });
}
function destroyWorktimeTable() {
    $('#worktime').DataTable().destroy();
}