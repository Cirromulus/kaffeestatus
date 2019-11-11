<?php
$f = fopen("last_off", "c+");
if(!$f)
       	echo "fopen errer";
$last_off = fgets($f, 11);
rewind($f);
$maschine_an = $last_off == "" || $last_off < strtotime("08:00");

if(isset($_GET['get']))
{
	header('Content-Type: application/json');
	echo json_encode($maschine_an);
	exit;
}

if(isset($_POST['machen']))
{
        if($maschine_an && $_POST['machen'] == "aus")
        {
                if(!fwrite($f, time(), 11))
                        echo "fwrite errer";
                $maschine_an = false;
                $from = "Kaffeemaschine@dfki.de";
                $to = "<Pascal.Pieper@dfki.de>; <Tobias.Brandt@dfki.de>";
                $subject = "Die Kaffeemaschine wird ausgemacht.";
                $message = "";
                $headers = "From:" . $from;
                //Disabled sending mails
                //mail($to,$subject,$message, $headers);
        }
	else if(!$maschine_an && $_POST['machen'] == "an")
        {
                $maschine_an = true;
                if(!fwrite($f, "0"))
                        echo "fwrite errer";
        }
}
fclose($f);
?>
<html>
<head>
<style>
body { text-align: center;}
h2 { margin: 0; font-size: 35em;}
.ja { color: #20FFA0 ;}
.nein { color: #FF4020 ;}
</style>
<script>
//This keeps users from re-sending post data on reload
document.onkeydown = fkey;
document.onkeypress = fkey
document.onkeyup = fkey;
function fkey(e){
    e = e || window.event;
    if (e.keyCode == 116) {
        window.location.href = window.location.href;
    }
};

<?php if(isset($_GET['live'])) { ?>

var localStatus = <?php echo $maschine_an ? "true" : "false"; ?>;
var audio = new Audio('alert.mp3');

document.addEventListener("DOMContentLoaded", () => {
  getStatus();
  setInterval(() => {
    getStatus();
  },5000);
});

let getStatus = () => {
  fetch("/?get").then(response => {
    if (response.ok) return response.json(); else throw new Error(response.status);
  }).then(json => {
    let node = document.getElementById("content");
      remoteVal = JSON.parse(json);
      if (remoteVal) {
        node.innerHTML = "Ja";
        node.classList.remove("nein");
        node.classList.add("ja");
      } else {
        node.innerHTML = "Nein";
        node.classList.remove("ja");
        node.classList.add("nein");
        if(localStatus){
          audio.play();
        }
      } 
      localStatus = remoteVal;
  }).catch(err => {console.log(err); });
};

<?php } ?>

</script>
</head>
<body>
<h1>Ist die Kaffeemaschine noch an?</h1>
<?php 
echo "<h2 id='content' class=";
if($maschine_an)
{
	echo "'ja'>Ja</h2>";
	echo "<form method=\"post\"><button name=\"machen\" value=\"aus\">(Ausmachen)</button></form>";
}else
{
	echo "'nein'>Nein</h2>";
	echo "<form method=\"post\"><button name=\"machen\" value=\"an\">(Anmachen)</button></form>";
}

if(!isset($_GET['live'])){	
?>
</br>
<a href="?live">Watch live</a>
<?php } ?>
</body>
</html>

