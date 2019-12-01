<?php
include_once 'db_connect.php';

function getVotePercentage($conn) {
    $results = getResults($conn);

    $none_perc = $results['none'] * 100 / $results['total'];
    $some_perc = $results['some'] * 100 / $results['total'];
    $more_perc = $results['more'] * 100 / $results['total'];

    return array('none' => $none_perc, 'some' => $some_perc, 'more' => $more_perc);
}

function getResults($conn) {
    $results = array('none' => 0, 'some' => 0, 'more' => 0, 'total' => 0, 'comment' => '');
    $sql = 'SELECT none, some, more, comment FROM poll_24_11_2019';
    $result = $conn->query($sql);

    foreach ($result->fetchAll() as $result) {
        $results['none'] = $results['none'] + $result['none'];
        $results['some'] = $results['some'] + $result['some'];
        $results['more'] = $results['more'] + $result['more'];
        $results['total'] = $results['total'] + 1;
        $results['comment'] = $result['comment'];
    }

    return $results;
}

function ip_checker($conn) {
    $sql = 'SELECT * FROM poll_24_11_2019 WHERE ip=:ip';
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
        ':none' => $vote[0],
        ':some' => $vote[1],
        ':more' => $vote[2],
        ':comment' => $vote[3],
        ':ip' => $_SERVER['REMOTE_ADDR']
    ];

    $sql = 'INSERT INTO poll_24_11_2019 (none, some, more, comment, ip) VALUES(:none, :some, :more, :comment, :ip)';
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

function getComments($conn) {
    $comments = array();

    $sql = 'SELECT comment FROM poll_24_11_2019';
    $result = $conn->query($sql);

    foreach ($result->fetchAll() as $comment) {
        array_push($comments, $comment['comment']);
    }

    return $comments;
}

if (isset($_POST) and !empty($_POST) and ip_checker($conn) == 0) {
    vote(
        $conn, array(
            ($_POST['vote'] == 'none') ? 1 : 0,
            ($_POST['vote'] == 'some') ? 1 : 0,
            ($_POST['vote'] == 'more') ? 1 : 0,
            $_POST['comment']
        )
    );
}

$results = getVotePercentage($conn);
$comments = getComments($conn);
?>

<!DOCTYPE html>
<html lang="sv">
<head>
    <title>Poll 2</title>
	<link rel="stylesheet" type="text/css" href="styleAll.css">
	<link rel="stylesheet" type="text/css" href="style2.css">
</head>
<body>
<div id="poll" style="<?php echo shower($conn, false)?>">
    <h1>Dagens Fråga:</h1>
    <h2>Hur många syskon har du?</h2>
    <form action="poll2.php" method="POST">
        <label>Inga
            <input type="radio" name="vote" value="none" class="vote-btn">
        </label>
	    <br>
        <label>1-2
            <input type="radio" name="vote" value="some" class="vote-btn">
        </label>
	    <br>
        <label>Fler än 2
            <input type="radio" name="vote" value="more" class="vote-btn">
        </label>
	    <br>
        <label>Kommentar:
            <input type="text" name="comment">
        </label>
	    <br>
        <input type="submit" value="Skicka" class="vote-btn">
    </form>
</div>
<div id="results" style="<?php echo shower($conn, true) ?>">
    <h2 id="notice">Du har redan röstat</h2>
    <h1>Resultat:</h1>

    <p>Inga: <?php echo $results['none']?>%</p>
	<div id="bar-none" class="bar" style="width: <?php echo $results['none'] . '%'?>"></div>
    <div class="result-bar bar" style="width: <?php  echo 100-$results['none']?>%;"></div>

    <p>1-2: <?php echo $results['some']?>%</p>
	<div id="bar-some" class="bar" style="width: <?php echo $results['some'] . '%'?>"></div>
    <div class="result-bar bar" style="width: <?php  echo 100-$results['some']?>%;"></div>

    <p>Fler än 2: <?php echo $results['more']?>%</p>
	<div id="bar-more" class="bar" style="width: <?php echo $results['more'] . '%'?>"></div>
    <div class="result-bar bar" style="width: <?php  echo 100-$results['more']?>%;"></div>

    <h2 id="comments-title">Kommentarer:</h2>
    <div class="comments">
        <?php foreach ($comments as $comment) {?>
            <div class="comment">
                <p><?php echo $comment ?></p>
            </div>
        <?php }?>
    </div>
</div>
</body>
</html>