<?php
/**
 *  HTML code for RationalGRL Translator.
 *
 *  More information: https://github.com/RationalArchitecture/RationalGRL
 *
 *  @author Marc van Zee 
 */

function HTMLDoc() {
    HTMLHeader();
    HTMLMainText();
    HTMLMainForm();
    HTMLFooter();
}

function HTMLHeader() {
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <meta name="description" content="">
    <meta name="author" content="">

    <title>RationalGRL: Arguments to Goal Models Translator</title>

    <!-- Bootstrap core CSS -->
    <link href="css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="css/signin.css" rel="stylesheet">

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
  </head>

  <body>

    <div class="container form-signin">
<?
}

function HTMLMainText() {
?>
<h2 class="form-signin-heading">RationalGRL: Export Arguments to GRL</h2>
        
        <p>This tool translates argument maps stored in the <a href="http://www.aifdb.org/search">AIFdb database</a> to models of the <a href="https://en.wikipedia.org/wiki/Goal-oriented_Requirements_Language">Goal-oriented Requirements Language (GRL)</a>, such that they can be imported into the Eclipse-based GRL modeling tool <a href="https://www.openhub.net/p/11712">jUCMNav</a>.</p>

        <p>You can create an argumentat map youself using the online tool <a href="http://ova.arg-tech.org/">OVA</a>. It is also possible to save these argument maps into AIFdb directly.</p>

        <p><b>Note that the argument maps have to conform to a specific syntax in order for them to be translateble to GRL! More details will be provided in this in the future.</b></p>

        To test the translator, you may try out the following AIFdb ids:
          <ul>
            <li><b>9012</b>: Schiphol Principles 2003 (<a href="http://www.arg-tech.org/AIFdb/argview/9012" target="_blank">view on AIFdb</a> / <a href="http://ova.arg-tech.org/analyse.php?url=local&aifdb=9012" target="_blank">view on OVA</a>)</li>
            <li><b>9011</b>: Schiphol Principles 2003 after review (<a href="http://www.arg-tech.org/AIFdb/argview/9011" target="_blank">view on AIFdb</a> / <a href="http://ova.arg-tech.org/analyse.php?url=local&aifdb=9011" target="_blank">view on OVA</a>)</li>
            <li><b>8996</b>: Schiphol Principles 2007 (<a href="http://www.arg-tech.org/AIFdb/argview/8996" target="_blank">view on AIFdb</a> / <a href="http://ova.arg-tech.org/analyse.php?url=local&aifdb=8996" target="_blank">view on OVA</a>)</li>
          </ul>
<?php
}

function HTMLConflictForm($conflicts_to_resolve) {
	global $ID, $EVALUATE;
?>
<h2 class="form-signin-heading">RationalGRL: Export Arguments to GRL</h2>

<p><b>For each of the following conflicting arguments, please choose which argument you prefer.</b></p>

<form action="<?=$_SERVER["PHP_SELF"];?>" method="GET" class="form-signin">
	<?php 
		foreach ($conflicts_to_resolve as $key => $conflict) {
			$node1 = $conflict[0];
			$node2 = $conflict[1];
	?>
	<fieldset id="removeEdge<?=$key?>" style="border: 0" class="radiofield">
	<b>Conflict <?=$key?>:</b></br>
	<input type="radio" value="<?=$node2->id.",".$node1->id?>" name="removeEdge<?=$key?>" checked><?=$node1->name?><br>
	<input type="radio" value="<?=$node1->id.",".$node2->id?>" name="removeEdge<?=$key?>"><?=$node2->name?>
	</fieldset>
	<?php
		}
	?>  <br>
<input type="submit" value="Resolve conflicts and export to GRL" class="btn btn-lg btn-primary btn-block">
<input type="hidden" name="conflicts" value="<?=Conflicts::RESOLVED;?>">
<input type="hidden" name="evaluate" value="<?=$EVALUATE?>">
<input type="hidden" name="id" value="<?=$ID?>">
</form> 
<?php
}

function HTMLFinishedText($text) {
?>
<h2 class="form-signin-heading">RationalGRL: Export Arguments to GRL</h2>

<p><h3>Export to GRL was successfull!</h3></p>

<div class="form-signin">
	<?=$text?>
<form action="<?=$_SERVER["PHP_SELF"];?>" method="GET">
<input type="submit" value="Go back" class="btn btn-lg btn-primary btn-block">	
</form>
</div>

<?php
}

function HTMLMainForm() {
?>
<form action="<?=$_SERVER["PHP_SELF"];?>" class="form-signin" method="GET">
        <label for="inputEmail" class="sr-only">AIFdb id</label>
        <input type="text" name="id" class="form-control" placeholder="AIFdb id" required autofocus>
        <div class="checkbox">
          <label><br>
            <input type="radio" name="conflicts" value="<?=Conflicts::IGNORE?>" checked> Do not export conflicts <br>
			  <input type="radio" name="conflicts" value="<?=Conflicts::ARBITRARY?>"> Export conflicts arbitrarily<br>
			  <input type="radio" name="conflicts" value="<?=Conflicts::CHOOSE?>"> Let me choose how to resolve conflicts<br><br>
        <input type="checkbox" name="evaluate" value="1" checked> Export evaluation of GRL Intentional elements based on argument extensions<br><br>
          </label>
        </div>
        <button class="btn btn-lg btn-primary btn-block" type="submit">Export to GRL</button>
      </form>
<?php
}
function HTMLFooter() {
?>
 	</div> <!-- /container -->
  </body>
</html>
<?php
}
?>