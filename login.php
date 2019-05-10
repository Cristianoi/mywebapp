<?php
    session_start();
    $action = $_POST["action"];

    $un = $_POST["username"];
    $pw = $_POST["password"];
    
    
    
    
    // Create connection
    $conn = new PDO ("mysql:host=remotemysql.com;dbname=cH8zLP9Lba;", "cH8zLP9Lba", "4fXoZk8jiK");

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    } 


    if (isset($_SESSION['loggeduser'])) {
        
        header("Location: settings.php");
            
    } else {
        // if LOGIN
        if ($action == "login") {
        
            if (!empty($un) && !empty($pw)){
                
                $sql = "SELECT * FROM users WHERE username='$un' AND password='$pw'";
                $results = $conn->query($sql);
                
            
                $row = $results->fetch();

                $id = $row[user_id];
                
                $message="checking credentials";

                if ($row == false) {
                    $message="wrong credentials";
                } else {
                    $_SESSION["loggeduser"] = $un;
                    $_SESSION["loggedid"] = $id;
                    

                    header("Location: settings.php");
                }
            } else {

                $message="fill all the fields";

            }
        
            
        // if SIGNUP
        } elseif ($action == "signup") {
            
            // reads in all fields in the signup form
            
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
                
                $message= "The passwords did not match, try registering again";
                
            } else {
            
            $dob= "$year-$month-$day";
            
            $sql = "SELECT * FROM users WHERE username='$newusername'";
            
            $row = $conn->query($sql)->fetch();
            
            //if no users are found with that username
            if ($row == false) {
                
                
                if ($conn->query
                ("INSERT INTO users (gender, name, height, weight, dob, activity_lev, email, username, password) VALUES ($gender, '$name', $height, $weight, '$dob', '$activitylev', '$newemail', '$newusername', '$newpassword')")
                == TRUE) {
                    
                    
                    $sql = "SELECT * FROM users WHERE username='$newusername'";
                    $results = $conn->query($sql);
                    $row = $results->fetch();
                    
                    if ($row == false) {
                    $message="User created but BMI not calculated";
                    } else {
                        
                        $id = $row[user_id];
                        
                        $heightm = $height / 100;
                        
                        $BMI = ($weight) / (pow($heightm, 2));
                        
                        echo $BMI;
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
                            $BMR = 655.1 + ( 9.563 * $weight) + (1.850 * $height) - ( 4.676 * $age ) ;
                        } elseif ($gender == 0) {
                            $BMR = 66.5 + ( 13.75 * $weight ) + ( 5.003 * $height ) - ( 6.755 * $age );
                        } else {
                            $message ="BMR not calculated, invalid gender";
                        }
                        
                        $message =  "$age";
                        
                        $BMI = round ($BMI,3);
                        $BMR = round ($BMR,3);
                        
                        
                        switch ($activitylev) {
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
                            default:
                                $tc = null;
                        }
                        
                        
                        $target_weight = 21.75 * pow(($height/100),2);
                        
                        $target_weeks = abs($weight -$target_weight) / 0.453592;
                        
                        $target_weight = round ($target_weight,3);
                        $target_weeks = round ($target_weeks,3);
                        
                        $breakfast = round ($tc * 0.2 , 3);
                        $lunch = round ($tc * 0.3 , 3);
                        $snack = round ($tc * 0.3 , 3);
                        $dinner = round ($tc * 0.2 , 3);
                        
                        $sql = "INSERT INTO user_calories (user_id, BMI, BMR, weight_status, tdee, target_weight, target_weeks, target_calories, breakfast, lunch, snack, dinner) VALUES ($id, $BMI, $BMR, '$weight_status', $tdee, $target_weight, $target_weeks, $tc, $breakfast, $lunch, $snack, $dinner)";
                        if ($conn->query($sql) == TRUE) {
                            $message = "User successfully created";
                        } else {
                            echo "Error: " . $sql . "<br>" . $conn->error;
                        }
                        
                        
                        
                        
                        
                    }
                }
                    
                     
                
            // if the username already exists
            } else {
            $message="The username $newusername already exists";
            }
            
            
            
            }
            }
            
            
        // when users logs out from settings
        } elseif ($action == "loggedout") {
    
            
            $message = "You're now logged out";
        // when user tries to go to settings when not logged in       
        } elseif ($action == "notloggedin") {
            
            $message = "You need to log in to access the settings";
                
        }
            
    
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
                
                <h1>Log In</h1>

                <h3>
                    <?php echo $message?>
                </h3>
                
                
                <div id='login_form'>
                    <form method='post' action='login.php'>
                        <label for='username' value='<?php echo $newusername ?>'>Username</label><br>
                        <input name='username' id='username' type="textbox" class='inputbox'/> <br/>
                        <label for='password' value='<?php echo $newpassword?>'>Password</label><br>
                        <input name='password' id='password' type='password' class='inputbox'/> <br/>
                        <input type="hidden" name="action" value="login">
                        <input type="submit" value="Log In">
                    </form>
                </div>
                
                <a id="registerButton" onClick="displayRegisterForm(); return false;" href="login.php">Not Registered? Click here!</a>
                
                <div id='register_form'>
                    <form method='post' action='login.php'>
                        
                        <label for='gender'>Gender</label><br/>
                        Male<input name='gender' id='gender' type="radio" value='1'>
                        Female<input name='gender' id='gender' type="radio" value='0'><br/>
                        <label for='newname'>Name</label><br/>
                        <input name='newname' id='newname' type="textbox" class='inputbox'/><br/>
                        <label for='newheight'>Height</label><br>
                        <input name='newheight' id='newheight' type="number" class='inputbox' min=10 max=250 value='180'/> cm<br/>
                        <label for='newweight'>Weight</label><br>
                        <input name='newweight' id='newweight' type="number" class='inputbox' min=10 max=250  step="0.1" value='80'/> kg<br/>
                        <label>Date Of Birth</label><br/>
                        
                        <select name="dob">
                            <option>- Day -</option>
                            <option value=01>1</option>
                            <option value=02>2</option>
                            <option value=03>3</option>
                            <option value=04>4</option>
                            <option value=05>5</option>
                            <option value=06>6</option>
                            <option value=07>7</option>
                            <option value=08>8</option>
                            <option value=09>9</option>
                            <option value=10>10</option>
                            <option value=11>11</option>
                            <option value=12>12</option>
                            <option value=13>13</option>
                            <option value=14>14</option>
                            <option value=15>15</option>
                            <option value=16>16</option>
                            <option value=17>17</option>
                            <option value=18>18</option>
                            <option value=19>19</option>
                            <option value=20>20</option>
                            <option value=21>21</option>
                            <option value=22>22</option>
                            <option value=23>23</option>
                            <option value=24>24</option>
                            <option value=25>25</option>
                            <option value=26>26</option>
                            <option value=27>27</option>
                            <option value=28>28</option>
                            <option value=29>29</option>
                            <option value=30>30</option>
                            <option value=31>31</option>
                            </select><select name="mob">
                            <option>- Month -</option>
                            <option value=01>January</option>
                            <option value=02>Febuary</option>
                            <option value=03>March</option>
                            <option value=04>April</option>
                            <option value=05>May</option>
                            <option value=06>June</option>
                            <option value=07>July</option>
                            <option value=08>August</option>
                            <option value=09>September</option>
                            <option value=10>October</option>
                            <option value=11>November</option>
                            <option value=12>December</option>
                        </select>
                        <select name="yob">
                            <option>- Year -</option>
                            <option value=2020>2020</option>
                            <option value=2019>2019</option>
                            <option value=2018>2018</option>
                            <option value=2017>2017</option>
                            <option value=2016>2016</option>
                            <option value=2015>2015</option>
                            <option value=2014>2014</option>
                            <option value=2013>2013</option>
                            <option value=2012>2012</option>
                            <option value=2011>2011</option>
                            <option value=2010>2010</option>
                            <option value=2009>2009</option>
                            <option value=2008>2008</option>
                            <option value=2007>2007</option>
                            <option value=2006>2006</option>
                            <option value=2005>2005</option>
                            <option value=2004>2004</option>
                            <option value=2003>2003</option>
                            <option value=2002>2002</option>
                            <option value=2001>2001</option>
                            <option value=2000>2000</option>
                            <option value=1999>1999</option>
                            <option value=1998>1998</option>
                            <option value=1997>1997</option>
                            <option value=1996>1996</option>
                            <option value=1995>1995</option>
                            <option value=1994>1994</option>
                            <option value=1993>1993</option>
                            <option value=1992>1992</option>
                            <option value=1991>1991</option>
                            <option value=1990>1990</option>
                            <option value=1989>1989</option>
                            <option value=1988>1988</option>
                            <option value=1987>1987</option>
                            <option value=1986>1986</option>
                            <option value=1985>1985</option>
                            <option value=1984>1984</option>
                            <option value=1983>1983</option>
                            <option value=1982>1982</option>
                            <option value=1981>1981</option>
                            <option value=1980>1980</option>
                            <option value=1979>1979</option>
                            <option value=1978>1978</option>
                            <option value=1977>1977</option>
                            <option value=1976>1976</option>
                            <option value=1975>1975</option>
                            <option value=1974>1974</option>
                            <option value=1973>1973</option>
                            <option value=1972>1972</option>
                            <option value=1971>1971</option>
                            <option value=1970>1970</option>
                            <option value=1969>1969</option>
                            <option value=1968>1968</option>
                            <option value=1967>1967</option>
                            <option value=1966>1966</option>
                            <option value=1965>1965</option>
                            <option value=1964>1964</option>
                            <option value=1963>1963</option>
                            <option value=1962>1962</option>
                            <option value=1961>1961</option>
                            <option value=1960>1960</option>
                            <option value=1959>1959</option>
                            <option value=1958>1958</option>
                            <option value=1957>1957</option>
                            <option value=1956>1956</option>
                            <option value=1955>1955</option>
                            <option value=1954>1954</option>
                            <option value=1953>1953</option>
                            <option value=1952>1952</option>
                            <option value=1951>1951</option>
                            <option value=1950>1950</option>
                            <option value=1949>1949</option>
                            <option value=1948>1948</option>
                            <option value=1947>1947</option>
                            <option value=1946>1946</option>
                            <option value=1945>1945</option>
                            <option value=1944>1944</option>
                            <option value=1943>1943</option>
                            <option value=1942>1942</option>
                            <option value=1941>1941</option>
                            <option value=1940>1940</option>
                            <option value=1939>1939</option>
                            <option value=1938>1938</option>
                            <option value=1937>1937</option>
                            <option value=1936>1936</option>
                            <option value=1935>1935</option>
                            <option value=1934>1934</option>
                            <option value=1933>1933</option>
                            <option value=1932>1932</option>
                            <option value=1931>1931</option>
                            <option value=1930>1930</option>
                        </select> <br/>
                        <label for='activitylev'>Activity Level</label><br>
                        <select name="activitylev" class='inputbox'>
                            <option value="sedentary" selected>Sedentary (little/no exercise)</option>
                            <option value="lightly_active">Lightly Active (sports 1-3 days a week)</option>
                            <option value="active"> Moderately Active (sports 3-5 days a week)</option>
                            <option value="very_active">Very Active (sports 6-7 days a week)</option>
                            <option value="extremely_active">Extremely Active (sports twice a day)</option>
                        </select>
                        
                        <label for='newemail'>Email</label><br>
                        <input name='newemail' id='newemail' type="email" class='inputbox'/><br/>
                        <label for='newusername'>Username</label><br>
                        <input name='newusername' id='newusername' class='inputbox'/><br/>
                        <label for='newpassword'>Password</label><br>
                        <input name='newpassword' id='newpassword' type='password' class='inputbox'/><br/>
                        <label for='confirm_password'>Confirm Password</label><br>
                        <input name='confirm_password' id='confirm_password' type='password' class='inputbox'/><br/>
                        <input type="hidden" name="action" value="signup">
                        <input type="submit" value="Sign Up">
                    </form>
                </div>
                
                <a id="loginButton" onClick="displayLoginForm(); return false;" href="login.php">Already Registered? Click here!</a>
                
                
                
                            
                    
             
                
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
