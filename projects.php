<?php
    include("Database/Database.php");

    class Project
    {
        private $db;

        function __construct()
        {
            $this->db = new Database();
        }

        function getList()
        {
            $result = $this->db->GetProjects();
            return $result;
        }
        //Search projects by status name
        function getProjectsByStatus()
        {
            $result = $this->db->getProjectsByStatus($_POST['search']);
            return $result;
        }

        function getProjectById($id)
        {
            $result = $this->db->GetProjectById($id);
            return $result;
        }

        //Get all available status for dropdown list
        function getStatuses()
        {
            $result = $this->db->GetStatuses();
            return $result;
        }

        //Updates an existing project
        //With this method we have $_POST['id']
        function update()
        {
            $errors = $this->validateInput();

            if($errors == false)
            {
                $this->db->updateProject($_POST);
                return true;
            }

            return $errors;
        }

        //Creates a new project or returns with validation errors
        function create()
        {
            $errors = $this->validateInput();
            //If there are no errors then create a new project
            if($errors == false)
            {
                $this->db->createProject($_POST);
                return true;
            }

            return $errors;
        }

        function deleteProject($id)
        {
            return $this->db->deleteProject($id);;
        }

        //Returns with the error messages or false (no error)
        function validateInput()
        {
            //input fields
            $title = $_POST['title'];
            $desc = $_POST['desc'];
            $status = $_POST['status'];
            $productOwner = $_POST['productOwner'];
            $email = $_POST['email'];

            //Validation for value that is less than 150 chars
            //Because of table constraints
            if(empty($title) || strlen($title) > 150)
            {
                $errors['title'] = "Hibás cím!";
            }
            if(empty($desc))
            {
                $errors['desc'] = "A leírás megadása kötelező!";
            }
            if(empty($status))
            {
                $errors['status'] = "A státusz megadása kötelező!";
            }
            if(empty($productOwner) || strlen($productOwner) > 150)
            {
                $errors['productOwner'] = "Hibás kapcsolattartó adat!";
            }
            if(empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 150)
            {
                $errors['email'] = "Hibás email cím!";
            }

            //If there are errors, return them
            if(isset($errors) && !empty($errors))
            {
                return $errors;
            }
            //Else return false for "no error"
            return false;
        }
    }
?>