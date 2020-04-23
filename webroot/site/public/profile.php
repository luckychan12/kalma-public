<?php include_once '../controller/profileController.php'?>

<!doctype html>
<html lang='en'>

<head>
    <?php include_once './components/global_head_inner.php'?>
    <link rel="stylesheet" href="assets/stylesheets/profile.css">
</head>

<body>

    <?php include_once './components/navbar_top.php' ?>

    <div class="container">
        <h1>My Profile</h1>
        <div class="row">
            <div class="col-xl-3 col-lg-4">
                <div class="container-fluid" id="aside">
                    <div class="user-name"><?= ($data->user->first_name ?? 'Unknown') . " " . ($data->user->last_name ?? '') ?></div>
                    <div class="user-info">
                        <div class="email-address"><i class="far fa-envelope"></i> <?= $data->user->email_address ?? 'Unknown' ?></div>
                        <div class="date-of-birth"><i class="far fa-calendar-alt"></i> <?= $data->user->date_of_birth ?></div>
                    </div>
                </div>
            </div>
            <div class="col-xl-9 col-lg-8">
                <div class="container-fluid" id="main">
                    <section class="profile-section" id="targets">
                        <h2>My Targets</h2>
                        <hr>
                        <?php
                            $valid_targets = ['sleep', 'calm', 'steps'];
                            foreach ($valid_targets as $target) {
                                $target_name = ucfirst($target);
                                $value = $data->user->target_strings->$target;
                                echo
<<<HTML
                        <div class="list-item row">
                            <div class="target-name col-4 col-sm-6">$target_name</div>
                            <div class="target-value col-6 col-sm-4">$value</div>
                            <div class="list-item-controls col-sm"><a href="#" class="edit-target-link" data-target="$target"><i class="fas fa-pencil-alt"></i><span class="d-inline d-sm-none">Edit</span></a></div>
                        </div>
HTML;
                            }
                        ?>

                    </section>
                    <section class="profile-section" id="sessions">
                        <h2>My Logged in Devices</h2>
                        <hr>
                        <div class="list-header row">
                            <div class="col-4 col-sm-6">Device ID</div>
                            <div class="col-6 col-sm-4">Last Login</div>
                        </div>
                        <hr>
                        <?php
                            foreach ($data->user->sessions as $session) {
                                $created_time = DateTime::createFromFormat(DATE_ISO8601, $session->created_time)->format(DATE_ISO8601);
                                $created_time = str_replace(' ', '&nbsp; &nbsp;', $created_time);
                                echo
<<<HTML
                        <div class="list-item row">
                            <div class="col-4 col-sm-6">$session->client_fingerprint</div>
                            <div class="col-6 col-sm-4 session-created">$created_time</div>
                            <div class="list-item-controls col-sm"><a href="#" class="logout-session-link" data-target="$session->client_fingerprint"><i class="fas fa-trash"></i><span class="d-inline d-sm-none">Log out of this device</span></a></div>
                        </div>
HTML;
                            }
                        ?>
                    </section>
                </div>
            </div>
        </div>
    </div>

    <div id="errorModal" class="modal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Uh-oh!</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p id="errorModalDescription"></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-dismiss="modal">Continue</button>
                </div>
            </div>
        </div>
    </div>

    <div id="targetsModal" class="modal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Targets</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form>
                        <?php
                            $default = array(
                                'sleep' => 510,
                                'calm' => 30,
                                'steps' => 10000,
                            );
                            $types = array(
                                'sleep' => 'periodic',
                                'calm' => 'periodic',
                                'steps' => 'logged',
                            );
                            foreach ($valid_targets as $target) {
                                $target_name = ucfirst($target);
                                $value = $data->user->targets->$target ?? $default[$target];
                                $value_string = str_pad(floor($value / 60 % 24), 2, '0', STR_PAD_LEFT)
                                                . ':' . str_pad($value % 60, 2, '0', STR_PAD_LEFT);
                                if ($types[$target] == 'periodic') echo
<<<HTML
                        <div class="form-group">
                            <label for="email">$target_name</label><br>
                            <input class="form-control target-field" type="time" name="{$target}" id="${target}_target" min="00:00" max="24:00" value="$value_string">
                        </div>
HTML;
                                else echo
<<<HTML
                        <div class="form-group">
                            <label for="email">$target_name</label><br>
                            <input class="form-control target-field" type="number" name="{$target}" id="${target}_target" step="1000" value="$value">
                        </div>
HTML;
                            }
                        ?>

                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button id="targetsModalSave" type="button" class="btn btn-primary" data-dismiss="modal">Save</button>
                </div>
            </div>
        </div>
    </div>

    <script type="text/javascript">
        let logoutLink = "<?= $_SESSION['links']->logout ?>";
        let targetsLink = "<?= $_SESSION['links']->account ?>/targets";
        let accessToken = "<?= $_SESSION['auth']->access_token ?>";
        let fingerprint = "<?= $_SESSION['fingerprint']?>"
    </script>
    <script type="text/javascript" src="assets/scripts/profile.js"></script>
</body>

