$(function() {

    let base_uri = 'https://kalma.club';

    // Convert dates to local format
    $('.session-created').each(function() {
        const isoDate = $(this).html();
        const date = new Date(isoDate);
        $(this).html(date.toLocaleString());
    });

    /* Log a device out when the corresponding row in the sessions table is clicked */
    $('.logout-session-link').each(function() {
        $(this).click(function() {

            let clientFingerprint = $(this).attr('data-target');

            $.ajax({
                method: 'POST',
                url: base_uri + logoutLink,
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
                            if (clientFingerprint === '' + fingerprint) {
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
                url: base_uri + targetsLink,
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