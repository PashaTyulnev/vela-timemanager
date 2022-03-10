var serverTime = new Date();

function updateTime() {
    /// Increment serverTime by 1 second and update the html for '#time'
    serverTime = new Date(serverTime.getTime() + 1000);
    $('#time').html(serverTime.toLocaleString());
}


/**
 * Fügt die PIN Ziffern ein/Zeigt eingegeben Ziffern an
 */
function enterPin() {
    $('.number').on("click", function () {
        let id = this.id
        let number = id.match(/\d+/)[0]
        for (let i = 1; i < 5; i++) {
            if ($('#digit' + i).val() == "-") {
                $('#digit' + i).val(number)
                break;
            }
        }
    })
}

/**
 * Verhindert, dass die PIN erhalten bleibt
 */
function flushPin(flushed = false) {

    if (!flushed) {
        for (let i = 1; i <= 4; i++) {
            $('#digit' + i).val('-')
        }
    } else {
        flushed
    }
    {
        let myOffcanvas = document.getElementById('offcanvasBottom')
        myOffcanvas.addEventListener('hidden.bs.offcanvas', function () {
            flushPin(false)
        })
    }


}

function authWithPin() {
    $('#pinEnter').on("click", function () {
        let allDigitsSet = false;
        for (let i = 1; i <= 4; i++) {
            if ($('#digit' + i).val() !== '-') {
                allDigitsSet = true;
            } else {
                allDigitsSet = false;
            }
        }

        if (!allDigitsSet) {
            $(".pinEntered").effect("shake");
        } else {
            $.ajax({
                url: '/auth/pin',
                data: {
                    digit1: $('#digit1').val(),
                    digit2: $('#digit2').val(),
                    digit3: $('#digit3').val(),
                    digit4: $('#digit4').val(),
                },
                method: 'POST'
            }).then(function (response) {
                $('html').html(response)
            })
        }

    })
}

/**
 *
 *Entfernt die letzte Zahl
 */
function deleteNumber() {
    $('#deleteNumber').on("click", function () {

        for (let i = 4; i > 0; i--) {
            if ($('#digit' + i).val() !== "-") {
                $('#digit' + i).val("-")
                break;
            }
        }
    })
}

/**
 * Open Checkin Screen for specific Employer
 */
function checkEmployer(){
    $('.employer').on("click", function () {
        console.log(this)

        //TODO IDs to complicated ids
        let userID = this.id

        $.ajax({
            url: '/openCheckinWindow',
            data: {
                userID:userID
            },
            method: 'POST'
        }).then(function (response) {

            //opens offcanvas
            let myOffcanvas =$('#userCheckInWindow')

            //puts checkin html into offcanvas
            myOffcanvas.html(response)

            let bsOffcanvas = new bootstrap.Offcanvas(myOffcanvas)

            //open offcanvas
            bsOffcanvas.show()
        })


    })
}
$(function () {
    updateTime();
    setInterval(updateTime, 1000);
    checkEmployer();
    // flushPin(false);
    // enterPin();
    // deleteNumber();
    // authWithPin();
});

