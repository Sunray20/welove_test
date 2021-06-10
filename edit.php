<?php
    require_once("projects.php");
    $project = new Project();
    
    $id = $_SERVER['QUERY_STRING'];

    //For status dropdown list
    $statuses = $project->getStatuses();

    //Get id from querystring if exists
    if(isset($id))
    {
        $proj = $project->getProjectById($id);
    }
    if(isset($_POST['submit']))
    {
        //If there is id then it's an update request
        if(isset($_POST['id']) && !empty($_POST['id']))
        {
            $errors = $project->update();
        }
        //else it's a new creation
        else
        {
            $errors = $project->create();
        }
    }
?>
<!doctype html>
<html lang="hu">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="Static/site.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-+0n0xVW2eSR5OomGNYDnhzAbDsOXxcvSN1TPprVMTNDbiYZCxYbOOl7+AMvyTG2x" crossorigin="anonymous">

    <title>Welove_Test | Edit</title>
  </head>

  <body>
    <nav class="navbar navbar-expand-lg navbar-dark" style="background-color: rgb(54, 57, 58);">
        <div class="container-fluid ms-3">
            <a class="navbar-brand" href="#">Welove Test</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" aria-current="page" href="index.php">Projektlista</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="#">Szerkesztés / Létrehozás</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Content -->
    <div class="container mt-3" >
        <form action="" method="POST">
            <input type="hidden" name="id" value="<?= $proj["id"] ?>">
            <div class="mb-3">
                <label for="title" class="form-label">Cím</label>
                <input type="text" class="form-control" name="title" value="<?= $proj["title"] ?>"/>
                <?php 
                    if(isset($errors['title']) == true)
                    {
                        echo '<p class="my-error">'.$errors['title'].'</p>';
                    }
                ?>
            </div>

            <div class="mb-3">
                <label for="desc" class="form-label">Leírás</label>
                <textarea name="desc" class="form-control" rows="4" cols="50"><?= $proj["description"] ?></textarea>
                <?php 
                    if(isset($errors['desc']) == true)
                    {
                        echo '<p class="my-error">'.$errors['desc'].'</p>';
                    }
                ?>
            </div>

            <div class="mb-3">
                <label for="status" class="form-label">Státusz</label> <br/>
                <select name="status">
                    <?php foreach($statuses as $status) : ?>
                        <option value="<?= $status['statName']?>" 
                            <?= ($status['statName'] == $proj['statName']) ? 'selected': '';?>>
                                <?= $status['statName']?>
                        </option>
                    <?php endforeach?>
                </select>
                <?php 
                    if(isset($errors['status']) == true)
                    {
                        echo '<p class="my-error">'.$errors['status'].'</p>';
                    }
                ?>
            </div>

            <div class="mb-3">
                <label for="productOwner" class="form-label">Kapcsolattartó neve</label>
                <input type="text" class="form-control" name="productOwner" value="<?= $proj["oName"] ?>"/>
                <?php 
                    if(isset($errors['productOwner']) == true)
                    {
                        echo '<p class="my-error">'.$errors['productOwner'].'</p>';
                    }
                ?>
            </div>

            <div class="mb-3">
                <label for="email" class="form-label">Kapcsolattartó e-mail címe</label>
                <input type="text" class="form-control" name="email" value="<?= $proj["email"] ?>"/>
                <?php 
                    if(isset($errors['email']) == true)
                    {
                        echo '<p class="my-error">'.$errors['email'].'</p>';
                    }
                ?>
            </div>

            <input type="submit" class="btn btn-primary" name="submit" value="Mentés" />
        </form>
    </div>
    <!-- End Content -->

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-gtEjrD/SeCtmISkJkNUaaKMoLD0//ElJ19smozuHV6z3Iehds+3Ulb9Bn9Plx0x4" crossorigin="anonymous"></script>
  </body>
</html>