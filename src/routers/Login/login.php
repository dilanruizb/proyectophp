<?php
    require_once("");
    if(!empty($_SESSION['usuario'])){
  header("D:\XAMPP\htdocs\proyectophp\public\index.php");
    }
?>

<div class="header">
        <a href="/login/index.php" class="logoh"><img src="/login/fotos/logocircular.png" class="lgh">
        <h2 class="logo-nombre">EESTN°2 <br> Proyec tec</h2>
        </a>
        <nav class="hda">
            <a href="/login/infor.php">info</a>
            <a href="/login/view/contacto/contactanos.php">contacto</a>
        </nav>
    </div>
<br>
<div class="bodi">
<div class="contenedor1">
<div class="">
        <form action="verificar.php" method="POST"  class="" autocomplete="off">
            <h1>iniciar sesion</h1>
            <div class="">
                <label for="exampleInputEmail1" class="form-label"><h2>Correo<br>Electronico</h2></label>
                <input type="email" name="correo" class="email  " id="exampleInputEmail1" aria-describedby="emailHelp">
            </div>
            <div class="">
                <label for="exampleInputPassword1" class="form-label"><h2>Contraseña</h2></label>
                <div class="">
                    <button type="button" onclick="mostrarContraseña('password' , 'eyepassword')">
                        <i id="eyepassword" class="fa-solid fa-eye changePassword"></i>
                    </button>
                </div>
                <input type="password" name="contraseña" class="password" id="password">
            </div>
                <?php if(!empty($_GET['error'])):?>
                    <div id="alertError" style="margin: auto; " class="alert alert-danger mb-2" role="alert">
                        <?= !empty($_GET['error']) ? $_GET['error'] : ""?>
                    </div>
                <?php endif;?>
                <br>
            <div class="">
                <button type="submit" class="login">Entrar</button>
            </div>
        </form>
        <br>
        <div class="">
            <a href="signup.php" class="novo">Create una cuenta</a>
        </div>
</div>
</div>
</div>
<?php
    require_once("c://xampp/htdocs/login/view/head/footer.php");
?>