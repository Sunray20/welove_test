<?php 
    if(isset($_POST['projectId']))
    {
        require_once("projects.php");
        $projects = new Project();

        return $projects->deleteProject($_POST['projectId']);
    }
?>