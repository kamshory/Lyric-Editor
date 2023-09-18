<?php
use Pico\Data\Entity\User;
use Pico\Request\PicoRequest;

require_once "inc/auth-with-login-form.php";

$inputGet = new PicoRequest(INPUT_GET);
$inputPost = new PicoRequest(INPUT_POST);
if($inputGet->equalsAction(PicoRequest::ACTION_EDIT) && $inputPost->getSave() == 'save')
{
    $user = new User(null, $database);
    $user->setUserId($currentLoggedInUser->getUserId());
    $password = $inputPost->getPassword();
    if(!empty($password))
    {
        $user->setPassword(hash('sha256', $inputPost->getPassword()));
    }
    $user->setName($inputPost->getName());
    $user->setBirthDay($inputPost->getBirthDay());
    $user->setGender($inputPost->getGender());

    $user->update();
    header('Location: '.basename(($_SERVER['PHP_SELF'])));
}

if($inputGet->equalsAction(PicoRequest::ACTION_EDIT))
{
    require_once "inc/header.php";
    $user = new User(null, $database);
    try
    {
    $user->findOneByUserId($currentLoggedInUser->getUserId());
    ?>
    <form action="" method="post">
    <table class="table table-responsive">
    <tbody>
      <tr>
        <td>Name</td>
        <td><input type="text" class="form-control" name="name" id="name" value="<?php echo $user->getName();?>" autocomplete="off"></td>
      </tr>
      <tr>
        <td>Gender</td>
        <td><select class="form-control" name="gender" id="gender">
        <option value="M"<?php echo $user->getGender() == 'M' ? ' selected':'';?>>Man</option>
        <option value="F"<?php echo $user->getGender() == 'W' ? ' selected':'';?>>Woman</option>
        </select></td>
      </tr>
      <tr>
        <td>Birth Day</td>
        <td><input type="date" class="form-control" name="birth_day" id="birth_day" value="<?php echo $user->getBirthDay();?>" autocomplete="off"></td>
      </tr>
      <tr>
        <td>Email</td>
        <td><input type="email" class="form-control" name="email" id="email" value="<?php echo $user->getEmail();?>" autocomplete="off"></td>
      </tr>
      <tr>
        <td>Username</td>
        <td><input type="text" class="form-control" name="username" id="username" value="<?php echo $user->getUsername();?>" autocomplete="off"></td>
      </tr>
      <tr>
        <td>Password</td>
        <td><input type="password" class="form-control" name="password" id="password" value="" autocomplete="off"></td>
      </tr>
    </tbody>
  </table>
  <input type="hidden" name="save" value="save">
  <button type="submit" class="btn btn-success">Update</button>
  <button type="button" class="btn btn-primary" onclick="window.location='profile.php'">Cancel</button>
    </form>
    <?php
    }
    catch(Exception $e)
    {

    }
    require_once "inc/footer.php";
}
else
{
    require_once "inc/header.php";
    $user = new User(null, $database);
    try
    {
    $user->findOneByUserId($currentLoggedInUser->getUserId());
    ?>
    <table class="table table-responsive">
    <tbody>
    <tr>
        <td>User ID</td>
        <td><?php echo $user->getUserId();?></td>
      </tr>
      <tr>
        <td>Username</td>
        <td><?php echo $user->getUsername();?></td>
      </tr>
      <tr>
        <td>Name</td>
        <td><?php echo $user->getName();?></td>
      </tr>
      <tr>
        <td>Gender</td>
        <td><?php echo $user->getGender() == 'M' ? 'Man' : 'Woman';?></td>
      </tr>
      <tr>
        <td>Birth Day</td>
        <td><?php echo $user->getBirthDay();?></td>
      </tr>
      <tr>
        <td>Email</td>
        <td><?php echo $user->getEmail();?></td>
      </tr>
    </tbody>
  </table>
  <button type="button" class="btn btn-primary" onclick="window.location='profile.php?action=edit'">Edit</button>
    <?php
    }
    catch(Exception $e)
    {
        
    }
    require_once "inc/footer.php";
}