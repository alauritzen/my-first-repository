<?php
require_once('../db_credentials.php');
require_once('../park_db_connect.php');

$limit=3;
$page=inputGet("page");
$offset = limitPage($page, $limit);
// ($page-1)*$limit;
$selection = "SELECT * FROM national_parks LIMIT $limit OFFSET $offset";

function getAllRows($dbc, $selection) {
    $data = [];
    $data['array']=$dbc->query($selection)->fetchAll(PDO::FETCH_ASSOC);
    return $data;
}

function inputGet($key) {
    return isset($_REQUEST[$key]) ? $_REQUEST[$key] : 1;
}

function limitPage($page, $limit) {
    return ($page<1) ? 0 : (($page-1)*$limit);
}

extract(getAllRows($dbc, $selection));
// var_dump($array);


$buttons=determineButtons($page);

function determineButtons($page) {
    $previous='<a href="?page='. ($page - 1) . '">Previous</a>';
    $next='<a href="?page=' . ($page + 1) . '">Next</a>';
    if ($page==1) {
        return'<p>' . $next . '</p>';
    } elseif ($page==4) {
        return'<p>' . $previous . '</p>';
    } else {
        return'<p>' . $previous . $next . '</p>';
    }
}

function specChar($input) {
    return htmlspecialchars($input);
}

if (!empty($_POST)) {
    if (!($_REQUEST['park_name']=="") && !($_REQUEST['park_location'])=="") {
        $newPark=array('park_name' => specChar($_REQUEST['park_name']), 'park_location'=>specChar($_REQUEST['park_location']), 'park_description'=>specChar($_REQUEST['park_description']));
            if (($_REQUEST['park_description'])=="") {
                $newPark['park_description']="It's undescribable!";
            }
            addPark($newPark, $dbc); 
    }
}

function addPark ($newPark, $dbc) {
    // var_dump($newPark);
    $sqlInsert="INSERT INTO national_parks (name, location, description)
    VALUES (:name, :location, :description)";

    $stmt=$dbc->prepare($sqlInsert);

    $stmt->bindValue(':name', $newPark['park_name'], PDO::PARAM_STR);
    $stmt->bindValue(':location', $newPark['park_location'], PDO::PARAM_STR);
    $stmt->bindValue(':description', $newPark['park_description'], PDO::PARAM_STR);
    $stmt->execute();
}


?>


<!DOCTYPE html>
<html>
<head>
    <title>National Parks output</title>
</head>
<body>

<h1>Some National Parks</h1>

<?php foreach($array as $park) { ?>
    <p>Name: <?= $park['name'] ?> </p>
    <p>Location: <?= $park['location'] ?> </p>
    <p>Date established: <?= $park['date_established'] ?> </p>
    <p>Area (in acres): <?= $park['area_in_acres'] ?> </p>
    <p>Description: <?= $park['description'] ?></p>
    <p>=====================</p>
<?php }; ?> <!-- end foreach -->

<?= $buttons ?>

<h1>Add your own!</h1>
<form method="POST">
<p>
    <label for="park_name">Name of park: </label>
    <input id="park_name" name="park_name" type="text">
</p>
<p>
    <label for "park_location">Location of park: </label>
    <input id="park_location" name="park_location" type="text">
</p>
<p>
    <label for "park_description">Description: </label>
    <input id="park_description" name="park_description" type="park_description">
</p>

<button type="submit">Submit</button>
</form>


</body>
</html>