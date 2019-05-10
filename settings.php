<?php
    session_start();
    $action = $_POST["action"];

    // Create connection
    $conn = new PDO ("mysql:host=localhost;dbname=webapp;", "ivancris", "mypass");
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    } 



    if ($action == "logout") {
        unset($_SESSION['loggeduser']);
        unset($_SESSION['loggedid']);

        
        
        header("Location: login.php");
                
    } elseif (isset($_SESSION['loggeduser'])) {
        
        $conn = new PDO ("mysql:host=localhost;dbname=webapp;", "ivancris", "mypass");
        // Check connection
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        } 
        $sql = "SELECT * FROM users WHERE username='$_SESSION[loggeduser]'";
            
        $row = $conn->query($sql)->fetch();
        
        if ($row == false) {
            $message="Error: No account found for the loggedin username";
        } else {
            
            $gender = $row[gender];
            $name = $row[name];
            $height = $row[height];
            $weight = $row[weight];
            $date = $row[dob];
            
            
            if (preg_match('#^(\d{4})-(\d{2})-(\d{2})$#', $date, $matches)) {
                $dob = $matches[3];
                $mob   = $matches[2];
                $yob  = $matches[1];
            } else {
                $message= "invalid date of birth format";
            }
            
            $activity_lev = $row[activity_lev];
            $email = $row[email];
            $username = $row[username];
            
            
            
        }
        
        
        
                
        if ($action == "update") {
            
            $gender = $_POST["gender"];
            $name = $_POST["newname"];
            $height = $_POST["newheight"];
            $weight = $_POST["newweight"];
            $day = (int)$_POST["dob"];
            $month = (int)$_POST["mob"];
            $year = (int)$_POST["yob"];
            $activitylev = $_POST["activitylev"];
            $newemail = $_POST["newemail"];
            $newusername = $_POST["newusername"];
            $newpassword = $_POST["newpassword"];
            $confirmpass = $_POST["confirm_password"];
            
            
            if ($gender==null || $name=='' || $height==0 || $weight==0 || is_int($day)==false || is_int($month)==false || is_int($year)==false || $newemail=='' || $newusername=='' || $newpassword=='' || $confirmpass=='') {
            
                
                $message= "Please enter all fields";
                
            } else {
            
            if ($newpassword != $confirmpass ) {
                
                $message= "The passwords did not match, try again";
                
            } else {
            
            $date= "$year-$month-$day";
                
            $loggedID= $_SESSION['loggedid'];
                
            if ($conn->query
            ("UPDATE users SET gender=$gender, name='$name', height=$height, dob='$date', activity_lev='$activitylev', password='$newpassword' WHERE user_id=$loggedID")
            == TRUE) {



                $heightm = $height / 100;

                $BMI = ($weight) / (pow($heightm, 2));

                if ($BMI<18.5) {
                    $weight_status = "Underweight";
                } elseif ($BMI<25) {
                    $weight_status = "Normal";
                } elseif ($BMI<30) {
                    $weight_status = "Overweight";
                } else {
                    $weight_status = "Obese";
                }

                $age = date("Y") - $year;

                if ($gender == 1) {
                    $BMR = 655.096 + ( 9.563 * $weight) + (1.850 * $height) - ( 4.676 * $age ) ;
                } elseif ($gender == 0) {
                    $BMR = 66.473 + ( 13.752 * $weight ) + ( 5.003 * $height ) - ( 6.755 * $age );
                } else {
                    $message ="BMR not calculated, invalid gender";
                }


                $BMI = round ($BMI,3);
                $BMR = round ($BMR,3);


                switch ($activitylev) {
                    case "sedentary":
                        $tdee= $BMR * 1.2;
                        break;
                    case "lightly_active":
                        $tdee= $BMR * 1.375;
                        break;
                    case "moderately_active":
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



                $sql = "UPDATE user_calories SET BMI=$BMI, BMR=$BMR, weight_status='$weight_status', tdee=$tdee, target_weight=$target_weight, target_weeks=$target_weeks, target_calories=$tc, breakfast=$breakfast, lunch=$lunch, snack=$snack, dinner=$dinner WHERE user_id=$loggedID";
                if ($conn->query($sql) == TRUE) {
                    $message = "User successfully updated";
                } else {
                    echo "Error: " . $sql . "<br>" . $conn->error;
                }
            }

            }
            
               
            
                
            }
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
        <script src="scripts/login.js" type="text/javascript"></script>
        
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
        
        
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
                
                <h1>Settings</h1>

                <h3>
                    <?php echo $message?>
                </h3>
                
                
                <div id='update_form'>
                    <form method='post' action='settings.php'>
                        
                        <label for='gender'>Gender</label><br/>
                        Male<input name='gender' id='gender' type="radio" value='1' <?php if ($gender==1){ ?> checked <?php } ?> >
                        Female<input name='gender' id='gender' type="radio" value='0' <?php if ($gender==0) { ?> checked <?php } ?> > <br/>
                        <label for='newname'>Name</label><br/>
                        <input name='newname' id='newname' type="textbox" class='inputbox' value='<?php echo $name ?>' /><br/>
                        <label for='newheight'>Height</label><br>
                        <input name='newheight' id='newheight' type="number" class='inputbox' min=10 max=250 value='<?php echo $height ?>'/> cm<br/>
                        <label for='newweight'>Weight</label><br>
                        <input name='newweight' id='newweight' type="number" class='inputbox' min=10 max=250 step="0.1" value='<?php echo $weight ?>'/> kg<br/>
                        <label>Date Of Birth</label><br/>
                        
                        <select name="dob">
                            <option>- Day -</option>
                            <option value="01" <?php if ($dob=='01'){ ?> selected <?php } ?> >1</option>
                            <option value="02" <?php if ($dob=='02'){ ?> selected <?php } ?> >2</option>
                            <option value="03" <?php if ($dob=='03'){ ?> selected <?php } ?> >3</option>
                            <option value="04" <?php if ($dob=='04'){ ?> selected <?php } ?> >4</option>
                            <option value="05" <?php if ($dob=='01'){ ?> selected <?php } ?> >5</option>
                            <option value="06" <?php if ($dob=='06'){ ?> selected <?php } ?> >6</option>
                            <option value="07" <?php if ($dob=='07'){ ?> selected <?php } ?> >7</option>
                            <option value="08" <?php if ($dob=='08'){ ?> selected <?php } ?> >8</option>
                            <option value="09" <?php if ($dob=='09'){ ?> selected <?php } ?> >9</option>
                            <option value="10" <?php if ($dob=='10'){ ?> selected <?php } ?> >10</option>
                            <option value="11" <?php if ($dob=='11'){ ?> selected <?php } ?> >11</option>
                            <option value="12" <?php if ($dob=='12'){ ?> selected <?php } ?> >12</option>
                            <option value="13" <?php if ($dob=='13'){ ?> selected <?php } ?> >13</option>
                            <option value="14" <?php if ($dob=='14'){ ?> selected <?php } ?> >14</option>
                            <option value="15" <?php if ($dob=='15'){ ?> selected <?php } ?> >15</option>
                            <option value="16" <?php if ($dob=='16'){ ?> selected <?php } ?> >16</option>
                            <option value="17" <?php if ($dob=='17'){ ?> selected <?php } ?> >17</option>
                            <option value="18" <?php if ($dob=='18'){ ?> selected <?php } ?> >18</option>
                            <option value="19" <?php if ($dob=='19'){ ?> selected <?php } ?> >19</option>
                            <option value="20" <?php if ($dob=='20'){ ?> selected <?php } ?> >20</option>
                            <option value="21" <?php if ($dob=='21'){ ?> selected <?php } ?> >21</option>
                            <option value="22" <?php if ($dob=='22'){ ?> selected <?php } ?> >22</option>
                            <option value="23" <?php if ($dob=='23'){ ?> selected <?php } ?> >23</option>
                            <option value="24" <?php if ($dob=='24'){ ?> selected <?php } ?> >24</option>
                            <option value="25" <?php if ($dob=='25'){ ?> selected <?php } ?> >25</option>
                            <option value="26" <?php if ($dob=='26'){ ?> selected <?php } ?> >26</option>
                            <option value="27" <?php if ($dob=='27'){ ?> selected <?php } ?> >27</option>
                            <option value="28" <?php if ($dob=='28'){ ?> selected <?php } ?> >28</option>
                            <option value="29" <?php if ($dob=='29'){ ?> selected <?php } ?> >29</option>
                            <option value="30" <?php if ($dob=='30'){ ?> selected <?php } ?> >30</option>
                            <option value="31" <?php if ($dob=='31'){ ?> selected <?php } ?> >31</option>
                            </select><select name="mob">
                            <option>- Month -</option>
                            <option value="01" <?php if ($mob=='01'){ ?> selected <?php } ?> >January</option>
                            <option value="02" <?php if ($mob=='02'){ ?> selected <?php } ?> >Febuary</option>
                            <option value="03" <?php if ($mob=='03'){ ?> selected <?php } ?> >March</option>
                            <option value="04" <?php if ($mob=='04'){ ?> selected <?php } ?> >April</option>
                            <option value="05" <?php if ($mob=='05'){ ?> selected <?php } ?> >May</option>
                            <option value="06" <?php if ($mob=='06'){ ?> selected <?php } ?> >June</option>
                            <option value="07" <?php if ($mob=='07'){ ?> selected <?php } ?> >July</option>
                            <option value="08" <?php if ($mob=='08'){ ?> selected <?php } ?> >August</option>
                            <option value="09" <?php if ($mob=='09'){ ?> selected <?php } ?> >September</option>
                            <option value="10" <?php if ($mob=='10'){ ?> selected <?php } ?> >October</option>
                            <option value="11" <?php if ($mob=='11'){ ?> selected <?php } ?> >November</option>
                            <option value="12" <?php if ($mob=='12'){ ?> selected <?php } ?> >December</option>
                        </select>
                        <select name="yob">
                            <option>- Year -</option>
                            <option value="2015" <?php if ($yob=='2015'){ ?> selected <?php } ?> >2015</option>
                            <option value="2014" <?php if ($yob=='2014'){ ?> selected <?php } ?> >2014</option>
                            <option value="2013" <?php if ($yob=='2013'){ ?> selected <?php } ?> >2013</option>
                            <option value="2012" <?php if ($yob=='2012'){ ?> selected <?php } ?> >2012</option>
                            <option value="2011" <?php if ($yob=='2011'){ ?> selected <?php } ?> >2011</option>
                            <option value="2010" <?php if ($yob=='2010'){ ?> selected <?php } ?> >2010</option>
                            <option value="2009" <?php if ($yob=='2009'){ ?> selected <?php } ?> >2009</option>
                            <option value="2008" <?php if ($yob=='2008'){ ?> selected <?php } ?> >2008</option>
                            <option value="2007" <?php if ($yob=='2007'){ ?> selected <?php } ?> >2007</option>
                            <option value="2006" <?php if ($yob=='2006'){ ?> selected <?php } ?> >2006</option>
                            <option value="2005" <?php if ($yob=='2005'){ ?> selected <?php } ?> >2005</option>
                            <option value="2004" <?php if ($yob=='2004'){ ?> selected <?php } ?> >2004</option>
                            <option value="2003" <?php if ($yob=='2003'){ ?> selected <?php } ?> >2003</option>
                            <option value="2002" <?php if ($yob=='2002'){ ?> selected <?php } ?> >2002</option>
                            <option value="2001" <?php if ($yob=='2001'){ ?> selected <?php } ?> >2001</option>
                            <option value="2000" <?php if ($yob=='2000'){ ?> selected <?php } ?> >2000</option>
                            <option value="1999" <?php if ($yob=='1999'){ ?> selected <?php } ?> >1999</option>
                            <option value="1998" <?php if ($yob=='1998'){ ?> selected <?php } ?> >1998</option>
                            <option value="1997" <?php if ($yob=='1997'){ ?> selected <?php } ?> >1997</option>
                            <option value="1996" <?php if ($yob=='1996'){ ?> selected <?php } ?> >1996</option>
                            <option value="1995" <?php if ($yob=='1995'){ ?> selected <?php } ?> >1995</option>
                            <option value="1994" <?php if ($yob=='1994'){ ?> selected <?php } ?> >1994</option>
                            <option value="1993" <?php if ($yob=='1993'){ ?> selected <?php } ?> >1993</option>
                            <option value="1992" <?php if ($yob=='1992'){ ?> selected <?php } ?> >1992</option>
                            <option value="1991" <?php if ($yob=='1991'){ ?> selected <?php } ?> >1991</option>
                            <option value="1990" <?php if ($yob=='1990'){ ?> selected <?php } ?> >1990</option>
                            <option value="1989" <?php if ($yob=='1989'){ ?> selected <?php } ?> >1989</option>
                            <option value="1988" <?php if ($yob=='1988'){ ?> selected <?php } ?> >1988</option>
                            <option value="1987" <?php if ($yob=='1987'){ ?> selected <?php } ?> >1987</option>
                            <option value="1986" <?php if ($yob=='1986'){ ?> selected <?php } ?> >1986</option>
                            <option value="1985" <?php if ($yob=='1985'){ ?> selected <?php } ?> >1985</option>
                            <option value="1984" <?php if ($yob=='1984'){ ?> selected <?php } ?> >1984</option>
                            <option value="1983" <?php if ($yob=='1983'){ ?> selected <?php } ?> >1983</option>
                            <option value="1982" <?php if ($yob=='1982'){ ?> selected <?php } ?> >1982</option>
                            <option value="1981" <?php if ($yob=='1981'){ ?> selected <?php } ?> >1981</option>
                            <option value="1980" <?php if ($yob=='1980'){ ?> selected <?php } ?> >1980</option>
                            <option value="1979" <?php if ($yob=='1979'){ ?> selected <?php } ?> >1979</option>
                            <option value="1978" <?php if ($yob=='1978'){ ?> selected <?php } ?> >1978</option>
                            <option value="1977" <?php if ($yob=='1977'){ ?> selected <?php } ?> >1977</option>
                            <option value="1976" <?php if ($yob=='1976'){ ?> selected <?php } ?> >1976</option>
                            <option value="1975" <?php if ($yob=='1975'){ ?> selected <?php } ?> >1975</option>
                            <option value="1974" <?php if ($yob=='1974'){ ?> selected <?php } ?> >1974</option>
                            <option value="1973" <?php if ($yob=='1973'){ ?> selected <?php } ?> >1973</option>
                            <option value="1972" <?php if ($yob=='1972'){ ?> selected <?php } ?> >1972</option>
                            <option value="1971" <?php if ($yob=='1971'){ ?> selected <?php } ?> >1971</option>
                            <option value="1970" <?php if ($yob=='1970'){ ?> selected <?php } ?> >1970</option>
                            <option value="1969" <?php if ($yob=='1969'){ ?> selected <?php } ?> >1969</option>
                            <option value="1968" <?php if ($yob=='1968'){ ?> selected <?php } ?> >1968</option>
                            <option value="1967" <?php if ($yob=='1967'){ ?> selected <?php } ?> >1967</option>
                            <option value="1966" <?php if ($yob=='1966'){ ?> selected <?php } ?> >1966</option>
                            <option value="1965" <?php if ($yob=='1965'){ ?> selected <?php } ?> >1965</option>
                            <option value="1964" <?php if ($yob=='1964'){ ?> selected <?php } ?> >1964</option>
                            <option value="1963" <?php if ($yob=='1963'){ ?> selected <?php } ?> >1963</option>
                            <option value="1962" <?php if ($yob=='1962'){ ?> selected <?php } ?> >1962</option>
                            <option value="1961" <?php if ($yob=='1961'){ ?> selected <?php } ?> >1961</option>
                            <option value="1960" <?php if ($yob=='1960'){ ?> selected <?php } ?> >1960</option>
                            <option value="1959" <?php if ($yob=='1959'){ ?> selected <?php } ?> >1959</option>
                            <option value="1958" <?php if ($yob=='1958'){ ?> selected <?php } ?> >1958</option>
                            <option value="1957" <?php if ($yob=='1957'){ ?> selected <?php } ?> >1957</option>
                            <option value="1956" <?php if ($yob=='1956'){ ?> selected <?php } ?> >1956</option>
                            <option value="1955" <?php if ($yob=='1955'){ ?> selected <?php } ?> >1955</option>
                            <option value="1954" <?php if ($yob=='1954'){ ?> selected <?php } ?> >1954</option>
                            <option value="1953" <?php if ($yob=='1953'){ ?> selected <?php } ?> >1953</option>
                            <option value="1952" <?php if ($yob=='1952'){ ?> selected <?php } ?> >1952</option>
                            <option value="1951" <?php if ($yob=='1951'){ ?> selected <?php } ?> >1951</option>
                            <option value="1950" <?php if ($yob=='1950'){ ?> selected <?php } ?> >1950</option>
                            <option value="1949" <?php if ($yob=='1949'){ ?> selected <?php } ?> >1949</option>
                            <option value="1948" <?php if ($yob=='1948'){ ?> selected <?php } ?> >1948</option>
                            <option value="1947" <?php if ($yob=='1947'){ ?> selected <?php } ?> >1947</option>
                            <option value="1946" <?php if ($yob=='1946'){ ?> selected <?php } ?> >1946</option>
                            <option value="1945" <?php if ($yob=='1945'){ ?> selected <?php } ?> >1945</option>
                            <option value="1944" <?php if ($yob=='1944'){ ?> selected <?php } ?> >1944</option>
                            <option value="1943" <?php if ($yob=='1943'){ ?> selected <?php } ?> >1943</option>
                            <option value="1942" <?php if ($yob=='1942'){ ?> selected <?php } ?> >1942</option>
                            <option value="1941" <?php if ($yob=='1941'){ ?> selected <?php } ?> >1941</option>
                            <option value="1940" <?php if ($yob=='1940'){ ?> selected <?php } ?> >1940</option>
                            <option value="1939" <?php if ($yob=='1939'){ ?> selected <?php } ?> >1939</option>
                            <option value="1938" <?php if ($yob=='1938'){ ?> selected <?php } ?> >1938</option>
                            <option value="1937" <?php if ($yob=='1937'){ ?> selected <?php } ?> >1937</option>
                            <option value="1936" <?php if ($yob=='1936'){ ?> selected <?php } ?> >1936</option>
                            <option value="1935" <?php if ($yob=='1935'){ ?> selected <?php } ?> >1935</option>
                            <option value="1934" <?php if ($yob=='1934'){ ?> selected <?php } ?> >1934</option>
                            <option value="1933" <?php if ($yob=='1933'){ ?> selected <?php } ?> >1933</option>
                            <option value="1932" <?php if ($yob=='1932'){ ?> selected <?php } ?> >1932</option>
                            <option value="1931" <?php if ($yob=='1931'){ ?> selected <?php } ?> >1931</option>
                            <option value="1930" <?php if ($yob=='1930'){ ?> selected <?php } ?> >1930</option>
                        </select> <br/>
                        <label for='activitylev'>Activity Level</label><br>
                        <select name="activitylev" class='inputbox'>
                            <option value="sedentary" <?php if ($activitylev=='sedentary'){ ?>selected<?php }?> >Sedentary (little/no exercise)</option>
                            <option value="lightly_active" <?php if ($activitylev=='lightly_active'){ ?>selected<?php }?>>Lightly Active (sports 1-3 days a week)</option>
                            <option value="active" <?php if ($activitylev=='active'){ ?>selected<?php }?>> Moderately Active (sports 3-5 days a week)</option>
                            <option value="very_active" <?php if ($activitylev=='very_active'){ ?>selected<?php }?>>Very Active (sports 6-7 days a week)</option>
                            <option value="extremely_active" <?php if ($activitylev=='extremely_active'){ ?>selected<?php }?>>Extremely Active (sports twice a day)</option>
                        </select>
                        
                        <label for='newemail'>Email</label><br>
                        <input name='newemail' id='newemail' type="email" class='inputbox' value='<?php echo $email ?>' readonly/><br/>
                        <label for='newusername'>Username</label><br>
                        <input name='newusername' id='newusername' class='inputbox' value='<?php echo $name ?>' readonly/><br/>
                        <label for='newpassword'>Password</label><br>
                        <input name='newpassword' id='newpassword' type='password' class='inputbox'/><br/>
                        <label for='confirm_password'>Confirm Password</label><br>
                        <input name='confirm_password' id='confirm_password' type='password' class='inputbox'/><br/>
                        <input type="hidden" name="action" value="update">
                        <input type="submit" value="Update">
                    </form>
                </div>
                
                <form method='post' action='settings.php'>
                        
                        <input type="hidden" name="action" value="delete">
                        <input type="submit" value="Delete Account">
                    
                </form>
            
                
                <form method='post' action='settings.php'>
                        
                        <input type="hidden" name="action" value="logout">
                        <input type="submit" value="Log Out">
                    
                </form>
                
                
                
                            
                    
             
                
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
