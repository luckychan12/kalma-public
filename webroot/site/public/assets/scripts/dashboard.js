$(function() {

    // Enable tooltips
    $('[data-toggle="tooltip"]').tooltip();

    // Get appropriate text color from CSS
    const style = getComputedStyle(document.body);
    const textColor = style.getPropertyValue('--c-text-on-bg');
    // Functions to format tick labels
    const countToString = (t) => Math.round(t + Number.EPSILON).toString();
    const minsToString = (t) => t >= 60 ? `${Math.floor(t / 60)}h` + (t % 60 ? `${t % 60}m` : '') : `${t % 60}m`;
    // Create the charts
    $(".chart").each(function() {
        const ctx = $(this)[0].getContext('2d');
        const chart = new Chart(ctx, {
            type: 'line',
            data: $(this).data('chart-data'),
            options: {
                legend: {
                    display: false,
                },
                scales: {
                    yAxes: [
                        {
                            ticks: {
                                stepSize: $(this).hasClass('logged') ? 1000 : 60,
                                min: 0,
                                callback: $(this).hasClass('logged') ? countToString : minsToString,
                                fontColor: textColor,
                            },
                        },
                    ],
                    xAxes: [
                        {
                            ticks: {
                                fontColor: textColor,
                            },
                            gridLines: {
                                display: false,
                            },
                        },
                    ],
                }
            }
        });
    });
});