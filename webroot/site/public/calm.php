<?php
include_once '../controller/calmController.php';
?>

<!doctype html>
<link lang='en'>
<?php include_once './components/global_head_inner.php' ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.3/Chart.min.js"></script>
<link rel="stylesheet" href="assets/stylesheets/trackerPages.css">
</head>
<body>
<?php include_once './components/navbar_top.php'; ?>
<!--Add sleep period overlay-->
<div id="addingCalm" class="modal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">Add Mindful Minutes Data</h2>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form method="post" action="calm.php">
                    <label>Start Time:</label>
                    <br>
                    <input id="addStartDate" class="form-control" type="date" name="startDate" onchange="setEndDate()" required>
                    <input id="addStartTime" class="form-control" type="time" name="startTime" onchange="setEndTime()" required>

                    <label>End Time:</label>
                    <br>
                    <input id="addEndDate" class="form-control" type="date" name="endDate" required>
                    <input id="addEndTime" class="form-control" type="time" name="endTime" required>

                    <label>Description:</label>
                    <br>
                    <input class="form-control" type="text" name="description" >
                    <div class="modal-footer">
                        <input type="submit" class="btn btn-primary" value="Submit" name="addCalm">
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<div id="overlayEdit" class="modal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">Edit Data</h2>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form method="post" action="calm.php">
                    <label>Start Time:</label>
                    <input id="editId" name="editId" value="0" hidden>
                    <br>
                    <input id="editStartDate" class="form-control" type="date" name="startDate" value="2020-02-01" onchange="setEditDate()" required>
                    <input id="editStartTime" class="form-control" type="time" name="startTime" value="20:00" onchange="setEditTime()" required>

                    <label>End Time:</label>
                    <br>
                    <input id="editEndDate" class="form-control" type="date" name="endDate" required>
                    <input id="editEndTime" class="form-control" type="time" name="endTime" required>

                    <label>Description:</label>
                    <br>
                    <input id="editDesc"  class="form-control" type="text" name="description">
                    <div class="modal-footer">
                        <input type="submit" class="btn btn-primary" value="Save" name="editCalm">
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<div id="overlayDelete" class="modal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">Are you sure?</h2>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form method="post" action="calm.php">
                    <input id="deleteId" name="deleteId" value="0" hidden>
                    <input type="submit" class="btn btn-primary" value="Yes" name="editSleep">
                    <input type="button" class="btn btn-secondary" data-dismiss="modal" value="No">
                </form>
            </div>
        </div>
    </div>
</div>
<!--Main Body-->
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-7 offset-lg-1">
            <div class="alert alert-danger alert-dismissible fade text-center <?= isset($message->error) ? "show" : "hide"?>" role="alert">
                <?= isset($message) && isset($message->error) ? "$message->message ($message->error)" : ''; ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <h1 style="margin-top: 20px">Mindful Minutes</h1>
            <button class="btn btn-primary date-selector" onclick=showWeekChart(); style="margin-left: 5px">Week</button>
            <button class="btn btn-primary date-selector" onclick=showMonthChart();>Month</button>
            <br>
            <div style="margin-top: 10px; margin-left: 5px">
                <!--Date choosers-->
                <form id="weekForm" method="get" action="calm.php"style="display: none">
                    <input onchange=document.getElementById('weekSubmit').click(); style="text-align: center" type="week" id="weekDate" value="<?php echo $setWeekDate ?>" name="weekDate">
                    <input type="submit" id="weekSubmit" hidden>
                </form>
                <form id="monthForm" method="get" action="calm.php"style="display: none">
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
                    <h2 style="font-size: x-large">Average Mindful Minutes (Overall):</h2>
                    <hr>
                    <?php echo $average?> Minutes a day
                </div>
                <div class="module">
                    <h2 style="font-size: x-large">Progress Today:</h2>
                    <hr>
                    <?php echo $progress_message?>% of your daily goal
                </div>
                <div class="module">
                    <h2 style="font-size: x-large">Average Start Time (overall):</h2>
                    <hr>
                    <?php echo $averageStartTime?>
                </div>
                <div class="module">
                    <h2 style="font-size: x-large">Average End Time (overall):</h2>
                    <hr>
                    <?php echo $averageEndTime?>
                </div>
            </div>
            <!--Add data button-->
            <div style="text-align: center">
                <button id="add-button" type="button" class="btn btn-primary" data-toggle="modal" data-target="#addingCalm">
                    Add Data
                </button>
            </div>
        </div>
    </div>
    <br>
    <hr>
    <br>
    <div class="row">
        <div class="offset-md-1">
            <div style="display: inline">
                <form method="post" action="calm.php">
                    <label>Search by date:</label>
                    <input id="dateSearch" type="date" name="searchDate" value="<?php if(isset($_POST['searchDate'])){echo $_POST['searchDate'];} ?>">
                    <input class="btn btn-primary" type="submit" value="Search">
                </form>
                <form  method="post" action="calm.php">
                    <label>Search by time: </label>
                    <input id="timeSearch" type="time" name="searchTime" value="<?php if(isset($_POST['searchTime'])){echo $_POST['searchTime'];} ?>">
                    <input class="btn btn-primary" type="submit" value="Search">
                </form>
                <form method="post" action="calm.php">
                    <input class="btn btn-secondary" type="submit" value="Cancel" name="showAll">
                </form>
            </div>
        </div>
    </div>
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
                    <th>Description</th>
                    <th>Progress Percentage</th>
                </tr>
                <?php
                if(isset($_POST['searchDate'])){
                    $searchDate = date('Y-m-d', strtotime($_POST['searchDate']));
                    foreach ($dataPoints->periods as $data) {
                        if (isset($data->id)) {
                            if($searchDate ==  date('Y-m-d', strtotime($data->start_time))) {
                                $StartTime = date('Y-m-d H:i', strtotime($data->start_time));
                                $EndTime = date('Y-m-d H:i', strtotime($data->stop_time));
                                $desc = "$data->description";
                                $send = "edit({$data->id},'{$StartTime}','{$EndTime}','{$desc}')";
                                echo
                                    "<tr>
                                        <td><a href=\"#\" data-toggle=\"modal\" data-target=\"#overlayEdit\" onclick=\"$send\">
                                        <i class=\"fas fa-pencil-alt\"></i></a> 
                                        <a href=\"#\" data-toggle=\"modal\" data-target=\"#overlayDelete\" onclick='remove($data->id)'>
                                        <i  class=\"fas fa-trash\"></a></td></td>    
                                        <td>" . date('d/m/Y H:i', strtotime($data->start_time)) . "</td>
                                        <td>" . date('d/m/Y H:i', strtotime($data->stop_time)) . "</td>
                                        <td>$data->duration_text</td>
                                        <td>$data->description</td>
                                        <td>$data->progress_percentage %</td>
                                    </tr>";
                            }
                        }
                    }
                }
                elseif(isset($_POST['searchTime'])){
                    $searchTime = date('H', strtotime($_POST['searchTime']));
                    foreach ($dataPoints->periods as $data) {
                        if (isset($data->id)) {
                            if($searchTime ==  date('H', strtotime($data->start_time))) {
                                $StartTime = date('Y-m-d H:i', strtotime($data->start_time));
                                $EndTime = date('Y-m-d H:i', strtotime($data->stop_time));
                                $desc = "$data->description";
                                $send = "edit({$data->id},'{$StartTime}','{$EndTime}','{$desc}')";
                                echo
                                    "<tr>
                                        <td><a href=\"#\" data-toggle=\"modal\" data-target=\"#overlayEdit\" onclick=\"$send\">
                                        <i class=\"fas fa-pencil-alt\"></i></a> 
                                        <a href=\"#\" data-toggle=\"modal\" data-target=\"#overlayDelete\" onclick='remove($data->id)'>
                                        <i  class=\"fas fa-trash\"></a></td></td>    
                                        <td>" . date('d/m/Y H:i', strtotime($data->start_time)) . "</td>
                                        <td>" . date('d/m/Y H:i', strtotime($data->stop_time)) . "</td>
                                        <td>$data->duration_text</td>
                                        <td>$data->description</td>
                                        <td>$data->progress_percentage %</td>
                                    </tr>";
                            }
                        }
                    }
                }
                else {
                    foreach ($dataPoints->periods as $data) {
                        if (isset($data->id)) {
                            $StartTime = date('Y-m-d H:i', strtotime($data->start_time));
                            $EndTime = date('Y-m-d H:i', strtotime($data->stop_time));
                            $desc = "$data->description";
                            $send = "edit({$data->id},'{$StartTime}','{$EndTime}','{$desc}')";
                            echo
                                "<tr>
                            <td><a href=\"#\" data-toggle=\"modal\" data-target=\"#overlayEdit\" onclick=\"$send\">
                            <i class=\"fas fa-pencil-alt\"></i></a> 
                            <a href=\"#\" data-toggle=\"modal\" data-target=\"#overlayDelete\" onclick='remove($data->id)'>
                            <i  class=\"fas fa-trash\"></a></td></td>    
                            <td>" . date('d/m/Y H:i', strtotime($data->start_time)) . "</td>
                            <td>" . date('d/m/Y H:i', strtotime($data->stop_time)) . "</td>
                            <td>$data->duration_text</td>
                            <td>$data->description</td>
                            <td>$data->progress_percentage %</td>
                        </tr>";
                        }
                    }
                }
                ?>

            </table>
        </div>
    </div>

</div>

<script>
    function setEditDate(){
        let addStartDate =  document.getElementById('editStartDate').value;
        let endDate = new Date(addStartDate);
        let m = endDate.getMonth() + 1;
        let d = endDate.getDate();
        m = m > 9 ? m : "0"+ m;
        d = d > 9 ? d : "0"+ d;
        document.getElementById('editEndDate').value = endDate.getFullYear() + "-" + m + "-" + d;
        document.getElementById('editEndDate').min = addStartDate;
        document.getElementById('editEndDate').max = addStartDate;
    }
    function setEditTime(){
        let startTime = document.getElementById('editStartTime').value;

        document.getElementById('editEndTime').value = startTime;
        document.getElementById('editEndTime').min = startTime;
    }

    function setDateToday(){
        let today = new Date();
        let m = today.getMonth() + 1;
        let d = today.getDate();
        m = m > 9 ? m : "0"+ m;
        d = d > 9 ? d : "0"+ d;
        document.getElementById('addStartDate').value = today.getFullYear() + "-" + m + "-" + d;
        let min = today.getMinutes();
        let h = today.getHours();
        min = min > 9 ? min : "0"+ min;
        h = h > 9 ? h : "0"+ h;
        document.getElementById('addStartTime').value = h + ":" + min;
    }
    function setEndTime(){
        let startTime = document.getElementById('addStartTime').value;

        document.getElementById('addEndTime').value = startTime;
        document.getElementById('addEndTime').min = startTime;
    }

    function setEndDate(){
        let addStartDate =  document.getElementById('addStartDate').value;
        let endDate = new Date(addStartDate);
        let m = endDate.getMonth() + 1;
        let d = endDate.getDate();
        m = m > 9 ? m : "0"+ m;
        d = d > 9 ? d : "0"+ d;
        document.getElementById('addEndDate').value = endDate.getFullYear() + "-" + m + "-" + d;
        document.getElementById('addEndDate').min = addStartDate;
        document.getElementById('addEndDate').max = addStartDate;
    }

    window.onload = function(){
        setDateToday();
        setEndDate();
        setEndTime();
    };


    function remove(id){
        document.getElementById("deleteId").value = id;
    }
    function edit(id, fullStart, fullEnd, desc){
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
        document.getElementById("editId").value = id;
        document.getElementById("editStartDate").value = startDate;
        setEditDate();
        document.getElementById("editStartTime").value = startTime;
        setEditTime()
        document.getElementById("editEndDate").value = endDate;
        document.getElementById("editEndTime").value = endTime;
        document.getElementById("editDesc").value = desc;
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
                    label: 'Minutes Meditated',
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
                        gridLines: {
                            zeroLineColor: textColor,
                            color: textColor,
                        },
                        scaleLabel: {
                            display: true,
                            labelString: "Number of minutes meditated",
                            fontColor:textColor,
                        },
                        ticks: {
                            fontColor:textColor,
                        }
                    }],
                    yAxes: [{
                        gridLines: {
                            display: false,
                        },
                        scaleLabel: {
                            display: true,
                            labelString: "Days of the week",
                            fontColor:textColor,
                        },
                        ticks: {
                            fontColor:textColor,
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
                    label: 'Minutes Meditated',
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
                            labelString: "Number of minutes meditated",
                            fontColor:textColor,
                        },
                        ticks: {
                            fontColor:textColor,
                        }
                    }],
                    yAxes: [{
                        gridLines: {
                            display: false,
                        },
                        scaleLabel: {
                            display: true,
                            labelString: "Days of the month",
                            fontColor:textColor,
                        },

                        ticks: {
                            fontColor:textColor,
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

