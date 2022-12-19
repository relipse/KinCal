<?php
require_once(__DIR__.'/lib/Calendar.php');
if (!empty($_POST['events'])){
    file_put_contents(__DIR__.'/data/events.txt', $_POST['events']);
}
?>
<!doctype HTML>
<html lang="en">
<head>
    <title>KinCal Text</title>
    <link rel="stylesheet" type="text/css" href="lib/calendar.css">
    <link rel="stylesheet" type="text/css" href="index.css">
    <script src="jslib/html2canvas.min.js"></script>
</head>
<body>
<?php
$cal = new Calendar(['num_weeks_visible'=>6]);
$handle = fopen(__DIR__.'/data/events.txt', "r");
if ($handle) {
    while (($line = fgets($handle)) !== false) {
        // process the line read.
        if (strpos($line, '//') === 0 || strpos($line, '#') === 0){
            //this is a comment line in the text file, just ignore it
            continue;
        }
        try {
            if (empty($line)){
                continue;
            }
            $parts = explode('|', $line);
            $datetime = (trim($parts[0]));
            $dparsed = date_parse($datetime);
            if ($dparsed['hour'] === false){
                $timeincluded = false;
            }else{
                $timeincluded = true;
            }
            //make sure there were no errors when parsing the date
            if (empty($dparsed['error_count'])) {
                $dt = new \DateTime(trim($datetime));
                $cal->add_event_details_ary([
                    'date' => $dt->format('Y-m-d H:i:s'),
                    'short_title' => trim($parts[1] ?? ''),
                    'color' => trim($parts[2] ?? ''),
                    'time_included' => $timeincluded,
                ]);
            }
        }catch(\Throwable $e){
            continue;
        }
    }

    fclose($handle);
}

echo $cal;
?>
<script>
    function shareCal() {
        const screenshotTarget = document.querySelector('div.calendar');

        html2canvas(screenshotTarget).then((canvas) => {
            const base64image = canvas.toDataURL("image/png");

            document.getElementById('share').innerHTML = '<img src="'+base64image+'" alt="Calendar">';
            //window.location.href = base64image;
        });
    }
</script>
<a href="#ss" onclick="shareCal(); return false">Share</a>
<div id="share"></div>
<form class="frmUpdateEvents" method="post">
    <h2>Update Events</h2>
    <p>First put the datetime, followed by a pipe | and then a description and optionally
        follow by another pipe and red, blue, or green to change the color of the event.
        Use // or # at the beginning of the line to add comments (which are ignored)
    </p>
    <textarea name="events"><?=htmlentities(file_get_contents(__DIR__.'/data/events.txt'))?></textarea>
    <button type="submit">Update Events</button>
</form>
</body>
</html>
