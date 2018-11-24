<?php

    include '../../seguridad/carrito/dbconnect.php';

    /* 
        Archivo que sirve para comprobar el funcionamiento del FOR UPDATE
        para probarlo solo hay que comprar el producto 7 en el carrito, ejecutar este archivo,
        he intentar realizar el pedido en la otra pestaña, si funciona bien, no se realizara el procesamiento
        del carrito hasta que este script haya acabado.
    */
    $conexion = @mysqli_connect(IP,USER,PW,DB);
    if(!$conexion){ echo '<h1 style="color:red;text-align:center;">Ha ocurrido un error al conectarse a la DB</h1>'; exit; }
    mysqli_set_charset($conexion,'utf8');

    echo "Iniciando ....    ";
    mysqli_autocommit($conexion, FALSE);

    $sql = "select stock from productos where nombre='PRODUCTO7' for update";
    $query = mysqli_prepare($conexion,$sql);
    mysqli_stmt_execute($query);
    mysqli_stmt_bind_result($query,$stock);
    mysqli_stmt_fetch($query);
    mysqli_stmt_close($query);
    sleep(10);
    echo  "Listo!";

    mysqli_commit($conexion);
    mysqli_close($conexion);

?>