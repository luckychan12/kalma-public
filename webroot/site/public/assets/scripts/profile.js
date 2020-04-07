$(function() {

    /* Log a device out when the corresponding row in the sessions table is clicked */
    $('.logout-session-link').each(function() {
        $(this).click(function() {

            let clientFingerprint = $(this).attr('data-target');

            $.ajax({
                method: 'POST',
                url: 'http://localhost' + logoutLink,
                headers : {
                    'Authorization' : `Bearer ${accessToken}`
                },
                data: JSON.stringify({
                    'client_fingerprint': clientFingerprint
                }),
                crossDomain: true,
                xhrFields: {
                    withCredentials: false
                },
                accepts: {
                    json: 'application/json'
                },
                dataType: 'json',
                complete: function(res) {
                    if (res.hasOwnProperty('responseJSON')) {
                        let data = res.responseJSON;
                        if (data.hasOwnProperty('error')) {
                            $('#errorModalDescription').html(`${data.message} (${data.error})`);
                            $('#errorModal').modal('show');
                        }
                        else {
                            if (clientFingerprint === '' + getFingerprint()) {
                                location.href = '?logout';
                            }
                            else {
                                location.reload();
                            }
                        }
                    }
                },
            });
        });

        /* Show update targets modal when table row is clicked */
        $('.edit-target-link').each(function() {
            $(this).click(function() {
                $('#targetsModal').modal('show');
            });
        });

        /* Update targets when modal save button is clicked */
        $('#targetsModalSave').click(function() {

            let targets = {};
            $('.target-field').each(function() {
                let type = $(this).attr('type');
                let value = $(this).val();
                let intVal;
                if (type === 'time') {
                    let [hours, minutes] = value.split(':').map((a) => parseInt(a, 10));
                    console.log(hours + " " + minutes);
                    intVal = hours * 60 + minutes;
                    console.log(intVal);
                }
                else {
                    intVal = parseInt(value);
                }

                let name = $(this).attr('name');
                targets[name] = intVal;
            });

            $.ajax({
                method: 'PUT',
                url: 'http://localhost' + targetsLink,
                headers: {
                    'Authorization': `Bearer ${accessToken}`
                },
                data: JSON.stringify({
                    'targets': targets
                }),
                crossDomain: true,
                xhrFields: {
                    withCredentials: false
                },
                accepts: {
                    json: 'application/json'
                },
                dataType: 'json',
                complete: function (res) {
                    if (res.hasOwnProperty('responseJSON')) {
                        let data = res.responseJSON;
                        if (data.hasOwnProperty('error')) {
                            $('#errorModalDescription').html(`${data.message} (${data.error})`);
                            $('#errorModal').modal('show');
                        } else {
                            location.reload();
                        }
                    }
                },
            });
        });

    });
});