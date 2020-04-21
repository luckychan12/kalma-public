<?php
session_start();
date_default_timezone_set('Europe/London');
include_once "../api_tasks/ApiConnector.php";
$api = new ApiConnector();
$GMT = new DateTimeZone('GMT');

//Deals with adding new data
if(isset($_POST['startDate'])){
    $newStartTime = new DateTime($_POST['startDate'] .' '. $_POST['startTime']);
    $newStartTime->setTimezone($GMT);
    $newStartTime = $newStartTime->format(DateTime::ISO8601);
    $newEndTime = new DateTime($_POST['endDate'] .' '. $_POST['endTime']);
    $newEndTime->setTimezone($GMT);
    $newEndTime = $newEndTime->format(DateTime::ISO8601);
    $descriptionString = 'description';
    if(isset($_SESSION['links'])) {
        $message = $api->addPeriodicData($_SESSION['links']->calm, $newStartTime, $newEndTime, $descriptionString, $_POST['description']);
    }
}

//Deals with editing new data
if(isset($_POST['editId'])){
    $id = $_POST['editId'];
    $newStartTime = new DateTime($_POST['newStartDate'] .' '. $_POST['newStartTime']);
    $newStartTime->setTimezone($GMT);
    $newStartTime = $newStartTime->format(DateTime::ISO8601);
    $newEndTime = new DateTime($_POST['newEndDate'] .' '. $_POST['newEndTime']);
    $newEndTime->setTimezone($GMT);
    $newEndTime = $newEndTime->format(DateTime::ISO8601);
    $desc =$_POST['newDescription'];
    $period['id'] = (int)$id;
    $period['start_time'] = $newStartTime;
    $period['stop_time'] = $newEndTime;
    $period['description'] = $desc;
    $periods = array($period);
    $data['periods'] = $periods;
    if(isset($_SESSION['links'])) {
        $message = $api->editData($_SESSION['links']->calm, $data);
    }
}

//Deals with deleting new data
if(isset($_POST['deleteId'])){
    $data['periods'] = array((int)$_POST['deleteId']);
    if(isset($_SESSION['links'])) {
        $message = $api->deleteData($_SESSION['links']->calm, $data);
    }
}

//Reads the data for the page
if(isset($_SESSION['links'])) {
    $dataPoints = $api->getData($_SESSION['links']->calm);
    if (isset($dataPoints->error)) {
        $_SESSION['links'] = null;
        header('Location: ./errorPage.php');
    }
}
else{
    header('Location: ./errorPage.php');
}

//When you select a new week
if (isset($_GET['weekDate'])){
    $selectedDate = new DateTime($_GET['weekDate']);
    $time = "this monday";
    echo '<script> document.onload = showWeekChart();</script>';
}
//when you select a new month
else if (isset($_GET['monthDate'])){
    $selectedDate = new DateTime($_GET['monthDate']);
    $time  ="last monday";
}
//if nothing selected goes to today
else {
    $selectedDate = new DateTime('today');
    $time  ="last monday";
}
$date = $selectedDate->format(DateTime::ISO8601);
$date = strtotime($date);


//sets the strings for the value of the dates
$setWeekDate = date('o', $date) . "-W" . date('W', $date);

$setMonthDate = date('Y', $date)."-".date('m', $date);

$date = $selectedDate->format(DateTime::ISO8601);

//For week display

//Gets the start and end of the selected week
$ts = strtotime($date);
$week = date('W', $ts);
$start = strtotime($time, $ts);
$end = strtotime('+1 weeks -1 minute', $start);
$start = date('Y-m-d H:i' , $start);
$end = date('Y-m-d H:i', $end);

//finds the data required for the table
$weekPoints = array();
$weekPoints = array(0,0,0,0,0,0,0);
$average = 0;
$i = 0;

foreach ($dataPoints->periods as $data) {
    if (isset($data->id)) {

        $i++;

        $average = $average + $data->duration;
        $startTime = date('Y-m-d H:i', strtotime($data->start_time));
        if (($startTime >= $start) && ($startTime <= $end)) {

            $n = -1;
            $weekPoints[date("N", strtotime($data->start_time)) + $n] = $data->duration;
        }
    }


}


if ($average > 0) {
    $average =number_format((($average / $i))) ;
}


//For month display

//Gets the start and end of the selected month
$ts = strtotime($date);
$start = (date('W', $ts) == 0) ? $ts : strtotime("first day of this month", $ts);

$end =  strtotime("last day of this month", $ts);
$start = date('Y-m-d H:i' , $start);
$end = date('Y-m-d H:i', $end);

//fills the month labels
$monthLabels = "";
for ($i = 1; $i <= date("t", strtotime($date)); $i++)
{
    $monthLabels = $monthLabels.$i.",";
}

//fills the data array with the correct data to use
$monthPoints = array_fill(0, date("t", strtotime($date)), 0);;
//sets the start and end of the current sleep to get the message on progress
$progress_message="0";
$last_night = new DateTime('today');
$last_night_start = $last_night->format('Y-m-d');
foreach ($dataPoints->periods as $data)
{
    if (isset($data->id)) {
        $startTime = date('Y-m-d H:i', strtotime($data->start_time));
        if (($startTime >= $start) && ($startTime <= $end)) {
            $n = -1;
            $monthPoints[date("j", strtotime($data->start_time)) + $n] = $data->duration;
        }
        //finds the progress message for the current sleep period
        $startTime = date('Y-m-d', strtotime($data->start_time));
        if (($startTime == $last_night_start)){
            $progress_message = $progress_message + $data->progress_percentage;
        }

    }

}
