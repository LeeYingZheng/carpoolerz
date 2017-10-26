<?php
    session_start();

    $username = $_SESSION['username'];
    $password = $_SESSION['password'];

    $dbconn = pg_connect("host=localhost port=5432 dbname=carpoolerz user=postgres password=postgres")
    or die('Could not connect: ' . pg_last_error());

    $query = /** @lang text */
        "SELECT * FROM systemuser WHERE username = '$username' AND password = '$password'";

    $result = pg_query($dbconn, $query);

    if (pg_num_rows($result) == 0) {
        header("Location: ../login.php");
    }

    $target_rideID = $ride_driver = $highest_bid = $current_passenger = $from_address = $to_address = $start_time = $end_time = "";

    if ($_GET['ride_id']) {
        $target_rideID = $_GET['ride_id'];
    } elseif (isset($_POST['editBidsTrigger'])) {
        $target_rideID = $_POST['p_rideID'];
    } else {
        echo "<h1>Error. No ride ID detected.<h1/>";
    }

    $get_ride_info_query = /** @php text */
        "SELECT * FROM ride WHERE ride_id = '$target_rideID'";

    $ride_info_result = pg_query($dbconn, $get_ride_info_query);

    $ride_info = pg_fetch_array($ride_info_result, NULL, PGSQL_ASSOC);

    $ride_driver = $ride_info["driver"];
    $highest_bid = $ride_info["highest_bid"];
    $current_passenger = $ride_info["passenger"];
    $from_address = $ride_info["from_address"];
    $to_address = $ride_info["to_address"];
    $start_time = $ride_info["start_time"];
    $end_time = $ride_info["end_time"];


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php include '../header.shtml'; ?>
    <link href="../main.css" rel="stylesheet" />
</head>

<body>
<?php include 'navbar-user.shtml'; ?>

<div class="container">
    <div class="container-fluid">
        <br/>
        <div class="page-header">
            <h2 class="text-center">Create/Update/Delete Your Bids:</h2>
        </div>
        <form role="form" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" name="edit_bid">
            <div class="form-group">
                <label><b>Driver: <?php echo $ride_driver ?></label>
            </div>

            <div class="form-group">
                <label><b>Pick Up Point:<b/> <?php echo $from_address ?></label>
            </div>

            <div class="form-group">
                <label><b>Drop Off Point:<b/> <?php echo $to_address ?></label>
            </div>

            <div class="form-group">
                <label><b>Start Time:<b/> <?php echo $start_time ?></label>
            </div>

            <div class="form-group">
                <label><b>Highest Bid:<b/> <?php echo $highest_bid ?></label>
            </div>

            <div class="form-group">
                <label for="edit_bid"><b>Create/Edit Bid:<b/> </label>
                <input type="text" name="p_bid" class="form-control" id="p_bid" placeholder="Current Highest Bid: <?php echo $highest_bid ?>"/>
            </div>
            <input type="hidden" name="p_rideID" value="<?php echo $target_rideID; ?>"/>
            <button type="submit" name="editBidsTrigger" class="form-control btn btn-primary">PLACE BID</button>
            <br/>
        </form>
    </div>
</div>

<?php

    if (isset($_POST['editBidsTrigger'])) {

        // Prepare relevant post variables
        $new_bid = $_POST['p_bid'];

        // echo $target_rideID;

        $check_bids_query = /** @php text */
                "SELECT * FROM bid WHERE passenger = '$username'";

        $check_bids_result = pg_query($dbconn, $check_bids_query);

        $row = pg_fetch_row($check_bids_result, null, PGSQL_ASSOC);

        // No bid + Better than highest bid --> Insert bid
        if ($new_bid > $highest_bid) {
            $insert_bid_query = /** @php text */
                    "INSERT INTO bid(amount, ride_id, passenger) values ('$new_bid', '$target_rideID', '$username')";
            pg_query($dbconn, $insert_bid_query);

            $update_rides_query = /** @php text */
                    "UPDATE ride SET highest_bid = '$new_bid' WHERE ride_id = '$target_rideID'";

            pg_query($dbconn, $update_rides_query);

            echo "<h1 class='text-center'>Bid successfully entered<h1/>";

        } else {
            echo "<h1 class='text-center'>Entered bid is lower than current winning bid!<h1/>";
            exit;
        }

    }



?>

<?php include '../footer.shtml'; ?>
</body>
