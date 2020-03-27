<?php
include_once 'header.php';
include_once '../controller/sleepController.php';
?>

<!doctype html>
<html lang='en'>
<head>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.3/Chart.min.js"></script>
<style>
    button {
        background-color: var(--c-secondary);
        color: var(--c-text-on-secondary);
        border: solid;
        border-color: var(--c-secondary-dark);
    }
    input {
        background-color: white;
        color: black;
        border-style: ridge;
        border-width: 1px ;
        border-color: lightgrey;
    }
    .average {
        color: var(--c-text-on-primary);
        background-color: var(--c-primary-dark);
        border-color: var(--c-primary-dark);
        border-radius: 20px;
        margin: 20px;
        padding: 10px;
        text-decoration: none;
    }
    .average:{
        border: 0;
    }
    #overlay {
        position: fixed;
        display: none;
        width: 100%;
        height: 100%;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: rgba(0,0,0,0.5);
        z-index: 99;
        cursor: pointer;
    }

    #addDisplay{
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%,-50%);
        -ms-transform: translate(-50%,-50%);
    }
    label {
        font-size: large;
        margin-top: 10px;
    }

</style>
</head>
<body>
<div id="overlay">
    <div id="addDisplay">
        <div class="average" style="text-align: center; padding: 40px">
            <button onclick="off()" style="float: right">X</button>
            <h1>Add Sleep Data</h1>
            <br>
            <form>
                <label>Start Time:</label>
                <br>
                <input type="date" name="startDate" required>
                <input type="time" name="startTime" required>
                <br>
                <label>End Time:</label>
                <br>
                <input type="date" name="endDate" required>
                <input type="time" name="endTime" required>
                <br>
                <label>Sleep Quality:</label>
                <br>
                <input type="number" max="5" min="1"  name="sleepQuality" required>
                <br>
                <input type="submit" value="Submit" style="background-color: var(--c-secondary); margin-top: 20px;border-color: var(--c-secondary-dark)">
            </form>
        </div>
    </div>
</div>
<div class="container-fluid">
    <div class="row">
        <div class="col-md-7 offset-md-1">
            <h1 style="margin-top: 20px">Sleep Period</h1>
            <button onclick=showWeekChart(); style="margin-left: 5px">W</button>
            <button onclick=showMonthChart();>M</button>
            <br>
            <div style="margin-top: 10px; margin-left: 5px">
                <form id="weekForm" method="get" action="sleep.php"style="display: none">
                    <input onchange=document.getElementById('weekSubmit').click(); style="text-align: center" type="week" id="weekDate" value="<?php echo $setWeekDate ?>" name="weekDate">
                    <input type="submit" id="weekSubmit" hidden>
                </form>
                <form id="monthForm" method="get" action="sleep.php"style="display: none">
                    <input onchange=document.getElementById('monthSubmit').click(); style="text-align: center" type="month" value="<?php echo $setMonthDate ?>" name="monthDate">
                    <input type="submit" id="monthSubmit" hidden>
                </form>
            </div>
            <br>
            <canvas id="myWeekChart" width="400" height="250" style="display: none"></canvas>
            <canvas id="myMonthChart" width="400" height="250" style="display: none"></canvas>
        </div>
        <div class="col-md-3">
            <div class="average">
                <span style="font-size: x-large">Average sleep:</span>
                <br><br>


                <div style="text-align: right;font-size: large"><?php echo $average?> Hours a day</div>
            </div>
            <div style="text-align: center">
                <button class="average" onclick="on()" style="text-align: center">
                    Add Data
                </button>
            </div>

        </div>

    </div>
</div>

<script>
    function on() {
        document.getElementById("overlay").style.display = "block";
    }

    function off() {
        document.getElementById("overlay").style.display = "none";
    }
function AddingData() {
    
}


    function showWeekChart() {
        document.getElementById('myWeekChart').style.removeProperty('display');
        renderWeekChart();
        document.getElementById('myMonthChart').style.display = 'none';
        document.getElementById('weekForm').style.removeProperty('display');
        document.getElementById('monthForm').style.display = 'none';
    }
    function showMonthChart() {
        document.getElementById('myMonthChart').style.removeProperty('display');
        renderMonthChart();
        document.getElementById('myWeekChart').style.setProperty('display', 'none');
        document.getElementById('monthForm').style.removeProperty('display');
        document.getElementById('weekForm').style.display = 'none';
    }
    function renderWeekChart() {

        var ctx = document.getElementById("myWeekChart").getContext('2d');
        var myChart = new Chart(ctx, {
            type: 'horizontalBar',
            data: {
                labels: ["Mon", "Tue", "Wed", "Thus", "Fri", "Sat","Sun"],
                datasets: [{
                    label: 'Hours of the day',
                    data: <?php
                    echo "[";
                    foreach ($weekPoints as $point)
                    {
                        echo $point. ",";
                    }
                    echo"]";
                    ?>,
                    backgroundColor: "#ffc400",
                    borderColor:"#c79400",
                    borderWidth: 1
                }]
            },
            options: {
                scales: {

                    xAxes: [{
                        scaleLabel: {
                            display: true,
                            labelString: "Number of hours slept",
                            fontColor:'#000000',
                        },
                        ticks: {
                            fontColor:'#000000',
                        }
                    }],
                    yAxes: [{
                        scaleLabel: {
                            display: true,
                            labelString: "Days of the week",
                            fontColor:'#000000',
                        },
                        ticks: {
                            fontColor:'#000000',
                            beginAtZero:true
                        }
                    }]
                }
            }
        });
    }

function renderMonthChart(){
    var ctx2 = document.getElementById("myMonthChart").getContext('2d');
    var myChart2 = new Chart(ctx2, {
        type: 'horizontalBar',
        data: {
            labels: [<?php echo $monthLabels ?>],
            datasets: [{
                label: 'Hours of the day',
                data: <?php
                echo "[";
                foreach ($monthPoints as $point)
                {
                    echo $point. ",";
                }
                echo"]";
                ?>,
                backgroundColor: "#ffc400",
                borderColor:"#c79400",
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                xAxes: [{
                    scaleLabel: {
                        display: true,
                        labelString: "Number of hours slept",
                        fontColor:'#000000',
                    },
                    ticks: {
                        fontColor:'#000000',
                    }
                }],
                yAxes: [{
                    scaleLabel: {
                        display: true,
                        labelString: "Days of the month",
                        fontColor:'#000000',
                    },

                    ticks: {
                        fontColor:'#000000',
                        beginAtZero:true
                    }
                }]
            }
        }
    });
}
</script>

<?php
//displays the correct chart based on what was already selected
if (isset($_GET['weekDate'])){
    echo '<script> document.onload = showWeekChart()</script>';
}
else if (isset($_GET['monthDate'])){
    echo '<script> document.onload = showMonthChart()</script>';
}
else {
    echo '<script> document.onload = showWeekChart()</script>';
}
?>
</body>

