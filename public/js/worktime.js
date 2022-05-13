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


function exportFormAction() {
    // $("#exportForm").submit(function (event) {
    //     event.preventDefault();
    //     let formData = new FormData(this)
    //     let objectId = formData.get("objectId")
    //
    //     let date = $('.monthSelect')
    //     date = date.prevObject.find(":selected")[1].value;
    //
    //     $.ajax({
    //         url: '/export-pdf',
    //         data: {
    //             objectId: objectId,
    //             datetime: date
    //         },
    //         method: 'POST'
    //     }).then(function (response) {
    //
    //         // Trick for making downloadable link
    //         var blob=new Blob(response,{type:'application/pdf'});
    //         var link=document.createElement('a');
    //         link.href=window.URL.createObjectURL(blob);
    //         //link.download="<FILENAME_TO_SAVE_WITH_EXTENSION>";
    //         link.click();
    //     })
    //
    // });
}


function createWorktimeTable() {
    $('#worktime').DataTable({
        "language": {
            "url": "//cdn.datatables.net/plug-ins/9dcbecd42ad/i18n/German.json"
        },
        "order": [[1, "asc"]],
        paging: false
    });
}

function destroyWorktimeTable() {
    $('#worktime').DataTable().destroy();
}

function activateListener() {
    $('.monthSelect').on('click', function () {

        // month, year 12-2021
        let date = this.value
        let objectId = $(this).data('object')

        console.log(objectId)

        $.ajax({
            url: '/load-worktime',
            data: {
                objectId: objectId,
                date:date
            },
            method: 'POST'
        }).then(function (response) {
            $('#workTimeTableContainer').html(response)
        })
    })
}
