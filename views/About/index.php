<?php 
    inc_header(); 

    // Set title
    Title('About Page');
?>

<h5>About</h5>

<?php LoadPartial('hello'); ?>


<?php 

    if($all_users) {
        if(is_array($all_users)) {
            foreach($all_users as $user) {
                echo $user->Email . '</br>';
            }
        }
        else {
            echo $all_users->Email;
        }
    }
    else {
        echo 'No users';
    }

?>


<?php inc_footer(); ?>