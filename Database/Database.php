<?php

require("Email.php");

class Database
{
  protected $servername;
  protected $dbName;
  protected $username;
  protected $password;
  protected $conn;

  function __construct()
  {
      $this->servername = "localhost";
      $this->dbName = "welove_test";
      $this->username = "root";
      $this->password = "";
      $this->conn = "";
      $this->Connect();
  }

  public function Connect()
  {
      try
      {
         $this->conn = new PDO("mysql:host=$this->servername;dbname=$this->dbName;charset=UTF8MB4", $this->username, $this->password);
         $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      }
      catch(PDOException $e) {
        echo "Connection failed: " . $e->getMessage(); die();
      }
  }

  public function GetProjects()
  {
    $projects = $this->conn->prepare("SELECT projects.id AS id, title, owners.name AS oName, statuses.name AS statName
                           FROM projects
                           INNER JOIN project_owner_pivot ON projects.id = project_id
                           INNER JOIN owners ON owner_id = owners.id
                           INNER JOIN project_status_pivot AS status ON projects.id = status.project_id
                           INNER JOIN statuses ON status_id = statuses.id");
    $projects->execute();
    return $projects->fetchAll();
  }

  public function getProjectsByStatus($statName)
  {
    $projects = $this->conn->prepare("SELECT projects.id AS id, title, owners.name AS oName, statuses.name AS statName
                           FROM projects
                           INNER JOIN project_owner_pivot ON projects.id = project_id
                           INNER JOIN owners ON owner_id = owners.id
                           INNER JOIN project_status_pivot AS status ON projects.id = status.project_id
                           INNER JOIN statuses ON status_id = statuses.id
                           WHERE statuses.name = :statName");
    $projects->execute(array(':statName' => $statName));
    return $projects->fetchAll();
  }

  public function GetProjectById($id)
  {
    if(isset($id))
    {
      $projects = $this->conn->prepare("SELECT projects.id AS id, title, description, owners.name AS oName, email, statuses.name AS statName
                           FROM projects
                           INNER JOIN project_owner_pivot AS owner ON projects.id = owner.project_id
                           INNER JOIN owners ON owner_id = owners.id
                           INNER JOIN project_status_pivot AS status ON projects.id = status.project_id
                           INNER JOIN statuses ON status_id = statuses.id
                           WHERE projects.id = :pId");
      $projects->execute(array(':pId' => $id));
      return $projects->fetch();
    }
  }

  public function GetStatuses()
  {
    $statuses = $this->conn->prepare("SELECT statuses.name AS statName FROM statuses");
    $statuses->execute();
    return $statuses->fetchAll();
  }

  public function createProject($newProj)
  {
    try{
      $this->conn->beginTransaction();
      
      $project = $this->conn->prepare("INSERT INTO projects (title, description) VALUES (:title, :desc)");
      $project->bindParam(":title", $newProj['title']);
      $project->bindParam(":desc", $newProj['desc']);
      $project->execute();
      //Ez tárolja az új project record id-jét
      $lastInsertedProjectId = $this->conn->lastInsertId(); 
      //var_dump($lastInsertedProjectId); die();

      $owner = $this->getOwnerIfExists($newProj['email']);
      //Ha még nincs ilyen owner akkor létre is hozzuk
      if(empty($owner))
      {
        $owner = $this->conn->prepare("INSERT INTO owners (name, email) VALUES (:name, :email)");
        $owner->bindParam(':name', $newProj['productOwner']);
        $owner->bindParam(':email', $newProj['email']);
        $owner->execute();
        $lastInsertedOwnerId = $this->conn->lastInsertId();
      }
      else
      {
        //Ha már van ilyen akkor csak beállítjuk az id-jét a pivot table-ben
        $lastInsertedOwnerId = $owner['id'];
        //$owner = $this->conn->prepare("UPDATE owners SET name = :name WHERE id = :id");
        //$owner->execute(array(':name' => $newProj['productOwner'], ':id' => $lastInsertedOwnerId));
      }

      //Frissítjük a pivot tablet
      $projectOwnerPivot = $this->conn->prepare("INSERT INTO project_owner_pivot (project_id, owner_id) VALUES (:project_id, :owner_id)");
      $projectOwnerPivot->bindParam(':project_id', $lastInsertedProjectId);
      $projectOwnerPivot->bindParam(':owner_id', $lastInsertedOwnerId);
      $projectOwnerPivot->execute();

      //Frissítjük a pivot tablet
      $status = $this->getStatusByName($newProj['status']);
      
      $projectStatusPivot = $this->conn->prepare("INSERT INTO project_status_pivot (project_id, status_id) VALUES (:project_id, :status_id)");
      $projectStatusPivot->bindParam(':project_id', $lastInsertedProjectId);
      $projectStatusPivot->bindParam(':status_id', $status["id"]);
      $projectStatusPivot->execute();

      $this->conn->commit();
    }
    catch(Exception $e){
      echo $e->getMessage();
      //Rollback the transaction.
      $this->conn->rollBack();
    }
  }

  public function updateProject($updatedProj)
  {
    try{
      $this->conn->beginTransaction();

      $project = $this->conn->prepare("UPDATE projects SET title = :title, description = :desc WHERE id = :pId");
      $project->execute(array(':title' => $updatedProj['title'], ':desc' => $updatedProj['desc'], ':pId' => $updatedProj['id']));

      $owner = $this->getOwnerIfExists($updatedProj['email']);
      //Ha még nincs ilyen owner akkor létre is hozzuk
      if(empty($owner))
      {
        throw new Exception('Not a valid owner');
        //Egy feature lett volna, hogy lehessen updatelés közben is új ownert létrehozni
        //De utólag átgondolva felesleges, nem megszokott működési forma update közben..
        /*$owner = $this->conn->prepare("INSERT INTO owners (name, email) VALUES (:name, :email)");
        $owner->bindParam(':name', $updatedProj['productOwner']);
        $owner->bindParam(':email', $updatedProj['email']);
        $owner->execute();
        $lastInsertedOwnerId = $this->conn->lastInsertId();

        //Kitöröljük az eredeti ownert aki ehhez a projekthez tartozott a pivotból, majd egy újat adunk hozzá
        $projectOwnerPivot = $this->conn->prepare("DELETE FROM project_owner_pivot WHERE project_id = :pId");
        $projectOwnerPivot->execute(array(':pId' => $updatedProj['id']));

        $projectOwnerPivot = $this->conn->prepare("INSERT INTO project_owner_pivot (project_id, owner_id) VALUES (:project_id, :owner_id)");
        $projectOwnerPivot->bindParam(':project_id', $updatedProj['id']);
        $projectOwnerPivot->bindParam(':owner_id', $lastInsertedOwnerId);
        $projectOwnerPivot->execute();*/
      }
      else
      {
        $lastInsertedOwnerId = $owner['id'];
        $owner = $this->conn->prepare("UPDATE owners SET name = :name WHERE id = :id");
        $owner->execute(array(':name' => $updatedProj['productOwner'], ':id' => $lastInsertedOwnerId));

        //Ha van ilyen owner és project is, akkor csak updatelni kell a pivot table-t
        $projectOwnerPivot = $this->conn->prepare("UPDATE project_owner_pivot SET owner_id = :owner_id WHERE project_id = :project_id");
        $projectOwnerPivot->bindParam(':project_id', $updatedProj['id']);
        $projectOwnerPivot->bindParam(':owner_id', $lastInsertedOwnerId);
        $projectOwnerPivot->execute();
      }

      //Status pivot table update
      $status = $this->getStatusByName($updatedProj['status']);

      $projectStatusPivot = $this->conn->prepare("UPDATE project_status_pivot SET status_id = :status_id WHERE project_id = :project_id");
      $projectStatusPivot->bindParam(':project_id', $updatedProj['id']);
      $projectStatusPivot->bindParam(':status_id', $status["id"]);
      $projectStatusPivot->execute();

      $this->conn->commit();

      //Not finished.: It would require the SMTP server and port + authentication,
      //and getting the changes between $updatedProj and the original one
      //$email = new Email;
      //$txt = $this->getChangesInProjects($new, $old);
      //$email->sendMail($updatedProj['email'], "Update in a project", $txt);
    }
    catch(Exception $e){
      echo $e->getMessage();
      //Rollback the transaction.
      $this->conn->rollBack();
    }
  }

  public function getOwnerIfExists($email)
  {
    $owner = $this->conn->prepare("SELECT id FROM owners WHERE email = :email");
    $owner->execute(array(':email' => $email));
    return $owner->fetch();
  }

  public function getStatusByName($statusName)
  {
    $status = $this->conn->prepare("SELECT id FROM statuses WHERE statuses.name = :statusName");
    $status->execute(array(':statusName' => $statusName));
    return $status->fetch();
  }

  public function deleteProject($id)
  {
    try{
      $this->conn->beginTransaction();
      
      $projectOwnerPivot = $this->conn->prepare("DELETE FROM project_owner_pivot WHERE project_id = :pId");
      $projectOwnerPivot->execute(array(':pId' => $id));

      $projectStatusPivot = $this->conn->prepare("DELETE FROM project_status_pivot WHERE project_id = :pId");
      $projectStatusPivot->execute(array(':pId' => $id));

      $project = $this->conn->prepare("DELETE FROM projects WHERE id = :pId");
      $project->execute(array(':pId' => $id));

      $this->conn->commit();
      return true;
    }
    catch(Exception $e){
      echo $e->getMessage();
      //Rollback the transaction.
      $this->conn->rollBack();
    }
  }
}

?>