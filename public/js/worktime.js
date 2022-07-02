$(document).ready(function () {


    $('#objectSelect').on("change", function () {

        let objectId = this.value;
        loadWorkTimeByObjectId(objectId)
    })
});

function loadWorkTimeByObjectId(objectId){
    $.ajax({
        url: '/load-worktime',
        data: {
            objectId: objectId
        },
        method: 'POST'
    }).then(function (response) {
        $('#workTimeTableContainer').html(response)
    })
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
    let uid = ""

    $('#monthSelect').on('change', function () {
        buildWorkTimeList()
    })

    $('#employerSelect').on('change', function () {
        buildWorkTimeList()
    })

    //Zeiteintrag bearbeiten
    $('.editTimeEntry').on('click', function () {
        uid = $(this).parent().parent().data("uid")

        let objectId = $('#objectId').val()

        $.ajax({
            url: '/edit-time-entry',
            data: {
                uid: uid
            },
            method: 'POST'
        }).then(function (response) {
            $('#modalContainer').html(response)
            let timeEntryPopup = new bootstrap.Modal(document.getElementById('timeEntryPopup'))
            timeEntryPopup.show()

            $('#editTimeEntriesForm').on('submit',function (e){
                e.preventDefault()
                let formData = new FormData(document.querySelector('#editTimeEntriesForm'))

                $.ajax({
                    url: '/save-time-entry',
                    data: formData,
                    method: 'POST',
                    processData: false,
                    contentType: false,
                }).then(function (response) {
                    timeEntryPopup.hide()
                    loadWorkTimeByObjectId(objectId)
                })

            })
        })
    })

    $('.deleteTimeEntry').on('click', function () {
        uid = $(this).parent().parent().data("uid")
        let objectId = $('#objectId').val()

        if (confirm('Möchtest du diesen Zeiteintrag wirklich löschen?')) {
            $.ajax({
                url: '/delete-time-entry',
                data: {
                    uid:uid
                },
                method: 'POST',
            }).then(function (response) {
                loadWorkTimeByObjectId(objectId)
            })
        }

    })

    $('#addTimeEntry').on('click', function () {
        $.ajax({
            url: '/open-add-time-entry-modal',
            method: 'POST',
        }).then(function (response) {

            $('#modalContainer').html(response)

            let timeEntryModal = new bootstrap.Modal(document.getElementById('addTimeEntryModal'))
            timeEntryModal.show()

        })
    })

}

function buildWorkTimeList() {
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
            date: date,
            employer: employer
        },
        method: 'POST'
    }).then(function (response) {
        $('#workTimeTableContainer').html(response)
        console.log("HE")
    })
}