<html>
    <head>
        <?php include 'includes/head.php'?>
        <?php session_start(); ?>
    </head>
    <body>
        <div id="container">
            <form id="register" action="register.php" method="post">
                <h1> Register Here </h1>
                <table>
                    <tr>
                        <td>Name:</td>
                        <td><input type="text" name="name" value="<?php echo $_SESSION['name']?>"></td>
                    </tr>
                    <tr>
                        <td>Hometown:</td>
                        <td><input type="text" name="hometown" value="<?php echo $hometown?>"></td>
                    </tr>
                    <tr>
                        <td>Gender (M/F):</td>
                        <td><input type="text" name="gender"value="<?php echo $gender?>"></td>
                    </tr>
                    <tr>
                        <td>Phone Number: </td>
                        <td><input type="text" name="phone_number" value="<?php echo $phone_number?>"></td>
                    </tr>
                    <tr>
                        <td>Mom's Name: </td>
                        <td><input type="text" name="moms_name" value="<?php echo $moms_name?>"></td>
                    </tr>
                    <tr>
                        <td></td>
                        <td><input type="submit" name="submit" value="Submit!"></td>
                    </tr>
                    </table>
            </form>
        </div>
    </body>
</html>