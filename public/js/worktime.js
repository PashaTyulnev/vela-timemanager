$( document ).ready(function() {


    $('#objectSelect').on("change", function () {

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
});


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
    $('#monthSelect').on('change', function () {
        buildWorkTimeList()
    })

    $('#employerSelect').on('change', function () {
        buildWorkTimeList()
    })

}

function buildWorkTimeList(){
    //ist id oder string "all"
    let monthSelect = $('#monthSelect option:selected');

    // month, year 12-2021
    let date = monthSelect.val();

    let objectId = $(monthSelect).data('object')

    //ist id oder string "all"
    let employer = $('#employerSelect').val();


    $.ajax({
        url: '/load-worktime',
        data: {
            objectId: objectId,
            date:date,
            employer:employer
        },
        method: 'POST'
    }).then(function (response) {
        $('#workTimeTableContainer').html(response)
    })
}
