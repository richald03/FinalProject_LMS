<?php
session_start();

// Ensure the teacher is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'teacher') {
    header("Location: index.php");
    exit();
}

// Simulate an event data structure (in a real-world scenario, this should be fetched from a database)
$events = isset($_SESSION['events']) ? $_SESSION['events'] : [];

// Handle event submissions (from the front-end form in the main dashboard)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['event_date'], $_POST['event_description'])) {
    $event_date = $_POST['event_date'];
    $event_description = $_POST['event_description'];

    // Add the event to the events array
    $events[] = ['date' => $event_date, 'description' => $event_description];
    $_SESSION['events'] = $events;  // Save the updated events array back to the session
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calendar - TipTopLearn</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f7fa;
            color: #333;
        }

        .container {
            max-width: 900px;
            margin: 50px auto;
        }

        h2 {
            color: #007bff;
            text-align: center;
            margin-bottom: 30px;
        }

        .calendar-container {
            background-color: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .datepicker-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .datepicker-container input {
            width: 60%;
            font-size: 1.2rem;
        }

        .event-form {
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 10px;
            margin-top: 20px;
            display: none;
        }

        .event-form h3 {
            color: #007bff;
        }

        .event-form textarea {
            width: 100%;
            height: 100px;
            border-radius: 5px;
            border: 1px solid #ddd;
            padding: 10px;
        }

        .btn-submit {
            background-color: #007bff;
            color: #fff;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
        }

        .btn-submit:hover {
            background-color: #0056b3;
        }

        .event-list {
            margin-top: 40px;
        }

        .event-list ul {
            list-style-type: none;
            padding: 0;
        }

        .event-list li {
            padding: 15px;
            background-color: #e7f3ff;
            margin-bottom: 10px;
            border-radius: 5px;
            display: flex;
            justify-content: space-between;
            color: #333;
        }

        .event-list .date {
            font-weight: bold;
        }

        .event-list .description {
            max-width: 75%;
        }

        /* Styling for the No events message */
        .no-events {
            font-style: italic;
            color: #bbb;
        }

        /* Hover effect for events */
        .event-list li:hover {
            background-color: #c6e0ff;
        }

        /* Form inputs and labels */
        .form-group label {
            font-weight: bold;
        }

        .cancel-btn {
            background-color: #f0f0f0;
            border: 1px solid #ddd;
            color: #333;
            padding: 10px 15px;
            border-radius: 5px;
        }

        .cancel-btn:hover {
            background-color: #e1e1e1;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Teacher's Calendar & Scheduling</h2>

    <!-- Calendar and Event Form -->
    <div class="calendar-container">
        <!-- Datepicker Input -->
        <div class="datepicker-container">
            <label for="datepicker">Pick a Date:</label>
            <input type="text" id="datepicker" class="form-control">
        </div>

        <!-- Event Form -->
        <div id="event-form" class="event-form">
            <h3>Add Event Description</h3>
            <form action="calendar.php" method="POST">
                <div class="form-group">
                    <label for="event-description">Event Description:</label>
                    <textarea id="event-description" name="event_description" placeholder="Enter event description" class="form-control"></textarea>
                </div>
                <input type="hidden" id="event-date" name="event_date">
                <button type="submit" class="btn-submit">Save Event</button>
                <button type="button" class="cancel-btn mt-2" id="cancel-event-button">Cancel</button>
            </form>
        </div>
    </div>

    <!-- Scheduled Events -->
    <div class="event-list">
        <h3>Scheduled Events</h3>
        <ul id="event-list">
            <?php
            if (!empty($events)) {
                foreach ($events as $event) {
                    echo '<li><span class="date">' . $event['date'] . ':</span> <span class="description">' . $event['description'] . '</span></li>';
                }
            } else {
                echo '<li class="no-events">No events scheduled yet.</li>';
            }
            ?>
        </ul>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>

<script>
    $(document).ready(function() {
        // Initialize datepicker with custom options
        $('#datepicker').datepicker({
            dateFormat: 'yy-mm-dd',  // Format the date as 'YYYY-MM-DD'
            onSelect: function(dateText) {
                $('#event-date').val(dateText);  // Set the hidden input value to the selected date
                $('#event-form').show();  // Show the event description form
            }
        });

        // Hide the event form when the cancel button is clicked
        $('#cancel-event-button').click(function() {
            $('#event-form').hide();
        });
    });
</script>

</body>
</html>