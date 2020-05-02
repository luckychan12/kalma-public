<?php
session_start();

require_once "../controller/ensureFingerprint.php";
require_once "../api_tasks/ApiConnector.php";

const UTC = DateTimeZone::UTC;
$api = ApiConnector::getConnection();


// If the user is not logged in, redirect to login
if (!isset($_SESSION['auth'])) {
    $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === "on") ? 'http' : 'http';
    header('Location: ./loginAndSignup?redirect=' . urlencode("$protocol://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"));
}


// If there is an edit/create form submission
if(isset($_POST['startDate'])){
    $periods = [
        [
            'start_time' => (new DateTime($_POST['startDate'] .' '. $_POST['startTime']))->format(DATE_ISO8601),
            'stop_time' => (new DateTime($_POST['endDate'] .' '. $_POST['endTime']))->format(DATE_ISO8601),
            'sleep_quality' => $_POST['sleepQuality']
        ],
    ];

    // Default to creating a new period
    $method = 'POST';

    // Include the period ID if an existing period is being edited,
    // and use the PUT method to Update the CRUD resource
    if (isset($_POST['editId'])) {
        $periods[0]['id'] = (int)$_POST['editId'];
        $method = 'PUT';
    }

    // Send the request
    $message = $api->request($method, $_SESSION['links']->sleep, ['periods' => $periods], true);
}


// Deals with deleting existing data
if(isset($_POST['deleteId'])){
    $data['periods'] = [(int)$_POST['deleteId']];
    $message = $api->request('DELETE', $_SESSION['links']->sleep, $data, true);
}


//Reads the data for the page
$dataPoints = $api->getData($_SESSION['links']->sleep);
if (isset($dataPoints->error)) {
    $_SESSION['links'] = null;
    header("Location: ./error.php?code=$dataPoints->error&message=$dataPoints->message");
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
$start = strtotime('+16 hours', $start);
$end = strtotime('+1 weeks -1 minute', $start);
$start = date('Y-m-d H:i' , $start);
$end = date('Y-m-d H:i', $end);

//finds the data required for the table
$weekPoints = array();
$weekPoints = array(0,0,0,0,0,0,0);
$average = 0;
$averageStartTime = 0;
$totalStartTime = 0;
$totalEndTime = 0;
$averageEndTime=0;
$i = 0;

foreach ($dataPoints->periods as $data) {
    if (isset($data->id)) {

        $i++;

        $average = $average + $data->duration;
        $startTime = date('Y-m-d H:i', strtotime($data->start_time));
        if (($startTime >= $start) && ($startTime <= $end)) {

            $n = -1;
            if (date("G", strtotime($data->start_time)) <= 16) {
                $n--;
            }
            $weekPoints[date("N", strtotime($data->start_time)) + $n] =$weekPoints[date("N", strtotime($data->start_time)) + $n] +  (float)number_format(($data->duration / 60), 2, ".","" );
        }
        if(date('H', strtotime($data->start_time)) > 16){
            $totalStartTime += (date('H', strtotime($data->start_time))-16)*60 + date('i', strtotime($data->start_time));
        }
        else{
            $totalStartTime += (date('H', strtotime($data->start_time))+8)*60 + date('i', strtotime($data->start_time));
        }
        if(date('H', strtotime($data->stop_time)) > 16){
            $totalEndTime += (date('H', strtotime($data->stop_time))-16)*60 + date('i', strtotime($data->stop_time));
        }
        else{
            $totalEndTime += (date('H', strtotime($data->stop_time))+8)*60 + date('i', strtotime($data->stop_time));
        }
    }


}


if ($average > 0) {
    $total =($average / $i)/60;
    $hours = floor($total);
    $mins = ($total-$hours) *60;
    $average =sprintf("%2.0f Hours %2.0f Minutes", $hours, $mins);
    $total =(($totalStartTime / $i)/60);
    $hours = floor($total);
    $mins = ($total-$hours) *60;
    $hours += 16;
    if ($hours >23){ $hours -= 24;}
    $averageStartTime = sprintf("%02.0f:%02.0f", $hours, $mins);
    $total =(($totalEndTime / $i)/60);
    $hours = floor($total);
    $mins = ($total-$hours) *60;
    $hours += 16;
    if ($hours >23){ $hours -= 24;}
    $averageEndTime = sprintf("%02.0f:%02.0f", $hours, $mins);
}


//For month display

//Gets the start and end of the selected month
$ts = strtotime($date);
$start = (date('W', $ts) == 0) ? $ts : strtotime("first day of this month", $ts);
$start = strtotime('+16 hours', $start);

$end =  strtotime("last day of this month", $ts);
$end = strtotime('+1 days +16 hours -1 min', $end);
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
$last_night = new DateTime('4pm');
$last_night_start = $last_night->format('Y-m-d H:i');
$last_night_end = $last_night->modify("+1day");
$last_night_end =$last_night_end->format('Y-m-d H:i');

foreach ($dataPoints->periods as $data)
{
    if (isset($data->id)) {
        $startTime = date('Y-m-d H:i', strtotime($data->start_time));
        if (($startTime >= $start) && ($startTime <= $end)) {
            $n = -1;
            if (date("G", strtotime($data->start_time)) <= 16) {
                $n--;
            }
            $monthPoints[date("j", strtotime($data->start_time)) + $n] = $monthPoints[date("j", strtotime($data->start_time)) + $n] + (float)number_format(($data->duration / 60), 2, ".","" );
        }
        //finds the progress message for the current sleep period
        if (($startTime >= $last_night_start) && ($startTime < $last_night_end)){
            $progress_message = $progress_message + $data->progress_percentage;
        }

    }

}
