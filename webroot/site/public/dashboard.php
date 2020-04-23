<?php
include_once "../controller/dashboardController.php";
?>
<!doctype html>
<html lang='en'>
<head>
    <?php include_once './components/global_head_inner.php'; ?>
    <?php include_once './components/charts_head.php'; ?>
    <link rel="stylesheet" href="assets/stylesheets/dashboard.css">
    <title>Dashboard | Kalma</title>
</head>
<body>
    <?php include_once './components/navbar_top.php'; ?>

    <main class="container-fluid">
        <h1><span id="greeting">Dashboard</h1>
        <div class="row" id="chartContainer">
            <div class="col-md-6 col-xl-4 mb-4">
                <div class="chart-group" id="sleepCard">
                    <h2>Sleep</h2>
                    <canvas class="chart periodic" id="sleepChart"></canvas>
                    <script type="text/javascript">
                        $("#sleepChart").data('chart-data', <?= json_encode($data->sleep_periods); ?>);
                    </script>
                    <div class="stat-list">
                        <?php
                        if (isset($data->sleep_stats)) {
                            foreach ($data->sleep_stats as $stat) echo
                            <<<HTML
                        <div class="row">
                            <div class="col-6 col-lg-4 col-xl-3 stat-label">$stat->label: </div>
                            <div class="col-6 col-lg-3 col-xl-2 stat-value">$stat->value</div>
                            <div class="col-lg-5 col-xl-7 stat-progress">
                                <div class="progress">
                                    <div class="progress-bar" style="width: $stat->width%" role="progressbar" aria-valuenow="$stat->value" aria-valuemin="0" aria-valuemax="100"></div>
                                    <div class="progress-bar progress-target" style="left: $stat->target_offset%;" role="progressbar" aria-valuenow="$stat->target" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                            </div>
                        </div>
                        <hr class="d-lg-none">
HTML;
                        }
                        else {
                            echo '<div class="error">No Data Available.</div>';
                        }
                        ?>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-4 mb-4">
                <div class="chart-group" id="calmCard">
                    <h2>Mindful Minutes</h2>
                    <canvas class="chart periodic" id="calmChart"></canvas>
                    <script type="text/javascript">
                        $("#calmChart").data('chart-data', <?= json_encode($data->calm_periods);?>);
                    </script>
                    <div class="stat-list">
                        <?php
                        if (isset($data->calm_stats)) {
                            foreach ($data->calm_stats as $stat) echo
                            <<<HTML
                        <div class="row">
                            <div class="col-6 col-lg-4 col-xl-3 stat-label">$stat->label: </div>
                            <div class="col-6 col-lg-3 col-xl-2 stat-value">$stat->value</div>
                            <div class="col-lg-5 col-xl-7 stat-progress">
                                <div class="progress">
                                    <div class="progress-bar" style="width: $stat->width%" role="progressbar" aria-valuenow="$stat->value" aria-valuemin="0" aria-valuemax="100"></div>
                                    <div class="progress-bar progress-target" style="left: $stat->target_offset%;" role="progressbar" aria-valuenow="$stat->target" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                            </div>
                        </div>
                        <hr class="d-lg-none">
HTML;
                        }
                        else {
                            echo '<div class="error">No Data Available.</div>';
                        }
                        ?>
                    </div>
                </div>
            </div>
            <div class="col-md-6 offset-md-3 col-xl-4 offset-xl-0 mb-4">
                <div class="chart-group" id="stepsCard">
                    <h2>Daily Steps</h2>
                    <canvas class="chart logged" id="stepsChart"></canvas>
                    <script type="text/javascript">
                        $("#stepsChart").data('chart-data', <?= json_encode($data->steps_log);?>);
                    </script>
                    <div class="stat-list">
                        <?php
                        if (isset($data->steps_stats)) {
                            foreach ($data->steps_stats as $stat) echo
<<<HTML
                        <div class="row">
                            <div class="col-6 col-lg-4 col-xl-3 stat-label">$stat->label: </div>
                            <div class="col-6 col-lg-3 col-xl-2 stat-value">$stat->value</div>
                            <div class="col-lg-5 col-xl-7 stat-progress">
                                <div class="progress">
                                    <div class="progress-bar" style="width: $stat->width%" role="progressbar" aria-valuenow="$stat->value" aria-valuemin="0" aria-valuemax="100"></div>
                                    <div class="progress-bar progress-target" style="left: $stat->target_offset%;" role="progressbar" aria-valuenow="$stat->target" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                            </div>
                        </div>
                        <hr class="d-lg-none">
HTML;
                        }
                        else {
                            echo '<div class="error">No Data Available.</div>';
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script type="text/javascript" src="assets/scripts/dashboard.js"></script>
</body>

