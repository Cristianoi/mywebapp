<?php
    session_start();
    $action = $_POST["action"];

    // Create connection
    $conn = new PDO ("mysql:host=localhost;dbname=webapp;", "ivancris", "mypass");
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    } 



    if (isset($_SESSION['loggeduser'])) {
        
            $id = $_SESSION['loggedid'];
        
            $sql = "SELECT * FROM users u INNER JOIN user_calories c ON u.user_id = c.user_id WHERE u.user_id =$id";
            
            
                
            if ($conn->query($sql) == TRUE) {
                
                
                $row = $conn->query($sql)->fetch();
                $weight_status = $row[weight_status];
                
                
                $target_weight = $row[target_weight];
                $target_weeks = $row[target_weeks];
                
                $breakfast = $row[breakfast];
                $lunch = $row[lunch];
                $snack = $row[snack];
                $dinner = $row[dinner];
                
                
                
                if ($action == "adjust_calories") {
                    $activitylevel = $_POST["daily_activity"];
                    
                    $height = $row[height];
                    $weight = $row[weight];
                    $BMR = $row[BMI];
                    $BMR = $row[BMR];
                    $weight_status = $row[weight_status];
                    
                    switch ($activitylevel) {
                    case "sedentary":
                        $tdee= $BMR * 1.2;
                        break;
                    case "lightly_active":
                        $tdee= $BMR * 1.375;
                        break;
                    case "active":
                        $tdee= $BMR * 1.55;
                        break;
                    case "very_active":
                        $tdee= $BMR * 1.725;
                        break;
                    case "extremely_active":
                        $tdee= $BMR * 1.9;
                        break;
                    }
                $tdee = round ($tdee, 3);

                switch ($weight_status) {
                    case "Underweight":
                        $tc = $tdee + 500;
                        break;
                    case "Normal":
                        $tc = $tdee;
                        break;
                    case "Overweight":
                        $tc = $tdee - 500;
                        break;
                    case "Obese":
                        $tc = $tdee - 500;
                        break;
                }

                switch (true) {
                    case $BMI < 25:
                        $target_weight = 25 * pow(($height/100),2);
                        break;
                    case $BMI > 30:
                        $target_weight = 30 * pow(($height/100),2);
                        break;
    
                }


                $target_weight = 21.75 * pow(($height/100),2);

                $target_weeks = abs($weight -$target_weight) / 0.453592;

                $target_weight = round ($target_weight,3);
                $target_weeks = round ($target_weeks,3);

                $breakfast = round ($tc * 0.2 , 3);
                $lunch = round ($tc * 0.3 , 3);
                $snack = round ($tc * 0.3 , 3);
                $dinner = round ($tc * 0.2 , 3);

                    $message= "updating";

                $sql = "UPDATE user_calories SET BMR=$BMR, weight_status='$weight_status', tdee=$tdee, target_weight=$target_weight,  target_weeks=$target_weeks, target_calories=$tc, breakfast=$breakfast, lunch=$lunch, snack=$snack, dinner=$dinner WHERE user_id=$id";
                if ($conn->query($sql) == TRUE) {
                    $message = "User successfully updated";
                } else {
                    echo "Error: " . $sql . "<br>" . $conn->error;
                }
                    
                $sql = "UPDATE users SET activity_lev='$activitylevel' WHERE user_id=$id";
                if ($conn->query($sql) == TRUE) {
                    $message = "User successfully updated";
                } else {
                    echo "Error: " . $sql . "<br>" . $conn->error;
                }
                    
                    
                }
                
            } else {
                echo "Error: " . $sql . "<br>" . $conn->error;
            }
            
        
    } else {
        
        
        header("Location: login.php");

    }
   
    


    
    
    
    

    
    
    
?>

<!doctype html>
<html lang="en">

	<head>
		<title>Log In</title>
        <link href="css/styles.css" rel="stylesheet">
        <script src="scripts/dailyplan.js" type="text/javascript"></script>
        <script src="scripts/bar_reader.js" type="text/javascript"></script>
        
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
        <script type="text/javascript" src="dist/quagga.min.js"></script>
        
        
        <meta name="viewport" content="width=device-width">
        
    </head>
        
        
	<body>
        
        <div id="wrapper">  
            
            <!--------------------------- Header Starts --------------------------->
            <header>
                
                
                
            </header>
            <!--------------------------- Header Ends --------------------------->

            
            <!--------------------------- Section Starts --------------------------->
            <section>
                
                <h1>Daily Plan</h1>
                
                <p><?php echo $message?></p>
                
                <h3>Your weight status is <?php echo $weight_status?></h3>
                <h3>Your target weight is <?php echo $target_weight?> kg and it will be achieved in around <?php echo round($target_weeks)?> weeks</h3>

                <div id="breakfast" class="day">
                    <h3>Breakfast</h3>
                    <a onclick="additem('b'); return false;" >+</a>
                </div>
                
                <div id="lunch" class="day">
                    <h3>Lunch</h3>
                    <a onclick="additem('l'); return false;" >+</a>
                </div>
                
                <div id="snack" class="day">
                    <h3>Snack</h3>
                    <a onclick="additem('s'); return false;" >+</a>
                </div>
                
                <div id="dinner" class="day">
                    <h3>Dinner</h3>
                    <a onclick="additem('d'); return false;" >+</a>
                </div>
                
                <div id="barcode-scanner"></div>
                <div id="barcode-code"></div>
                
                <table style="width:100%; margin-top:100px; ">
                    <caption>Suggested Macronutrients</caption>
                    <tr>
                        <th>Meal</th>
                        <th>Calories Consumed</th>
                        <th>Target Calories</th>
                    </tr>
                    <tr>
                        <td>Breakfast</td>
                        <td></td>
                        <td><?php echo $breakfast?></td>
                    </tr>
                    <tr>
                        <td>Lunch</td>
                        <td></td>
                        <td><?php echo $lunch?></td>
                    </tr>
                    <tr>
                        <td>Snacks and Drinks</td>
                        <td></td>
                        <td><?php echo $snack?></td>
                    </tr>
                    <tr>
                        <td>Dinner</td>
                        <td></td>
                        <td><?php echo $dinner?></td>
                    </tr>
                    
                    
                </table>
             
                <div id='adjust_form'>
                    <form method='post' action='dailyplan.php'>
                        <br>
                        <label for='daily_activity'>Daily Activity Level</label>
                        <select name="daily_activity" class='inputbox'>
                            <option value="sedentary" <?php if ($activitylevel=='sedentary'){ ?> selected <?php } ?> >Sedentary (little/no exercise)</option>
                            <option value="lightly_active" <?php if ($activitylevel=='lightly_active'){ ?> selected <?php } ?> >Lightly Active (office work)</option>
                            <option value="active" <?php if ($activitylevel=='active'){ ?> selected <?php } ?> > Moderately Active (construcion work or 1 hour sport)</option>
                            <option value="very_active" <?php if ($activitylevel=='very_active'){ ?> selected <?php } ?> >Very Active (2-3 hours sport)</option>
                            <option value="extremely_active" <?php if ($activitylevel=='extremely_active'){ ?> selected <?php } ?> >Extremely Active (at least 4 hours sport)</option>
                        </select>
                        <input type="hidden" name="action" value="adjust_calories"> <br>
                        <input type="submit" value="Adjust Calories">
                    </form>
                </div>
                
            </section>
            <!--------------------------- Section Ends --------------------------->

            
            
            
            <!--------------------------- Footer Starts --------------------------->
            
            <!--------------------------- Footer Ends --------------------------->
            
        </div>
		
        
		
        
	</body>
	
    <footer>
        
    <div class="mobile-bottom-bar">
      <a href="dailyplan.php" class="footer-link">
        <i class="fa fa-cog"></i> <span class='footer-text'>Daily Plan</span>
      </a>
      <a href="settings.php" class="footer-link">
        <i class="fa fa-sign-out"></i> <span class='footer-text'>Settings</span>
      </a>
    </div>
    
    </footer>
</html>
