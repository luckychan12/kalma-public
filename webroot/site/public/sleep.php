<?php
include_once '../controller/sleepController.php';
?>

<!doctype html>
<link lang='en'>
<?php include_once './components/global_head_inner.php' ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.3/Chart.min.js"></script>
<link rel="stylesheet" href="assets/stylesheets/sleep.css">
</head>
<body>
<?php include_once './components/navbar_top.php'; ?>
<!--Add sleep period overlay-->
<div id="overlay">
    <div id="addDisplay" class="col-md-6 offset-md-3">
        <div class="outer" id="popup">
            <button id="close" onclick="off()">x</button>
            <div class="inner-content">
                <h1>Add Sleep Data</h1>
                <hr>
                <form method="post" action="sleep.php">
                    <label>Start Time:</label>
                    <br>
                    <input class="form-control" type="date" name="startDate" required>
                    <input class="form-control" type="time" name="startTime" required>

                    <label>End Time:</label>
                    <br>
                    <input class="form-control" type="date" name="endDate" required>
                    <input class="form-control" type="time" name="endTime" required>

                    <label>Sleep Quality:</label>
                    <br>
                    <input class="form-control" type="number" max="5" min="1"  name="sleepQuality" required>
                    <hr>
                    <input type="submit" class="btn btn-primary" value="Submit" name="addSleep">
                </form>
            </div>
        </div>
    </div>
</div>
<!--Main Body-->
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-7 offset-lg-1">
            <h1 style="margin-top: 20px">Sleep Period</h1>
            <button class="btn btn-primary date-selector" onclick=showWeekChart(); style="margin-left: 5px">W</button>
            <button class="btn btn-primary date-selector" onclick=showMonthChart();>M</button>
            <br>
            <div style="margin-top: 10px; margin-left: 5px">
                <!--Date choosers-->
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
            <!--Graphs-->
            <canvas id="myWeekChart" width="400" height="250" style="display: none"></canvas>
            <canvas id="myMonthChart" width="400" height="250" style="display: none"></canvas>
        </div>
        <!--Average Display-->
        <div class="col-lg-3">
            <div class="outer">
                <div class="module">
                    <h2 style="font-size: x-large">Average sleep:</h2>
                    <hr>
                    <?php echo $average?> Hours a day
                </div>
                <div class="module">
                    <h2 style="font-size: x-large">Progress:</h2>
                    <hr>
                    <?php echo $progress_message?>
                </div>
            </div>
            <!--Add data button-->
            <div style="text-align: center">
                <button id="add-button" class="btn btn-primary" onclick="on()">
                    Add Data
                </button>
            </div>
        </div>
    </div>
    <br>
    <hr>
    <br>
    <!--Data table-->
    <div class="row" style="margin-bottom: 200px">
        <div class="col-lg-10 offset-sm-1">
            <table class="table">
                <tr>
                    <th>ID</th>
                    <th>Start Time</th>
                    <th>End Time</th>
                    <th>Duration</th>
                    <th>Quality</th>
                    <th>Progress Percentage</th>
                </tr>
                <?php
                foreach ($dataPoints->periods as $data) {
                    if (isset($data->id)) {
                        echo
                            "<tr>
                            <td>$data->id</td>    
                            <td>". date('d-m-Y H:i', strtotime($data->start_time))."</td>
                            <td>".date('d-m-Y H:i', strtotime($data->stop_time))."</td>
                            <td>$data->duration_text</td>
                            <td>$data->sleep_quality</td>
                            <td>$data->progress_percentage %</td>
                        </tr>";
                    }
                }
                ?>
            </table>
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
                    label: 'Hours slept',
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
                    label: 'Hours slept',
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

