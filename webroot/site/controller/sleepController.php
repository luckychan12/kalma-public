<?php
session_start();
include_once "../api_tasks/apiConnect.php";
$api = new ApiConnect();


if(isset($_POST['startDate'])){
    $newStartTime = new DateTime($_POST['startDate'] .' '. $_POST['startTime']);
    $newStartTime = $newStartTime->format(DateTime::ISO8601);
    $newEndTime = new DateTime($_POST['endDate'] .' '. $_POST['endTime']);
    $newEndTime = $newEndTime->format(DateTime::ISO8601);
    $message = $api->addSleepData($newStartTime,$newEndTime,$_POST['sleepQuality']);
    if (isset($message->error)) {
        header('Location: ./errorPage.php');
    }
}


$dataPoints = $api->getData("api/user/".$_SESSION['user_id']."/sleep");
if (isset($dataPoints->error)){
    header('Location: ./errorPage.php');
}



if (isset($_GET['weekDate'])){
    $selectedDate = new DateTime($_GET['weekDate']);
    $time = "this monday";
    echo '<script> document.onload = showWeekChart();</script>';
}
else if (isset($_GET['monthDate'])){
    $selectedDate = new DateTime($_GET['monthDate']);
    $time  ="last monday";
}
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
            $weekPoints[date("N", strtotime($data->start_time)) + $n] = $data->duration / 60;
        }
    }


}


if ($average > 0) {
    $average =number_format((($average / $i) / 60), 2, '.', '') ;
}


//For month display

//Gets the start and end of the selected month
$ts = strtotime($date);
$start = (date('w', $ts) == 0) ? $ts : strtotime("first day of this month", $ts);
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
$progress_message="0% of your daily goal";
$last_night = new DateTime('4pm');
$last_night_start = $last_night->format(DateTime::ISO8601);
$last_night_end = $last_night->modify("+1day");
$last_night_end =$last_night_end->format(DateTime::ISO8601);

foreach ($dataPoints->periods as $data)
{
    if (isset($data->id)) {
        $startTime = date('Y-m-d H:i', strtotime($data->start_time));
        if (($startTime >= $start) && ($startTime <= $end)) {
            $n = -1;
            if (date("G", strtotime($data->start_time)) <= 16) {
                $n--;
            }
            $monthPoints[date("j", strtotime($data->start_time)) + $n] = $data->duration / 60;
        }
        //finds the progress message for the current sleep period
        if (($startTime >= $last_night_start) && ($startTime < $last_night_end)){
            $progress_message = $data->progress_message;
        }

    }

}