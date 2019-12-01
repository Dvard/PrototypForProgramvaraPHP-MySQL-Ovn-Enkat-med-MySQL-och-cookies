<?php
include_once 'db_connect.php';

function getVotePercentage($conn) {
    $results = getResults($conn);

    $yes_perc = $results['yes'] * 100 / $results['total'];
    $no_perc = $results['no'] * 100 / $results['total'];

    return array('yes' => $yes_perc, 'no' => $no_perc);
}

function getResults($conn) {
    $results = array('yes' => 0, 'no' => 0, 'total' => 0);
    $sql = 'SELECT yes, no FROM poll_22_11_2019';
    $result = $conn->query($sql);

    foreach ($result->fetchAll() as $result) {
        $results['yes'] = $results['yes'] + $result['yes'];
        $results['no'] = $results['no'] + $result['no'];
        $results['total'] = $results['total'] + 1;
    }

    return $results;
}

function ip_checker($conn) {
    $sql = 'SELECT * FROM poll_22_11_2019 WHERE ip=:ip';
    $stmt = $conn->prepare($sql);
    $stmt->execute(['ip' => $_SERVER['REMOTE_ADDR']]);

    if (empty($stmt->fetchAll())) {
        return false;
    } else {
        return true;
    }
}

function vote($conn, $vote) {
    $row = [
        ':yes' => $vote[0],
        ':no' => $vote[1],
        ':ip' => $_SERVER['REMOTE_ADDR']
    ];

    $sql = 'INSERT INTO poll_22_11_2019 (yes, no, ip) VALUES(:yes, :no, :ip)';
    $result = $conn->prepare($sql);
    $res = $result->execute($row);

}

function shower($conn, $isReversed) {
    $prefix = 'display: ';
    if (ip_checker($conn)) {
        if (!$isReversed) {
            return $prefix . 'none;';
        } else {
            return $prefix . 'block;';
        }
    } else {
        if (!$isReversed) {
            return $prefix . 'block;';
        } else {
            return $prefix . 'none;';
        }
    }
}

if (isset($_POST) and !empty($_POST) and ip_checker($conn) == 0) {
    vote($conn, array(($_POST['vote'] == 'Ja') ? 1 : 0, ($_POST['vote'] == 'Ja') ? 0 : 1));
}

$results = getVotePercentage($conn);
?>

<!DOCTYPE html>
<html lang="sv">
<head>
    <title>Poll</title>
	<link rel="stylesheet" type="text/css" href="styleAll.css">
	<link rel="stylesheet" type="text/css" href="style1.css">
</head>
<body>
<div id="poll" style="<?php echo shower($conn, false)?>">
    <h1>Dagens Fråga:</h1>
    <h2>Är du hungrig?</h2>
    <form action="index.php" method="POST">
        <input type="submit" name="vote" value="Ja" class="vote-btn">
        <input type="submit" name="vote" value="Nej" class="vote-btn vote-negative">
    </form>
</div>
<div id="results" style="<?php echo shower($conn, true) ?>">
    <h2 id="notice">Du har redan röstat</h2>
    <h1>Resultat:</h1>
    <div id="result-bar">
        <div id="bar-yes" style="width: <?php echo $results['yes'] . '%'?>">
            <p>Ja <?php echo $results['yes']?>%</p>
        </div>
        <div id="bar-no" style="width: <?php echo $results['no'] . '%'?>">
            <p>Nej <?php echo $results['no']?>%</p>
        </div>
    </div>
</div>
</body>
</html>