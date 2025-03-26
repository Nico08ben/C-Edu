<!DOCTYPE html>
<html lang="es">
<head>
    <?php include "../SIDEBAR/Docente/head.php" ?>
    <link rel="stylesheet" href="tareascss.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <title>TAREAS ASIGNADAS</title>
</head>
<body>
    <?php include "../SIDEBAR/Docente/sidebar.php" ?>

    <section class="home">
        <div class="main-content">
            <div class="header">
                <h1 id="titulo1-header">TAREAS ASIGNADAS</h1>
                <div class="profile">
                <i class="fa-regular fa-bell"></i>
                    <div class="profile-info">
                        <div><h3>Antonio</h3></div>
                        <div><p>Docente de Matemáticas</p></div>
                    </div>
                    <a href="../UserProfile/index.php"><i class="fa-solid fa-user"></i></a>
                </div>
            </div>
            
            <div class="Tareas">
                <div class="tabla-contendor">
                    <h2>Ejemplo de tabla de tareas</h2>
                    <table>
                        <thead>
                            <tr>
                                <th><i class="fa-solid fa-hammer"></i><label>  Tareas</label></th>
                                <th><i class="fa-solid fa-user-tie"></i><label>  Creador</label></th>
                                <th><i class="fa-solid fa-circle-info"></i><label>  Información</label></th>
                                <th><i class="fa-solid fa-sliders"></i><label>  Estado</label></th>
                                
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Planificar Calendario</td>
                                <td>Coordinador</td>
                                <td>Planificar el calendario para este nuevo año lectivo</td>
                                <td>Pendiente</td>
                                <td><button class="btn-datalles">Detalles</button></td>
                            </tr>
                        </tbody>
                        
                    </table>
                </div>
            </div>
        </div>
    </section>
        
</body>
</html>