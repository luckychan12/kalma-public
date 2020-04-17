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
<!--Edit Overlay-->
<div id="overlayEdit">
    <div id="addDisplay" class="col-md-6 offset-md-3">
        <div class="outer" id="popup">
            <button id="close" onclick="off()">x</button>
            <div class="inner-content">
                <h1>Edit Sleep Data</h1>
                <hr>
                <form method="post" action="sleep.php">
                    <label>Start Time:</label>
                    <input id="editId" name="editId" value="0" hidden>
                    <br>
                    <input id="editStartDate" class="form-control" type="date" name="startDate" value="2020-02-01" required>
                    <input id="editStartTime" class="form-control" type="time" name="startTime" value="20:00" required>

                    <label>End Time:</label>
                    <br>
                    <input id="editEndDate" class="form-control" type="date" name="endDate" required>
                    <input id="editEndTime" class="form-control" type="time" name="endTime" required>

                    <label>Sleep Quality:</label>
                    <br>
                    <input id="editSleepQuality" class="form-control" type="number" max="5" min="1"  name="sleepQuality" required>
                    <hr>
                    <input type="submit" class="btn btn-primary" value="Save" name="editSleep">
                </form>
            </div>
        </div>
    </div>
</div>
<!--Delete Overlay-->
<div id="overlayDelete">
    <div id="addDisplay" class="col-md-6 offset-md-3">
        <div class="outer" id="popup">
            <button id="close" onclick="off()">x</button>
            <div class="inner-content">
                <h1>Are you sure?</h1>
                <hr>
                <form method="post" action="sleep.php">
                    <input id="deleteId" name="deleteId" value="0" hidden>

                    <input type="submit" class="btn btn-primary" value="Yes" name="editSleep">
                    <input type="button" class="btn btn-primary" onclick="off()" value="No">
                </form>
            </div>
        </div>
    </div>
</div>
<!--Main Body-->
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-7 offset-lg-1">
            <div class="alert alert-danger alert-dismissible fade text-center <?= isset($message->message) ? "show" : "hide"?>" role="alert">
                <?= $message->message. " (" . isset($message->error).")" ?? ""; ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <h1 style="margin-top: 20px">Sleep Period</h1>
            <button class="btn btn-primary date-selector" onclick=showWeekChart(); style="margin-left: 5px">Week</button>
            <button class="btn btn-primary date-selector" onclick=showMonthChart();>Month</button>
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
                    <h2 style="font-size: x-large">Average sleep (Overall):</h2>
                    <hr>
                    <?php echo $average?>
                </div>
                <div class="module">
                    <h2 style="font-size: x-large">Progress Today:</h2>
                    <hr>
                    <?php echo $progress_message?>% of your daily goal
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
                    <th></th>
                    <th>Start Time</th>
                    <th>End Time</th>
                    <th>Duration</th>
                    <th>Quality</th>
                    <th>Progress Percentage</th>
                </tr>
                <?php
                foreach ($dataPoints->periods as $data) {
                    if (isset($data->id)) {
                        $StartTime = date('Y-m-dTH:i', strtotime($data->start_time));
                        $EndTime = date('Y-m-dTH:i', strtotime($data->stop_time));
                        $send = "edit({$data->id},'{$StartTime}','{$EndTime}',{$data->sleep_quality})";
                        echo
                            "<tr>
                            <td><a href=\"#\" onclick=".$send.";>Edit
                            <i class=\"fas fa-pencil-alt\"></i></a> 
                            <a href=\"#\" onclick='remove($data->id)'>Delete
                            <i  class=\"fas fa-trash\"></a></td>  
                            <td>". date('Y-m-d H:i', strtotime($data->start_time))."</td>
                            <td>".date('Y-m-d H:i', strtotime($data->stop_time))."</td>
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
    function remove(id){
        document.getElementById("overlayDelete").style.display = "block";
        document.getElementById("deleteId").value = id;
    }
    function edit(id, fullStart, fullEnd, quality){
        let start = new Date(fullStart);
        let m = start.getMonth() + 1;
        let d = start.getDate();
        m = m > 9 ? m : "0"+m;
        d = d > 9 ? d : "0"+d;
        let startDate = start.getFullYear() + "-" + m + "-" + d;
        let hour = start.getHours();
        hour = hour > 9 ? hour :"0"+hour;
        let min = start.getMinutes();
        min = min > 9 ? min :"0"+min;
        let startTime = hour + ":" + min;

        let end = new Date(fullEnd);
        m = end.getMonth() + 1;
        d = end.getDate();
        m = m > 9 ? m : "0"+m;
        d = d > 9 ? d : "0"+d;
        let endDate = end.getFullYear() + "-" + m + "-" + d;
         hour = end.getHours();
        hour = hour > 9 ? hour :"0"+hour;
         min = end.getMinutes();
        min = min > 9 ? min :"0"+min;
        let endTime = hour+ ":" + min;
        document.getElementById("overlayEdit").style.display = "block";
        document.getElementById("editId").value = id;
        document.getElementById("editStartDate").value = startDate;
        document.getElementById("editStartTime").value = startTime;
        document.getElementById("editEndDate").value = endDate;
        document.getElementById("editEndTime").value = endTime;
        document.getElementById("editSleepQuality").value =quality;
    }

    function on() {
        document.getElementById("overlay").style.display = "block";
    }

    function off() {
        document.getElementById("overlay").style.display = "none";
        document.getElementById("overlayEdit").style.display = "none";
        document.getElementById("overlayDelete").style.display = "none";
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
        const style = getComputedStyle(document.body);
        const textColor = style.getPropertyValue('--c-text-on-bg');
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
                            fontColor: textColor,
                        },
                        ticks: {
                            fontColor: textColor,
                        },
                        gridLines: {
                            zeroLineColor: textColor,
                            color: textColor,
                        },
                    }],
                    yAxes: [{
                        gridLines: {
                            display:false,
                        },
                        scaleLabel: {
                            display: true,
                            labelString: "Nights of the week",
                            fontColor: textColor,
                        },
                        ticks: {
                            fontColor: textColor,
                            beginAtZero:true
                        }
                    }]
                }
            }
        });
    }

    function renderMonthChart(){
        const style = getComputedStyle(document.body);
        const textColor = style.getPropertyValue('--c-text-on-bg');
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
                        gridLines: {
                            zeroLineColor: textColor,
                            color: textColor,
                        },
                        scaleLabel: {
                            display: true,
                            labelString: "Number of hours slept",
                            fontColor: textColor,
                        },
                        ticks: {
                            fontColor: textColor,
                        }
                    }],
                    yAxes: [{
                        gridLines: {
                            display: false,
                        },
                        scaleLabel: {
                            display: true,
                            labelString: "Days of the month",
                            fontColor: textColor,
                        },

                        ticks: {
                            fontColor: textColor,
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

