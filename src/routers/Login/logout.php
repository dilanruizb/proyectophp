<?php
//cierra la sesion iniciada 
    session_start();
    session_destroy();
    header("Location:panel_control.php");

?>