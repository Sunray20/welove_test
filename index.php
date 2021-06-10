<?php
    require_once("projects.php");
    $projects = new Project();

    if(isset($_POST['search']) && !empty($_POST['search']))
    {
        //Státusz alapján szűrés
        $projects = $projects->getProjectsByStatus();
    }
    else
    {
        //Egyébként az összes elem listázása
        $projects = $projects->getList();
    }
?>

<!Doctype html>
<html lang="hu">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="Static/site.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-+0n0xVW2eSR5OomGNYDnhzAbDsOXxcvSN1TPprVMTNDbiYZCxYbOOl7+AMvyTG2x" crossorigin="anonymous">

    <title>Welove_Test | List</title>
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
                        <a class="nav-link active" aria-current="page" href="#">Projektlista</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="edit.php">Szerkesztés / Létrehozás</a>
                    </li>
                </ul>
                <form class="d-flex ms-auto me-3" action="<?= $_SERVER['PHP_SELF']; ?>" method="POST">
                    <input class="form-control me-2" name="search" type="search" placeholder="Search" aria-label="Search">
                    <button class="btn btn-success" type="submit">Search</button>
                </form>
            </div>
        </div>
    </nav>
    <!-- Content -->
    <div class="container mt-3">
        <?php foreach($projects as $project) :?>
            <div class="card" data-id="<?= $project['id'] ?>">
                <div class="card-body">
                    <div class="row">
                        <div class="col-10">
                            <h5 class="card-title"><?= $project['title'] ?></h5>
                        </div>
                        <div class="col-2 text-end">
                            <p><?= $project['statName'] ?></p>
                        </div>
                    </div>
                    
                    <p class="card-text mb-4"><?= $project['oName'] ?></p>
                    <form action="<?= $_SERVER['PHP_SELF']; ?>" method="GET">
                        <a href="edit.php?<?= $project['id'] ?>" name="edit" class="btn btn-primary">Szerkesztés</a>
                        <a href="#" data-id="<?= $project['id']  ?>" name="delete" class="btn btn-danger delete">Törlés</a>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <!-- End Content -->

    <script src="https://code.jquery.com/jquery-3.6.0.js"integrity="sha256-H+K7U5CnXl1h5ywQfKtSj8PCmoN9aaq30gDh27Xc0jk=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-gtEjrD/SeCtmISkJkNUaaKMoLD0//ElJ19smozuHV6z3Iehds+3Ulb9Bn9Plx0x4" crossorigin="anonymous"></script>
  </body>
</html>

<script>
    $(document).ready(function(){
        $('.delete').on('click', function(){
            let id = $(this).data("id");

            $.ajax({
                url: "HandleAjaxRequest.php",
                type: 'post',
                data : {
                    projectId: id,
                },
                success: function(){
                    let elementToHide = $('*[data-id='+id+']')[0];
                    $(elementToHide).hide();
                },
            });
        });
    });
</script>