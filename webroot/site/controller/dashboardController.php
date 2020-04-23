<?php
session_start();
include_once '../api_tasks/ApiConnector.php';

/**
 * Make a GET request to the API
 * and return the response body as an assoc array.
 * Redirects to the error page if there's an error response
 *
 * @param string $link The URI to GET
 *
 * @param array $params
 * @return object The response body
 */
function get(string $link, array $params = []) : ?object
{
    $api = ApiConnector::getConnection();
    $data = $api->getData($link, $params);
    if (isset($data->error)) {
        header("Location: ./error.php?message=$data->message&code=$data->error");
        exit();
    }
    return $data;
};

/**
 * Convert an array of sleep/calm periods, as served by the API, into a Chart.js compatible data object
 *
 * @param array  $periods   All periods in the last 7 days
 * @param string $name      The name of the dataset
 * @param array  $rgb       The colour of the chart
 * @param bool   $offsetDay If true, early-morning periods will be counted in the previous day's total
 *
 * @return object
 */
function format_periods(array $periods, string $name, array $rgb, bool $offsetDay = false) : ?object
{
    // Get the last 7 days as a DatePeriod object
    try {
        $start = new DateTime('- 1 week');
        $end = new DateTime('now');

        $interval = DateInterval::createFromDateString('1 day');
        $week_day = new DatePeriod($start, $interval, $end);
    }
    catch (Exception $e) {
        return null;
    }

    // Iterate over the last 7 days
    $labels = $data = [];
    foreach ($week_day as $day) {
        $label = $day->format('D');
        // Count total minutes recorded on this day
        $total_duration = 0;
        foreach ($periods as $period) {
            $start_time = DateTime::createFromFormat(DATE_ISO8601, $period->start_time);
            if ($offsetDay) $start_time = $start_time->sub(DateInterval::createFromDateString('8 hours'));
            $period_day = $start_time->format('D');
            if ($period_day == $label) {
                $total_duration += $period->duration;
            }
        }
        $labels[] = $label;
        $data[] = $total_duration;
    }

    $bg_rgba = array_merge($rgb, [0.3]);
    $line_rgba = array_merge($rgb, [1]);

    $dataset = array(
        'label' => $name,
        'data' => $data,
        'backgroundColor' => "rgba(" . implode(', ', $bg_rgba) . ")",
        'borderColor' => "rgba(" . implode(', ', $line_rgba) . ")",
        'lineTension' => 0,
    );

    return (object)['labels' => $labels, 'datasets' => [$dataset]];
}

/**
 * Convert the array of daily steps entries, as served by the API, to a Chart.js data object
 *
 * @param array $entries
 * @param array $rgb
 * @return object
 */
function format_steps(array $entries, array $rgb) : object {
    // Get the last 7 days as a DatePeriod object
    try {
        $start = new DateTime('- 1 week');
        $end = new DateTime('now');

        $interval = DateInterval::createFromDateString('1 day');
        $week_day = new DatePeriod($start, $interval, $end);
    }
    catch (Exception $e) {
        return null;
    }

    $labels = $data = [];
    $i = 0;
    foreach ($week_day as $day) {
        $label = $day->format('D');
        $labels[] = $label;
        if ($i < count($entries)) {
            $entry = $entries[$i];
            $day_logged = DateTime::createFromFormat(DATE_ISO8601, $entry->date_logged)->format('D');
            if ($day_logged == $label) {
                $data[] = $entry->step_count;
                $i++;
                continue;
            }
        }
        // If there are no more entries, or the next entry isn't for today, default to 0
        $data[] = 0;
    }

    $bg_rgba = array_merge($rgb, [0.3]);
    $line_rgba = array_merge($rgb, [1]);

    $dataset = array(
        'label' => 'Daily Steps',
        'data' => $data,
        'backgroundColor' => "rgba(" . implode(', ', $bg_rgba) . ")",
        'borderColor' => "rgba(" . implode(', ', $line_rgba) . ")",
        'lineTension' => 0,
        'steppedLine' => 'middle',
    );

    return (object)['labels' => $labels, 'datasets' => [$dataset]];
}

/**
 * Calculate the average duration, in minutes, of an array of periodic data served by the API
 * @param array $periods
 * @return int
 */
function average_duration(array $periods) : int
{
    if (count($periods) < 1) return 0;
    $sum = 0;
    foreach ($periods as $period) {
        $sum += $period->duration;
    }
    return floor($sum / count($periods));
}

function average_steps(array $entries) : int
{
    if (count($entries) < 1) return 0;
    $sum = 0;
    foreach ($entries as $entry) {
        $sum += $entry->step_count;
    }
    return floor($sum / count($entries));
}

/**
 * Convert an integer number of minutes into a string of the form "00h 00m",
 * where either segment will be omitted if possible
 * @param int $mins
 * @return string
 */
function to_time_string(int $mins) : string
{
    $h = floor($mins / 60) . 'h';
    $m = $mins % 60 . 'm';
    if ($mins >= 60) {
        return $h . ($mins   % 60 > 0 ? " $m" : '');
    }
    return $m;
}

/**
 * Take a periodic data API response and produce an array of stats to be added to the view
 * @param object $periodic_data
 * @return array
 */
function build_periodic_stats(object $periodic_data) : array
{
    $avg_duration = average_duration($periodic_data->periods);
    $target = $periodic_data->target;
    if ($target === null || $target === 0) $target = 510; // Default to 8h 30m
    return array(
        (object)array(
            'label' => '7-day Average',
            'value' => to_time_string($avg_duration),
            'width' => $avg_duration < 1 ? 1 : ($avg_duration / $target) * 75,
            'target' => to_time_string($target),
            'target_offset' => 74,
        ),
    );
}

/**
 * Get User & well-being data to populate the view
 */

$data = new stdClass();

if (!isset($_SESSION['auth'])) {
    header('Location: ./login-and-signup.php?redirect=dashboard');
    exit();
}

$account_data = get($_SESSION['links']->account);
$data->user = $account_data->user;
$data->links = $account_data->links;

$lastWeek = date('Y-m-d', strtotime('-1 week 8 hours'));

$params = ['from' => $lastWeek];

$sleep_data = get($data->links->sleep, $params);
$calm_data = get($data->links->calm, $params);
$steps_data = get($data->links->steps, $params);


if ($sleep_data !== null) {
    $data->sleep_stats = build_periodic_stats($sleep_data);
    $data->sleep_periods = format_periods($sleep_data->periods, 'Daily Sleep', [156, 39, 176], true);
}
else {
    $data->sleep_periods = [];
}

if ($calm_data !== null) {
    $data->calm_stats = build_periodic_stats($calm_data);
    $data->calm_periods = format_periods($calm_data->periods, 'Mindful Minutes', [233, 30, 99]);
}
else {
    $data->calm_periods = [];
}

if ($steps_data !== null) {
    $avg_steps = average_steps($steps_data->entries);
    $steps_target = $steps_data->target;
    if ($steps_target === null || $steps_target === 0) $steps_target = 10000; // Default to 10 000
    $data->steps_stats = array(
        (object)array(
            'label' => '7-day Average',
            'value' => $avg_steps,
            'width' => $avg_steps < 1 ? 1 : ($avg_steps / $steps_target) * 75,
            'target' => $steps_target,
            'target_offset' => 74,
        ),
    );
    $data->steps_log = format_steps($steps_data->entries, [77, 208, 225]);
}
else {
    $data->steps_log = [];
}